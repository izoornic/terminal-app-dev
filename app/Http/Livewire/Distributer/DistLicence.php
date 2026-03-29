<?php

namespace App\Http\Livewire\Distributer;

use App\Models\KursEvra;
use App\Models\LicencaNaplata;
use App\Models\TerminalLokacija;
use App\Models\LicencaParametar;
use App\Models\LicenceZaTerminal;
use App\Models\DistributerUserIndex;
use App\Models\LicencaDistributerTip;
use App\Models\LicencaDistributerCena;
use App\Models\LicencaParametarTerminal;

use App\Http\Helpers;

use App\Actions\Licence\CryptoSign;
use App\Actions\Licence\CenaLicence;
use App\Actions\Terminali\SelectedTerminalInfo;

use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Query\Builder;

use App\Exports\LicencaNaplataExport;
use Maatwebsite\Excel\Facades\Excel;

use App\Actions\Terminali\TerminaliReadActions;

class DistLicence extends Component
{
    use WithPagination;

    //set on MOUNT
    public $distId;
    public $modelId;
    public $ditributer_info;
    public $br_terminala;

    //SEARCH MAIN
    public $searchTerminalSn;
    public $searchMesto;
    public $searchTipLicence;
    public $searchPib;

    //DODAJ LICENCU MODAL
    public $dodajLicencuModalVisible;
    public $licence_dodate_terminalu = []; 
    //public $distrib_terminal_id;
    public $licence_za_dodavanje = [];
    public $dani_trajanja;
    public $datum_kraja_licence;
    public $datum_pocetka_licence;
    public $datum_pocetak_error;

    //PREGLED LICENCE MODAL
    public $pregledLicencaShowModal;
    public $licenca_naplata_id;
    public $podaci_licence;
    public $naplata_podaci_licence;
    public $razduzi_iznos;
    public $razduzi_datum;
    public $razduzi_iznos_error;
    public $licenca_ima_parametre;
    public $licenca_moze_da_se_brise;
    public $mnth_diff;
    public $lic_nenaplativa;

    //BRISANJE LICENCE
    public $modalConfirmDeleteVisible;
    public $licencaDeleteInfo;
    public $canDelete;

    //PARAMETRI LICENCE MODAL
    public $parametri;
    public $parametriModalVisible;
    public $pm_licenca_tip_id;
    //niz sa globalno dodeljenim parametrima za licencu
    public $licenca_tip_parametri;
    public $pm_licenca_naziv;
    public $licenca_parametri_ids;

    //cene licenci Zeta i Distributer
    public $cene_licenci;
    public $unete_cene_licenci;
    public $unete_cene_error;
    public $kurs_evra;

    //PRODUZENJE LICENCE
    public $produziLicModalVisible;
    public $produzenje_cene;
    public $licenca_distributer_cena_id;
    public $produzenje_unete_cene_error;
    public $produzenje_cena_licence;
    public $naziv_licence;
    public $produzenje_tip_licence;

    //ERROR MODAL
    public $errorModalVisible;
    public $error_message;

    //komentari na terminalima
    public $komentariTerminalVisible;
    public $modalKomentariVisible;

    public $selectedTerminals = [];
    public $selectAll;
    public $allInPage = [];
    public $produziCheckedMode = false;
    


    public function mount()
    {
        $this->distId = DistributerUserIndex::select('licenca_distributer_tipsId')->where('userId', '=', auth()->user()->id)->first()->licenca_distributer_tipsId;
        $this->ditributer_info = LicencaDistributerTip::where('id', '=', $this->distId)->first();
        $this->br_terminala = TerminalLokacija::select()->where('distributerId', '=', $this->distId)->count();
        foreach(LicencaDistributerCena::idLicenciDistributera($this->distId) as $key){
            $this->unete_cene_licenci[$key] = 0;
            $this->unete_cene_error[$key] = '';
        }
        $this->komentariTerminalVisible = auth()->user()->vidi_komentare_na_terminalu ?: 0;
        $this->kurs_evra = KursEvra::latest()->first();
        $this->produzenje_cene = [];
    }

    /**
     * The validation rules
     *
     * @return array
     */
    public function rules()
    {
        return [
            'datum_pocetka_licence' => ['required', 'date_format:"Y-m-d"'],
            'datum_kraja_licence' => ['required', 'date_format:"Y-m-d"']       
        ];
    }

