<div>
    <livewire:bankomati.komponente.bankomat-info :bankomat_lokacija_id="$modelId" />
    <div class="mt-4">
        <x-jet-label for="bankomat_status" value="Status bankomata" />   
        <select wire:model="bankomat_status" id="" class="block appearance-none w-full bg-gray-100 border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
            @foreach (App\Models\BankomatStatusTip::getAll() as $key => $value)    
                <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
        </select>
        @error('bankomat_status') <span class="error">{{ $message }}</span> @enderror
    </div>
    <div class="mt-4">
        <x-jet-label for="datum_promene" value="Datum promene" />
        <div class="flex">
            <x-jet-input id="datum_promene" type="date" class="mt-1 block" value="{{ $datum_promene }}" wire:model="datum_promene" /> <span class="p-2 mt-2">{{ App\Http\Helpers::datumFormatDanFullYear($datum_promene) }}</span>
        </div>
        @error('datum_promene') <span class="error">{{ $message }}</span> @enderror
        @if($datum_promene_error != '')
            <p class="text-red-500"> {{$datum_promene_error}} </p>
        @endif
    </div>

    <hr class="my-4" />
    <div class="flex justify-center items-center">
        
        <button class="flex text-lg font-bold bg-sky-100 text-sky-900 uppercase border border-sky-900 rounded-md p-1.5 hover:bg-gray-700 hover:text-white" wire:click="newStatus">
            <x-heroicon-c-arrow-path-rounded-square class="w-6 h-6 mr-2"/>
            <span class="mx-2">Promeni status</span>
        </button>
    </div>
</div>
