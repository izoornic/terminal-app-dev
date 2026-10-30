<?php

namespace App\Http\Livewire\TerminalAcq;

use App\Actions\TerminalAcq\AktivniTidUredjaja;
use Livewire\Attributes\Locked;

/**
 * Za stranice koje premeštaju terminale (Terminali, Licence, Lokacije, Distributer):
 * uređaj pod aktivnim ACQ TID-om se ne premešta odatle, nego kroz "ACQ terminali"
 * (deinstalacija, prekonfiguracija). Greška se prikazuje u postojećem modalu
 * (licencaError = 'acq', modalErorLicencaVisible), tekst je u
 * livewire/terminal-acq/zabrana-premestanja.blade.php.
 */
trait ZabranaPremestanjaAcq
{
    /**
     * Aktivni ACQ TID-ovi izabranih uređaja (za poruku).
     *
     * @var list<string>
     */
    #[Locked]
    public array $acqTidovi = [];

    /**
     * @param  array<int, int|string>  $terminalLokacijaIds  id-jevi terminal_lokacijas redova
     * @return bool true kad je bar jedan uređaj pod aktivnim TID-om (greška je prikazana)
     */
    protected function zabranaPremestanjaAcq(array $terminalLokacijaIds): bool
    {
        $this->acqTidovi = AktivniTidUredjaja::zaTerminalLokacije($terminalLokacijaIds);
        if ($this->acqTidovi === []) {
            return false;
        }

        $this->licencaError = 'acq';
        $this->modalErorLicencaVisible = true;

        return true;
    }
}