    /**
     * [Description for pregledLicenceShovModal]
     *
     * @param mixed $tre_loc_id - TerminalLokacija ID
     * 
     * @return [type]
     * 
     */
    public function pregledLicenceShovModal($tre_loc_id, $lnid, $mnth_diff)
    {
        
        $this->resetTerm();
        $this->licenca_naplata_id = $lnid;
        
        $this->modelId = $tre_loc_id;

        $this->podaci_licence = $this->licencaInfo();
        
        $this->naplata_podaci_licence = LicencaNaplata::where('id', '=', $this->licenca_naplata_id)->first();
        $this->licenca_parametri_ids = [
            'terminal_lokacijaId' => $this->naplata_podaci_licence->terminal_lokacijaId,
            'distributerId' => $this->naplata_podaci_licence->distributerId,
            'licenca_distributer_cenaId' => $this->naplata_podaci_licence->licenca_distributer_cenaId
        ];
        $this->razduzi_datum = Helpers::datumKalendarNow();
        $this->licenca_ima_parametre = $this->licencaImaParametre($this->podaci_licence->licenca_tipId);
        $this->licenca_moze_da_se_brise = $this->canDeleteLcence();
        $this->mnth_diff = $mnth_diff;
        $this->lic_nenaplativa = $this->naplata_podaci_licence->nenaplativ;
        $this->pregledLicencaShowModal = true;
    }

    public function produziLicencuShovModal($tre_loc_id, $lnid)
    {
        $this->pregledLicencaShowModal = false;

        $this->resetTerm();
        $this->licenca_naplata_id = $lnid;
        $this->modelId = $tre_loc_id;
        $this->podaci_licence = $this->licencaInfo();
        $this->naziv_licence = $this->podaci_licence->licenca_naziv;
        $this->produzenje_tip_licence = $this->podaci_licence->licenca_tipId;
        $this->licenca_distributer_cena_id = $this->podaci_licence->ldcid;
        $this->naplata_podaci_licence = LicencaNaplata::where('id', '=', $this->licenca_naplata_id)->first();
        $this->datum_pocetka_licence = $this->naplata_podaci_licence->datum_kraj_licence;
        $this->datum_kraja_licence = Helpers::firstDayOfMounth(Helpers::addMonthsToDate($this->datum_pocetka_licence, 1));
        $this->produzenje_cene[0] = new CenaLicence($this->licenca_distributer_cena_id, $this->datum_pocetka_licence, $this->datum_kraja_licence);
        
        //PARAMETI ZA IZABRANU LICENCU
        $this->parametri = LicencaParametarTerminal::where('terminal_lokacijaId', '=', $this->naplata_podaci_licence->terminal_lokacijaId)
                                                    ->where('distributerId', '=', $this->naplata_podaci_licence->distributerId)
                                                    ->where('licenca_distributer_cenaId', '=', $this->naplata_podaci_licence->licenca_distributer_cenaId)  
                                                    ->pluck('licenca_parametarId')->all();
        //GLOBALNO DODELJENI parametri za tip licence
        $this->licenca_tip_parametri = LicencaParametar::where('licenca_tipId', '=', $this->produzenje_tip_licence)->pluck('id')->all();
        
        $this->produziLicModalVisible = true;
    }

