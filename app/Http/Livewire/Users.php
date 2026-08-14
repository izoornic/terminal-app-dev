<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Lokacija;
use App\Models\UserHistory;
use App\Models\PozicijaTip;
use App\Models\PozicijaKategoriy;
use App\Models\KorisnikRadniStatus;
use App\Models\KorisnikRadniStatusHistory;
use App\Models\KorisnikRadniOdnos;
use App\Models\KorisnikRadniOdnosHistory;
use App\Models\LicencaDistributerTip;
use App\Models\DistributerUserIndex;
use App\Models\DistributerLokacijaIndex;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

use Illuminate\Validation\Rules\Password;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;

class Users extends Component 
{
    use WithPagination;
    
    public $modalFormVisible;
    public $modalConfirmDeleteVisible;
    public $modelId;
    public $name;
    public $email;
    public $pozicijaId;
    public $pozicijaList;
    public $telegramId;
    public $tel;
    public $password;
    public $vidi_komentare_na_terminalu;

    //Radni status
    public $modalRadniStatusVisible;
    public $radniStatusId;
    public $oldRadniStatusId;

    //lokacija
    public $lokacijaId;

    //radni odnos
    public $radniOdnosId;
    public $oldRadniOdnosId;

    //new user
    public $newUser;
    //poruka koja se prikazuje posle akcije
    public $actionMessage;
    //pretraga 
    public $searchName;
    public $searchLokacija;
    public $searchRStatus;
    public $searchPozicija;
    //order
    public $orderBy;
    public $orderDirection;

    //search DIsttributera
    public $plokacijaTip;
    public $searchPlokacijaRegion;
    public $searchPLokacijaNaziv;
    public $searchPlokacijaMesto;

    //Info i predtraga tabele distributeri
    public $distributerId;
    public $searchPDistNaziv;
    public $searchPDdistMesto;

    //promena distributera za test usera
    public $promeniDitributeraModalVisible;
    public $testUserId;
    public $testUserDistributer;

    //Kategorije korisnika
    public $selectedKatId;
    public $pozicijeKategorije;
  

    #[On('izaberiKategoriju')]
    public function postaviKategoriju($katId)
    {
        $this->selectedKatId = $katId;
        $this->searchPozicija = null;
        $this->pozicijeKategorije = PozicijaKategoriy::find($this->selectedKatId)->pozicije->pluck('id')->toArray();
        $this->resetPage();
    }

    #[On('sortClick')]
    public function sortClick($field)
    {
        if ($this->orderBy === $field) {
            $this->orderDirection = $this->orderDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->orderBy = $field;
            $this->orderDirection = 'desc';
            $this->dispatch('fieldChange', $field);
        }
        $this->dispatch('sortChange', $this->orderDirection);
    }

    public function mount(){
        
        $this->selectedKatId = PozicijaKategoriy::orderBy('menu_order')->first()->id;
        $this->orderDirection = 'desc';
        $this->orderBy = 'id';
        $this->pozicijeKategorije = PozicijaKategoriy::find($this->selectedKatId)->pozicije->pluck('id')->toArray();
    }

    public function pozizicijeKategorije()
    {
        return PozicijaKategoriy::find($this->selectedKatId)->pozicije->pluck('naziv', 'id');
    }

    /**
     * The validation rules
     *
     * 'password' => ['required', 'confirmed', Password::min(8)
     *                   ->mixedCase()
     *                  ->letters()
     *                   ->numbers()
     *                   ->symbols()
     *                   ->uncompromised(),
     *           ],
     * 
     * 
     * @return array
     */
    public function rules()
    {
        $retval = [  
            'name' => 'required',
            'pozicijaId' => 'required',
            'lokacijaId' => 'required',
            'telegramId' => ['digits_between:4,20', 'nullable'],
            'tel' => ['digits_between:8,11', 'nullable'],
            'radniOdnosId' => 'required',
        ];

        if($this->newUser){
            $retval['email'] = ['required', 'string', 'email', 'max:255', 'unique:users'];
            $retval['password'] = ['required', Password::min(8)->letters()->numbers()->symbols()];
        }

        return $retval;
    }

