<div class="relative bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
   <div class="flex">
        <div class="py-1">
            <x-heroicon-o-atm class="w-6 h-6 mr-1" />
        </div>
        
        <div>
            @if($multySelected)
            @foreach ($multiSelectedInfo as $item)
                    <p>Bankomat SN: <span class="font-bold">{{$item->b_sn}}</span> &nbsp;&nbsp;&nbsp; Bankomat ID: <span class="font-bold">{{$item->b_terminal_id}}</span></p>
                    <p>Staus: <span class="font-bold">{{ $item->status_naziv }}</span></p>
                    <p class="text-sm">Model: <span class="font-bold">{{ $item->model }}</span> | Proizvođač: <span class="font-bold">{{$item->proizvodjac}}</span></p>
                    <p class="text-sm mb-2 border-b-2 border-white">Lokacija: {{ $item->blokacija_naziv }}&nbsp;{{ $item->blokacija_naziv_sufix }}, {{$item->blokacija_mesto}}</p>
                @endforeach
            @else
                <div class="">
                    <div>
                        <p>Bankomat SN: <span class="font-bold">{{$selectedBankomat->b_sn}}</span> &nbsp;&nbsp;&nbsp; Bankomat ID: <span class="font-bold">{{$selectedBankomat->b_terminal_id}}</span>&nbsp;&nbsp;&nbsp; Staus: <span class="font-bold">{{$selectedBankomat->status_naziv }}</span></p>
                        <p class="text-sm">Model: <span class="font-bold">{{ $selectedBankomat->model }}</span> | Proizvođač: <span class="font-bold">{{$selectedBankomat->proizvodjac}}</span></p>
                        <p class="text-sm">Lokacija: <span class="font-bold">{{ $selectedBankomat->blokacija_naziv }}&nbsp;{{ $selectedBankomat->blokacija_naziv_sufix }}</span></p> 
                        <p class="text-sm">PIB: <span class="font-bold">{{ $selectedBankomat->pib }}</span></p>
                         <p class="text-sm">Adresa: <span class="font-bold">{{$selectedBankomat->blokacija_adresa}}</span></p>
                        <p class="text-sm">Mesto: <span class="font-bold">{{$selectedBankomat->blokacija_mesto}}</span></p>
                        <p class="text-sm">Region: <span class="font-bold">{{$selectedBankomat->r_naziv}}</span></p>
                        <p class="text-sm">Poslednja promena: <span class="font-bold">{{ App\Http\Helpers::datumFormat($selectedBankomat->last_updated) }}</span></p>    
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>