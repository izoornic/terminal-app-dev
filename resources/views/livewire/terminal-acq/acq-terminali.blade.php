<div class="p-6">
    @if ($porukaListe)
        <div class="mb-4 p-3 rounded-md border border-green-300 bg-green-50 text-sm text-green-800">{{ $porukaListe }}</div>
    @endif

    {{-- BRZI FILTERI --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach ($brziFilteri as $kljuc => $naziv)
            <button wire:click="postaviBrziFilter('{{ $kljuc }}')"
                    @class([
                        'text-sm border rounded-md px-3 py-1.5',
                        'bg-gray-700 text-white' => $brziFilter === $kljuc,
                        'bg-white text-gray-700 hover:bg-gray-100' => $brziFilter !== $kljuc,
                    ])>
                {{ $naziv }}
            </button>
        @endforeach
    </div>

    {{-- The data table --}}
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500"></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">TID</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">SN uređaja</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Trgovac / naziv na slipu</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">PIB</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">MID</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Status</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Ukupno: {{ $data->total() }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- SEARCH ROW --}}
                            <tr class="bg-orange-50">
                                <td> <x-heroicon-o-funnel class="mx-auto text-orange-600 w-4 h-4" /> </td>
                                <td class="px-1"><x-jet-input wire:model.live.debounce.400ms="searchTid" class="block bg-orange-50 w-full" type="text" placeholder="TID" /></td>
                                <td class="px-1"><x-jet-input wire:model.live.debounce.400ms="searchSn" class="block bg-orange-50 w-full" type="text" placeholder="SN" /></td>
                                <td class="px-1"><x-jet-input wire:model.live.debounce.400ms="searchTrgovac" class="block bg-orange-50 w-full" type="text" placeholder="Trgovac / slip" /></td>
                                <td class="px-1"><x-jet-input wire:model.live.debounce.400ms="searchPib" class="block bg-orange-50 w-full" type="text" placeholder="PIB" /></td>
                                <td class="px-1"><x-jet-input wire:model.live.debounce.400ms="searchMid" class="block bg-orange-50 w-full" type="text" placeholder="MID" /></td>
                                <td class="px-1">
                                    <select wire:model.live="searchStatus" class="block appearance-none bg-orange-50 w-full border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-2 leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">---</option>
                                        @foreach ($statusiFilter as $id => $naziv)
                                            <option value="{{ $id }}">{{ $naziv }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td></td>
                            </tr>
                            <!-- DATA  -->
                            @forelse ($data as $item)
                                <tr wire:key="tid-{{ $item->id }}" @if($loop->even) class="bg-gray-50" @endif>
                                    <td class="px-2 py-1">
                                        <button class="mt-1 text-sm text-gray-700 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="prikaziDetalj({{ $item->id }})" title="Detalj">
                                            <x-heroicon-o-eye class="w-5 h-5 mx-2 my-1" />
                                        </button>
                                    </td>
                                    <td class="px-1 py-2 font-semibold whitespace-nowrap">
                                        {{ $item->acq_tid }}
                                        @if ($item->testni)
                                            <span class="ml-1 text-xs text-indigo-700">testni</span>
                                        @endif
                                    </td>
                                    <td class="px-1 py-2 whitespace-nowrap">{{ $item->sn ?? '—' }}</td>
                                    <td class="px-1 py-2">
                                        <div>{{ $item->lokacija_naziv ?? '—' }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->naziv_na_slipu }} {{ $item->lokacija_mesto ? '· '.$item->lokacija_mesto : '' }}</div>
                                    </td>
                                    <td class="px-1 py-2">{{ $item->lokacija_pib }}</td>
                                    <td class="px-1 py-2 text-xs">{{ $item->lokacija_acq_mid }}</td>
                                    <td class="px-1 py-2 text-sm">
                                        {{ $statusi[$item->efektivni_statusId] ?? '—' }}
                                        @if ($item->terminalId === null)
                                            <div class="text-xs text-gray-500">bez uređaja</div>
                                        @endif
                                    </td>
                                    <td class="px-1 py-2 text-xs text-gray-500">{{ $item->datum_instalacije?->format('d.m.Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-4 text-sm whitespace-no-wrap" colspan="8">Nema rezultata</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        {{ $data->links() }}
    </div>

    {{-- DETALJ TID-a ########################################################## --}}
    <x-jet-dialog-modal wire:model.live="detaljVisible">
        <x-slot name="title">
            <div class="flex">
                <x-heroicon-o-credit-card class="w-6 h-6 mr-2"/>
                {{ $detalj['acq']->acq_tid ?? '' }}
                @if ($detalj && $detalj['acq']->testni)
                    <span class="ml-2 text-sm text-indigo-700">testni</span>
                @endif
            </div>
        </x-slot>

        <x-slot name="content">
            @if ($detalj)
                @php($acq = $detalj['acq'])
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="font-semibold text-gray-700 mb-1">TID</div>
                        <div>Status: <span class="font-semibold">{{ $statusi[$detalj['statusId']] ?? '—' }}</span>
                            <span class="text-xs text-gray-500">{{ $acq->terminalId ? '(status uređaja)' : '(TID bez uređaja)' }}</span></div>
                        <div>Naziv na slipu: {{ $acq->naziv_na_slipu ?? '—' }}</div>
                        <div>Komunikacija: {{ $acq->komunikacija ?? '—' }}</div>
                        <div>ICCID: {{ $acq->iccid ?? '—' }}</div>
                        <div>Inventarni broj: {{ $acq->inventarni_broj ?? '—' }}</div>
                        <div>Datum zahteva: {{ $acq->datum_zahteva?->format('d.m.Y') ?? '—' }}</div>
                        <div>Datum instalacije: {{ $acq->datum_instalacije?->format('d.m.Y') ?? '—' }}</div>
                        @if ($acq->trazeniTerminalTip)
                            <div>Traženi model: {{ $acq->trazeniTerminalTip->proizvodjac }} {{ $acq->trazeniTerminalTip->model }}</div>
                        @endif
                        @if ($acq->prethodni)
                            <div>Prekonfigurisan sa: {{ $acq->prethodni->acq_tid }}</div>
                        @endif
                        @if ($acq->naslednik)
                            <div>Prekonfiguracija na: {{ $acq->naslednik->acq_tid }}</div>
                        @endif
                        @if ($acq->zahtev_acq_mid)
                            <div class="text-amber-700">MID posle prekonfiguracije: {{ $acq->zahtev_acq_mid }}</div>
                        @endif
                        @if ($acq->dodatne_opcije)
                            <div>Dodatne opcije: {{ $acq->dodatne_opcije }}</div>
                        @endif
                        @if ($acq->napomena)
                            <div>Napomena: {{ $acq->napomena }}</div>
                        @endif
                        <div class="text-xs text-gray-500 mt-1">Izvor: {{ $acq->izvor }}{{ $acq->izvor_fajl ? ' ('.$acq->izvor_fajl.')' : '' }} · poslednja izmena: {{ $acq->korisnikIme }}, {{ $acq->updated_at?->format('d.m.Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-700 mb-1">Uređaj</div>
                        @if ($acq->terminal)
                            <div>SN: <span class="font-semibold">{{ $acq->terminal->sn }}</span></div>
                        @else
                            <div class="text-gray-500">TID nema uređaj.</div>
                        @endif

                        <div class="font-semibold text-gray-700 mt-3 mb-1">Trgovac (lokacija)</div>
                        @if ($detalj['lokacija'])
                            @php($lokacija = $detalj['lokacija'])
                            <div class="font-semibold">{{ $lokacija->l_naziv }} {{ $lokacija->l_naziv_sufix }}</div>
                            <div>{{ $lokacija->adresa }}, {{ $lokacija->mesto }} {{ $lokacija->ptt }}</div>
                            <div>PIB: {{ $lokacija->pib ?? '—' }} · MID: {{ $lokacija->acq_mid ?? '—' }} · MCC: {{ $lokacija->mcc ?? '—' }}</div>
                            @foreach ($detalj['kontakti'] as $kontakt)
                                <div>{{ $kontakt->name }} {{ $kontakt->tel }}</div>
                            @endforeach
                        @else
                            <div class="text-gray-500">Bez lokacije.</div>
                        @endif
                    </div>
                </div>

                {{-- AKCIJE --}}
                @if ($porukaAkcije)
                    <div class="mt-4 p-3 rounded-md border border-green-300 bg-green-50 text-sm text-green-800">{{ $porukaAkcije }}</div>
                @endif
                @if ($greskaAkcije)
                    <div class="mt-4 p-3 rounded-md border border-red-300 bg-red-50 text-sm text-red-700">{{ $greskaAkcije }}</div>
                @endif

                <div class="mt-4 p-3 rounded-md border border-gray-200 text-sm">
                    <div class="font-semibold text-gray-700 mb-2">Akcije</div>

                    @if ($acq->terminalId === null && $acq->terminal_statusId === $statusZaInstalacijuId)
                        <div class="flex flex-wrap items-end gap-2">
                            <div>
                                <x-jet-label for="dodelaSn" value="SN uređaja za dodelu" />
                                <x-jet-input wire:model="dodelaSn" id="dodelaSn" class="block mt-1 w-56" type="text" />
                            </div>
                            <x-jet-button wire:click="dodeliUredjaj" wire:loading.attr="disabled">Dodeli uređaj</x-jet-button>
                            <x-jet-danger-button wire:click="opozovi" wire:confirm="Opozvati TID {{ $acq->acq_tid }}?" wire:loading.attr="disabled">Opozovi</x-jet-danger-button>
                        </div>
                        <div class="mt-1 text-xs text-gray-500">Ako uređaj nije na lokaciji TID-a, premešta se tamo sa statusom „Za instalaciju”.</div>
                    @elseif ($acq->terminalId === null && $acq->terminal_statusId === $statusCekaPrekonfiguracijuId)
                        <div class="mb-2">Stari TID: <span class="font-semibold">{{ $acq->prethodni?->acq_tid ?? '—' }}</span>
                            @if ($acq->zahtev_acq_mid) <span class="text-amber-700">· MID lokacije biće zamenjen sa {{ $acq->zahtev_acq_mid }}</span> @endif
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-jet-button wire:click="izvrsiPrekonfiguraciju" wire:confirm="Tehničar je prekonfigurisao uređaj? Stari TID {{ $acq->prethodni?->acq_tid }} se zatvara, a uređaj prelazi na {{ $acq->acq_tid }}." wire:loading.attr="disabled">Izvrši prekonfiguraciju</x-jet-button>
                            <x-jet-danger-button wire:click="opozovi" wire:confirm="Opozvati TID {{ $acq->acq_tid }}? Stari TID ostaje aktivan." wire:loading.attr="disabled">Opozovi</x-jet-danger-button>
                        </div>
                    @elseif ($acq->terminalId !== null)
                        <div class="flex flex-wrap items-end gap-2">
                            <div>
                                <x-jet-label for="deinstalacijaLokacijaId" value="Uređaj ide u" />
                                <select wire:model="deinstalacijaLokacijaId" id="deinstalacijaLokacijaId" class="block mt-1 border-gray-300 rounded-md text-sm">
                                    <option value="">---</option>
                                    @foreach ($ciljneLokacije as $id => $naziv)
                                        <option value="{{ $id }}">{{ $naziv }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-jet-label for="deinstalacijaStatusId" value="Status uređaja" />
                                <select wire:model="deinstalacijaStatusId" id="deinstalacijaStatusId" class="block mt-1 border-gray-300 rounded-md text-sm">
                                    <option value="">---</option>
                                    @foreach ($statusiUredjaja as $id => $naziv)
                                        <option value="{{ $id }}">{{ $naziv }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-jet-danger-button wire:click="deinstaliraj" wire:confirm="Deinstalirati TID {{ $acq->acq_tid }} i premestiti uređaj?" wire:loading.attr="disabled">Deinstaliraj</x-jet-danger-button>
                        </div>
                        @unless ($acq->naslednik)
                            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2">
                                <x-jet-secondary-button wire:click="otvoriPrekonfiguraciju">
                                    <x-heroicon-o-arrow-path class="w-4 h-4 mr-2" />
                                    Prekonfiguriši
                                </x-jet-secondary-button>
                                <span class="text-xs text-gray-500">Ručno, za zahtev koji nije stigao Excelom (ista ili nova firma).</span>
                            </div>
                        @else
                            <div class="mt-3 text-xs text-gray-500">Prekonfiguracija je već zatražena: {{ $acq->naslednik->acq_tid }}.</div>
                        @endunless
                    @else
                        <div class="text-gray-500">TID je zatvoren; nema akcija.</div>
                    @endif
                </div>

                <div class="font-semibold text-gray-700 mt-6 mb-1 text-sm">Istorija</div>
                @if ($detalj['istorija']->isEmpty())
                    <div class="text-sm text-gray-500">Nema ranijih stanja.</div>
                @else
                    <table class="min-w-full text-xs divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-1 py-2 text-left text-gray-500">Do</th>
                                <th class="px-1 py-2 text-left text-gray-500">Status</th>
                                <th class="px-1 py-2 text-left text-gray-500">Uređaj</th>
                                <th class="px-1 py-2 text-left text-gray-500">Lokacija</th>
                                <th class="px-1 py-2 text-left text-gray-500">MID</th>
                                <th class="px-1 py-2 text-left text-gray-500">Korisnik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($detalj['istorija'] as $stanje)
                                <tr wire:key="tid-istorija-{{ $stanje->id }}">
                                    <td class="px-1 py-1 whitespace-nowrap">{{ $stanje->created_at?->format('d.m.Y') }}</td>
                                    <td class="px-1 py-1">{{ $statusi[$stanje->terminal_statusId] ?? '—' }}</td>
                                    <td class="px-1 py-1">{{ $stanje->terminal?->sn ?? '—' }}</td>
                                    <td class="px-1 py-1">{{ $stanje->lokacija?->l_naziv ?? '—' }}</td>
                                    <td class="px-1 py-1">{{ $stanje->acq_mid ?? '—' }}</td>
                                    <td class="px-1 py-1">{{ $stanje->korisnikIme }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('detaljVisible', false)">Zatvori</x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
