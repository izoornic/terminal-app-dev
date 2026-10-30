<div>
    <x-jet-dialog-modal wire:model.live="modalVisible">
        <x-slot name="title">
            <div class="flex">
                <x-heroicon-o-arrow-path class="w-6 h-6 mr-2"/>
                Prekonfiguracija {{ $stariTid }}
            </div>
        </x-slot>

        <x-slot name="content">
            <p class="text-sm text-gray-500 mb-4">Za zahtev koji nije stigao Excelom. Podaci trgovca su popunjeni sa sadašnje lokacije; za novu firmu unesite njene podatke.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-jet-label for="novi_tid" value="Novi ACQ TID *" />
                    <x-jet-input wire:model="novi_tid" id="novi_tid" class="block mt-1 w-full" type="text" placeholder="PN000000" />
                </div>
                <div>
                    <x-jet-label for="datum_zahteva" value="Datum zahteva *" />
                    <x-jet-input wire:model="datum_zahteva" id="datum_zahteva" class="block mt-1 w-full" type="date" />
                    <x-jet-input-error for="datum_zahteva" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="naziv_trgovca" value="Naziv trgovca *" />
                    <x-jet-input wire:model="naziv_trgovca" id="naziv_trgovca" class="block mt-1 w-full" type="text" />
                </div>
                <div>
                    <x-jet-label for="pib" value="PIB *" />
                    <x-jet-input wire:model="pib" id="pib" class="block mt-1 w-full" type="text" />
                </div>
                <div>
                    <x-jet-label for="adresa" value="Adresa *" />
                    <x-jet-input wire:model="adresa" id="adresa" class="block mt-1 w-full" type="text" />
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div class="col-span-2">
                        <x-jet-label for="mesto" value="Mesto *" />
                        <x-jet-input wire:model="mesto" id="mesto" class="block mt-1 w-full" type="text" />
                    </div>
                    <div>
                        <x-jet-label for="ptt" value="PTT" />
                        <x-jet-input wire:model="ptt" id="ptt" class="block mt-1 w-full" type="text" />
                    </div>
                </div>
                <div>
                    <x-jet-label for="acq_mid" value="ACQ MID" />
                    <x-jet-input wire:model="acq_mid" id="acq_mid" class="block mt-1 w-full" type="text" />
                </div>
                <div>
                    <x-jet-label for="mcc" value="MCC" />
                    <x-jet-input wire:model="mcc" id="mcc" class="block mt-1 w-full" type="text" />
                </div>
                <div>
                    <x-jet-label for="kontakt" value="Kontakt osoba" />
                    <x-jet-input wire:model="kontakt" id="kontakt" class="block mt-1 w-full" type="text" />
                </div>
                <div>
                    <x-jet-label for="telefon" value="Telefon" />
                    <x-jet-input wire:model="telefon" id="telefon" class="block mt-1 w-full" type="text" />
                </div>
                <div>
                    <x-jet-label for="naziv_na_slipu" value="Naziv na slipu" />
                    <x-jet-input wire:model="naziv_na_slipu" id="naziv_na_slipu" class="block mt-1 w-full" type="text" />
                </div>
                <div>
                    <x-jet-label for="komunikacija" value="Komunikacija" />
                    <select wire:model="komunikacija" id="komunikacija" class="block mt-1 w-full border-gray-300 rounded-md">
                        <option value="">---</option>
                        @foreach ($komunikacije as $vrednost)
                            <option value="{{ $vrednost }}">{{ $vrednost }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <x-jet-label for="napomena" value="Napomena (npr. mail od koga i kada)" />
                <x-jet-input wire:model="napomena" id="napomena" class="block mt-1 w-full" type="text" />
            </div>

            <div class="mt-4">
                <x-jet-secondary-button wire:click="proveri" wire:loading.attr="disabled">
                    <x-heroicon-o-document-magnifying-glass class="w-4 h-4 mr-2" />
                    Proveri
                </x-jet-secondary-button>
            </div>

            {{-- REZULTAT PROVERE --}}
            @if ($pregled)
                <div class="mt-4 p-3 rounded-md border border-gray-200 text-sm">
                    @if ($pregled['stari_opis'])
                        <div>Stari TID: {{ $pregled['stari_opis'] }}</div>
                    @endif
                    @foreach ($pregled['greske'] as $poruka)
                        <div class="mt-1 text-red-700">{{ $poruka }}</div>
                    @endforeach
                    @foreach ($pregled['upozorenja'] as $poruka)
                        <div class="mt-1 text-amber-700">{{ $poruka }}</div>
                    @endforeach

                    @if (! $pregled['greske'])
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            <div>
                                <x-jet-label for="lokacija" value="Lokacija u aplikaciji" />
                                <select wire:model.live="lokacija" id="lokacija" class="block mt-1 w-full border-gray-300 rounded-md text-sm">
                                    @if ($lokacija === '')
                                        <option value="">--- izaberite ---</option>
                                    @endif
                                    @foreach ($pregled['opcije'] as $opcija)
                                        <option value="{{ $opcija['id'] }}">{{ $opcija['naziv'] }}</option>
                                    @endforeach
                                    <option value="{{ \App\Http\Livewire\TerminalAcq\RucnaPrekonfiguracija::NOVA_LOKACIJA }}">
                                        {{ $pregled['opcije'] ? 'Nova podlokacija (isti PIB)' : 'Nova lokacija' }}
                                    </option>
                                </select>
                            </div>
                            @if ($lokacija === \App\Http\Livewire\TerminalAcq\RucnaPrekonfiguracija::NOVA_LOKACIJA)
                                <div>
                                    <x-jet-label for="regionId" value="Region nove lokacije" />
                                    <select wire:model="regionId" id="regionId" class="block mt-1 w-full border-gray-300 rounded-md text-sm">
                                        <option value="">---</option>
                                        @foreach ($regioni as $id => $naziv)
                                            <option value="{{ $id }}">{{ $naziv }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                        <label class="flex items-center mt-4">
                            <x-jet-checkbox wire:model="izvrsiOdmah" />
                            <span class="ml-2 text-gray-700">Tehničar je već prekonfigurisao uređaj — izvrši odmah</span>
                        </label>
                    @endif
                </div>
            @endif

            @if ($greska)
                <div class="mt-4 p-3 rounded-md border border-red-300 bg-red-50 text-sm text-red-700">{{ $greska }}</div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('modalVisible', false)">Otkaži</x-jet-secondary-button>
            <x-jet-button class="ml-2" wire:click="sacuvaj" wire:loading.attr="disabled" :disabled="! $pregled || $pregled['greske']">Sačuvaj</x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