    /**
     * The read function.
     *
     */
    public function read()
    {
        //dd($this->pozizicijeKategorije());
        $order = 'id';
        switch($this->orderBy){
            case 'uid':
                $order = 'id';
            break;
            case 'name':
                $order = 'users.name';
            break;
            case 'lokacija':
                $order = 'r_naziv';
            break;
            case 'pozicija':
                $order = 'users.pozicija_tipId';
            break;
            case 'status':
                $order = 'rstid';
            break;
        };

        $pozicijeIds = $this->selectedKatId
            ? PozicijaKategoriy::find($this->selectedKatId)->pozicije->pluck('id')->toArray()
            : [];

        $nazivSubquery = DB::raw('(SELECT naziv FROM pozicija_tips WHERE id = users.pozicija_tipId LIMIT 1) as naziv');

        $latestRsSubquery = '(SELECT rs.id FROM korisnik_radni_statuses krs JOIN radni_status_tips rs ON krs.radni_statusId = rs.id WHERE krs.korisnikId = users.id ORDER BY krs.id DESC LIMIT 1)';
        $latestRsNazivSubquery = '(SELECT rs.rs_naziv FROM korisnik_radni_statuses krs JOIN radni_status_tips rs ON krs.radni_statusId = rs.id WHERE krs.korisnikId = users.id ORDER BY krs.id DESC LIMIT 1)';
        $latestRoSubquery = '(SELECT ro.id FROM korisnik_radni_odnos kro JOIN radni_odnos_tips ro ON kro.radni_odnosId = ro.id WHERE kro.korisnikId = users.id ORDER BY kro.id DESC LIMIT 1)';
        $latestRoNazivSubquery = '(SELECT ro.ro_naziv FROM korisnik_radni_odnos kro JOIN radni_odnos_tips ro ON kro.radni_odnosId = ro.id WHERE kro.korisnikId = users.id ORDER BY kro.id DESC LIMIT 1)';

        if($this->selectedKatId == 4){
            $query = User::select(
                    'users.*',
                    $nazivSubquery,
                    DB::raw('users.pozicija_tipId as ptid'),
                    DB::raw("$latestRsSubquery as rstid"),
                    DB::raw("$latestRsNazivSubquery as rs_naziv"),
                    DB::raw('(SELECT bl_naziv FROM blokacijas WHERE id = users.lokacijaId LIMIT 1) as l_naziv'),
                    DB::raw('(SELECT bl_mesto FROM blokacijas WHERE id = users.lokacijaId LIMIT 1) as mesto'),
                    DB::raw('(SELECT br.r_naziv FROM blokacijas b JOIN bankomat_regions br ON b.bankomat_region_id = br.id WHERE b.id = users.lokacijaId LIMIT 1) as r_naziv'),
                    DB::raw("$latestRoSubquery as rot_id"),
                    DB::raw("$latestRoNazivSubquery as ro_naziv"))
                ->whereIn('users.pozicija_tipId', $pozicijeIds)
                ->when($this->searchName, function($query) {
                    return $query->where('users.name', 'like', '%'.$this->searchName.'%');
                })
                ->when($this->searchLokacija, function($query) {
                    return $query->whereExists(function($sub) {
                        $sub->select(DB::raw(1))->from('blokacijas')
                            ->whereColumn('blokacijas.id', 'users.lokacijaId')
                            ->where('blokacijas.bl_naziv', 'like', '%'.$this->searchLokacija.'%');
                    });
                })
                ->when($this->searchRStatus > 0, function($query) {
                    $statusId = $this->searchRStatus;
                    return $query->whereExists(function($sub) use ($statusId) {
                        $sub->select(DB::raw(1))->from('korisnik_radni_statuses')
                            ->whereColumn('korisnik_radni_statuses.korisnikId', 'users.id')
                            ->where('korisnik_radni_statuses.radni_statusId', $statusId);
                    });
                })
                ->when($this->searchPozicija, function($query) {
                    return $query->where('users.pozicija_tipId', $this->searchPozicija);
                })
                ->orderBy($order, $this->orderDirection);
        }else{
            $query = User::select(
                    'users.*',
                    $nazivSubquery,
                    DB::raw('users.pozicija_tipId as ptid'),
                    DB::raw("$latestRsSubquery as rstid"),
                    DB::raw("$latestRsNazivSubquery as rs_naziv"),
                    DB::raw('(SELECT l_naziv FROM lokacijas WHERE id = users.lokacijaId LIMIT 1) as l_naziv'),
                    DB::raw('(SELECT mesto FROM lokacijas WHERE id = users.lokacijaId LIMIT 1) as mesto'),
                    DB::raw('(SELECT r.r_naziv FROM lokacijas l JOIN regions r ON l.regionId = r.id WHERE l.id = users.lokacijaId LIMIT 1) as r_naziv'),
                    DB::raw("$latestRoSubquery as rot_id"),
                    DB::raw("$latestRoNazivSubquery as ro_naziv"))
                ->whereIn('users.pozicija_tipId', $pozicijeIds)
                ->when($this->searchPozicija, function($query) {
                    return $query->where('users.pozicija_tipId', $this->searchPozicija);
                })
                ->when($this->searchName, function($query) {
                    return $query->where('users.name', 'like', '%'.$this->searchName.'%');
                })
                ->when($this->searchLokacija, function($query) {
                    return $query->whereExists(function($sub) {
                        $sub->select(DB::raw(1))->from('lokacijas')
                            ->whereColumn('lokacijas.id', 'users.lokacijaId')
                            ->where('lokacijas.l_naziv', 'like', '%'.$this->searchLokacija.'%');
                    });
                })
                ->when($this->searchRStatus > 0, function($query) {
                    $statusId = $this->searchRStatus;
                    return $query->whereExists(function($sub) use ($statusId) {
                        $sub->select(DB::raw(1))->from('korisnik_radni_statuses')
                            ->whereColumn('korisnik_radni_statuses.korisnikId', 'users.id')
                            ->where('korisnik_radni_statuses.radni_statusId', $statusId);
                    });
                })
                ->orderBy($order, $this->orderDirection);
        }

        return $query->paginate(Config::get('global.paginate'));
    }

