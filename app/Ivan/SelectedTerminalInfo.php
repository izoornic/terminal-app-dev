<?php
namespace App\Ivan;

use App\Models\TerminalLokacija;
use App\Models\LicenceZaTerminal;

class SelectedTerminalInfo 
{

    public static function selectedTerminalInfo($tlid)
    {
        return TerminalLokacija::select('terminal_lokacijas.*', 'terminals.sn', 'terminals.terminal_tipId as tid', 'terminal_status_tips.ts_naziv', 'lokacijas.l_naziv', 'lokacijas.adresa', 'lokacijas.mesto', 'lokacijas.pib', 'lokacijas.lokacija_tipId', 'lokacijas.email', 'lokacija_kontakt_osobas.name', 'lokacija_kontakt_osobas.tel', 'regions.r_naziv', 'regions.id as rid', 'terminal_tips.model as treminal_model', 'terminal_tips.proizvodjac as treminal_proizvodjac', 'licenca_distributer_tips.distributer_naziv')
                    ->where('terminal_lokacijas.id',  $tlid)
                    ->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                    ->leftJoin('terminal_status_tips', 'terminal_lokacijas.terminal_statusId', '=', 'terminal_status_tips.id')
                    ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
                    ->leftJoin('lokacija_kontakt_osobas', 'lokacijas.id', '=', 'lokacija_kontakt_osobas.lokacijaId')
                    ->leftJoin('regions', 'lokacijas.regionId', '=', 'regions.id')
                    ->leftJoin('terminal_tips', 'terminals.terminal_tipId', '=', 'terminal_tips.id')
                    ->leftJoin('licenca_distributer_tips', 'terminal_lokacijas.distributerId', '=', 'licenca_distributer_tips.id')
                    -> first();
    }

    public static function selectedTerminalInfoSerialNumber($sn)
    {
        return TerminalLokacija::select('terminal_lokacijas.*', 'terminals.sn', 'terminals.terminal_tipId as tid', 'terminal_status_tips.ts_naziv', 'lokacijas.l_naziv', 'lokacijas.mesto', 'lokacijas.pib', 'lokacija_kontakt_osobas.name', 'lokacija_kontakt_osobas.tel', 'regions.r_naziv')
                    ->where('terminals.sn',  $sn)
                    ->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                    ->leftJoin('terminal_status_tips', 'terminal_lokacijas.terminal_statusId', '=', 'terminal_status_tips.id')
                    ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
                    ->leftJoin('lokacija_kontakt_osobas', 'lokacijas.id', '=', 'lokacija_kontakt_osobas.lokacijaId')
                    ->leftJoin('regions', 'lokacijas.regionId', '=', 'regions.id')
                    ->first();
    }

    public static function selectedTerminalInfoTerminalId($tid)
    {
        return TerminalLokacija::select('terminal_lokacijas.*', 'terminals.sn', 'terminals.terminal_tipId as tid', 'terminal_status_tips.ts_naziv', 'lokacijas.l_naziv', 'lokacijas.mesto', 'lokacijas.pib', 'lokacija_kontakt_osobas.name', 'lokacija_kontakt_osobas.tel', 'regions.r_naziv', 'regions.id as rid')
                    ->where('terminalId', $tid)            
                    ->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                    ->leftJoin('terminal_status_tips', 'terminal_lokacijas.terminal_statusId', '=', 'terminal_status_tips.id')
                    ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
                    ->leftJoin('lokacija_kontakt_osobas', 'lokacijas.id', '=', 'lokacija_kontakt_osobas.lokacijaId')
                    ->leftJoin('regions', 'lokacijas.regionId', '=', 'regions.id')
                    -> first(); 
    }

    public static function selectedTerminalInfoTerminalLokacijaId($tid)
    {
        return TerminalLokacija::select('terminal_lokacijas.*', 'terminals.sn', 'terminals.terminal_tipId as tid', 'terminal_status_tips.ts_naziv', 'lokacijas.l_naziv', 'lokacijas.mesto', 'lokacijas.pib','lokacijas.lokacija_tipId', 'lokacija_kontakt_osobas.name', 'lokacija_kontakt_osobas.tel', 'regions.r_naziv', 'regions.id as rid', 'terminal_tips.model as treminal_model', 'terminal_tips.proizvodjac as treminal_proizvodjac', 'licenca_distributer_tips.distributer_naziv')
                    ->where('terminal_lokacijas.id', $tid)            
                    ->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                    ->leftJoin('terminal_status_tips', 'terminal_lokacijas.terminal_statusId', '=', 'terminal_status_tips.id')
                    ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
                    ->leftJoin('lokacija_kontakt_osobas', 'lokacijas.id', '=', 'lokacija_kontakt_osobas.lokacijaId')
                    ->leftJoin('regions', 'lokacijas.regionId', '=', 'regions.id')
                    ->leftJoin('terminal_tips', 'terminals.terminal_tipId', '=', 'terminal_tips.id')
                    ->leftJoin('licenca_distributer_tips', 'terminal_lokacijas.distributerId', '=', 'licenca_distributer_tips.id')
                    -> first(); 
    }

    //OVA funkcija ne radi...
    public function terminalInfo($id, $typeOf)
    { 
        return TerminalLokacija::select('terminal_lokacijas.*', 'terminals.sn', 'terminals.terminal_tipId as tid', 'terminal_status_tips.ts_naziv', 'lokacijas.l_naziv', 'lokacijas.adresa', 'lokacijas.mesto', 'lokacija_kontakt_osobas.name', 'lokacija_kontakt_osobas.tel', 'regions.r_naziv', 'regions.id as rid')
                    ->when($typeOf == "terminal_lokacija", function ($rtval) use($id){
                        return $rtval->where('terminal_lokacijas.id',  $id);
                    })
                    ->when($typeOf == "terminalID", function ($rtval) use($id){
                        return $rtval->where('terminalId', $id);
                    })
                    ->when($typeOf == "terminalSN", function ($rtval) use($id){
                        return $rtval->where('terminals.sn', $id);
                    })
                    ->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
                    ->leftJoin('terminal_status_tips', 'terminal_lokacijas.terminal_statusId', '=', 'terminal_status_tips.id')
                    ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
                    ->leftJoin('lokacija_kontakt_osobas', 'lokacijas.id', '=', 'lokacija_kontakt_osobas.lokacijaId')
                    ->leftJoin('regions', 'lokacijas.regionId', '=', 'regions.id')
                    -> first();
        
    }

    //provera da li terminal ima na sebi aktivnu licencu 
    public static function terminalImaLicencu($terminalId)
    {
        $tlid = TerminalLokacija::where('terminalId', $terminalId) -> first()->id;
        
        return LicenceZaTerminal::where('terminal_lokacijaId', $tlid) -> exists();
    }

}