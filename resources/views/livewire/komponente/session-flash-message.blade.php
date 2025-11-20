<div> 
    @if($status)
        <div class="w-full">
            <div id="toast-success" class="mx-auto flex justify-between items-center w-full max-w-lg p-4 mb-4 text-gray-800 bg-green-50 rounded-lg shadow dark:text-gray-400 dark:bg-gray-800" role="alert">
                <div class="flex items-center">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-50 rounded-lg dark:bg-red-800 dark:text-green-200">
                        <x-heroicon-m-check-circle class="w-5 h-5"/>
                        <span class="sr-only">Check icon</span>
                    </div>
                    <div class="ms-3 text-sm font-normal">{{ $status }}</div>
                </div>

                <button wire:click="closeAlert" type="button" class="ms-auto -mx-1.5 -my-1.5 bg-green-50 text-gray-800 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-dismiss-target="#toast-success" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <x-heroicon-o-x-mark class="w-6 h-6/" />
                </button>
            </div>
        </div>
    @endif
    @if($error)
        <div class="w-full">
            <div id="toast-success" class="mx-auto flex justify-between items-center w-full max-w-lg p-4 mb-4 text-gray-800 bg-red-100 rounded-lg shadow dark:text-gray-400 dark:bg-gray-800" role="alert">
                <div class="flex items-center">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-600 bg-red-100 rounded-lg dark:bg-red-800 dark:text-green-200">
                        <x-heroicon-c-exclamation-triangle class="w-6 h-6" />
                        <span class="sr-only">Check icon</span>
                    </div>
                    <div class="ms-3 text-sm font-normal">{{ $error }}</div>
                </div>
                <button wire:click="closeAlert" type="button" class="ms-auto -mx-1.5 -my-1.5 bg-red-100 text-gray-800 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-dismiss-target="#toast-success" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <x-heroicon-o-x-mark class="w-6 h-6/" />
                </button>
            </div>
        </div>
    @endif
</div>