    public function produziLicenceChecked()
    {
        if (empty($this->selectedTerminals)) return;

        $neispravni = [];
        foreach ($this->selectedTerminals as $lnid) {
            $naplata = LicencaNaplata::where('id', $lnid)
                ->where('distributerId', $this->distId)
                ->where('aktivna', 1)
                ->whereNotNull('razduzeno')
                ->where('razduzeno', '>', 0)
                ->whereRaw('LOWER(licenca_naziv) LIKE ?', ['%esir%'])
                ->first();

            if (!$naplata) {
                $neispravni[] = $lnid;
            }
        }

        if (count($neispravni)) {
            $this->error_message = 'Greška! Nije moguće produžiti licence. Svi selektovani terminali moraju imati aktivnu ESIR licencu sa izvršenim razduženjima (ID licenci: ' . implode(', ', $neispravni) . ').';
            $this->errorModalVisible = true;
            return;
        }

        $prvaNaplata = LicencaNaplata::find($this->selectedTerminals[0]);

        $this->resetTerm();
        $this->naziv_licence = $prvaNaplata->licenca_naziv;
        $this->licenca_distributer_cena_id = $prvaNaplata->licenca_distributer_cenaId;
        $this->datum_pocetka_licence = $prvaNaplata->datum_kraj_licence;
        $this->datum_kraja_licence = Helpers::firstDayOfMounth(Helpers::addMonthsToDate($this->datum_pocetka_licence, 1));
        $this->produzenje_cene[0] = new CenaLicence($this->licenca_distributer_cena_id, $this->datum_pocetka_licence, $this->datum_kraja_licence);

        $licenceInf = LicencaDistributerCena::licencaCenaIdInfo($this->licenca_distributer_cena_id);
        $this->produzenje_tip_licence = $licenceInf->licenca_tipId;

        $this->parametri = LicencaParametarTerminal::where('terminal_lokacijaId', $prvaNaplata->terminal_lokacijaId)
            ->where('distributerId', $prvaNaplata->distributerId)
            ->where('licenca_distributer_cenaId', $prvaNaplata->licenca_distributer_cenaId)
            ->pluck('licenca_parametarId')->all();
        $this->licenca_tip_parametri = LicencaParametar::where('licenca_tipId', '=', $this->produzenje_tip_licence)->pluck('id')->all();

        $this->produziCheckedMode = true;
        $this->produziLicModalVisible = true;
    }

    public function produziLicencu()
    {
        $this->updated(0, 0);
        $this->produzenje_cena_licence = preg_replace('/[,]/', '.', $this->produzenje_cena_licence);
        if($this->produzenje_cena_licence <= 0 || !is_numeric($this->produzenje_cena_licence)){
            $this->produzenje_unete_cene_error = 'Greška! Cena licence mora biti broj veći od 0!';
            return;
        }
        $this->produzenje_unete_cene_error = '';

        if ($this->produziCheckedMode) {
            foreach ($this->selectedTerminals as $lnid) {
                $naplata = LicencaNaplata::where('id', $lnid)
                    ->where('distributerId', $this->distId)
                    ->where('aktivna', 1)
                    ->whereNotNull('razduzeno')
                    ->where('razduzeno', '>', 0)
                    ->whereRaw('LOWER(licenca_naziv) LIKE ?', ['%esir%'])
                    ->first();

                if (!$naplata) continue;

                $datum_pocetka = $naplata->datum_kraj_licence;
                $datum_kraja = Helpers::firstDayOfMounth(Helpers::addMonthsToDate($datum_pocetka, 1));

                $parametri = LicencaParametarTerminal::where('terminal_lokacijaId', $naplata->terminal_lokacijaId)
                    ->where('distributerId', $naplata->distributerId)
                    ->where('licenca_distributer_cenaId', $naplata->licenca_distributer_cenaId)
                    ->pluck('licenca_parametarId')->all();

                $this->izvediProduzenjeLicence(
                    $naplata->id,
                    $naplata->terminal_lokacijaId,
                    $naplata->licenca_distributer_cenaId,
                    $datum_pocetka,
                    $datum_kraja,
                    $this->produzenje_cena_licence,
                    $parametri,
                    $naplata->licenca_naziv
                );
            }
            $this->selectedTerminals = [];
            $this->produziCheckedMode = false;
        } else {
            $this->izvediProduzenjeLicence(
                $this->licenca_naplata_id,
                $this->modelId,
                $this->licenca_distributer_cena_id,
                $this->datum_pocetka_licence,
                $this->datum_kraja_licence,
                $this->produzenje_cena_licence,
                $this->parametri,
                $this->naziv_licence
            );
        }

        $this->produziLicModalVisible = false;
    }

