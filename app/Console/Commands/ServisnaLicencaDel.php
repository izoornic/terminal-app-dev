<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LicenceZaTerminal;

class ServisnaLicencaDel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'servisnalicenca:del';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Brise servisne licence koje su istekle';

    /**
     * Create a new command instance.
     * Local rucno pokrenuti
     * php artisan  servisnalicenca:del
     * Cron na servery
     * /usr/local/bin/php /home/epos-servis-app/artisan servisnalicenca:del
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * 
     * @return int
     */
    public function handle()
    {
        if(LicenceZaTerminal::deleteExpiredServiceLicences()){
            $this->info('Servisne licence koje su istekle su obrisane!');
        }else{
            $this->info('Nema servisnih licenci koje su istekle!');
        }
    }
}