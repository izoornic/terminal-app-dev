<div class="relative bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
   <div class="flex">
        <div class="py-1">
            <x-heroicon-o-tag class="w-6 h-6 mr-1" />
        </div>
        
        <div>
            @if($multySelected)
                {{-- VISE BANKOMATA U LISTI --}}
               
                @foreach ($multiSelectedInfo as $item)
                    <div class="w-full mb-2 border-b-2 border-sky-500">
                        <p class="font-bold">{{$item->bp_tip_naziv}}</p>
                        <p>SN: <span class="font-bold">{{$item->b_sn}}</span>
                            @if($item->b_terminal_id != null)
                                &nbsp;&nbsp;&nbsp; Terminal ID: <span class="font-bold">{{$item->b_terminal_id}}</span>
                            @endif
                            &nbsp;&nbsp;&nbsp;Status: <span class="font-bold">{{ $item->status_naziv }}</span>
                        </p>
                        <p class="text-sm">Model: <span class="font-bold">{{ $item->model }}</span> | Proizvođač: <span class="font-bold">{{$item->proizvodjac}}</span></p>
                        <p class="text-sm">Lokacija: <span class="font-bold">{{ $item->blokacija_naziv }}</span>&nbsp;{{ $item->blokacija_naziv_sufix }}, {{$item->blokacija_adresa}}, {{$item->blokacija_mesto}}</p>
                    </div>
                @endforeach
            @else
                {{-- JEDAN BANKOMAT U LISTI --}}
                <div>
                    <p class="font-bold">{{$selectedBankomat->bp_tip_naziv}}</p>
                    <p>SN: <span class="font-bold">{{$selectedBankomat->b_sn}}</span>
                        @if($selectedBankomat->b_terminal_id != null)
                            &nbsp;&nbsp;&nbsp; Terminal ID: <span class="font-bold">{{$selectedBankomat->b_terminal_id}}</span>
                        @endif
                        &nbsp;&nbsp;&nbsp;Status: <span class="font-bold">{{ $selectedBankomat->status_naziv }}</span>
                    </p>
                    <p class="text-sm">Model: <span class="font-bold">{{ $selectedBankomat->model }}</span> | Proizvođač: <span class="font-bold">{{$selectedBankomat->proizvodjac}}</span></p>
                    <p class="text-sm">Lokacija: <span class="font-bold">{{ $selectedBankomat->blokacija_naziv }}&nbsp;{{ $selectedBankomat->blokacija_naziv_sufix }},</span> {{$selectedBankomat->blokacija_adresa}}, {{$selectedBankomat->blokacija_mesto}}, {{$selectedBankomat->r_naziv}}
                    </p> 
                    <p class="text-sm">PIB: <span class="font-bold">{{ $selectedBankomat->pib }}</span></p>
                    <p class="text-sm">Vlasnik: <span class="font-bold">{{$selectedBankomat->vlasnik_naziv}}  {{ $selectedBankomat->vlasnik_naziv_sufix }},</span> {{$selectedBankomat->vlasnik_adresa}}, {{$selectedBankomat->vlasnik_mesto}}</p>
                    <p class="text-sm">Poslednja promena: <span class="font-bold">{{ App\Http\Helpers::datumFormat($selectedBankomat->last_updated) }}</span></p>    
                </div>
            @endif
        </div>
    </div>
</div>