    private function izvediProduzenjeLicence($naplata_id, $tre_loc_id, $licenca_distributer_cena_id, $datum_pocetka, $datum_kraja, $cena, $parametri, $naziv_licence)
    {
        if (Helpers::numberOfDaysBettwen($datum_pocetka, $datum_kraja) < 1) return;

        $licenceInf = LicencaDistributerCena::licencaCenaIdInfo($licenca_distributer_cena_id);
        $licenca_tip_id = $licenceInf->id;

        $key_arr = [
            'terminal_lokacijaId' => $tre_loc_id,
            'distributerId' => $this->distId,
            'licenca_distributer_cenaId' => $licenca_distributer_cena_id,
        ];

        $terminal_info = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($tre_loc_id);
        $kraj_licence_za_api = Helpers::firstDayOfMounth(Helpers::addMonthsToDate($datum_pocetka, 1));

        $vals_ins = [
            'mesecId'=> 0,
            'terminal_sn' => $terminal_info->sn,
            'datum_pocetak' => $datum_pocetka,
            'datum_kraj' => $kraj_licence_za_api,
            'datum_prekoracenja' => Helpers::addDaysToDate($kraj_licence_za_api, $this->ditributer_info->dani_prekoracenja_licence),
            'naziv_licence' => $naziv_licence
        ];
        $this->AddToLicenceZaTerminal($key_arr, $vals_ins);

        //oslobodi stari zapis u tabelu 'licenca_naplatas'
        LicencaNaplata::where('id', '=', $naplata_id)->update(['aktivna' => 0]);

        //dodaj red u tabelu 'licenca_naplatas'
        $datum_prekoracenja = Helpers::addDaysToDate($datum_kraja, $this->ditributer_info->dani_prekoracenja_licence);
        $model_lic_nap = [
            'datum_pocetka_licence' => $datum_pocetka,
            'datum_kraj_licence'  => $datum_kraja,
            'datum_isteka_prekoracenja' => $datum_prekoracenja,
            'dist_zaduzeno' => $cena,
            'dist_datum_zaduzenja' => Helpers::datumKalendarNow(),
            'licenca_naziv' => $naziv_licence,
            'terminal_sn' => $terminal_info->sn,
        ];
        foreach ($key_arr as $key => $val) {
            $model_lic_nap[$key] = $val;
        }
        LicencaNaplata::create($model_lic_nap);

        if (count($parametri)) LicencaParametarTerminal::addParametarsToLicence($key_arr, $licenca_tip_id, $parametri);
    }

    public function dodajIzPregledaLicenceShovModal($tre_loc_id)
    {
        $this->pregledLicencaShowModal = false;
        $this->dodajLicencaShowModal($tre_loc_id);
    }

    public function parametriIzPregledaLicenceShovModal($lnid)
    {
        $this->pregledLicencaShowModal = false;
        $this->parametriLicenceShowModal($lnid);
    }

    public function deleteIzPregledaLicencuShowModal($tre_loc_id, $ldtidd)
    {
        $this->pregledLicencaShowModal = false;
        $this->deleteLicencuShowModal($tre_loc_id, $ldtidd);
    }
    /**
     * Koliko terminala ima distributer function.
     *
     * @return integer
     */
    public function prebrojLicenceDistributera()
    {
        return LicencaNaplata::select()->where('distributerId', '=', $this->distId)->where('aktivna','=', 1)->count();
    }

        /**
     * Shows dodaj Licenca modal.
     *
     * @return void
     */
    public function dodajLicencaShowModal($tre_loc_id)
    {
        if(!$this->resetTerm()){
            $this->error_message = 'Greška! Nije moguće dodati licencu, jer distributer nema dodeljenih licenci!';
            $this->errorModalVisible = true;
            return;
        };
        //$this->distrib_terminal_id = $ldtidd;
        $this->modelId = $tre_loc_id;
        $this->licence_dodate_terminalu = $this->licenceDodateTerminalu();
        //if ($this->licence_dodate_terminalu[0] == null) $this->licence_dodate_terminalu = [];
        $this->dodajLicencuModalVisible = true;
    }

    private function licenceDodateTerminalu()
    {
        return LicencaNaplata::where('terminal_lokacijaId', '=', $this->modelId)->where('aktivna', '=', 1)
                    ->pluck('licenca_distributer_cenaId')->all();
    }

