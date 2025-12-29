<div>
    <div class="mt-4">
        <x-jet-label for="kategorija" value="Kategorija:" />   
        @if($is_edit)
            <span class="font-bold"></span> {{ $kategorija_naziv }} </span>
        @else
            <select wire:model="kategorija" id="" class="block appearance-none w-full bg-gray-100 border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                    <option value="">---</option>
                @foreach (App\Models\TerminalTip::tipoviList() as $key => $value)    
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
            @error('kategorija') <span class="error">{{ $message }}</span> @enderror
        @endif
    </div>
    <div class="mt-4">
        <x-jet-label for="sifra" value="Šifra:" />   
        <x-jet-input wire:model="sifra" id="" class="block mt-1 w-full" type="text" />
        @error('sifra') <span class="error">{{ $message }}</span> @enderror
    </div>
    <div class="mt-4">
        <x-jet-label for="naziv" value="Naziv:" />   
        <x-jet-input wire:model="naziv" id="" class="block mt-1 w-full" type="text" />
        @error('naziv') <span class="error">{{ $message }}</span> @enderror
    </div>
    <div class="mt-4">
        <x-jet-label for="opis" value="Opis:" />   
        <x-jet-textarea wire:model="opis" id="" class="block mt-1 w-full" type="text" />
        @error('opis') <span class="error">{{ $message }}</span> @enderror
    </div>
    <div class="mt-4">
        <x-jet-label for="cena" value="Cena:" />   
        <x-jet-input wire:model="cena" id="" class="block mt-1 w-full" type="number" />
        @error('cena') <span class="error">{{ $message }}</span> @enderror
    </div>
    <div class="mt-4">
        <x-jet-label for="jedinica_mere" value="Jedinica mere:" />   
        <select wire:model="jedinica_mere" id="" class="block appearance-none w-full bg-gray-100 border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
            @foreach ($jedinica_mere_list as $value)    
                <option value="{{ $value }}">{{ $value }}</option>
            @endforeach
        </select>
        @error('jedinica_mere') <span class="error">{{ $message }}</span> @enderror
    </div>
    <div class="mt-4">
        <x-jet-label for="min_kolcina" value="Minimalna količina:" />
        <x-jet-input wire:model="min_kolcina" id="" class="block mt-1 w-full" type="number" />
        @error('min_kolcina') <span class="error">{{ $message }}</span> @enderror
    </div>
    @if(!$is_edit)
        <div class="mt-4">
            <x-jet-label for="kolicina" value="Početna količina:" />   
            <x-jet-input wire:model="kolicina" id="" class="block mt-1 w-full" type="number" />
            @error('kolicina') <span class="error">{{ $message }}</span> @enderror
        </div>
    @endif
    <div class="mt-4">
         <label class="flex items-center">
            <input type="checkbox" wire:model="aktivan" 
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <span class="ml-2 text-sm text-gray-700">Aktivan</span>
        </label>
    </div>

    @if(!$is_edit)
        <div class="mt-4">
            <x-jet-label for="lokacija" value="Lokacija:" />
            @if($lokacija)
                <livewire:komponente.lokacija-info :lokacija_id="$lokacija" /> 
            @else
                <livewire:komponente.izbor-lokacije :tipovi_lokacija="[1, 2]" />
            @endif
            @error('lokacija') <span class="error">{{ $message }}</span> @enderror
        </div>
    @endif

    <hr class="my-4" />
    <div class="flex justify-center items-center">
        
        <button class="flex text-lg font-bold bg-sky-100 text-sky-900 uppercase border border-sky-900 rounded-md p-1.5 hover:bg-gray-700 hover:text-white" wire:click="newRezervniDeo()">
            <x-heroicon-c-cog class="fill-current w-6 h-6 ml-2"/>
            @if($is_edit)
                <span class="mx-2">Uredi rezervni deo</span>
            @else
                <span class="mx-2">Dodaj novi rezervni deo</span>
            @endif
        </button>
    </div>
</div>
