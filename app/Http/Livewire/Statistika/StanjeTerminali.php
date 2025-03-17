<?php

namespace App\Http\Livewire\Statistika;

use Livewire\Component;
use App\Models\Terminal;
use App\Models\Lokacija;
use App\Models\TerminalLokacija;
use App\Models\LicencaDistributerTip;

use Illuminate\Support\Facades\DB;

class StanjeTerminali extends Component
{
    public $searchModel = '';
    public $searchLokacijaTip = '';
    public $searchTerminalTip = '';
    public $filterInfo = '';

    public function prepareData()
    {
        if($this->searchLokacijaTip != ''){
            //prikaz stanja zavisi od izabrane lokacije
            switch(true){
                case ($this->searchLokacijaTip == '1' || $this->searchLokacijaTip == '2'):
                    // Servisni centar ili Magacin
                     $this->filterInfo = 'Ukupan broj terminala koji se nalaze na izabranom tipu lokacije (Servisni centar ili Magacin).';
                    $lokacije_ids = Lokacija::where('lokacija_tipId', $this->searchLokacijaTip)->pluck('id');
                    return TerminalLokacija::select('lokacijas.l_naziv as lokacija_naziv', 'lokacijas.mesto','terminal_lokacijas.lokacijaId as pid', DB::raw('count(*) as total'))
                    ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
                    ->whereIn('lokacijaId', $lokacije_ids)
                    ->when($this->searchTerminalTip != '', function ($rtval){
                        return $rtval->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                        ->where('terminals.terminal_tipId', $this->searchTerminalTip);
                    })
                    ->groupBy('lokacijaId')
                    ->get();
                    break;
                case ($this->searchLokacijaTip == '3'):
                    // korisnik terminala    
                    $this->filterInfo = 'Ukupan broj terminala koji se nalaze kod korisnika. U ovaj broj su uključeni svi terminali koji su dodeljeni korisnicima ili od strane Distributera ili direktno od strane Zete (MTS-a).';               
                    return TerminalLokacija::select('lokacija_tips.lt_naziv as lokacija_naziv', 'lokacija_tips.lt_naziv as mesto','lokacija_tips.id as pid', DB::raw('count(*) as total'))
                    ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
                    ->leftJoin('lokacija_tips', 'lokacijas.lokacija_tipId', '=', 'lokacija_tips.id')
                    ->when($this->searchTerminalTip != '', function ($rtval){
                        return $rtval->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                        ->where('terminals.terminal_tipId', $this->searchTerminalTip);
                    })
                    ->where('lokacijas.lokacija_tipId', $this->searchLokacijaTip)
                    ->groupBy('lokacijas.lokacija_tipId')
                    ->get();
                    break;
                case ($this->searchLokacijaTip == '4'):
                    // Distributer
                    $this->filterInfo = 'Broj terminala za pojedinačne "Distributere" u koji su ubrojani svi terminali koji su dodeljeni distributeru. Terminali koji se nalaze kod korisnika i terminali koji se nalaze kod distributera.';
                    $distlist = LicencaDistributerTip::pluck('id');
                    return TerminalLokacija::select('licenca_distributer_tips.distributer_naziv as lokacija_naziv', 'licenca_distributer_tips.distributer_mesto as mesto','licenca_distributer_tips.id as pid', DB::raw('count(*) as total'))
                    ->leftJoin('licenca_distributer_tips', 'terminal_lokacijas.distributerId', '=', 'licenca_distributer_tips.id')
                    ->when($this->searchTerminalTip != '', function ($rtval){
                        return $rtval->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                        ->where('terminals.terminal_tipId', $this->searchTerminalTip);
                    })
                    ->whereIn('terminal_lokacijas.distributerId', $distlist)
                    ->groupBy('terminal_lokacijas.distributerId')
                    ->get();
                    break;
            }            
        }
        $this->filterInfo = 'Prikazan broj terminala za "Distributere" se odnosi samo na terminale koji su dodati distributerima a nisu prebačeni korisnicima.';
        return TerminalLokacija::select('lokacija_tips.id as lokacija_naziv', 'lokacija_tips.lt_naziv as mesto','lokacija_tips.id as pid', DB::raw('count(*) as total'))
            ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
            ->leftJoin('lokacija_tips', 'lokacijas.lokacija_tipId', '=', 'lokacija_tips.id')
            ->when($this->searchTerminalTip != '', function ($rtval){
                return $rtval->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                ->where('terminals.terminal_tipId', $this->searchTerminalTip);
            })
            ->groupBy('lokacijas.lokacija_tipId')
            ->orderBy('lokacija_tips.id')
            ->get();
    }

    public function read()
    {
        $data = $this->prepareData();
        
        $data->each(function ($item, $key){
            switch(true){
                case ($this->searchLokacijaTip == '1' || $this->searchLokacijaTip == '2'):
                    // Servisni centar ili Magacin
                    $item->modeli = TerminalLokacija::select('terminal_tips.model as model', 'terminal_tips.proizvodjac' ,DB::raw('count(*) as total'))
                    ->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                    ->leftJoin('terminal_tips', 'terminals.terminal_tipId', '=', 'terminal_tips.id')
                    ->when($this->searchTerminalTip != '', function ($rtval){
                        return $rtval->where('terminals.terminal_tipId', $this->searchTerminalTip);
                    })
                    ->where('terminal_lokacijas.lokacijaId', $item->pid)
                    ->groupBy('terminals.terminal_tipId')
                    ->get();
                    break;
                case ($this->searchLokacijaTip == '3'):
                    // korisnik terminala
                    $item->modeli = TerminalLokacija::select('terminal_tips.model as model', 'terminal_tips.proizvodjac' ,DB::raw('count(*) as total'))
                    ->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                    ->leftJoin('terminal_tips', 'terminals.terminal_tipId', '=', 'terminal_tips.id')
                    ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
                    ->when($this->searchTerminalTip != '', function ($rtval){
                        return $rtval->where('terminals.terminal_tipId', $this->searchTerminalTip);
                    })
                    ->where('lokacijas.lokacija_tipId', $item->pid)
                    ->groupBy('terminals.terminal_tipId')
                    ->get();
                    break;
                case ($this->searchLokacijaTip == '4'):
                    // Distributer
                    $item->modeli = TerminalLokacija::select('terminal_tips.model as model', 'terminal_tips.proizvodjac' ,DB::raw('count(*) as total'))
                    ->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                    ->leftJoin('terminal_tips', 'terminals.terminal_tipId', '=', 'terminal_tips.id')
                    ->when($this->searchTerminalTip != '', function ($rtval){
                        return $rtval->where('terminals.terminal_tipId', $this->searchTerminalTip);
                    })
                    ->where('terminal_lokacijas.distributerId', $item->pid)
                    ->groupBy('terminals.terminal_tipId')
                    ->get();
                    break;
                default:
                $item->modeli = TerminalLokacija::select('id')
                    ->where('terminal_lokacijas.terminal_statusId', 123456)
                    ->get();
            }    
        });

        return $data;
    }

    public function render()
    {
        return view('livewire.statistika.stanje-terminali', [
            'data' => $this->read()
        ]);
    }
}
