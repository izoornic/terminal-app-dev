<div>
     <label for="lokacija">Rezervni deo:</label>
    <livewire:rdelovi.komponente.rzervni-deo-info :partType_id="$partStock->part_type_id" :key="time()" />
    <div class="mt-4">
        <label for="lokacija">Lokacija:</label>
        <livewire:komponente.lokacija-info :lokacija_id="$partStock->lokacija_id" :key="time()"/>
    </div>
    
    <div class="mt-4">
        <label for="trenutno sttanje">Trenutno stanje: </label>
        <span class="font-bold mr-2">{{ $partStock->kolicina_dostupna }}</span>
    </div>

    <div class="mt-4 form-group">
        <label for="kolicina">Dodaj na stanje: </label>
        <input type="number" class="form-control" wire:model.defer="kolicina" id="kolicina" ><br />
        @error('kolicina') <span class="error">{{ $message }}</span> @enderror
    </div>

    <hr class="my-4" />
    <div class="flex justify-center items-center">
        
        <button class="flex text-lg font-bold bg-sky-100 text-sky-900 uppercase border border-sky-900 rounded-md p-1.5 hover:bg-gray-700 hover:text-white" wire:click="save()">
           <x-heroicon-o-arrow-down-on-square-stack class="w-6 h-6"/>
            <span class="mx-2">Dodaj na stanje</span>
        </button>
    </div>
</div>
