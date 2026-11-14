<div class="p-6">
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500"></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Opcija</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Opis</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Redosled</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">TID-ova</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Aktivna</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($data as $item)
                                <tr wire:key="acq-opcija-{{ $item->id }}" @class(['bg-gray-50' => $loop->even, 'text-gray-400' => ! $item->aktivna])>
                                    <td class="px-2 py-1">
                                        <button class="mt-1 text-sm text-gray-700 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="showUpdateModal({{ $item->id }})" title="Izmeni">
                                            <x-heroicon-o-pencil-square class="w-5 h-5 mx-2 my-1" />
                                        </button>
                                    </td>
                                    <td @class(['px-1 py-2 font-semibold', 'line-through' => ! $item->aktivna])>{{ $item->naziv }}</td>
                                    <td class="px-1 py-2 text-sm">{{ $item->opis }}</td>
                                    <td class="px-1 py-2">{{ $item->redosled }}</td>
                                    <td class="px-1 py-2">{{ $item->terminali_count }}</td>
                                    <td class="px-1 py-2">
                                        <button wire:click="promeniAktivnost({{ $item->id }})"
                                                wire:confirm="{{ $item->aktivna ? 'Deaktivirati opciju? Neće se više nuditi na TID-ovima, a TID-ovi koji je imaju zadržavaju je.' : 'Aktivirati opciju?' }}"
                                                @class([
                                                    'text-xs uppercase border rounded-md px-2 py-1',
                                                    'border-green-600 text-green-700 hover:bg-green-600 hover:text-white' => $item->aktivna,
                                                    'border-gray-400 text-gray-500 hover:bg-gray-500 hover:text-white' => ! $item->aktivna,
                                                ])>
                                            {{ $item->aktivna ? 'Da' : 'Ne' }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-4 text-sm whitespace-no-wrap" colspan="6">Nema opcija</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3 text-xs text-gray-500">Opcija se ne briše nego deaktivira: ne nudi se više na TID-ovima, a TID-ovi koji je imaju je i dalje vide.</div>

    {{-- NOVA / IZMENI OPCIJU --}}
    <x-jet-dialog-modal wire:model.live="newEditModalVisible">
        <x-slot name="title">
            <div class="flex">
                <x-heroicon-o-adjustments-horizontal class="w-6 h-6 mr-2"/>
                {{ $is_edit ? 'Izmeni opciju' : 'Nova opcija' }}
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                <x-jet-label for="naziv" value="Naziv" />
                <x-jet-input wire:model="naziv" id="naziv" class="block mt-1 w-full" type="text" maxlength="64" />
                @error('naziv') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <x-jet-label for="opis" value="Opis (nije obavezan)" />
                <x-jet-input wire:model="opis" id="opis" class="block mt-1 w-full" type="text" maxlength="255" />
                @error('opis') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <x-jet-label for="redosled" value="Redosled" />
                <x-jet-input wire:model="redosled" id="redosled" class="block mt-1 w-32" type="number" min="1" />
                @error('redosled') <span class="error">{{ $message }}</span> @enderror
            </div>
            <label class="flex items-center gap-2 mt-4 text-sm text-gray-700">
                <input type="checkbox" wire:model="aktivna" class="rounded border-gray-300">
                Aktivna (nudi se na TID-ovima)
            </label>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('newEditModalVisible')" wire:loading.attr="disabled">Otkaži</x-jet-secondary-button>
            <x-jet-button class="ml-2" wire:click="{{ $is_edit ? 'sacuvajIzmene' : 'sacuvajNovu' }}" wire:loading.attr="disabled">Sačuvaj</x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