    /**
     * Modal koji menja distributera test useru
     *
     * @param mixed $id
     * 
     * @return [type]
     * 
     */
    public function promeniDistirbuteraShowModal($id)
    {
        $this->testUserId = $id;
        $this->testUserDistributer = DistributerUserIndex::where('userId', '=', $id)->first()->licenca_distributer_tipsId;
        $this->promeniDitributeraModalVisible = true;
    }

    public function promeniDistributera()
    {
        DistributerUserIndex::where('userId', '=', $this->testUserId)->update(['licenca_distributer_tipsId' => $this->testUserDistributer] );
        $this->promeniDitributeraModalVisible = false;
    }
    /**
     * Shows the create NEW USER modal
     *
     * @return void
     */
    public function createShowModal()
    {
        $this->resetValidation();
        $this->resetForm();
        if(isset($this->pozizicijeKategorije()[8])){
            $this->pozicijaId = 8;
            $this->radniOdnosId = 3;
        }
        $this->modalFormVisible = true;
        $this->newUser = true;
    }

    /**
     * Shows the form modal AFTHER BUTTON CLICK
     * in update mode.
     *
     * @param  mixed $id
     * @return void
     */
    public function updateShowModal($id)
    {
        $this->newUser = false;
        $this->resetValidation();
        //$this->reset();
        $this->modelId = $id;
        $this->loadModel();
        
        //dd($this->distributerId);

        $this->modalFormVisible = true;
    }

    private function resetForm()
    {
        $this->modelId = '';
        $this->name = '';
        $this->password = '';
        $this->email = '';
        $this->pozicijaId = '';
        $this->lokacijaId = '';
        $this->telegramId = '';
        $this->tel = '';
        $this->vidi_komentare_na_terminalu = '';
        $this->radniOdnosId  = '';
    }

    /**
     * Loads the model data
     * of this component.
     *
     * @return void
     */
    public function loadModel()
    {
        $data = User::find($this->modelId);
        // Assign the variables here
        $this->name         = $data->name;
        $this->pozicijaId   = $data->pozicija_tipId;
        $this->lokacijaId   = $data->lokacijaId;
        $this->email        = $data->email;
        $this->telegramId   = ($data->telegramId > 0) ? $data->telegramId : "";
        $this->tel          = ($data->tel) ? ltrim($data->tel, '+381') : '';
        $this->vidi_komentare_na_terminalu = ($data->pozicija_tipId === 8) ? $data->vidi_komentare_na_terminalu : 0;

        $radniOdnos = KorisnikRadniOdnos::where('korisnikId', $this->modelId)->first();
        $this->radniOdnosId = $radniOdnos->radni_odnosId ?? '';
        $this->oldRadniOdnosId = $this->radniOdnosId;

        if($this->pozicijaId == 8){
            $this->distributerId = DistributerUserIndex::where('userId', '=', $this->modelId)->first()->licenca_distributer_tipsId;
        }
    }

