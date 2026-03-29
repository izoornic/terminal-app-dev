<div>
     @if(count($historyData))
        <div class="ml-6 flex relative mb-4">
            <div class="flex items-center gap-2"><span class="flex justify-center items-center w-6 h-6 bg-sky-100 rounded-full ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900">  <x-icon-history-active class="fill-current w-3 h-3 mr-0" /></span> <span>- Aktivni na lokaciji</span>
            </div>
            <div class="pl-4 flex items-center gap-2"><span class="flex justify-center items-center w-6 h-6 bg-sky-100 rounded-full ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900">  <x-icon-history-past class="fill-orange-600 w-3 h-3 mr-0" /></span> <span>- Istorija terminala</span>
            </div>
        </div>
        <ol class="relative border-l border-gray-200 dark:border-gray-700">
            @foreach($historyData as $item)                 
            <li class="mb-4 ml-6">            
                <span class="flex absolute -left-3 justify-center items-center w-6 h-6 bg-sky-100 rounded-full ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900">
                    @if($item->izvor == 'aktivan')
                        <x-icon-history-active class="fill-current w-3 h-3 mr-0" />
                    @else
                        <x-icon-history-past class="fill-orange-600 w-3 h-3 mr-0" />
                    @endif
                </span>
                <h3 class="flex items-center mb-1 text-lg font-semibold text-gray-900 dark:text-white"> - <span class="bg-sky-100 text-sky-900 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-blue-200 dark:text-blue-800 ml-3">{{ App\Http\Helpers::datumFormat($item->updated_at) }}</span></h3>
                <p class="mb-0 text-base font-normal text-gray-500 dark:text-gray-400">Terminal: <span class="font-bold">{{ $item->terminal_sn }}</span></p>
                <p class="mb-0 text-base font-normal text-gray-500 dark:text-gray-400">Status: <span class="font-bold">{{ $item->status_naziv }}</span></p>
                <p class="mb-2 text-base font-normal text-gray-500 dark:text-gray-400">
                        @if($item->distributer != null)Distributer: <span class="font-bold"> {{ $item->distributer }} </span> @endif
                </p>
            </li>
            @endforeach
        </ol>
    @endif
</div>
