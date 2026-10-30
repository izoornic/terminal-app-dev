<div>
    <x-jet-dialog-modal wire:model.live="modalVisible">
        <x-slot name="title">
            <div class="flex">
                <x-heroicon-o-plus-circle class="w-6 h-6 mr-2"/>
                Dodaj terminal (HALK TID)
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-jet-label for="acq_tid" value="ACQ TID *" />
                    <x-jet-input wire:model="acq_tid" id="acq_tid" class="block mt-1 w-full" type="text" placeholder="PN000000" />
                    <x-jet-input-error for="acq_tid" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="sn" value="SN uređaja *" />
                    <x-jet-input wire:model.live.debounce.500ms="sn" id="sn" class="block mt-1 w-full" type="text" />
                    <x-jet-input-error for="sn" class="mt-2" />
                </div>
            </div>

            {{-- ŠTA TID NASLEĐUJE OD UREĐAJA --}}
            @if ($sn !== '')
                <div class="mt-4 p-3 rounded-md border text-sm {{ $uredjaj ? 'bg-gray-50 border-gray-200' : 'bg-red-50 border-red-200 text-red-700' }}">
                    @if ($uredjaj)
                        <div>Status uređaja (i TID-a): <span class="font-semibold">{{ $uredjaj['status'] ?? '—' }}</span>
                            @if ($uredjaj['vendor']) <span class="text-gray-500">· vendor {{ $uredjaj['vendor'] }}</span> @endif
                        </div>
                        @if ($uredjaj['lokacija'])
                            <div class="font-semibold mt-1">{{ $uredjaj['lokacija']->l_naziv }} {{ $uredjaj['lokacija']->l_naziv_sufix }}</div>
                            <div>{{ $uredjaj['lokacija']->adresa }}, {{ $uredjaj['lokacija']->mesto }} · PIB {{ $uredjaj['lokacija']->pib ?? '—' }}</div>
                            @foreach ($uredjaj['kontakti'] as $kontakt)
                                <div>{{ $kontakt->name }} {{ $kontakt->tel }}</div>
                            @endforeach
                        @endif
                        @if ($uredjaj['aktivniTid'])
                            <div class="mt-1 text-red-700">Uređaj je već pod aktivnim TID-om {{ $uredjaj['aktivniTid'] }}.</div>
                        @endif
                        @if ($uredjaj['upozorenje'])
                            <div class="mt-1 text-amber-700">{{ $uredjaj['upozorenje'] }}</div>
                        @endif
                    @else
                        Uređaj sa ovim SN ne postoji ili nema lokaciju.
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <x-jet-label for="naziv_na_slipu" value="Naziv na slipu" />
                    <x-jet-input wire:model="naziv_na_slipu" id="naziv_na_slipu" class="block mt-1 w-full" type="text" />
                    <x-jet-input-error for="naziv_na_slipu" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="komunikacija" value="Komunikacija" />
                    <select wire:model="komunikacija" id="komunikacija" class="block mt-1 w-full border-gray-300 rounded-md">
                        <option value="">---</option>
                        @foreach ($komunikacije as $vrednost)
                            <option value="{{ $vrednost }}">{{ $vrednost }}</option>
                        @endforeach
                    </select>
                    <x-jet-input-error for="komunikacija" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="iccid" value="ICCID SIM-a" />
                    <x-jet-input wire:model="iccid" id="iccid" class="block mt-1 w-full" type="text" />
                    <x-jet-input-error for="iccid" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="inventarni_broj" value="Inventarni broj" />
                    <x-jet-input wire:model="inventarni_broj" id="inventarni_broj" class="block mt-1 w-full" type="text" />
                    <x-jet-input-error for="inventarni_broj" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="datum_zahteva" value="Datum zahteva" />
                    <x-jet-input wire:model="datum_zahteva" id="datum_zahteva" class="block mt-1 w-full" type="date" />
                    <x-jet-input-error for="datum_zahteva" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="datum_instalacije" value="Datum instalacije" />
                    <x-jet-input wire:model="datum_instalacije" id="datum_instalacije" class="block mt-1 w-full" type="date" />
                    <x-jet-input-error for="datum_instalacije" class="mt-2" />
                </div>
            </div>

            <div class="mt-4">
                <x-jet-label for="dodatne_opcije" value="Dodatne opcije" />
                <x-jet-textarea wire:model="dodatne_opcije" id="dodatne_opcije" class="block mt-1 w-full" rows="2" />
            </div>
            <div class="mt-4">
                <x-jet-label for="napomena" value="Napomena" />
                <x-jet-textarea wire:model="napomena" id="napomena" class="block mt-1 w-full" rows="2" />
            </div>
            <label class="flex items-center mt-4">
                <x-jet-checkbox wire:model="testni" />
                <span class="ml-2 text-sm text-gray-700">Testni TID</span>
            </label>

            {{-- PODACI BANKE NA LOKACIJI: samo ako su prazni --}}
            @if ($uredjaj && $uredjaj['lokacija'] && (! $uredjaj['lokacija']->acq_mid || ! $uredjaj['lokacija']->mcc || ! $uredjaj['lokacija']->ptt))
                <div class="mt-4 p-3 border border-gray-200 rounded-md">
                    <div class="text-sm text-gray-700 mb-2">Prazni podaci banke na lokaciji (postojeće vrednosti se menjaju kroz formu lokacije):</div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @if (! $uredjaj['lokacija']->acq_mid)
                            <div>
                                <x-jet-label for="acq_mid" value="ACQ MID" />
                                <x-jet-input wire:model="acq_mid" id="acq_mid" class="block mt-1 w-full" type="text" />
                                <x-jet-input-error for="acq_mid" class="mt-2" />
                            </div>
                        @endif
                        @if (! $uredjaj['lokacija']->mcc)
                            <div>
                                <x-jet-label for="mcc" value="MCC" />
                                <x-jet-input wire:model="mcc" id="mcc" class="block mt-1 w-full" type="text" />
                                <x-jet-input-error for="mcc" class="mt-2" />
                            </div>
                        @endif
                        @if (! $uredjaj['lokacija']->ptt)
                            <div>
                                <x-jet-label for="ptt" value="PTT" />
                                <x-jet-input wire:model="ptt" id="ptt" class="block mt-1 w-full" type="text" />
                                <x-jet-input-error for="ptt" class="mt-2" />
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('modalVisible', false)">Otkaži</x-jet-secondary-button>
            <x-jet-button class="ml-2" wire:click="sacuvaj" wire:loading.attr="disabled">Sačuvaj</x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
