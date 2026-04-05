<div>
    @if(count($historyData))
        <ol class="relative border-l border-gray-200 dark:border-gray-700">
            @foreach($historyData as $item)                 
            <li class="mb-4 ml-6">            
                <span class="flex absolute -left-3 justify-center items-center w-6 h-6 bg-sky-100 rounded-full ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900">
                    @if($item->tabela == 'tlh')
                        <x-heroicon-o-calendar class="w-3 h-3" />
                    @else
                        <x-icon-tiket class="fill-orange-600 w-3 h-3" />
                    @endif
                </span>
                @if($item->tabela == 'tlh')
                    <h3 class="flex items-center mb-1 text-lg font-semibold text-gray-900 dark:text-white"> - <span class="bg-sky-100 text-sky-900 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-blue-200 dark:text-blue-800 ml-3">{{ App\Http\Helpers::datumFormat($item->updated_at) }}</span></h3>
                    <p class="mb-0 text-base font-normal text-gray-500 dark:text-gray-400">Status: <span class="font-bold">{{ $item->status_naziv }}</span></p>
                    <p class="mb-0 text-base font-normal text-gray-500 dark:text-gray-400">Lokacija: <span class="font-bold">{{ $item->lokacija }} , {{ $item->mesto }}</span> </p>
                    @if($item->distributer != null)
                    <p class="mb-0 text-base font-normal text-gray-500 dark:text-gray-400" >
                       Distributer: <span class="font-bold"> {{ $item->distributer }} </span> 
                       @if($item->campagin != null) | Kampanja: <span class="font-bold"> {{ $item->campagin }} </span> 
                       @endif
                    </p>
                    @endif
                @else
                    <h3 class="flex items-center mb-1 text-lg font-semibold text-gray-900 dark:text-white"> - <span class="bg-sky-100 text-sky-900 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-blue-200 dark:text-blue-800 ml-3">{{ App\Http\Helpers::datumFormat($item->updated_at) }}</span></h3>
                    <p class="mb-0 text-base font-normal text-gray-500 dark:text-gray-400"><span class="font-bold"><x-jet-nav-link href="{{ route( 'tiketview', ['id' => $item->lokacija] ) }}">Tiket #{{ $item->lokacija }}</span></x-jet-nav-link> , {{ $item->mesto }} : {{ $item->dodeljen }}</p>
                    <p class="mb-2 text-base font-normal text-gray-500 dark:text-gray-400">Opis kvara: <span class="font-bold">{{ $item->user_ime }}</span></p>
                @endif
            </li>
            @endforeach
        </ol>
    @endif
</div>