    /**
     * lokacijeTipa
     *
     * @param  mixed $tipId
     */
    public function lokacijeTipa($tipId = 4)
    {
        return Lokacija::select('lokacijas.*', 'regions.r_naziv')
            ->where('lokacija_tipId', '=', $tipId)
            ->leftJoin('regions', 'lokacijas.regionId', '=', 'regions.id')
            ->where('l_naziv', 'like', '%'.$this->searchPLokacijaNaziv.'%')
            ->where('mesto', 'like', '%'.$this->searchPlokacijaMesto.'%')
            ->where('lokacijas.regionId', ($this->searchPlokacijaRegion > 0) ? '=' : '<>', $this->searchPlokacijaRegion)
            ->paginate(Config::get('global.modal_search'), ['*'], 'loc');
    }

    
    /**
     * Prikazuje naziv lokacije na koju se vezuje distributer
     *
     * @return void
     */
    public function izabranaLokacija()
    {
        $retval = Lokacija::select('lokacijas.*', 'regions.r_naziv' , 'licenca_distributer_tips.distributer_naziv', 'licenca_distributer_tips.distributer_mesto', 'licenca_distributer_tips.id as distId')
                            ->leftJoin('lokacija_tips', 'lokacijas.lokacija_tipId', '=', 'lokacija_tips.id')
                            ->leftJoin('regions', 'lokacijas.regionId', '=', 'regions.id')
                            ->leftJoin('distributer_lokacija_indices', 'lokacijas.id', '=', 'distributer_lokacija_indices.lokacijaId')
                            ->leftJoin('licenca_distributer_tips', 'licenca_distributer_tips.id', '=', 'distributer_lokacija_indices.licenca_distributer_tipsId')
                            ->where('lokacijas.id', '=', $this->lokacijaId)
                            ->first();
        $this->distributerId = $retval->distId;
        return $retval;
    }

    /**
     * Prikazuje podatke o firmi za koju se veyuje distributer
     *
     * @return void
     */
    public function izabraniDistributer()
    {
        return LicencaDistributerTip::where('id', '=', $this->distributerId)
                            ->first();
    }



    /**
     * The data for the model mapped
     * in this component.
     *
     * @return array
     */
    public function modelData()
    {
        $tell = ($this->tel != '') ? '+381'.$this->tel : '';
        $mdata = [  
            'name' => $this->name,
            'pozicija_tipId' => $this->pozicijaId,
            'lokacijaId' => $this->lokacijaId,
            'telegramId' => $this->telegramId,
            'tel' => $tell,
            'vidi_komentare_na_terminalu' => ($this->pozicijaId == 8) ? ($this->vidi_komentare_na_terminalu) ? 1 : 0 : 0,
        ];

        if($this->newUser){
            $mdata['email'] = $this->email;
            $mdata['password'] = Hash::make($this->password);
        };
        return $mdata;
    }

    /**
     * The create NEW USER function.
     *
     * @return void
     */
    public function create()
    {
        if($this->pozicijaId == 8){
            //dodaje se novi distributer
            $this->radniOdnosId = 3;
            if(!$this->distributerId){
                $this->modalFormVisible = false;
                return;
            }
        }
        
        /* try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors());
        } */
        $this->validate();
        DB::transaction(function(){
            $nUser = User::create($this->modelData());
            KorisnikRadniStatus::create([
                'korisnikId' => $nUser->id,
                'radni_statusId' => 1,
            ]);
            KorisnikRadniOdnos::create([
                'korisnikId' => $nUser->id,
                'radni_odnosId' => $this->radniOdnosId,
            ]);
            if($this->pozicijaId == 8){
                DistributerUserIndex::create([
                    'userId' => $nUser->id,
                    'licenca_distributer_tipsId' => $this->distributerId
                ]);
            }
        });
        $this->modalFormVisible = false;
    }

    /**
     * The update function
     *
     * @return void
     */
    public function update()
    {
        $this->validate();
        User::find($this->modelId)->update($this->modelData());
        if($this->oldRadniOdnosId != $this->radniOdnosId){
            DB::transaction(function(){
               //prvo trenutna vrednost iz tabele
               $cuurent = KorisnikRadniOdnos::where('korisnikId', $this->modelId)->first();
               if($cuurent){
                    //Ako je imao radni odnos pre edita
                    //zatim upis u history tabelu
                    KorisnikRadniOdnosHistory::create(['korisnik_radni_odnosId' => $cuurent['id'], 'korisnikId' => $cuurent['korisnikId'], 'radni_odnosId' => $cuurent['radni_odnosId'], 'created_at' => $cuurent['created_at'], 'updated_at' => $cuurent['updated_at']]);
                    //update trenutnog stanja
                    KorisnikRadniOdnos::where('korisnikId', $this->modelId)->update(['radni_odnosId' => $this->radniOdnosId]);
               }else{
                    KorisnikRadniOdnos::create([
                        'korisnikId' => $this->modelId,
                        'radni_odnosId' => $this->radniOdnosId
                    ]);
               }
              
               
            });
        }
        $this->modalFormVisible = false;
    }

