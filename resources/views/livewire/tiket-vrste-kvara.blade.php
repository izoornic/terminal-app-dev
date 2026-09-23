<div class="p-6">
    {{-- The data table --}}
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500"></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Vrsta kvara</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Nadležnost (online prijava)</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Redosled</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Tiketa</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Ukupno: {{ $data->total() }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        {{-- SEARCH ROW --}}
                            <tr class="bg-orange-50">
                                <td> <x-heroicon-o-funnel class="mx-auto text-orange-600 w-4 h-4" /> </td>
                                <td class="px-2"><x-jet-input wire:model.live="searchNaziv" id="" class="block bg-orange-50 w-full" type="text" placeholder="Vrsta kvara" /></td>
                                <td class="px-2">
                                    <select wire:model.live="searchNadleznost" id="" class="block appearance-none bg-orange-50 w-full border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">---</option>
                                        @foreach ($nadleznosti as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <!-- DATA  -->
                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr wire:key="vrsta-kvara-{{ $item->id }}" @if($loop->even) class="bg-gray-50" @endif >
                                        <td class="px-2 py-1">
                                            <button class="mt-2 text-sm text-gray-700 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="showUpdateModal({{ $item->id }})" title="Izmeni">
                                                <x-heroicon-o-pencil-square class="w-5 h-5 mx-2 my-1" />
                                            </button>
                                        </td>
                                        <td class="px-1 py-2">{{ $item->tok_naziv }}</td>
                                        <td class="px-1 py-2">{{ $nadleznosti[$item->tok_dodela_nadleznostiId] ?? '---' }}</td>
                                        <td class="px-1 py-2">{{ $item->list_order }}</td>
                                        <td class="px-1 py-2">{{ $item->tiketi_count }}</td>
                                        <td class="px-1 py-1 text-center">
                                            <button class="text-sm bg-white text-red-600 uppercase border border-red-600 rounded-md p-1.5 hover:bg-red-600 hover:text-white" wire:click="showDeleteModal({{ $item->id }})" title="Obriši">
                                                <x-heroicon-o-trash class="w-4 h-4 mr-0" />
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="px-6 py-4 text-sm whitespace-no-wrap" colspan="6">Nema rezultata</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        {{ $data->links() }}
    </div>

    {{-- NOVA / IZMENI VRSTU KVARA ############################################### --}}
    <x-jet-dialog-modal wire:model.live="newEditModalVisible">
        <x-slot name="title">
            <div class="flex">
                <x-heroicon-o-wrench-screwdriver class="w-6 h-6 mr-2"/>
                @if ($is_edit) Izmeni vrstu kvara
                @else
                    Nova vrsta kvara
                @endif
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                <x-jet-label for="tok_naziv" value="Naziv" />
                <x-jet-input wire:model="tok_naziv" id="tok_naziv" class="block mt-1 w-full" type="text" maxlength="128" />
                @error('tok_naziv') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="mt-4">
                <x-jet-label value="Nadležnost kod online prijave" />
                @foreach ($nadleznosti as $key => $value)
                    <label class="flex items-center mt-2 text-sm text-gray-700">
                        <input type="radio" wire:model="tok_dodela_nadleznostiId" name="tok_dodela_nadleznostiId" value="{{ $key }}" class="mr-2">
                        {{ $value }}
                    </label>
                @endforeach
                @error('tok_dodela_nadleznostiId') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="mt-4">
                <x-jet-label for="list_order" value="Redosled u listi" />
                <x-jet-input wire:model="list_order" id="list_order" class="block mt-1 w-32" type="number" min="1" />
                @error('list_order') <span class="error">{{ $message }}</span> @enderror
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('newEditModalVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>

            @if ($is_edit)
                <x-jet-button class="ml-2" wire:click="updateVrstaKvara" wire:loading.attr="disabled">
                    {{ __('Sačuvaj izmene') }}
                </x-jet-button>
            @else
                <x-jet-button class="ml-2" wire:click="saveNewVrstaKvara" wire:loading.attr="disabled">
                    {{ __('Sačuvaj') }}
                </x-jet-button>
            @endif
        </x-slot>
    </x-jet-dialog-modal>

    {{-- DELETE ############################################### --}}
    <x-jet-dialog-modal wire:model.live="deleteModalVisible">
        <x-slot name="title">
            <div class="flex">
                <x-heroicon-o-trash class="w-6 h-6 mr-2"/>
                Obriši vrstu kvara
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                @if ($broj_tiketa > 0)
                    <h2 class="font-bold text-red-600">Vrsta kvara je korišćena na {{ $broj_tiketa }} tiketa i ne može biti obrisana.</h2>
                @else
                    <h2 class="font-bold text-red-600">Da li ste sigurni da želite da obrišete vrstu kvara?</h2>
                    @if ($broj_akcija > 0)
                        <p class="mt-2 text-sm text-gray-600">Biće obrisani i koraci za servisera vezani za ovu vrstu kvara ({{ $broj_akcija }}).</p>
                    @endif
                @endif

                <p class="mt-2 text-sm text-gray-600">Vrsta kvara: <span class="font-bold">{{ $tok_naziv }}</span></p>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('deleteModalVisible')" wire:loading.attr="disabled">
                Otkaži
            </x-jet-secondary-button>
            @if ($broj_tiketa == 0)
                <x-jet-danger-button class="ml-2" wire:click="deleteVrstaKvara" wire:loading.attr="disabled">
                    Obriši vrstu kvara
                </x-jet-danger-button>
            @endif
        </x-slot>
    </x-jet-dialog-modal>
</div>