    /**
     * Dodaj licence function.
     *
     * @return void
     */
    public function dodajLicenceTerminalu()
    {
        $this->validate();
        $this->updated(0, 0);

        if(Helpers::numberOfDaysBettwen(Helpers::datumKalendarNow(), $this->datum_pocetka_licence) > 10){
            $this->datum_pocetak_error = 'Greška! Datum počteka licence ne može biti više od 10 dana od današnje dana';
            return;
        }
        $this->datum_pocetak_error = '';
        //provera da li je uneo cenu za svaku licencu
        foreach($this->unete_cene_licenci as $key => $value){
            if(in_array($key, $this->licence_za_dodavanje)){
                //dd($value, floatval($value));
                $value = preg_replace('/[,]/', '.', $value);
                if($value <= 0 || !is_numeric($value)){
                    $this->unete_cene_error[$key] = 'Greška! Cena licence mora biti broj veći od 0!';
                    return;
                }
                $this->unete_cene_error[$key] = '';
                $this->unete_cene_licenci[$key] = floatval($value);
            }
        } 

        $this->dani_trajanja = Helpers::numberOfDaysBettwen($this->datum_pocetka_licence, $this->datum_kraja_licence);
        if($this->dani_trajanja < 1) return;

        $terminal_info = SelectedTerminalInfo::selectedTerminalInfoTerminalLokacijaId($this->modelId);

        if(count($this->licence_za_dodavanje)){
            //BRISANJE SERVISNIH LICENCI AKO IH IMA
            $this->deleteAllServiceLicences($this->modelId);

            $model_data = [
                'distributerId' => $this->distId,
                'terminal_lokacijaId' => $this->modelId,
                'datum_pocetak' => $this->datum_pocetka_licence,
                'datum_kraj' => $this->datum_kraja_licence,
                'licenca_broj_dana' => $this->dani_trajanja
            ];
            
            foreach($this->licence_za_dodavanje as $lc){
                //$licenca_tip_id = LicencaDistributerCena::where('id', '=', $lc)->first()->licenca_tipId;
                //dd($licenca_tip_id, $this->parametri);

                $model_data['licenca_distributer_cenaId'] = $lc;
                $licenceInf = LicencaDistributerCena::licencaCenaIdInfo($lc);
                $nazivLicence = $licenceInf->licenca_naziv;
                $licenca_tip_id = $licenceInf->id;
               
                $datum_prekoracenja = Helpers::addDaysToDate($this->datum_kraja_licence, $this->ditributer_info->dani_prekoracenja_licence);
                $datum_prekoracenja = Helpers::moveWikendToMonday($datum_prekoracenja);
                //niz samo za API tabelu
                $kraj_licence_za_api = Helpers::firstDayOfMounth(Helpers::addMonthsToDate($this->datum_pocetka_licence, 1));
                $datum_prekoracenja_privremene = Helpers::addDaysToDate($kraj_licence_za_api, $this->ditributer_info->dani_prekoracenja_licence);
                $datum_prekoracenja_privremene = Helpers::moveWikendToMonday($datum_prekoracenja_privremene);

                //dodaj licence terminalu za prezimanje
                $key_arr = [
                    'terminal_lokacijaId' => $this->modelId,
                    'distributerId' => $this->distId,
                    'licenca_distributer_cenaId' => $lc,
                ];
                
                
                $vals_ins = [
                    'mesecId'=> 0,
                    'terminal_sn' => $terminal_info->sn,
                    'datum_pocetak' => $this->datum_pocetka_licence,
                    'datum_kraj' =>  $kraj_licence_za_api,
                    'datum_prekoracenja' => $datum_prekoracenja_privremene,
                    'naziv_licence' => $nazivLicence
                ];

                //dodaj licence terminalu za prezimanje
                $this->AddToLicenceZaTerminal($key_arr, $vals_ins); 
               
                //dodaj red u tabelu 'licenca_naplatas'
                $model_lic_nap = [
                'datum_pocetka_licence' => $this->datum_pocetka_licence,
                'datum_kraj_licence'  => $this->datum_kraja_licence,
                'datum_isteka_prekoracenja' => $datum_prekoracenja,
                'dist_zaduzeno' => $this->unete_cene_licenci[$lc],
                'dist_datum_zaduzenja' => Helpers::datumKalendarNow(),
                'licenca_naziv' => $nazivLicence,
                'terminal_sn' => $terminal_info->sn,
                ];

                foreach($key_arr as $key=>$val){
                    $model_lic_nap[$key] = $val;
                }

                 //proveri da li je licenca nova ili je postojala u proslosti pa obrisana
                $licenca_postoji = LicencaNaplata::where($key_arr)
                    ->first();
                if(!$licenca_postoji){
                    //ako ne postoji, obelezi novu licencu
                    $model_lic_nap['nova_licenca'] = 1; //nova licenca

                }
                
                LicencaNaplata::create($model_lic_nap);
                if(count($this->parametri)) LicencaParametarTerminal::addParametarsToLicence($key_arr, $licenca_tip_id, $this->parametri);
                
            }
        }
        $this->resetTerm();
        $this->dodajLicencuModalVisible = false;
        $this->resetPage();    
    }

