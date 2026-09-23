<?php

namespace Tests;

use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

/**
 * Mail transport koji pada (kao nedostupan SMTP) za zadate adrese, a ostale "isporučuje"
 * u $transport->isporuceno. Za razliku od Mail::fake(), ide kroz pravi Mailer i Mailable.
 */
trait NedostupanSmtp
{
    /**
     * @var AbstractTransport&object{isporuceno: list<string>}
     */
    private AbstractTransport $transport;

    /**
     * @param  list<string>  $nedostupneAdrese
     */
    private function smtpNedostupanZa(array $nedostupneAdrese): void
    {
        $this->transport = new class($nedostupneAdrese) extends AbstractTransport
        {
            /**
             * @var list<string>
             */
            public array $isporuceno = [];

            /**
             * @param  list<string>  $nedostupneAdrese
             */
            public function __construct(private array $nedostupneAdrese)
            {
                parent::__construct();
            }

            protected function doSend(SentMessage $message): void
            {
                foreach ($message->getEnvelope()->getRecipients() as $primalac) {
                    if (in_array($primalac->getAddress(), $this->nedostupneAdrese, true)) {
                        throw new TransportException('Connection could not be established with host "smtp.test:587"');
                    }

                    $this->isporuceno[] = $primalac->getAddress();
                }
            }

            public function __toString(): string
            {
                return 'test-smtp';
            }
        };

        Mail::extend('test-smtp', fn () => $this->transport);
        config([
            'mail.default' => 'test-smtp',
            'mail.mailers.test-smtp' => ['transport' => 'test-smtp'],
        ]);
    }
}