    /**
     * The delete function.
     *
     * @return void
     */
    public function delete()
    {
        //ovde mora transakcija
        try {
            DB::transaction(function(){
                $user = User::find($this->modelId);
                UserHistory::create([
                    'korisnikId' => $this->modelId,
                    'pozicija_tipId' => $user->pozicija_tipId,
                    'lokacijaId'  => $user->lokacijaId,
                    'telegramId'  => $user->telegramId,
                    'tel'  => $user->tel,
                    'name'  => $user->name,
                    'email'  => $user->email,
                    'email_verified_at'  => $user->email_verified_at,
                    'password'  => $user->password,
                    'remember_token'  => $user->remember_token,
                    'current_team_id'  => $user->current_team_id,
                    'profile_photo_path'  => $user->profile_photo_path,
                ]);
               
                //KorisnikRadniOdnos::where('korisnikId', $this->modelId)->delete();
                //KorisnikRadniStatus::where('korisnikId', $this->modelId)->delete();
                User::find($this->modelId)->update(['pozicija_tipId'=> 7, 'password' => Hash::make(Str::random(40))]);
            });
        } catch (\Throwable $th) {
            abort(403, 'Unauthorized action!.');
        }
        $this->modalConfirmDeleteVisible = false;
        $this->resetPage();
    }

    /**
     * Shows the delete confirmation modal.
     *
     * @param  mixed $id
     * @return void
     */
    public function deleteShowModal($id)
    {
        $this->newUser = false;
        $this->modelId = $id;
        $this->modalConfirmDeleteVisible = true;
        $this->name = User::find($this->modelId)->name;
    }    

    public function render()
    {
        return view('livewire.users', [
            'data' => $this->read(),
        ]);
    }

    /*  public function updated($key, $value)
    {
         $this->pozicijeKategorije = PozicijaKategoriy::find($this->selectedKatId)->pozicije->pluck('id')->toArray();
    } */

       /* ----------------------------------- Radni Status Modal ------------------------------------------*/
    /**
     * Shows the create modal
     *
     * @return void
     */
    public function createShowRadniStatusModal()
    {
        $this->modalRadniStatusVisible = true;
    }

    /**
     * Shows the form modal CALLED AFTER BTN CLICK
     * in update mode.
     *
     * @param  mixed $id
     * @return void
     */
    public function updateShowRadniStatusModal($id)
    {
        $this->newUser = false;
        //$this->reset();
        $this->modalRadniStatusVisible = true;
        $this->modelId = $id;
        $this->loadRadniStatusModel();
    }

    /**
     * Loads the model data
     * of this component.
     *
     * @return void
     */
    public function loadRadniStatusModel()
    {
        $data = User::find($this->modelId);
        // Assign the variables here
        $this->name = $data->name;
        $data_status = KorisnikRadniStatus::where('korisnikId', $this->modelId)->first();
        $this->radniStatusId = $data_status->radni_statusId;
        $this->oldRadniStatusId = $this->radniStatusId;
    }

    /**
     * The update Radni Status function
     *
     * @return void
     */
    public function updateRadniStatus()
    {
        if($this->radniStatusId != $this->oldRadniStatusId){
            DB::transaction(function(){
                //prvo trenutna vrednost iz tabele 
                $cuurent = KorisnikRadniStatus::where('korisnikId', $this->modelId)->first();
                //zatim upis u history tabelu
                KorisnikRadniStatusHistory::create(['korisnik_radni_statusId' => $cuurent['id'], 'korisnikId' => $cuurent['korisnikId'], 'radni_statusId' => $cuurent['radni_statusId'], 'created_at' => $cuurent['created_at'], 'updated_at' => $cuurent['updated_at']]);
                //update trenutnog stanja
                KorisnikRadniStatus::where('korisnikId', $this->modelId)->update(['radni_statusId' => $this->radniStatusId]);
            });
        }
       $this->modalRadniStatusVisible = false;
    }
    
}