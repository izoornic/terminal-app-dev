<div class="relative bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-4" role="alert">
   <div class="flex">
        <div class="py-1">
            <x-heroicon-o-tag class="w-6 h-6 mr-1" />
        </div>
        
        <div class="w-full">
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
    <div>
        @if($kontaktOsobe->count() && $multySelected == false)
            <button onClick='if (document.getElementById("kontaktOsobe").style.display == "none") {
                                    document.getElementById("kontaktOsobe").style.display = "block";
                                }else{
                                    document.getElementById("kontaktOsobe").style.display = "none";
                                }' 
                    class="mt-1 text-sm text-gray-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white">
                    <x-heroicon-o-user-card class="w-8 h-8 -m-1 -mb-3" />
            </button> 
        
            <div id="kontaktOsobe" style="display: none">
                <table class="min-w-full divide-y-2 divide-black">
                    @foreach ($kontaktOsobe as $osoba)
                        <tr class="bg-sky-50 py-1">
                            <td class="px-2 font-bold">{{$osoba->ime}}</td>
                            <td class="px-2"><x-heroicon-o-phone class="w-4 h-4"/></td>  
                            <td class="px-2 font-bold">{{$osoba->telefon}}</td>
                            <td><x-heroicon-o-envelope class="w-4 h-4"/></td>
                            <td class="px-2 font-bold">{{$osoba->email}}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </div>  
</div>