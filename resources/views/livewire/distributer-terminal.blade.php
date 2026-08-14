<div class="p-6">
    <div class="flex items-center justify-between px-4 py-3 text-right sm:px-6">
        <div class="flex font-semibold text-xl">
            <div class="ml-2 pr-2"><x-icon-distributer class="fill-red-600 w-6 h-6" /></div>
            <div class="text-red-600">{{ $dist_name }}</div>
        </div>
        <div class="flex">
            <x-jet-secondary-button wire:click="downloadExcel" wire:loading.attr="disabled" class="ml-2">
                <x-icon-file-excel class="fill-current w-4 h-4 mr-2" />
                {{ __('Preuzmi Excel') }}
            </x-jet-secondary-button>
            <div class="ml-2">
                <button data-tooltip-target="tooltip-default" type="button" class="mt-1">
                    <x-heroicon-s-information-circle class="fill-green-500 w-8 h-8" />
                </button>

                <div id="tooltip-default" role="tooltip" class="absolute z-100 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip dark:bg-gray-700">
                    Excel fajl sadrži samo licence koje su istekle ili ističu početkom sledećeg meseca.
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
            </div>
        </div>
    </div>


    {{-- The data table --}}
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-2 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"></th>
                                <th class="px-2 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Serijski broj</th>
                                <th colspan="2" class="px-2 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Lokacija</th>
                                <th class="px-2 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Licenca</th>
                                <th class="px-2 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    <x-icon-blacklist-scull class="fill-red-400 w-6 h-6" />
                                </th>
                                <th class="px-2 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Početak</th>
                                <th class="px-2 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Kraj</th>
                                <th colspan="5" class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Broj terminala: {{ $broj_terminala }} <br /> Broj licenci: {{ $broj_licenci }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">  
                        {{-- SEARCH ROW --}}
                            <tr class="bg-orange-50">
                                <td></td>
                                <td>
                                    <x-jet-input wire:model.live="searchTerminalSn" id="" class="block bg-orange-50 w-48" type="text" placeholder="Serijski broj" />
                                </td>
                                <td colspan="2">
                                    <x-jet-input wire:model.live="searchMesto" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži mesto, adresu, naziv" />
                                </td>
                                <td>
                                    <select wire:model.live="searchTipLicence" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">---</option>
                                        @foreach (App\Models\LicencaDistributerCena::LicenceDistributera($distId) as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                        <option value="1000">Bez licence</option>
                                    </select>
                                </td>
                                <td>
                                    <select wire:model.live="searchNenaplativ" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">---</option>
                                        <option value="1">Da</option>
                                    </select>
                                </td>
                                <td colspan="2">
                                    <x-jet-input wire:model.live="searchPib" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži PIB" />
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            @php
                                $olditem = new stdClass();
                                $olditem->id = '';
                            @endphp
                            @if ($data->count())
                                @foreach ($data as $item)
                                    @if($olditem->id == $item->id)
                                        @php
                                            $item->isDuplicate = true;
                                        @endphp
                                    @else
                                        @php
                                            $item->isDuplicate = false;
                                        @endphp
                                    @endif

                                    <tr>
                                        <td>
                                            @if($item->zaduzeno)
                                                @if($item->razduzeno && $item->razduzeno > 0)
                                                    <x-heroicon-m-check class="fill-green-500 w-5 h-5 mt-0.5 mx-2" />
                                                @else
                                                    <x-heroicon-c-exclamation-triangle class="fill-red-500 h-5 w-5 mx-2" />
                                                @endif
                                            @endif
                                            @if($item->nenaplativ)
                                                <x-icon-blacklist-scull class="mx-auto fill-red-400 w-6 h-6" />
                                            @endif
                                        </td>
                                        <td class="px-2 py-2">  
                                            @if($item->isDuplicate)
                                                <x-heroicon-c-arrow-turn-down-right class="fill-red-400 w-5 h-5 ml-4" />
                                            @else
                                                {{ $item->sn }}
                                            @endif 
                                        </td>
                                        <td class="px-2 py-2">
                                            @if($item->isDuplicate)
                                                <x-heroicon-c-arrow-long-right class="fill-red-400 w-5 h-5" />
                                            @else
                                                @if($item->is_duplicate)<span class="text-red-500">*</span>@endif
                                                {{ $item->l_naziv }}&nbsp;{{ $item->l_naziv_sufix }}, {{ $item->mesto }}
                                            @endif   
                                        </td>
                                        <td>
                                            @if(!$item->isDuplicate)
                                                <div class="flex align-middle">
                                                    @if($item->latitude != '' && $item->longitude != '') 
                                                    <span class="mt-2">
                                                        <a href="{{ App\Http\Helpers::createGmapLink($item->latitude, $item->longitude) }}" target="_blank"> 
                                                            <x-icon-map-pin class="fill-sky-800 w-4 h-4 mr-2" />
                                                        </a>
                                                    </span> 
                                                    @endif
                                            
                                                <x-jet-secondary-button class="px-1" onclick="copyToCliboard('{{$item->adresa}}', '{{$item->mesto}}')" wire:click="showLatLogModal({{ $item->lokid }})" title="Dodaj koordinate" >
                                                    <x-icon-map-pin-plus class="fill-sky-800 w-4 h-4 mr-2" /> 
                                                </x-jet-secondary-button>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-2 py-2">
                                            @if($item->isDuplicate) 
                                                <span class="float-left pr-2">
                                                    <x-heroicon-c-plus class="fill-red-400 w-5 h-5" />
                                                </span>
                                            @endif
                                            {{ $item->licenca_naziv }} 
                                        </td>  
                                        <td td class="px-1 py-1">
                                            @if($item->nenaplativ > 0)
                                                <x-icon-blacklist-scull class="fill-red-400 w-5 h-5" />
                                            @endif
                                        </td> 
                                        
                                        <td class="px-2 py-2">@if($item->datum_pocetka_licence != '') {{ App\Http\Helpers::datumFormatDan($item->datum_pocetka_licence) }} @endif</td>
                                        <td>
                                            <div class="float-right">
                                            @if($item->month_diff !='')
                                                @if($item->month_diff == 0)
                                                   <x-icon-acient-clock class="fill-red-300 w-4 h-4" />
                                                @elseif($item->month_diff < 0)
                                                    <x-icon-calendar-x class="fill-red-600 w-4 h-4" />
                                                @endif
                                            @endif
                                            </div>
                                        </td>
                                        
                                        <td class="px-2 py-2">@if($item->datum_kraj_licence != '') {{ App\Http\Helpers::datumFormatDan($item->datum_kraj_licence) }} @endif</td>                                       
                                        <td></td>
                                        <td></td>
                                        <td class="px-1 py-1">
                                            @if(!$item->isDuplicate)
                                                <x-jet-secondary-button class="ml-2" wire:click="terminalInfoShowModal({{ $item->id }})" title="Info">
                                                    <x-heroicon-s-information-circle class="fill-red-500 w-6 h-6" />
                                                </x-jet-secondary-button>
                                            @endif
                                        </td>
                                        <td class="px-1 py-1">
                                            @if(!$item->isDuplicate)
                                                <button class="px-2 py-1 text-sm relative text-gray-600 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="commentsShowModal({{ $item->id }})" title="Komentari">
                                                    <div class="mx-1 text-lg">{{ $item->br_komentara}}</div>
                                                    <x-heroicon-o-chat-bubble-bottom-center-text class="z-10 absolute top-1 right-1 text-gray-400 -mt-1.5 ml-2.5 w-4 h-4"/>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @php
                                        $olditem = $item;
                                    @endphp
                                @endforeach
                            @else 
                                <tr>
                                    <td class="px-6 py-4 text-sm whitespace-no-wrap" colspan="4">No Results Found</td>
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

    {{-- The Delete Modal #######################################################################--}}
    <x-jet-dialog-modal wire:model.live="modalConfirmDeleteVisible">
        <x-slot name="title">
                {{ __('Brisanje licence') }}
        </x-slot>

        <x-slot name="content">
                <div my-4>
                    Da li ste sigurni da želite da obišete licence sa terminla!
                </div>
        </x-slot>

        <x-slot name="footer">
        
            <x-jet-secondary-button wire:click="$toggle('modalConfirmDeleteVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
       
            <x-jet-danger-button class="ml-2" wire:click="delteLicenca" wire:loading.attr="disabled">
                {{ __('Obriši licencu') }}
            </x-jet-danger-button>
        </x-slot>
    </x-jet-dialog-modal>

    {{-- The Terminal Info Modal ##################################################### --}}
    <x-jet-dialog-modal wire:model.live="modalTerminalInfoVisible">
        <x-slot name="title">
            <x-heroicon-m-information-circle class="float-left fill-red-500 w-6 h-6 mr-2 mt-1"/>
                Info
        </x-slot>
        <x-slot name="content">
            @if($modalTerminalInfoVisible)
            <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />

            <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                <p class="font-bold">Licence:</p>   
                @foreach($licenceNaziviInfo as $lice_inf)
                    <p class="pl-2">{{ $lice_inf->licenca_naziv }}</p>
                @endforeach
            </div>
            <div class="bg-red-50 border border-red-500 text-red-500 px-4 py-3 rounded relative my-4 " role="alert">
                <p class="">BRISANJE SVIH LICENCI<br />
                <span class="font-bold block sm:inline">Sve licence dodate terminalu će biti obrisane.</span><br />
                Sa brisanjem licenci nenaplaćene licence se razdužuju sa 0.00 dinara.
                <br /><br />
                <x-jet-danger-button wire:click="deleteLicenceTerminala()" wire:loading.attr="disabled">
                    {{ __('Obriši sve licence') }}
                </x-jet-danger-button>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <x-heroicon-c-exclamation-triangle class="fill-red-500 h-6 w-6 " />
                </span>
                </p>
            </div>
            @endif
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalTerminalInfoVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>

         {{-- LAT LOG MODAL ##########################################################--}}
     <x-jet-dialog-modal wire:model.live="latLogVisible">
        <x-slot name="title">
            <x-icon-map-pin-plus class="float-left fill-sky-800 w-6 h-6 mx-auto" />
            {{ __('Koordinate') }}
        </x-slot>

        <x-slot name="content">
            @if($latLogVisible)
                <div>Lokacija:</div>
                    <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                        <div class="flex">
                            <div class="py-1">
                                <x-heroicon-c-map-pin class="fill-current w-6 h-6 mr-2" />
                            </div>
                            <div>
                                <p class="font-bold">{{ $odabranaLokacija->l_naziv}}, </p>
                                <p class="text-sm">{{ $odabranaLokacija->adresa }}</p>
                                <p class="text-sm">{{ $odabranaLokacija->mesto }}</p>
                                <p class="text-sm">Region: {{ $odabranaLokacija->r_naziv }}</p>
                            </div>
                    </div>
                </div> 
                <div class="mt-4">
                    <div>Koordinate:</div>
                    <div class="flex">
                        <div class="flex-1">
                            <x-jet-label for="latLogValue" value="{{ __('Lat, Long') }}" />
                            <x-jet-input wire:model.live="latLogValue" id="" class="block mt-1 w-full" type="text" />
                                @error('lat_value') <span class="error">{{ $message }}</span> @enderror
                                @error('long_value') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex-2 ml-4 mt-4 pt-2">
                            <x-jet-danger-button class="ml-2" wire:click="removeLatLog" wire:loading.attr="disabled">
                                <x-heroicon-o-trash class="text-current w-6 h-6" />
                            </x-jet-danger-button>
                        </div>
                    </div>
                </div>
            @endif 
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('latLogVisible')" wire:loading.attr="disabled">
                    {{ __('Otkaži') }}
            </x-jet-secondary-button>
            <x-jet-danger-button class="ml-2" wire:click="addOrUpdateLatLog" wire:loading.attr="disabled">
                    {{ __('Dodaj koordinate') }}
            </x-jet-danger-button>
        </x-slot>
     </x-jet-dialog-modal>

    {{-- KOMENTARI MODAL --}}
    <x-jet-dialog-modal wire:model.live="modalKomentariVisible">
        <x-slot name="title">
            KOMENTARI
        </x-slot>
        <x-slot name="content"> 
            @if($modalKomentariVisible)
                 <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
                
                <div class="flex mb-4">
                        <livewire:komponente.prikazkomentara 
                            :terminalLokacijaId="$modelId"
                            :canEdit="true" />
                </div>
            @endif
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalKomentariVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>

     <script>
        function copyToCliboard(adresa, mesto) {
            navigator.clipboard.writeText(adresa+ ", "+ mesto);
        } 
     </script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</div>