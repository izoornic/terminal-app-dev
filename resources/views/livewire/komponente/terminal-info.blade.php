<div class="relative bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
   <div class="flex">
        <div class="py-1">
            <x-icon-terminal class="fill-current w-4 h-4 mr-2" />
        </div>
        
        <div>
            @if($multySelected)
                @foreach ($multiSelectedInfo as $item)
                    <p>Terminal: <span class="font-bold">{{$item->sn}}</span> &nbsp;&nbsp;&nbsp; Staus: <span class="font-bold">{{ $item->ts_naziv }}</span>
                        @if($item->acq_tid) &nbsp;&nbsp;&nbsp; TID: <span class="font-bold">{{ $item->acq_tid }}</span> @endif
                    </p>
                    <p class="text-sm mb-2 border-b-2 border-white">Lokacija: {{ $item->l_naziv }}&nbsp;{{ $item->l_naziv_sufix }}, {{$item->mesto}}</p>
                @endforeach
            @elseif($selectedTerminal)
                <div class="">
                    <div>
                        <p>Terminal: <span class="font-bold">{{$selectedTerminal->sn}}</span> &nbsp;&nbsp;&nbsp; Staus: <span class="font-bold">{{ $selectedTerminal->ts_naziv }}</span></p>
                        <p class="text-sm">Lokacija: <span class="font-bold">{{ $selectedTerminal->l_naziv }}&nbsp;{{ $selectedTerminal->l_naziv_sufix }}</span></p> 
                        <p class="text-sm">Mesto: <span class="font-bold">{{$selectedTerminal->mesto}}</span></p>
                        <p class="text-sm">Model: <span class="font-bold">{{ $selectedTerminal->treminal_model }}</span> | Proizvođač: <span class="font-bold">{{$selectedTerminal->treminal_proizvodjac}}</span></p>
                        <p class="text-sm">
                            Distributer: <span class="font-bold">{{ $selectedTerminal->distributer_naziv }}</span>
                            @if($selectedTerminal->campagin_name) | Kampanja: <span class="font-bold">{{$selectedTerminal->campagin_name}}</span> @endif
                        </p>
                        <p class="text-sm">PIB: <span class="font-bold">{{ $selectedTerminal->pib }}</span></p>
                        <p class="text-sm">Poslednja promena: <span class="font-bold">{{ App\Http\Helpers::datumFormat($selectedTerminal->updated_at) }}</span></p>    
                        @if($acq)
                            <div class="mt-2 pt-2 border-t border-sky-300">
                                <p>ACQ TID: <span class="font-bold">{{ $acq['acq_tid'] }}</span>
                                    @if($acq['testni']) <span class="text-sm">(testni)</span> @endif
                                </p>
                                @if($acq['naziv_na_slipu'])
                                    <p class="text-sm">Naziv na slipu: <span class="font-bold">{{ $acq['naziv_na_slipu'] }}</span></p>
                                @endif
                                <p class="text-sm">MID: <span class="font-bold">{{ $acq['acq_mid'] ?? '—' }}</span> | MCC: <span class="font-bold">{{ $acq['mcc'] ?? '—' }}</span>
                                    @if($acq['komunikacija']) | Komunikacija: <span class="font-bold">{{ $acq['komunikacija'] }}</span> @endif
                                </p>
                                @if($acq['datum_instalacije'])
                                    <p class="text-sm">Instaliran (TID): <span class="font-bold">{{ $acq['datum_instalacije'] }}</span></p>
                                @endif
                                @if($acq['naslednik'])
                                    <p class="text-sm">Zatražena prekonfiguracija na: <span class="font-bold">{{ $acq['naslednik'] }}</span></p>
                                @endif
                            </div>
                        @endif
                    </div>
                    @if($selectedTerminal->blacklist)
                        <div class="absolute top-5 right-3">
                            <x-icon-blacklist-scull class="fill-current w-8 h-8" />
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>