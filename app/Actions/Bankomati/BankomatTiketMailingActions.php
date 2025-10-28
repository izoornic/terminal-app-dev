<?php

namespace App\Actions\Bankomati;

use App\Models\BankomatTiketPrioritetTip;
use App\Models\BankomatTiketKvarTip;
use App\Models\BankomatTiket;
use App\Models\User;

use App\Http\Helpers;

class BankomatTiketMailingActions
{
    public ?int $tiketId;
    public $tiket;
    public $tiketLokacija;
    public $tiketRegion;
    public $userLogovan;

    public $email_primaoci;

    function __construct(?int $tid) {
        $this->tiketId = $tid;
        //$this->tiketRegion = $this->tiketRegion($this->tiketId);
            
        $this->tiket = BankomatTiket::where('id', $this->tiketId)->first();
        //dd($this->tiket);
        $this->tiketLokacija = $this->tiket->lokacija;//BankomatTiket::find($this->tiketId)->lokacija;
        //dd($this->tiketLokacija);
        $this->tiketRegion = $this->tiketLokacija->bankomat_region_id;

        $this->userLogovan = 0; //(auth()->user() == null) ? 0 : 1;

       $this->email_primaoci = $this->setEmailPrimaoce();
    }
    
    /**
     * Send emails to primaci based on given subject and optional list of kommentari.
     *
     * @param string $subject Predefinisan moze biti: 'novi', 'komentar', 'dodeljen', 'zatvoren'.
     * @param array|null $comentari Optional list of kommentari.
     */
    public function sendEmails($subject, $comentari = null)
    {
        //dd($this->tiketData($subject), $this->email_primaoci);
        foreach ($this->email_primaoci as $mail_address) {
            try {
                Mail::to($mail_address)->send(new NotyfyMail($this->tiketData($subject), $comentari));
            } catch (Exception $e) {
                if (count(Mail::failures()) > 0) {
                    $failures[] = $mail_address;
                }
            }
        }
        //dd($this->email_primaoci);
    }

    /**
     * setEmailPrimaoce
     *
     * @return object
     */
    private function setEmailPrimaoce()
    {
        $retval = [];
        $email_primaci = [
            'kreirao' => ($this->tiket->user_prijava_id == null) ? null : $this->userEmail($this->tiket->user_prijava_id),
            'dodeljen' => ($this->tiket->user_dodeljen_id == null) ? null : $this->userEmail($this->tiket->user_dodeljen_id),
            'sef' => $this->sefServisa()
        ];

        //dd($email_primaci);

        foreach($email_primaci as $primac){
            if($primac != null){
                if($primac->id != auth()->user()->id){
                        if(!in_array($primac->email, $retval)){
                            array_push($retval, $primac->email);
                        }
                    }   
            }
        }
        //dd($retval);
        return $retval;
    }
    
    /**
     * userEmail
     *
     * @return void
     */
    private function userEmail($id)
    {
        return User::select('id','email','pozicija_tipId')
                    ->where('id', '=', $id)
                    ->first();
    }

    /**
     * id Sefa Servisa otvorenog tiketa
     *
     * @return void
     */
    private function sefServisa()
    { 
        return User::select('users.id', 'users.name', 'users.tel', 'users.email', 'users.pozicija_tipId')
                    ->join('blokacijas', 'blokacijas.id', '=', 'users.lokacijaId')
                    ->join('bankomat_regions', 'bankomat_regions.id', '=', 'blokacijas.bankomat_region_id')
                    ->where('users.pozicija_tipId', '=', 10)
                    ->where('bankomat_regions.id', '=', $this->tiketRegion)
                    ->first();
    }
    
