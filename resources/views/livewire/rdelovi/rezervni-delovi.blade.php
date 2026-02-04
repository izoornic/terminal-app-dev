<div class="p-6">
    <livewire:komponente.session-flash-message />
    {{-- Filteri --}}
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pretraga</label>
                <input type="text" 
                    wire:model.debounce.300ms="searchNaziv" 
                    placeholder="Naziv"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pretraga</label>
                <input type="text" 
                    wire:model.debounce.300ms="searchSifra" 
                    placeholder="Šifra"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>


            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokacija</label>
                <select wire:model="locationId" 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Sve lokacije</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->l_naziv }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategorija</label>
                <select wire:model="categoryId" 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Sve kategorije</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->model }}, {{ $category->proizvodjac }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <label class="flex items-center">
                    <input type="checkbox" wire:model="showLowStockOnly" 
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Samo nisko stanje</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deo</th>
                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategorija</th>
                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokacija</th>
                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ukupno</th>
                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rezervisano</th>
                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dostupno</th>
                    <th colspan="4" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Akcija</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($stocks as $stock)
                    <tr>
                        <td class="px-2 py-2 whitespace-nowrap">
                            <button class="text-sm text-gray-700 uppercase border rounded-md p-1 hover:bg-gray-700 hover:text-white" wire:click="updateShowModal({{ $stock->tipid }})" title="Uredi">
                                <x-heroicon-o-pencil-square class="w-5 h-5 mr-0" />
                            </button>
                        </td>
                        <td class="px-2 py-2 text-center">
                            @if($stock->kolicina_dostupna <= 0)
                                <x-heroicon-m-minus-circle class="w-6 h-6 text-red-800" />
                            @elseif($stock->kolicina_dostupna <= $stock->partType->min_kolicina)
                                <x-icon-lov-stock class="w-6 h-6 fill-amber-400" />
                            @else
                                <x-heroicon-c-check-circle class="w-6 h-6 text-green-600"/>
                            @endif
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $stock->partType->naziv }}</div>
                            <div class="text-sm text-gray-500">{{ $stock->partType->sifra }}</div>
                        </td>
                        <td>
                            <div class="text-sm font-medium text-gray-900">{{ $stock->kategorija }}</div>
                            <div class="text-sm text-gray-500"> {{ $stock->proizvodjac }}</div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $stock->location->l_naziv }}</div>
                            <div class="text-sm text-gray-500"> {{ $stock->location->region->r_naziv }}</div>
                        </td>
                        <td class="px-2 py-2 text-sm text-gray-900">
                            {{ $stock->kolicina_ukupno }}
                        </td>
                        <td class="px-2 py-2 text-sm text-gray-900">
                            {{ $stock->kolicina_rezervisana }}
                        </td>
                        <td class="px-2 py-2 text-sm font-semibold text-gray-900">
                            {{ $stock->kolicina_dostupna }}
                        </td>
                        
                        <td class="px-1 py-1">
                            <button class="text-sm text-gray-700 uppercase border rounded-md p-0.5 hover:bg-gray-700 hover:text-white" wire:click="addStockShowModal({{ $stock->id }})" title="Dodaj količinu">
                                <x-heroicon-o-arrow-down-on-square-stack class="w-6 h-6 mr-0"/>
                            </button>
                        </td>
                        <td class="px-1 py-1">
                            <button class="text-sm text-gray-700 uppercase border rounded-md p-0.5 hover:bg-gray-700 hover:text-white" wire:click="removeStockShowModal({{ $stock->id }})" title="Oduzmi količinu">
                                <x-heroicon-o-arrow-up-on-square-stack class="w-6 h-6 mr-0"/>
                            </button>
                        </td>
                        <td class="px-1 py-1">
                            <button class="text-sm text-gray-700 uppercase border rounded-md p-1 hover:bg-gray-700 hover:text-white" wire:click="moveStockShowModal({{ $stock->id }})" title="Premesti">
                                <x-heroicon-o-arrows-right-left class="w-6 h-6 mr-0"/>
                            </button>
                        </td>
                        <td class="px-1 py-1">
                            <button class="text-sm text-sky-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white" wire:click="stocklHistoryShowModal({{ $stock->tipid }})" title="Istorija transvera">
                                <x-icon-history class="fill-current w-4 h-4 mr-0"/>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-2 py-2 text-center text-gray-500">
                            Nema podataka za prikaz
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $stocks->links() }}
    </div>
   {{--  ######################################## ALL IN ONE MODAL #################################################### --}}
    <x-jet-dialog-modal wire:model="modalVisible">
        <x-slot name="title">
            {{ $modal_title }}
        </x-slot>

        <x-slot name="content">
            @if($modal_type == 'dodaj_novi_deo')
                <livewire:rdelovi.komponente.novi-rezervni-deo :key="time()" />
            @elseif($modal_type == 'dodaj_kolicinu')
                <livewire:rdelovi.komponente.dodaj-kolicinu :stock_id="$stock_id" :key="time()" />
            @elseif($modal_type == 'oduzmi_kolicinu')
                <livewire:rdelovi.komponente.oduzmi-kolicinu :stock_id="$stock_id" :key="time()" />
            @elseif($modal_type == 'premesti_kolicinu')
                <livewire:rdelovi.komponente.premesti-deo :stock_id="$stock_id" :key="time()" />
            @elseif($modal_type == 'azuriraj_rezervni_deo')
                <livewire:rdelovi.komponente.novi-rezervni-deo :is_edit="true" :part_id="$part_id" :key="time()" />
            @elseif($modal_type == 'prikazi_istoriju')
                <livewire:rdelovi.komponente.rezervni-deo-histroy :part_id="$part_id" :key="time()" />
            @endif
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalVisible')" wire:loading.attr="disabled">
                    {{ __('Otkaži') }}
            </x-jet-secondary-button>
        </x-slot>
     </x-jet-dialog-modal>
    
</div>
