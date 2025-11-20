<div>
   @foreach($data as $item)
            <div class="flex justify-between bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                <div>
                    <div>Kontakt osoba:</div>
                    <div class="flex">
                        <div class="py-1">
                            <x-heroicon-o-user-card class="w-8 h-8 mr-2" />
                        </div>
                        <div>
                            <p class="font-bold">{{ $item->ime }}</p>
                            <p class="text-sm">{{ $item->telefon }}</p>
                            <p class="text-sm"> {{ $item->email }}</p>
                        </div>
                    </div>
                </div>
                <div>
                    <button class="text-sm bg-white text-red-600 uppercase border border-red-600 rounded-md p-1.5 hover:bg-red-600 hover:text-white" wire:click="removeKontaktOsobu({{ $item->id }})" title="Obriši">
                        <x-heroicon-o-trash class="w-4 h-4 mr-0" />
                    </button>
                </div>
            </div>
    @endforeach
    @if(!$newKontaktOsobaVisible)
        <div class="mt-4">
            <button class="flex text-sky-800  border rounded-md border-sky-800 pt-2 mr-2 hover:bg-sky-800 hover:text-white" wire:click="addKontaktOsobuVisible()" title="Dodaj kontakt osobu" >
                <x-heroicon-o-user-plus class="w-6 h-6 mx-2 mb-1 -mt-1" />
                <span class="mb-1 mr-2">Dodaj kontakt osobu</span>
            </button>
        </div>
    @else
        <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mt-6 mb-6">
            <hr />
            <p class="flex">
                <x-heroicon-o-user-card class="mt-1 w-8 h-8" />
                Kontakt osoba:
            </p>
            <div class="mt-4">
                <x-jet-label for="kontakt_name" value="Ime" />
                <x-jet-input wire:model="kontakt_name" id="" class="block mt-1 w-full" type="text" />
                @error('kontakt_name') <span class="error">{{ $message }}</span> @enderror
            </div> 
            <div class="mt-4">
                <x-jet-label for="kontakt_tel" value="Broj telefona" />
                <div class="mt-4 flex rounded-md shadow-sm mb-4">
                    <x-jet-input wire:model="kontakt_tel" id="" class="block mt-1 w-full" type="text" />
                    @error('kontakt_tel') <span class="error">{{ $message }}</span>@enderror
                </div> 
            </div>
            <div class="mt-4">
                <x-jet-label for="kontakt_email" value="e-mail" />
                <x-jet-input wire:model="kontakt_email" id="" class="block mt-1 w-full" type="text" />
                @error('kontakt_email') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="flex justify-center items-center mt-4">
            
                <button class="flex text-lg font-bold bg-sky-100 text-sky-900 uppercase border border-sky-900 rounded-md p-1.5 hover:bg-gray-700 hover:text-white" wire:click="addKontaktOsobu()" title="Dodaj kontakt osobu">
                   <x-heroicon-o-user-plus class="w-6 h-6 mx-2 mb-1 -mt-2" />
                    <span class="mx-2">Dodaj kontakt osobu</span>
                </button>
            </div>
        </div>
    @endif
</div>