    /**
     * tiketRegion
     *
     * @param  mixed $tikid
     * @return void
     */
    private function tiketRegion($tikid)
    {
        return Region::select('regions.id as rid')
                    ->join('lokacijas', 'lokacijas.regionId', '=', 'regions.id')
                    ->join('terminal_lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
                    ->join('tikets', 'tikets.tremina_lokacijalId', '=', 'terminal_lokacijas.id')
                    ->where('tikets.id', '=', $tikid)
                    ->first()->rid;
    }
  
    /**
     * userInfo
     *
     * @return object
     */
    private function userInfo($id)
    {
        return User::select('users.id', 'users.name', 'users.tel', 'users.email', 'users.pozicija_tipId')
                        ->where('users.id', '=', $id)
                        ->first();
    }

    /**
     * Podaci koji se prikazuju u email poruci
     *
     * @param  mixed $tik
     * @return void
     */
    private function tiketData($sub)
    {
        $bankomat_info = BankomatInformation::bankomatInfo($this->tiket->bankoamt_lokacija_id);
        //$this->tikedd(t);
        $dodeljen_ime = ($this->tiket->user_dodeljen_id != null) ? $this->userInfo($this->tiket->user_dodeljen_id)->name : 'Tiket nije dodeljen';
        $kreirao = ($this->tiket->user_prijava_id != null) ? $this->userInfo($this->tiket->user_prijava_id)->name : 'on line';
        
        $subject = '';
        $heding = '';
        $zatvorio = '';

        switch($sub){
            case 'novi':
                $subject = 'Novi tiket #';
                $heding = 'Na servisnom portalu je otvoren novi tiket #';
            break;
            case 'komentar':
                $subject = 'Novi komentar na tiket - #';
                $heding = 'Novi komentar na tiket - #';
            break;
            case 'dodeljen':
                $subject = 'Dodeljen tiket #';
                $heding = 'Na servisnom portalu dodeljen vam je tiket #';
            break;
            case 'zatvoren':
                $subject = 'Zatvoren tiket #';
                $heding = 'Na servisnom portalu zatvoren je tiket #';
                $zatvorio = ' | Tiket zatvorio: '.auth()->user()->name;
            break;
        }
        
        $opisKvaraObj = ($this->tiket->bankomat_tiket_kvar_tip_id) ? BankomatTiketKvarTip::where('id', '=', $this->tiket->bankomat_tiket_kvar_tip_id)->first() : null;
        $opisKvara = ($opisKvaraObj == null) ? '' : $opisKvaraObj->btkt_naziv;

        $bankomat_lokacija = ($bankomat_info->is_duplicate) ? "*" : "";
        $bankomat_lokacija .= $bankomat_info->blokacija_naziv;
        $bankomat_lokacija .= ($bankomat_info->is_duplicate) ? ' - '.$bankomat_info->blokacija_naziv_sufix : "";
        $bankomat_lokacija .=', '.$bankomat_info->blokacija_adresa.', '.$bankomat_info->blokacija_mesto;
        
        //TODO KONTAKT ODSBE VISE NJIH
        
       $mail_data = [
        'subject'   =>  $subject.$this->tiket->id,
        'tiketlink' =>  'https://servis.epos.rs/bankomat-tiketview?id='.$this->tiket->id,
        'hedaing'   =>  $heding.$this->tiket->id,
        'row1'      =>  'Prioritet: '.BankomatTiketPrioritetTip::where('id', '=', $this->tiket->bankomat_tiket_prioritet_id)->first()->btpt_naziv.' | Kreiran: '.Helpers::datumFormat($this->tiket->created_at),
        'row2'      =>  'Otvorio: '.$kreirao,
        'row3'      =>  'Dodeljen: '.$dodeljen_ime. ' '. $zatvorio,
        'row4'      =>  'Kvar: '.$opisKvara,
        'row5'      =>  'Opis: '.$this->tiket->opis,
        'row6'      =>  ' -::-  ---  -::-',
        'row7'      =>  'Proizvod: '.$bankomat_info->bp_tip_naziv.' | sn: '.$bankomat_info->b_sn,
        'row8'      =>  'Status: '.$bankomat_info->status_naziv,
        'row9'      =>  'Lokacija: '.$bankomat_lokacija,
        'row10'     =>  'Region: '. $bankomat_info->r_naziv,
        'row11'     =>  'Kontakt: ', //. $bankomat_info->name.'  tel: '.$bankomat_info->tel
        ];

        return $mail_data;
    }

    /**
     * sef Servisa za izabrani Terminal 
     *
     * @param  mixed $terminalLokacijaId
     * @return object
     */
   /*  public static function sefServisaTerminal($terminalLokacijaId)
    {
        //region id
        $regid = Lokacija::select('regionId')
                    ->join('terminal_lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
                    ->where('terminal_lokacijas.id', '=', $terminalLokacijaId)
                    ->first()
                    ->regionId;
        //ovde mora dodatna logika
        // da se proveri da li sef servisa radi
    
        return User::select('users.id', 'users.name', 'users.tel', 'users.email', 'users.pozicija_tipId')
        ->join('lokacijas', 'lokacijas.id', '=', 'users.lokacijaId')
        ->where('users.pozicija_tipId', '=', 3)
        ->where('lokacijas.regionId', '=',  $regid)
        ->first();
    } */
}