<div>
    @if(count($historyData))
        <ol class="relative border-l border-gray-200 dark:border-gray-700">
            @foreach($historyData as $item)                 
            <li class="mb-4 ml-6">            
                <span class="flex absolute -left-3 justify-center items-center w-6 h-6 bg-sky-100 rounded-full ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900">
                    @if($item->vrsta_akcije == 1)
                        {{-- AKCIJA PREMESTANJA ILI STATUSA --}}
                        <x-heroicon-o-calendar-days class="w-3 h-3"/>
                    @elseif($item->vrsta_akcije == 2)
                        {{-- AKCIJA TIKET --}}
                        <x-icon-ticket-plus class="fill-orange-600 w-3 h-3"/>
                    @elseif($item->vrsta_akcije == 3)
                        {{-- AKCIJA NAPLATE --}}
                        @if($item->history_action_id == 5)  
                            <x-heroicon-o-currency-euro class="w-4 h-4 text-green-600"/> 
                        @else 
                            <x-heroicon-o-currency-euro class="w-4 h-4 text-red-600" /> 
                        @endif
                    @endif
                </span> 
                <h3 class="flex items-center mb-1 text-lg font-semibold text-gray-900 dark:text-white"> - 
                    <span class="bg-sky-100 text-sky-900 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-blue-200 dark:text-blue-800 ml-3">{{ App\Http\Helpers::datumFormat($item->updated_at) }}</span> - 
                    <span class="text-sky-900 text-sm font-medium py-0.5 rounded dark:bg-blue-200 dark:text-blue-800 ml-1">{{$item->akcija}}</span>
                </h3>
                @if($item->vrsta_akcije == 1)
                    {{-- AKCIJA PREMESTANJA ILI STATUSA --}}
                    @if($item->history_action_id == 1 || $item->history_action_id == 2 || $item->history_action_id == 4)
                        <p class="mb-0 text-base font-normal text-gray-500 dark:text-gray-400">
                            Status: <span class="font-bold">{{ $item->status_naziv }}</span>
                        </p>
                    @endif
                    @if($item->history_action_id == 1 || $item->history_action_id == 3 || $item->history_action_id == 4)
                        <p class="mb-2 text-base font-normal text-gray-500 dark:text-gray-400">
                            Lokacija: <span class="font-bold">{{ $item->bl_naziv }} , {{ $item->bl_mesto }}</span>
                        </p>
                    @endif
                @elseif($item->vrsta_akcije == 2)
                    {{-- AKCIJA TIKET --}}
                    <p class="mb-0 text-base font-normal text-gray-500 dark:text-gray-400"><span class="font-bold"><x-jet-nav-link href="{{ route( 'bankomat-tiketview', ['id' => $item->bankomat_tiket_id] ) }}">Tiket #{{ $item->bankomat_tiket_id }}</span></x-jet-nav-link></p>
                    <p class="mb-2 text-base font-normal text-gray-500 dark:text-gray-400">Opis kvara: <span class="font-bold">@if($item->btkt_naziv) {{ $item->btkt_naziv }} @else Ostalo @endif</span></p>
                @elseif($item->vrsta_akcije == 3)
                    {{-- AKCIJA NAPLATE --}}
                    
                @endif
            </li>
            @endforeach
        </ol>
    @endif
</div>