    private function deleteAllServiceLicences($terminal_lokacija_id)
    {
        //BRISANJE SERVISNIH LICENCI AKO IH IMA
        $servisne_licence = LicenceZaTerminal::where(['terminal_lokacijaId'=>$terminal_lokacija_id, 'licenca_poreklo' => 2])->get();
        $servisne_licence->each(function($item){
            //da li ima parametre - obrisi ih
            LicencaParametarTerminal::deleteParametars([
                'terminal_lokacijaId' => $item->terminal_lokacijaId,
                'distributerId' => $item->distributerId,
                'licenca_distributer_cenaId' => $item->licenca_distributer_cenaId
            ]);
            $item->delete();
        });
    }

    /**
     * The reset form for new teminal
     *
     * @return bool
     */
    public function resetTerm():bool
    {
        if(!$this->unete_cene_error || !$this->unete_cene_licenci) return false;
        $this->datum_pocetka_licence = Helpers::datumKalendarNow();
        $this->datum_kraja_licence = Helpers::firstDayOfMounth(Helpers::addMonthsToDate($this->datum_pocetka_licence, 1));
        $this->licence_za_dodavanje = [];
        //$this->distrib_terminal_id = 0;
        $this->dani_trajanja = Helpers::numberOfDaysBettwen($this->datum_pocetka_licence, $this->datum_kraja_licence);
        $this->parametri = [];
        foreach($this->unete_cene_error as $key => $value){
            $this->unete_cene_error[$key] = '';
        }
        foreach($this->unete_cene_licenci as $key => $value){
            $this->unete_cene_licenci[$key] = 0;
        }
        $this->razduzi_iznos = 0;
        $this->razduzi_iznos_error = '';
        $this->mnth_diff = 1;
        $this->produzenje_cene = [];
        $this->produzenje_unete_cene_error = '';
        $this->produzenje_cena_licence = 0;
        $this->naziv_licence = '';
        $this->produzenje_tip_licence = 0;
        $this->datum_pocetak_error = '';
        $this->lic_nenaplativa = 0;
        $this->licenca_parametri_ids = [];

        return true;
    }
    
    

    /**
     * update or create zapis u tabeli licenca_terminas 
     *
     * @param mixed $key_arr
     * @param mixed $vals_ins
     * 
     * @return [type]
     * 
     */
    private function AddToLicenceZaTerminal($key_arr, $vals_ins)
    {
        $signature_cripted =  CryptoSign::criptSignature($vals_ins);
        $vals_ins['signature'] = $signature_cripted;
        $vals_ins['licenca_poreklo'] = 3;

        LicenceZaTerminal::updateOrCreate( $key_arr, $vals_ins );
    }

    private function licencaImaParametre($licenca_tip_id): bool
    {
        return (LicencaParametar::where('licenca_tipId', '=', $licenca_tip_id)->first()) ? true : false;
    }


    /**
     * Modal za brisanje licence
     *
     * @param mixed $tre_loc_id
     * @param mixed $ldtidd
     * 
     * @return [type]
     * 
     */
    public function deleteLicencuShowModal($tre_loc_id, $lnid)
    {
        $this->licence_dodate_terminalu = $this->licenceDodateTerminalu();
        $this->modelId = $tre_loc_id;
        //$this->distrib_terminal_id = $ldtidd;
        $this->licenca_naplata_id = $lnid;
        $this->licencaDeleteInfo = $this->licencaInfo();
        $this->canDelete = $this->canDeleteLcence();
        $this->modalConfirmDeleteVisible = true;
    }

