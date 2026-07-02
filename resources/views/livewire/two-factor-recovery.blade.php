<div class="p-6">
    {{-- Prikaz generisanih recovery kodova --}}
    @if (count($recoveryCodes))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4"
             x-data="{ codes: @js($recoveryCodes) }">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-green-800">
                        Novi recovery kodovi za: {{ $generatedForName }}
                    </h3>
                    <p class="mt-1 text-xs text-green-700">
                        Sačuvajte ove kodove i predajte ih korisniku. Stari kodovi više ne važe.
                        Svaki kod se može iskoristiti samo jednom.
                    </p>
                </div>
                <div class="flex space-x-2">
                    <x-jet-secondary-button type="button"
                        x-on:click="navigator.clipboard.writeText(codes.join('\n'))">
                        {{ __('Kopiraj') }}
                    </x-jet-secondary-button>
                    <x-jet-secondary-button type="button" wire:click="$set('recoveryCodes', [])">
                        {{ __('Zatvori') }}
                    </x-jet-secondary-button>
                </div>
            </div>

            <div class="mt-3 grid max-w-xl grid-cols-2 gap-2 rounded-md bg-white p-3 font-mono text-sm text-gray-700">
                @foreach ($recoveryCodes as $code)
                    <div wire:key="code-{{ $loop->index }}">{{ $code }}</div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($actionMessage)
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ $actionMessage }}
        </div>
    @endif

    {{-- Pretraga --}}
    <div class="mb-4 flex items-center justify-between">
        <div class="w-full max-w-sm">
            <x-jet-input type="text" class="block w-full" wire:model.live.debounce.400ms="search"
                placeholder="{{ __('Pretraga po imenu ili email-u...') }}" />
        </div>
    </div>

    {{-- Tabela korisnika sa uključenom 2FA --}}
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Ime</th>
                                <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Email</th>
                                <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase leading-4 tracking-wider text-gray-500"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr wire:key="user-{{ $item->id }}">
                                        <td class="px-6 py-2">{{ $item->name }}</td>
                                        <td class="px-6 py-2">{{ $item->email }}</td>
                                        <td class="px-6 py-2 flex justify-end">
                                            <x-jet-button wire:click="confirmGenerateShowModal({{ $item->id }})">
                                                {{ __('Generiši nove kodove') }}
                                            </x-jet-button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="px-6 py-4 text-sm whitespace-no-wrap" colspan="3">
                                        {{ __('Nema korisnika sa uključenom 2FA zaštitom.') }}
                                    </td>
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

    {{-- Modal potvrde --}}
    <x-jet-dialog-modal wire:model.live="confirmingGenerationVisible">
        <x-slot name="title">
            {{ __('Generisanje recovery kodova') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Generisanjem novih recovery kodova za korisnika') }}
            <span class="font-semibold">{{ $userName }}</span>
            {{ __('svi postojeći kodovi prestaju da važe. Da li ste sigurni?') }}
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('confirmingGenerationVisible')" wire:loading.attr="disabled">
                {{ __('Odustani') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-2" wire:click="generateRecoveryCodes" wire:loading.attr="disabled">
                {{ __('Generiši kodove') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
