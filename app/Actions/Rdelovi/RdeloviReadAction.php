<?php

namespace App\Actions\Rdelovi;

use App\Models\PartStock;

class RdeloviReadAction
{
    public static function PartStockRead($search, $sortField=null, $sortAsc=true): object
    {
        // Extract search parameters
        $searchNaziv = $search['searchNaziv'] ?? null;
        $searchSifra = $search['searchSifra'] ?? null;
        $locationId = $search['locationId'] ?? null;
        $categoryId = $search['categoryId'] ?? null;
        $showLowStockOnly = $search['showLowStockOnly'] ?? null;

        $sort_field = $sortField ?? 'kolicina_dostupna';

        return PartStock::query()
            ->select(
                'part_stocks.*', 
                'part_types.id as tipid',
                'part_types.sifra',
                'part_types.naziv',
                'part_types.category_id', 
                'lokacijas.l_naziv',
                'regions.r_naziv',
                'terminal_tips.model as kategorija',
                'terminal_tips.proizvodjac'
                )
            ->leftJoin('part_types', 'part_types.id', '=', 'part_stocks.part_type_id')
            ->leftJoin('lokacijas', 'lokacijas.id', '=', 'part_stocks.lokacija_id')
            ->leftJoin('regions', 'regions.id', '=', 'lokacijas.regionId')
            ->leftJoin('terminal_tips', 'terminal_tips.id', '=', 'part_types.category_id')
            ->when($searchNaziv, function ($q, $searchNaziv) {
                    $q->where('naziv', 'like', '%' . $searchNaziv . '%');
            })
            ->when($searchSifra, function ($q, $searchSifra) {
                    $q->where('sifra', 'like', '%' . $searchSifra . '%');
            })
            ->when($locationId, function ($q, $locationId) {
                    $q->where('lokacija_id', $locationId);
            })
            ->when($categoryId, function ($q, $categoryId) {
                    $q->where('category_id', $categoryId);
            })
            ->when($showLowStockOnly, function ($q, $showLowStockOnly) {
                $q->whereRaw('part_stocks.kolicina_dostupna <= part_types.min_kolicina');
            })
            ->orderBy($sort_field, $sortAsc ? 'asc' : 'desc');
    }
}