    public function delteLicenca()
    {
        DB::transaction(function(){
            $naplata_row = LicencaNaplata::where('id', '=', $this->licenca_naplata_id)->first();
            
            LicencaParametarTerminal::where('terminal_lokacijaId', '=', $naplata_row->terminal_lokacijaId)
                                        ->where('distributerId', '=', $naplata_row->distributerId)
                                        ->where('licenca_distributer_cenaId', '=', $naplata_row->licenca_distributer_cenaId)
                                        ->delete();
            LicenceZaTerminal::where('terminal_lokacijaId', '=', $naplata_row->terminal_lokacijaId)
                        ->where('distributerId', '=', $naplata_row->distributerId)
                        ->where('licenca_distributer_cenaId', '=', $naplata_row->licenca_distributer_cenaId)
                        ->delete();
            //Licenca koja je regulare i istekla je
           
            if(isset($naplata_row->razduzeno)) LicencaNaplata::where('id', '=', $naplata_row->id)->update(['aktivna' => 0]);

            //Licenca koja nije zaduzena od strane Zete
            if(!isset($naplata_row->zaduzeno)) LicencaNaplata::where('id', '=', $naplata_row->id)->delete();
            
        });
        $this->modalConfirmDeleteVisible = false;
        $this->resetPage();
    }

    private function canDeleteLcence()
    {
        $licence_details = LicencaNaplata::select('*')
            ->where('id', '=', $this->licenca_naplata_id)
            ->orderBy('id', 'desc')
            ->first();
        $expire = Helpers::monthDifference($licence_details->datum_kraj_licence);
        //ako Zeta nije zaduzila i distributer nije naplatio od korisnika moze da brise
        if(!isset($licence_details->zaduzeno) && !isset($licence_details->dist_razduzeno)) return true;
        //ako je Zeta razduzila distributera i licenca je istekla moze da brise
        if(isset($licence_details->razduzeno) && $expire < 1) return true;
        //u svim ostalim slucajevima ne moze
        return false;  
    }

    /**
     * The read function. searchTipLicence
     *
     * @return object
     */
    private function licencaInfo()
    {
        return LicencaNaplata::select('licenca_naplatas.id','licenca_tips.licenca_naziv','licenca_naplatas.distributerId', 'licenca_naplatas.terminal_lokacijaId', 'licenca_naplatas.licenca_distributer_cenaId', 'licenca_distributer_cenas.licenca_tipId', 'licenca_distributer_cenas.id as ldcid')
            ->leftJoin('licenca_distributer_cenas', 'licenca_distributer_cenas.id', '=', 'licenca_naplatas.licenca_distributer_cenaId')
            ->leftJoin('licenca_tips', 'licenca_tips.id', '=', 'licenca_distributer_cenas.licenca_tipId')
            ->where('licenca_naplatas.id', '=', $this->licenca_naplata_id)
            ->first();
    }

    /**
     * Parametri Licence
     *
     * @param mixed $naziv
     * 
     * @return [type]
     * 
     */
    public function parametriLicenceShowModal($licencaNaplatalid)
    {
        //$this->resetTerm();
        $this->licenca_naplata_id = $licencaNaplatalid;
        //dd($this->licenca_naplata_id);
        $lic_info = $this->licencaInfo();
        $this->pm_licenca_tip_id = $lic_info->licenca_tipId;
        $this->pm_licenca_naziv = $lic_info->licenca_naziv;
        


        //CEKIRANI PARAMETI ZA IZABRANU LICENCU
        $this->parametri = LicencaNaplata::leftJoin('licenca_parametar_terminals', function($join)
            {
                $join->on('licenca_naplatas.terminal_lokacijaId', '=', 'licenca_parametar_terminals.terminal_lokacijaId');
                $join->on('licenca_naplatas.distributerId', '=', 'licenca_parametar_terminals.distributerId');
                $join->on('licenca_naplatas.licenca_distributer_cenaId', '=', 'licenca_parametar_terminals.licenca_distributer_cenaId');
            })
            ->where('licenca_naplatas.id', '=', $this->licenca_naplata_id)
            ->pluck('licenca_parametar_terminals.licenca_parametarId')->all();
        
        foreach($this->parametri as $key => $value){
            if($value == null) unset($this->parametri[$key]);
        }
        //GLOBALNO DODELJENI parametri za tip licence
        $this->licenca_tip_parametri = LicencaParametar::where('licenca_tipId', '=', $this->pm_licenca_tip_id)->pluck('id')->all();
        
        $this->parametriModalVisible = true;

        //dd($this->parametri, $this->licenca_tip_parametri, $this->pm_licenca_tip_id);
    }

