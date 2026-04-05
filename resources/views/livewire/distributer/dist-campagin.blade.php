<div class="p-6">
    {{-- The data table --}}
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-2 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"></th>
                                <th class="px-2 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Datum</th>
                                <th class="px-2 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Naziv</th>
                                <th class="px-2 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Opis</th>
                                <th class="px-2 py-3 bg-gray-50 text-center text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-2 py-3 bg-gray-50 text-center text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr>
                                        <td class="px-2 py-2"><button class="p-2 text-sm relative text-gray-500 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="updateShowModal({{ $item->id }})" title="Izmeni kampanju">
                                                <x-heroicon-o-pencil-square class="w-5 h-5" />
                                            </button></td>
                                        <td class="px-2 py-2">{{ App\Http\Helpers::datumFormatDan($item->created_at) }}</td>
                                        <td class="px-2 py-2">{{ $item->campagin_name }}</td>
                                        <td class="px-2 py-2">{{ $item->campagin_description }}</td>
                                        <td class="px-2 py-2 text-center">
                                            @if($item->active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktivna</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-500">Neaktivna</span>
                                            @endif
                                        </td>
                                        <td class="px-2 py-2 text-right whitespace-nowrap">
                                           

                                            <x-jet-secondary-button class="ml-1" wire:click="toggleActive({{ $item->id }})" title="{{ $item->active ? 'Deaktiviraj kampanju' : 'Aktiviraj kampanju' }}">
                                                @if($item->active)
                                                    <svg class="fill-green-500 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zM337 209L209 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L303 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/></svg>
                                                @else
                                                    <svg class="fill-gray-400 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M384 80c8.8 0 16 7.2 16 16V416c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V96c0-8.8 7.2-16 16-16H384zM64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64z"/></svg>
                                                @endif
                                            </x-jet-secondary-button>

                                            <button class="p-2 text-sm bg-red-500 relative text-white uppercase border rounded-md hover:bg-white hover:text-red-500 ml-1" wire:click="deleteShowModal({{ $item->id }})" title="Obriši kampanju">
                                                <x-heroicon-o-trash class="w-5 h-5" />
                                            <button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="px-6 py-4 text-sm whitespace-no-wrap" colspan="4">Nema rezultata</td>
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

    {{-- Modal Form --}}
    <x-jet-dialog-modal wire:model="modalFormVisible">
        <x-slot name="title">
            @if($is_update)
                {{ __('Izmeni kampanju') }}
            @else
                {{ __('Dodaj novu kampanju') }}
            @endif
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                <x-jet-label for="campagin_name" value="{{ __('Naziv kampanje') }}" />
                <x-jet-input wire:model="campagin_name" id="campagin_name" class="block mt-1 w-full" type="text" />
                @error('campagin_name') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <x-jet-label for="campagin_description" value="{{ __('Opis kampanje') }}" />
                <x-jet-input wire:model="campagin_description" id="campagin_description" class="block mt-1 w-full" type="text" />
                @error('campagin_description') <span class="error">{{ $message }}</span> @enderror
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalFormVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>

            @if($is_update)
                <x-jet-danger-button class="ml-2" wire:click="update" wire:loading.attr="disabled">
                    {{ __('Izmeni') }}
                </x-jet-danger-button>
            @else
                <x-jet-danger-button class="ml-2" wire:click="create" wire:loading.attr="disabled">
                    {{ __('Dodaj kampanju') }}
                </x-jet-danger-button>
            @endif
        </x-slot>
    </x-jet-dialog-modal>

    {{-- Delete Modal --}}
    <x-jet-dialog-modal wire:model="modalConfirmDeleteVisible">
        <x-slot name="title">
            {{ __('Obriši kampanju') }}
        </x-slot>

        <x-slot name="content">
            <div class="mb-4">Da li ste sigurni da želite da obrišete kampanju:</div>
            <div>Naziv: <span class="font-bold">{{ $campagin_name }}</span></div>
            <div>Opis: {{ $campagin_description }}</div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalConfirmDeleteVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
            <x-jet-danger-button class="ml-2" wire:click="delete" wire:loading.attr="disabled">
                {{ __('Obriši kampanju') }}
            </x-jet-danger-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
