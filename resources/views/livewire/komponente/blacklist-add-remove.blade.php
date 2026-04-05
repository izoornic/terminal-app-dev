<div>
    <div class="font-bold text-xl text-center text-red-400 mb-6 px-4">
        <x-heroicon-o-question-mark-circle class="text-green-600 w-8 h-8 mx-auto" />
        {{$canBlacklistErorr}}
    </div>

    <div class="mt-4">
         @if ($canBlacklist)
         <div>
            <div class="mt-4">
            <p> Dodaj komentar<span class="text-red-500">*</span></p>
            <x-jet-textarea id="newKoment" type="textarea" class="mt-1 block w-full disabled:opacity-50" wire:model.defer="newKoment" />
            @error('newKoment') <span class="error">{{ $message }}</span> @enderror
            </div>
         </div>
            <hr class="my-4" />
            <div class="flex justify-center items-center">
                
                <button class="flex text-lg font-bold bg-sky-100 text-sky-900 uppercase border border-sky-900 rounded-md p-1.5 hover:bg-gray-700 hover:text-white" wire:click="blacklistUpdate()">
                    <x-icon-blacklist-scull class="fill-current w-6 h-6 ml-2"/>
                    <span class="mx-2">{{ $btn_text }}</span>
                </button>
            </div>
            
        @endif
    </div>
</div>
