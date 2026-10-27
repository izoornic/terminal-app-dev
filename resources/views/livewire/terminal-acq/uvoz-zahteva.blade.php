<div class="p-6">
    {{-- REZULTAT POSLEDNJEG UPISA --}}
    @if ($rezultat)
        <div class="mb-6 p-4 border border-green-300 bg-green-50 rounded-md text-sm text-green-800">
            <div class="flex items-center font-semibold">
                <x-heroicon-o-check-circle class="w-5 h-5 mr-2" />
                Zahtev je upisan.
            </div>
            <div class="mt-1">
                TID-ova: {{ $rezultat['tidova'] }}, novih lokacija: {{ $rezultat['lokacija'] }}, novih kontakata: {{ $rezultat['kontakata'] }}.
            </div>
            @foreach ($rezultat['upozorenja'] as $upozorenje)
                <div class="mt-1 text-amber-700">{{ $upozorenje }}</div>
            @endforeach
        </div>
    @endif

    @if ($greska)
        <div class="mb-6 p-4 border border-red-300 bg-red-50 rounded-md text-sm text-red-700">
            <div class="flex items-center font-semibold">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 mr-2" />
                Ništa nije upisano.
            </div>
            <div class="mt-1">{{ $greska }}</div>
        </div>
    @endif

    {{-- UPLOAD --}}
    @if (! $redovi)
        <form wire:submit="procitaj" class="flex flex-wrap items-end gap-4">
            <div>
                <x-jet-label for="fajl" value="Excel zahteva banke (instalacija ili prekonfiguracija)" />
                <input wire:model="fajl" id="fajl" type="file" accept=".xlsx,.xls" class="mt-1 block text-sm text-gray-700" />
                <x-jet-input-error for="fajl" class="mt-2" />
            </div>
            <x-jet-button type="submit" wire:loading.attr="disabled" wire:target="fajl,procitaj">
                <x-heroicon-o-document-magnifying-glass class="w-4 h-4 mr-2" />
                Učitaj i proveri
            </x-jet-button>
            <span wire:loading wire:target="fajl,procitaj" class="text-sm text-gray-500">Učitavanje…</span>
        </form>
        <p class="mt-3 text-sm text-gray-500">Učitavanje ništa ne upisuje u bazu; prvo se prikazuje pregled po redu.</p>
    @else
        {{-- PREGLED --}}
        <div class="flex flex-wrap items-end justify-between gap-4 mb-4">
            <div class="text-sm text-gray-700">
                <div class="font-semibold">{{ $nazivFajla }}</div>
                <div>Redova: {{ count($redovi) }}, izabrano za upis: {{ $brojIzabranih }}</div>
            </div>
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <x-jet-label for="datumZahteva" value="Datum zahteva" />
                    <x-jet-input wire:model="datumZahteva" id="datumZahteva" type="date" class="mt-1 block" />
                    <x-jet-input-error for="datumZahteva" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="regionZaSve" value="Region za sve nove lokacije" />
                    <div class="flex mt-1">
                        <select wire:model="regionZaSve" id="regionZaSve" class="border-gray-300 rounded-md text-sm">
                            <option value="">---</option>
                            @foreach ($regioni as $id => $naziv)
                                <option value="{{ $id }}">{{ $naziv }}</option>
                            @endforeach
                        </select>
                        <x-jet-secondary-button class="ml-2" wire:click="primeniRegionZaSve">Primeni</x-jet-secondary-button>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto shadow border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-3 text-left font-medium text-gray-500">Upis</th>
                        <th class="px-2 py-3 text-left font-medium text-gray-500">Red</th>
                        <th class="px-2 py-3 text-left font-medium text-gray-500">TID</th>
                        <th class="px-2 py-3 text-left font-medium text-gray-500">Trgovac (zahtev)</th>
                        <th class="px-2 py-3 text-left font-medium text-gray-500">Lokacija u aplikaciji</th>
                        <th class="px-2 py-3 text-left font-medium text-gray-500">Region (nova)</th>
                        <th class="px-2 py-3 text-left font-medium text-gray-500">SN (opciono)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($redovi as $red)
                        @php($kljuc = $red['red'])
                        <tr wire:key="zahtev-red-{{ $kljuc }}" @class(['bg-red-50' => $red['greske'], 'align-top'])>
                            <td class="px-2 py-2">
                                <x-jet-checkbox wire:model.live="izbori.{{ $kljuc }}.izabran" :disabled="(bool) $red['greske']" />
                            </td>
                            <td class="px-2 py-2 text-gray-500">{{ $kljuc }}</td>
                            <td class="px-2 py-2 whitespace-nowrap">
                                <div class="font-semibold">{{ $red['tid'] ?? $red['tid_sirovo'] }}</div>
                                @if ($red['vrsta'] === \App\Actions\TerminalAcq\ProcitajZahtevBanke::PREKONFIGURACIJA)
                                    <div class="text-xs text-indigo-700">Prekonfiguracija</div>
                                    <div class="text-xs text-gray-500">stari: {{ $red['stari_opis'] ?? $red['stari_tid'] }}</div>
                                @else
                                    <div class="text-xs text-gray-500">Instalacija</div>
                                @endif
                                @if ($red['model'])
                                    <div class="text-xs text-gray-500">{{ $red['model'] }}</div>
                                @endif
                            </td>
                            <td class="px-2 py-2">
                                <div class="font-semibold">{{ $red['naziv_trgovca'] }}</div>
                                <div class="text-xs text-gray-500">{{ $red['adresa'] }}, {{ $red['mesto'] }} {{ $red['ptt'] }}</div>
                                <div class="text-xs text-gray-500">PIB {{ $red['pib'] }} · MID {{ $red['acq_mid'] }} · MCC {{ $red['mcc'] }}</div>
                                @if ($red['kontakt'] || $red['telefon'])
                                    <div class="text-xs text-gray-500">{{ $red['kontakt'] }} {{ $red['telefon'] }}</div>
                                @endif
                                @foreach ($red['greske'] as $poruka)
                                    <div class="mt-1 text-xs text-red-700">{{ $poruka }}</div>
                                @endforeach
                                @foreach ($red['upozorenja'] as $poruka)
                                    <div class="mt-1 text-xs text-amber-700">{{ $poruka }}</div>
                                @endforeach
                            </td>
                            <td class="px-2 py-2">
                                <select wire:model.live="izbori.{{ $kljuc }}.lokacija" class="w-64 border-gray-300 rounded-md text-xs" @disabled($red['greske'])>
                                    @if ($red['lokacijaId'] === null && $red['opcije'] && ($izbori[$kljuc]['lokacija'] ?? '') === '')
                                        <option value="">--- izaberite ---</option>
                                    @endif
                                    @foreach ($red['opcije'] as $opcija)
                                        <option value="{{ $opcija['id'] }}">{{ $opcija['naziv'] }}</option>
                                    @endforeach
                                    <option value="{{ \App\Http\Livewire\TerminalAcq\UvozZahteva::NOVA_LOKACIJA }}">
                                        {{ $red['opcije'] ? 'Nova podlokacija (isti PIB)' : 'Nova lokacija' }}
                                    </option>
                                </select>
                            </td>
                            <td class="px-2 py-2">
                                @if (($izbori[$kljuc]['lokacija'] ?? null) === \App\Http\Livewire\TerminalAcq\UvozZahteva::NOVA_LOKACIJA)
                                    <select wire:model="izbori.{{ $kljuc }}.regionId" class="border-gray-300 rounded-md text-xs" @disabled($red['greske'])>
                                        <option value="">---</option>
                                        @foreach ($regioni as $id => $naziv)
                                            <option value="{{ $id }}">{{ $naziv }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>
                            <td class="px-2 py-2">
                                @if ($red['vrsta'] === \App\Actions\TerminalAcq\ProcitajZahtevBanke::INSTALACIJA)
                                    <x-jet-input wire:model="izbori.{{ $kljuc }}.sn" type="text" class="w-40 text-xs" placeholder="SN uređaja" :disabled="(bool) $red['greske']" />
                                @else
                                    <span class="text-xs text-gray-500">uređaj starog TID-a</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-end gap-3 mt-6">
            <x-jet-secondary-button wire:click="otkazi">Otkaži</x-jet-secondary-button>
            <x-jet-button
                wire:click="potvrdi"
                wire:loading.attr="disabled"
                wire:confirm="Upisati {{ $brojIzabranih }} izabranih redova? Upis je jedna celina: greška u bilo kom redu poništava ceo upis."
                :disabled="$brojIzabranih === 0">
                <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-2" />
                Upiši izabrane ({{ $brojIzabranih }})
            </x-jet-button>
        </div>
    @endif
</div>