    public function updateParametreLicence()
    {
        LicencaParametarTerminal::updateParametars($this->licenca_parametri_ids, $this->pm_licenca_tip_id, $this->parametri);
        $this->parametri = [];
        $this->parametriModalVisible = false;
    }


    /**
     * Razduzi licence
     *
     * @return void
     */
    public function razduziUplatu()
    {
        if($this->razduzi_iznos <= 0 || !is_numeric($this->razduzi_iznos)){
            $this->razduzi_iznos_error = 'Greška! Cena licence mora biti broj veći od 0!';
            return;
        }
        $this->razduzi_iznos_error = '';
        $this->razduzi_iznos = floatval($this->razduzi_iznos);
        LicencaNaplata::where('id', '=', $this->naplata_podaci_licence->id)->update(['dist_razduzeno' => $this->razduzi_iznos, 'dist_datum_razduzenja' => $this->razduzi_datum]);
        
        $this->pregledLicencaShowModal = false;
    }

    public function downloadExcel()
    {
        return Excel::download(new LicencaNaplataExport($this->distId), 'licenca_naplata.xlsx');
    }

    /**
     * Prikazuje komentare na terminalu
     * @param  mixed $id
     * @return void
     */
     public function commentsShowModal($id)
    {
        //$this->newKoment = '';
        $this->resetErrorBag();
        $this->modelId = $id; //ovo je id terminal lokacija tabele
        
        $this->modalKomentariVisible = true;
    }

    /**
     * The read function. searchTipLicence
     *
     * @return object
     */
    public function read()
    {
        $this->allInPage = [];
        $search = [
            'searchSB' => $this->searchTerminalSn,
            'searchLokacija' => $this->searchMesto,
            'searchTipLicence' => $this->searchTipLicence,
            'searchPib' => $this->searchPib,
        ];

        $builder = TerminaliReadActions::DistributerTerminaliRead($this->distId, $search);

        $perPage = Config::get('global.terminal_paginate');
        $licens = $builder->paginate($perPage, ['*'], 'terminali');   

        $licens->getCollection()->transform(function ($item) {
            /* $licenca = LicenceZaTerminal::where('terminal_lokacijaId', $item->tlid)->first();
            $item->tzlid = $licenca ? $licenca->licenca_poreklo : 0; */
            $this->allInPage[] = $item->lnid;
            return $item;
        });

        return $licens;
        
    }

    /**
     * updated 
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function updated($key, $value)
    {
        $exp = Str::of($key)->explode(delimiter: '.');
        if($exp[0] === 'selectAll' && is_numeric($value)){
            //dd($this->allInPage);
           foreach($this->allInPage as $termid){
               if(!in_array($termid, $this->selectedTerminals)){
                array_push($this->selectedTerminals, $termid);
               }  
           }
        }elseif($exp[0] === 'selectAll' && empty($value)){
            $this->selectedTerminals = array_diff($this->selectedTerminals, $this->allInPage);
        }

        //setuje kraj licnece na prvi dan u izabranom mesecu
        if($this->dodajLicencuModalVisible){
            $this->datum_kraja_licence = Helpers::firstDayOfMounth($this->datum_kraja_licence);
            //cene licenci
            if(count($this->licence_za_dodavanje)){
                $this->cene_licenci = [];
                foreach($this->licence_za_dodavanje as $lc){
                    $this->cene_licenci[$lc] = new CenaLicence($lc, $this->datum_pocetka_licence, $this->datum_kraja_licence);
                    //$this->unete_cene_licenci[$lc] = $this->cene_licenci[$lc]->dist_cena_din;
                }

            }
        }
        //prodizetak licence
        if($this->produziLicModalVisible){
            $this->datum_kraja_licence = Helpers::firstDayOfMounth($this->datum_kraja_licence);
            $this->produzenje_cene[0] = new CenaLicence($this->licenca_distributer_cena_id, $this->datum_pocetka_licence, $this->datum_kraja_licence);
        }

        
    }

    public function render()
    {
        return view('livewire.distributer.dist-licence', [
            'data' => $this->read(), 'br_licenci' => $this->prebrojLicenceDistributera(),
        ]);
    }
}