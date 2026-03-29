<?php
namespace App\Actions\Lokacije;

use Illuminate\Support\Facades\DB;

class LokacijaHistory
{
    /**
     * Returns chronological list of terminals that were assigned to a location.
     * Combines history records (past assignments) with current active assignments.
     *
     * @param int $lokacijaId
     * @return array
     */
    public static function lokacijaHistoryData($lokacijaId)
    {
        return DB::select(
            "SELECT * FROM (
                SELECT
                    'history' as 'izvor',
                    tlh.korisnikIme,
                    tlh.created_at,
                    tlh.updated_at,
                    t.sn as 'terminal_sn',
                    tt.model as 'terminal_model',
                    tst.ts_naziv as 'status_naziv',
                    ldt.distributer_naziv as 'distributer'
                FROM terminal_lokacija_histories tlh
                LEFT JOIN terminals t ON tlh.terminalId = t.id
                LEFT JOIN terminal_tips tt ON t.terminal_tipId = tt.id
                LEFT JOIN terminal_status_tips tst ON tlh.terminal_statusId = tst.id
                LEFT JOIN licenca_distributer_tips ldt ON tlh.distributerId = ldt.id
                WHERE tlh.lokacijaId = ?
            ) a
            UNION ALL
            SELECT * FROM (
                SELECT
                    'aktivan' as 'izvor',
                    tl.korisnikIme,
                    tl.created_at,
                    tl.updated_at,
                    t.sn as 'terminal_sn',
                    tt.model as 'terminal_model',
                    tst.ts_naziv as 'status_naziv',
                    ldt.distributer_naziv as 'distributer'
                FROM terminal_lokacijas tl
                LEFT JOIN terminals t ON tl.terminalId = t.id
                LEFT JOIN terminal_tips tt ON t.terminal_tipId = tt.id
                LEFT JOIN terminal_status_tips tst ON tl.terminal_statusId = tst.id
                LEFT JOIN licenca_distributer_tips ldt ON tl.distributerId = ldt.id
                WHERE tl.lokacijaId = ?
            ) b
            ORDER BY updated_at DESC",
            [$lokacijaId, $lokacijaId]
        );
    }
}
