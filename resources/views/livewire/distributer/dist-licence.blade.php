<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex">
            <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Licence terminala') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Pregled i dodavanje licenci terminalima.
            </p></div>
            <div class="ml-6">
                <p class="mt-1 text-sm text-gray-600">
                    Ukupno licenci: {{ $br_licenci }}<br /> Ukupno terminala {{$br_terminala}}
                </p>
            </div>
        </div>
    <div class="flex items-center justify-end px-4 py-3 text-right sm:px-6">
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
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">
                                    @if($komentariTerminalVisible)
                                        <input type="checkbox" wire:model.live="selectAll"  class="form-checkbox h-6 w-6 text-blue-500">
                                    @endif
                                </th>
                                <th class="bg-gray-50 px-2 py-3 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">::</th>
                                <th class="bg-gray-50 px-2 py-3 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Serijski broj</th>
                                <th class="bg-gray-50 px-2 py-3 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Lokacija</th>
                                <th class="bg-gray-50 px-2 py-3 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Licenca</th>
                                <th class="bg-gray-50 px-2 py-3 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Početak</th>
                                <th class="bg-gray-50 text-gray-500" ></th>
                                <th class="bg-gray-50 px-2 py-3 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Kraj</th>
                                <th colspan="4" class="bg-gray-50 px-6 py-3 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">  
                        {{-- SEARCH ROW --}}
                            <tr class="bg-orange-50">
                                <td></td>
                                <td></td>
                                <td>
                                    <x-jet-input wire:model.live="searchTerminalSn" id="" class="block bg-orange-50 w-44" type="text" placeholder="Serijski broj" />
                                </td>
                                <td>
                                    <x-jet-input wire:model.live="searchMesto" id="" class="block bg-orange-50 w-48" type="text" placeholder="Pretraži naziv" />
                                </td>
                                <td>
                                    <select wire:model.live="searchTipLicence" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="0">---</option>
                                        @foreach (App\Models\LicencaDistributerCena::LicenceDistributera($distId) as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                        <option value="1000">Bez licence</option>
                                    </select>
                                </td>
                                <td></td>
                                <td></td>
                                <td colspan="5">
                                    <x-jet-input wire:model.live="searchPib" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži PIB" />
                                </td>
                            </tr>
                            @php
                                $olditem = new stdClass();
                                $olditem->id = '';
                            @endphp

                            {{-- THE DATA TABLE --}}
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
                                        <td class="px-1 py-1">
                                            @if($komentariTerminalVisible)
                                                <input type="checkbox" value="{{ $item->lnid }}" wire:model.live="selectedTerminals"  class="form-checkbox h-6 w-6 text-blue-500">
                                            @endif
                                        </td>
                                        <td class="pl-2">                                           
                                            @if($item->blacklist)
                                                <span><x-icon-blacklist-scull class="w-5 h-5" /></span>
                                            @elseif($item->zaduzeno)
                                                @if($item->razduzeno && $item->razduzeno > 0)
                                                    <x-heroicon-c-check class="fill-green-500 w-6 h-6 mt-0.5" />
                                                @else
                                                    <x-heroicon-c-exclamation-triangle class="fill-red-500 h-5 w-5" />
                                                @endif
                                            @endif
                                            @if($item->nenaplativ)
                                                <x-icon-blacklist-scull class="mx-auto fill-red-400 w-6 h-6" />
                                            @endif
                                        </td>
                                        <td class="px-2 py-2">  
                                            @if($item->isDuplicate)
                                                <x-heroicon-c-arrow-turn-down-right class="fill-sky-400 w-5 h-5 ml-4" />
                                            @else
                                                {{ $item->sn }}
                                            @endif 
                                        </td>
                                        <td class="px-2 py-2">
                                            @if($item->isDuplicate)
                                                <x-heroicon-c-arrow-long-right class="fill-sky-400 w-5 h-5" />
                                            @else
                                                @if($item->is_duplicate)<span class="text-red-500">*</span>@endif
                                                {{ $item->l_naziv }}&nbsp;{{ $item->l_naziv_sufix }}<br />{{ $item->adresa }}, {{ $item->mesto }}
                                            @endif   
                                        </td>
                                        <td class="px-2 py-2">
                                            @if($item->isDuplicate) 
                                                <span class="float-left pr-2"><x-heroicon-c-plus class="fill-sky-400 w-5 h-5" /></span>
                                            @endif
                                            {{ $item->licenca_naziv }} 
                                        </td>  
                                        <td class="px-2 py-2">@if($item->datum_pocetka_licence != '') {{ App\Http\Helpers::datumFormatDan($item->datum_pocetka_licence) }} @endif</td>
                                        <td>
                                            @if($item->month_diff !='')
                                                @if($item->month_diff == 0)
                                                    <x-icon-acient-clock class="fill-red-300 w-4 h-4" />
                                                @elseif($item->month_diff < 0)
                                                    <x-icon-calendar-x class="fill-red-600 w-4 h-4" />
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-2 py-2">@if($item->datum_kraj_licence != '') {{ App\Http\Helpers::datumFormatDan($item->datum_kraj_licence) }} @endif</td>                                       
                                        <td class="px-1 py-1">
                                            @if($item->licenca_naziv != '')
                                                <a class="p-0.5 cursor-pointer flex border border-stone-500 bg-stone-50 hover:bg-stone-500 text-stone-700 hover:text-white rounded" title="Pregled licence" wire:click="pregledLicenceShovModal('{{$item->id}}', '{{$item->lnid}}', {{$item->month_diff}})">
                                                    <x-icon-licenca class="fill-current w-6 h-8 pl-1 m-0.5" />
                                                </a>
                                            @else
                                                <a class="p-0.5 cursor-pointer flex border border-stone-500 bg-stone-50 hover:bg-stone-500 text-stone-700 hover:text-white rounded" title="Dodaj licencu" wire:click="dodajLicencaShowModal('{{$item->id}}')">
                                                    <x-icon-licenca-plus class="fill-current w-6 h-8 pl-1" />
                                                </a>
                                            @endif
                                        </td>
                                        <td>                                           
                                            @if(isset($item->dist_zaduzeno))
                                                @if($item->dist_razduzeno)
                                                    <x-heroicon-m-arrow-path-rounded-square class="fill-green-500 w-6 h-6 mt-0.5 mr-0.5 float-left"  />
                                                    @money($item->dist_razduzeno) RSD
                                                @else
                                                    <x-heroicon-c-arrow-uturn-up class="fill-orange-400 w-5 h-5 mt-1 float-left" />
                                                    @money($item->dist_zaduzeno) RSD
                                                @endif
                                            @endif
                                        </td>  
                                        <td>
                                            @if(isset($item->dist_zaduzeno))
                                                @if($item->dist_razduzeno)
                                                <!-- RACUN -->
                                                    <a href="/dist-pdf-predracun?tip=r&lnid={{$item->lnid}}" target="_blank" class="flex border border-green-500 bg-green-100 hover:bg-green-600 text-stone-700 hover:text-white font-bold uppercase px-1 mr-2 rounded" title="Račun PDF">
                                                        <x-icon-file-pdf class="fill-current w-4 h-4 mr-1 mt-1" />
                                                        R
                                                    </a>
                                                @else
                                                <!-- PREDRACUN -->
                                                    <a href="/dist-pdf-predracun?tip=p&lnid={{$item->lnid}}" target="_blank" class="flex border border-orange-600 bg-orange-100 hover:bg-orange-600 text-stone-700 hover:text-white font-bold uppercase px-1 mr-2 rounded" title="Predračun PDF">
                                                        <x-icon-file-pdf class="fill-current w-4 h-4 mr-1 mt-1" />
                                                        P
                                                    </a>
                                                @endif
                                            @endif
                                        </td>   
                                        <td>
                                            @if($komentariTerminalVisible)
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
    {{-- selekciju više licenci moze da vrsi samo korisnik koji vidi komentare na terminalima --}}
    @if($komentariTerminalVisible)
        <div class="bg-blue-100 border-t border-b border-blue-500 text-blue-700 px-4 py-3 my-4 flex flex-row" role="alert">
            <div class="basis-1/2"><p class="text-sm">Ukupno izabranih licenci: <span class="font-bold"> {{ count($selectedTerminals) }}</span></p></div>
            <div class="basis-1/4 text-right mr-6">
            </div>
            <div class="basis-1/4 text-right mr-6">
                @if(count($selectedTerminals))
                    <button class="flex border border-green-500 bg-green-50 hover:bg-green-500 text-green-700 hover:text-white font-bold rounded py-1 px-2" wire:click="produziLicenceChecked()">
                        <x-icon-licenca-produzi class="fill-current w-8 h-8 px-1 py-1" />
                        <span class="mt-1">{{ __('Produži licence') }}</span>
                    </button>
                @endif
            </div>
        </div>
    @endif
   
    {{--  DODAJ LICENCU Modal ###############################################  --}}
    <x-jet-dialog-modal wire:model.live="dodajLicencuModalVisible">
        <x-slot name="title">
            {{ __('Dodaj licence terminalu') }}
        </x-slot>
        <x-slot name="content">
             @if($dodajLicencuModalVisible)
                <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
            @endif
            <div class="my-4">
                @if(count($licence_dodate_terminalu))
                    <div class="bg-gray-50 border-t-4 border-gray-500 px-4 py-3">
                        <p>Ranije dodate licence:</p>
                            @foreach( App\Models\LicencaDistributerCena::naziviDodatihLicenci($licence_dodate_terminalu) as $licenca)
                                <div class="mt-2">
                                    <div class="inline-block align-middle text-sm mr-4">Licenca: <span class="font-bold"> {{ $licenca->licenca_naziv }}</span></div>
                                </div>
                            @endforeach
                    </div>
                @endif
            </div>
            <div class="px-4 py-2 bg-green-50 border-t-4 border-green-400">
                <p class="font-bold">Kurs evra:</p>
                <table class="min-w-full divide-y divide-gray-200">
                    <tr class="text-center">
                        <td>Datum preuzimanja</td>
                        <td class="border-x border-slate-500">Datum kursa</td>
                        <td>Prodajni</td>
                        <td class="border-x border-slate-500">Srednji</td>
                        <td>Kupovni</td>
                    </tr>
                    <tr class="text-center">
                        <td>{{ App\Http\Helpers::datumFormatDan($kurs_evra->datum_preuzimanja) }}</td>
                        <td class="border-x border-slate-500">{{ App\Http\Helpers::datumFormatDan($kurs_evra->datum_kursa) }}</td>
                        <td>{{$kurs_evra->kupovni_kurs}}</td>
                        <td class="border-x border-slate-500">{{$kurs_evra->srednji_kurs}}</td>
                        <td>{{$kurs_evra->prodajni_kurs}}</td>
                    </tr>
                </table>
            </div>
            <div class="my-4">
            @if(count($licence_za_dodavanje))
                <div class="border-y py-2 bg-gray-50">
                    <p class="ml-4 font-bold">Trajanje licence:</p>
                    <div class="flex justify-between">
                        <div class="pl-4 my-4 flex">
                            <div class="mt-4 px-4">
                                <x-icon-acient-clock-inverse class="fill-blue-500 w-6 h-6" />
                            </div>
                            <div>
                                <x-jet-label for="datum_pocetka_licence" value="Datum početka licence" />
                                <x-jet-input id="datum_pocetka_licence" type="date" class="mt-1 block" value="{{ $datum_pocetka_licence }}" wire:model.live="datum_pocetka_licence" />
                                @error('datum_pocetka_licence') <span class="error">{{ $message }}</span> @enderror
                                <p class="p-2">{{ App\Http\Helpers::datumFormatDanFullYear($datum_pocetka_licence) }}</p>
                                @if($datum_pocetak_error != '')
                                    <p class="text-red-500"> {{$datum_pocetak_error}} </p>
                                @endif
                            </div>
                        </div>
                        <div class="pr-4 mt-4 flex">
                            <div class="mt-4 px-4">
                                <x-icon-acient-clock class="fill-blue-500 w-6 h-6" />
                            </div>
                            <div>
                                <x-jet-label for="datum_kraja_licence" value="Datum isteka licence" />
                                <x-jet-input id="datum_kraja_licence" type="date" class="mt-1 block" value="{{ $datum_kraja_licence }}" wire:model.live="datum_kraja_licence" />
                                @error('datum_kraja_licence') <span class="error">{{ $message }}</span> @enderror
                                <p class="p-2">{{ App\Http\Helpers::datumFormatDanFullYear($datum_kraja_licence) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            @if(!$dani_trajanja)
                <div class="my-4">
                    <div class="bg-red-50 border border-red-500 text-red-500 px-4 py-3 rounded relative my-4 " role="alert">
                        <p class="">Greška!<br />
                        <span class="font-bold block sm:inline">Datum isteka licence mora biti veći od datuma početka!</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                             <x-heroicon-c-exclamation-triangle class="fill-red-500 h-6 w-6"/>
                        </span>
                        </p>
                    </div>
                </div>
            @endif

            <div class="my-4">
                    <div>
                        @foreach( App\Models\LicencaDistributerCena::naziviNeDodatihLicenci($licence_za_dodavanje, $distId, $licence_dodate_terminalu) as $licenca_dodatak)
                        <div class="my-4 border-y py-2 bg-gray-50">
                            <input id="licAddM" type="checkbox" value="{{ $licenca_dodatak->id }}" wire:model.live="licence_za_dodavanje"  class="form-checkbox h-6 w-6 text-blue-500">
                            <span class="font-bold pl-2">{{ $licenca_dodatak->licenca_naziv }}</span>
                            @if(in_array($licenca_dodatak->id, $licence_za_dodavanje))
                                <div class="mt-2">
                                    <div class="max-w-2xl grid grid-cols-3 gap-2 mt-4 mb-4 ml-4">
                                        <div>Cena licence:
                                            <div class="px-1 flex bg-white text-center">
                                                <x-jet-input wire:model="unete_cene_licenci.{{$licenca_dodatak->id}}" id="" class="block form-input rounded-none w-28" type="text" />
                                                <span class="flex-1 px-1 pt-3 border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">RSD</span>
                                            </div>
                                        </div>
                                        <div class="px-1 border-x text-center">
                                            <p>Preporučena cena:</p>
                                            @if($dodajLicencuModalVisible)
                                            <p><span class="font-bold">@money($cene_licenci[$licenca_dodatak->id]->dist_cena_din)</span> RSD</p>
                                            <p><span class="font-bold">@money($cene_licenci[$licenca_dodatak->id]->dist_cena_eur)</span> EUR</p>
                                            @endif
                                        </div>
                                        <div class="px-1 text-center">
                                        <p>Cena Zeta:</p>
                                            @if($dodajLicencuModalVisible)
                                            <p><span class="font-bold">@money($cene_licenci[$licenca_dodatak->id]->zeta_cena_din)</span> RSD</p>
                                            <p><span class="font-bold">@money($cene_licenci[$licenca_dodatak->id]->zeta_cena_eur)</span> EUR</p>
                                            @endif
                                        </div>
                                    </div>
                                    @if($unete_cene_error[$licenca_dodatak->id] != '')
                                    <div class="px-4 text-red-600">
                                        {{ $unete_cene_error[$licenca_dodatak->id] }}
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="max-w-2xl grid grid-cols-5 gap-2 mt-4 mb-4 ml-10 border-t">
                                    @foreach(App\Models\LicencaParametar::parametriLicence($licenca_dodatak->licenca_tipId) as $parametar)
                                        <div class="px-1 rounded-md text-center">
                                            <input id="{{$parametar->id}}" type="checkbox" value="{{$parametar->id}}" wire:model.live="parametri"  class="form-checkbox h-6 w-6 text-blue-500 my-2"><br />
                                            <label class="break-words" for="{{$parametar->id}}">{{$parametar->param_opis}}</label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
            </div>
            
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('dodajLicencuModalVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
            @if(count($licence_za_dodavanje))
                <x-jet-button class="ml-2" wire:click="dodajLicenceTerminalu" wire:loading.attr="disabled">
                    {{ __('Dodaj licence') }}
                </x-jet-button>
            @endif
        </x-slot>
    </x-jet-dialog-modal>

    {{-- OBRISI LICENCU Modal ######################################################### --}}
    <x-jet-dialog-modal wire:model.live="modalConfirmDeleteVisible">
        <x-slot name="title">
            {{ __('Brisanje licence') }}
        </x-slot>

        <x-slot name="content">
            @if($modalConfirmDeleteVisible)
                <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
            @endif
            <div my-4>
                <p>Da li ste sigurni da želite da obišete licencu!<br />
                @if($licencaDeleteInfo)
                    <span class="font-bold">{{ $licencaDeleteInfo->licenca_naziv }}</span>
                @endif
                </p>
                @if(!$canDelete)
                    <div class="bg-red-50 border border-red-500 text-red-500 px-4 py-3 rounded relative my-4 " role="alert">
                        <p class="">Greška!<br />
                        <span class="font-bold block sm:inline">Zeta System DOO je napravio zaduženje za ovu licencu.</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <x-heroicon-c-exclamation-triangle class="fill-red-500 h-6 w-6"/>
                        </span>
                        </p>
                    </div>
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalConfirmDeleteVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
            @if($canDelete)
            <x-jet-danger-button class="ml-2" wire:click="delteLicenca" wire:loading.attr="disabled">
                {{ __('Obriši licencu') }}
            </x-jet-danger-button>
            @endif
        </x-slot>
    </x-jet-dialog-modal>
        
    {{-- PARAMETRI MODAL #################################################### --}}
    <x-jet-dialog-modal wire:model.live="parametriModalVisible">
        <x-slot name="title">
            <x-icon-licenca-parametri class="float-left fill-red-500 w-5 h-5 mr-2 mt-1" />
                Parametri licence
        </x-slot>
        <x-slot name="content">
            @if($parametriModalVisible)
                <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
                <div class="mr-4 mb-4">Licenca: <span class="font-bold">{{$pm_licenca_naziv}}</span></div>
                <div class="flex border-b border-blue-500 mt-2">
                    <div class="max-w-2xl grid grid-cols-5 gap-2 mb-4 ml-4">
                        @foreach(App\Models\LicencaParametar::parametriLicence($pm_licenca_tip_id) as $parametar)
                            <div class="px-1 bg-white rounded-md text-center">
                                <input id="{{$parametar->id}}" type="checkbox" value="{{$parametar->id}}" wire:model.live="parametri"  class="form-checkbox h-6 w-6 text-blue-500 my-2"><br />
                                <label class="break-words" for="{{$parametar->id}}">{{$parametar->param_opis}}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('parametriModalVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
            <x-jet-danger-button class="ml-2" wire:click="updateParametreLicence" wire:loading.attr="disabled">
                {{ __('Izmeni parametre') }}
            </x-jet-danger-button>
        </x-slot>
    </x-jet-dialog-modal>

    
    {{-- PREGLED LICENCE MODAL #########################################  --}}
    <x-jet-dialog-modal wire:model.live="pregledLicencaShowModal">
        <x-slot name="title">
            <x-icon-licenca class="float-left fill-current w-5 h-5 mr-2 mt-1" />
            @if($pregledLicencaShowModal)    
                Licenca: <span class="font-bold">{{$podaci_licence->licenca_naziv}}</span>
            @endif
        </x-slot>
        <x-slot name="content">
            @if($pregledLicencaShowModal)
                <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
                @if($mnth_diff < 1 || $lic_nenaplativa)
                    <div class="my-4 border-y py-2 bg-red-50 flex mx-4">
                        @if($lic_nenaplativa)
                            <x-icon-blacklist-scull class="fill-red-600 w-8 h-8 mx-4" />
                            <p class="flex-1 mt-2 font-bold text-lg text-red-600 uppercase">Licenca je označena kao "Nenaplativa"</p>
                        @else
                            @if($mnth_diff < 0)
                                <x-icon-calendar-x class="fill-red-600 w-8 h-8 mx-4" />
                                <p class="flex-1 mt-2 font-bold text-lg text-red-600 uppercase">Licenca je istekla!</p>
                            @else
                                <x-icon-acient-clock class="fill-red-300 w-8 h-8 mx-4" />
                                <p class="flex-1 mt-2 font-bold text-lg text-red-600 uppercase">Licenca uskoro ističe</p>
                            @endif
                        @endif
                    </div>
                @endif
                <div class="my-4 border-y py-2 bg-gray-50">
                    <p class="font-bold pl-2 mt-2">Trajanje:</p>
                    <div class="flex border-b pb-2">
                        <div class="pl-4 flex w-1/2">
                            <div class="mt-4 px-4">
                                <x-icon-acient-clock-inverse class="fill-blue-500 w-6 h-6" />
                            </div>
                            <div>
                               <p>Datum početka licence: </p>
                               <p class="font-bold">{{App\Http\Helpers::datumFormatDanFullYear($naplata_podaci_licence->datum_pocetka_licence)}}</p>
                            </div>
                        </div>
                        <div class="pr-4 flex">
                            <div class="mt-4 px-4">
                                <x-icon-acient-clock class="fill-blue-500 w-6 h-6" />
                            </div>
                            <div>
                                <p>Datum isteka licence:</p>
                                <p class="font-bold">{{App\Http\Helpers::datumFormatDanFullYear($naplata_podaci_licence->datum_kraj_licence)}}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex pb-2 mt-4">
                        <div class="pl-2 w-1/2 border-r border-slate-500">
                            <p class="font-bold">
                                <x-heroicon-c-arrow-uturn-up class="fill-orange-400 w-5 h-5 float-left" />
                                Zaduženo:
                            </p>
                            <p class="pl-2">
                                <span class="font-bold">@money($naplata_podaci_licence->dist_zaduzeno)</span> RSD
                            </p>
                            <p>Datum zaduženja: {{ App\Http\Helpers::datumFormatDanFullYear($naplata_podaci_licence->dist_datum_zaduzenja) }}</p>
                        </div>
                        <div class="pl-4">
                            <p class="font-bold pl-2">
                                <x-heroicon-m-arrow-path-rounded-square class="fill-green-500 w-6 h-6 mr-0.5 float-left"  />
                                Razduženo:
                            </p>
                            @if($naplata_podaci_licence->dist_razduzeno)
                                <p class="pl-2">    
                                    <span class="font-bold">@money($naplata_podaci_licence->dist_zaduzeno)</span> RSD
                                </p>
                                <p>Datum razduženja: {{ App\Http\Helpers::datumFormatDanFullYear($naplata_podaci_licence->dist_datum_razduzenja) }}</p>
                            @else
                                <div class="flex ml-4">
                                    <x-jet-input wire:model="razduzi_iznos" id="" class="block form-input rounded-none w-28" type="text" />
                                    <span class="flex-1 px-1 pt-3 border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">RSD</span>
                                    <x-jet-secondary-button wire:click="razduziUplatu" wire:loading.attr="disabled" class="flex-2 ml-4">
                                        {{ __('Razduži') }}
                                    </x-jet-secondary-button>
                                </div>
                                <div class="mt-2 pl-4">
                                    <p class="pl-2">Datum razduženja:</p>
                                    <x-jet-input wire:model="razduzi_datum" id="" class="block form-input rounded-none" type="date" />
                                </div>
                                @if($razduzi_iznos_error != '')
                                <div class="px-4 text-red-600">
                                    {{ $razduzi_iznos_error }}
                                </div>
                                @endif
                            @endif
                        </div>
                    </div>

                </div>

                <div class="my-4 border-y py-2 bg-gray-50">
                    <div class="flex ml-2">
                        <x-icon-zeta-logo class="w-8 h-8 mr-2" />
                        <span class="flex-1 text-lg font-bold mt-1">Zeta System DOO</span>
                    </div>
                    <div class="flex pb-2 mt-4">
                        <div class="pl-2 w-1/2 border-r border-slate-500">
                            <p class="font-bold">
                                <x-heroicon-c-arrow-uturn-up class="fill-orange-400 w-5 h-5 float-left" />
                                Zaduženo:
                            </p>
                            <p class="pl-2"> 
                                <span class="font-bold">@money($naplata_podaci_licence->zaduzeno)</span> RSD
                            </p>
                            <p>Datum zaduženja:@if($naplata_podaci_licence->datum_zaduzenja) {{ App\Http\Helpers::datumFormatDanFullYear($naplata_podaci_licence->datum_zaduzenja) }}@endif</p>
                        </div>
                        <div class="pl-4 w-1/2 relative">
                            <p class="font-bold pl-2">
                                 <x-heroicon-m-arrow-path-rounded-square class="fill-green-500 w-6 h-6 mr-0.5 float-left"  />
                                Razduženo:
                            </p>
                            <p class="pl-2"> 
                                <span class="font-bold">@money($naplata_podaci_licence->razduzeno)</span> RSD
                            </p>
                            <p>Datum razaduženja: @if($naplata_podaci_licence->datum_razduzenja){{ App\Http\Helpers::datumFormatDanFullYear($naplata_podaci_licence->datum_razduzenja) }}@endif</p>
                            @if($naplata_podaci_licence->zaduzeno && !$naplata_podaci_licence->razduzeno)
                                <span class="absolute top-0 bottom-0 right-0 px-4 py-4">
                                    <x-heroicon-c-exclamation-triangle class="fill-red-500 h-6 w-6 " />
                                </span>
                            @elseif($naplata_podaci_licence->razduzeno)
                                <span class="absolute top-0 bottom-0 right-0 px-4 py-4">
                                     <x-heroicon-c-check class="fill-green-500 w-8 h-8" />
                                </span>
                            @endif
                        </div>
                    </div>
                    @if(!$naplata_podaci_licence->razduzeno)
                        <div class="px-4 text-red-600"> Licenca je privremena dok se uplata prema Zeta System DOO ne razduži!</div>
                    @endif
                </div>
                @if($naplata_podaci_licence->razduzeno && !$lic_nenaplativa)
                <div class="my-4 border-y py-2 bg-gray-50">
                    <div class="flex mx-4">
                        <a class="cursor-pointer flex border border-green-500 bg-green-50 hover:bg-green-500 text-green-700 hover:text-white font-bold rounded w-min" wire:click="produziLicencuShovModal('{{$modelId}}', '{{$licenca_naplata_id}}')" title="Produži licencu">
                            <x-icon-licenca-produzi class="fill-current w-8 h-8 px-1 py-1" />
                        </a>
                        <p class="flex-1 mt-2 ml-2 font-bold">Produži licencu</p>
                    </div>
                </div>
                @endif
                <div class="my-4 border-y py-2 bg-gray-50">
                    <div class="flex mx-4">
                        <a class="cursor-pointer flex border border-stone-500 bg-stone-50 hover:bg-stone-500 text-stone-700 hover:text-white font-bold rounded w-min" wire:click="dodajIzPregledaLicenceShovModal('{{$modelId}}')" title="Dodaj licencu">
                            <x-icon-licenca-plus class="fill-current w-8 h-8 px-1 py-1" />
                        </a>
                        <p class="flex-1 mt-2 ml-2 font-bold">Dodaj licence terminalu</p>
                    </div>
                </div>
                @if($licenca_ima_parametre)
                    <div class="my-4 border-y py-2 bg-gray-50">
                        <div class="flex mx-4">
                             <a class="cursor-pointer flex border border-sky-500 text-sky-500 bg-stone-50 hover:bg-sky-500 hover:text-white font-bold rounded w-min" wire:click="parametriIzPregledaLicenceShovModal('{{$licenca_naplata_id}}')" title="Parametri licence">
                                <x-icon-licenca-parametri class="fill-current w-8 h-8 px-1 py-1" />
                            </a>
                            <p class="flex-1 mt-2 ml-2 font-bold">Parametri licence</p>
                        </div>
                    </div>
                @endif
                @if($licenca_moze_da_se_brise && !$lic_nenaplativa)
                    <div class="my-4 border-y py-2 bg-gray-50">
                        <div class="flex mx-4">
                            <a class="cursor-pointer flex border border-red-500 text-red-500 bg-stone-50 hover:bg-red-500 hover:text-white font-bold uppercase rounded w-min" wire:click="deleteIzPregledaLicencuShowModal('{{$modelId}}', '{{$licenca_naplata_id}}')" title="Obriši licencu">
                                <x-icon-licenca-obrisi class="fill-current w-8 h-8 px-1 py-1" />
                            </a>
                            <p class="flex-1 mt-2 ml-2 font-bold">Obriši licencu</p>
                        </div>
                    </div>
                @endif
            @endif
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('pregledLicencaShowModal')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>

    {{-- PRODUZI LICENCU MODAL ######################################################### --}}
    <x-jet-dialog-modal wire:model.live="produziLicModalVisible">
        <x-slot name="title">
            <x-icon-licenca-produzi class="float-left fill-green-500 w-5 h-5 mr-2 mt-1"/>
                Produži licencu
            Produži licencu <span class="font-bold">@if($produziLicModalVisible){{$naziv_licence}}@endif</span>
        </x-slot>
        <x-slot name="content">
            @if($produziLicModalVisible && !$produziCheckedMode)
                <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
            @elseif($produziCheckedMode)
                <div class="px-4 py-2 bg-blue-50 border-l-4 border-blue-400 mb-2">
                    Produženje ESIR licence za <span class="font-bold">{{ count($selectedTerminals) }}</span> selektovanih terminala.
                </div>
            @endif
            <div class="px-4 py-2 bg-green-50 border-t-4 border-green-400">
                <p class="font-bold">Kurs evra:</p>
                <table class="min-w-full divide-y divide-gray-200">
                    <tr class="text-center">
                        <td>Datum preuzimanja</td>
                        <td class="border-x border-slate-500">Datum kursa</td>
                        <td>Prodajni</td>
                        <td class="border-x border-slate-500">Srednji</td>
                        <td>Kupovni</td>
                    </tr>
                    <tr class="text-center">
                        <td>{{ App\Http\Helpers::datumFormatDan($kurs_evra->datum_preuzimanja) }}</td>
                        <td class="border-x border-slate-500">{{ App\Http\Helpers::datumFormatDan($kurs_evra->datum_kursa) }}</td>
                        <td>{{$kurs_evra->kupovni_kurs}}</td>
                        <td class="border-x border-slate-500">{{$kurs_evra->srednji_kurs}}</td>
                        <td>{{$kurs_evra->prodajni_kurs}}</td>
                    </tr>
                </table>
            </div>
            <div class="my-4">
                <div class="border-y py-2 bg-gray-50">
                    <p class="ml-4 font-bold">Trajanje licence:</p>
                    <div class="flex justify-between">
                        <div class="pl-4 my-4 flex">
                            <div class="mt-4 px-4">
                                <x-icon-acient-clock-inverse class="fill-blue-500 w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-sm mb-1">Datum početka licence</p>
                                <div class="border border-gray-300 rounded p-2 text-center">{{ App\Http\Helpers::datumFormatDanFullYear($datum_pocetka_licence) }}</div>
                            </div>
                        </div>
                        <div class="pr-4 mt-4 flex">
                            <div class="mt-4 px-4">
                                <x-icon-acient-clock class="fill-blue-500 w-6 h-6" />
                            </div>
                            <div>
                                <x-jet-label for="datum_kraja_licence" value="Datum isteka licence" />
                                <x-jet-input id="datum_kraja_licence" type="date" class="mt-1 block" value="{{ $datum_kraja_licence }}" wire:model.live="datum_kraja_licence" />
                                @error('datum_kraja_licence') <span class="error">{{ $message }}</span> @enderror
                                <p class="p-2">{{ App\Http\Helpers::datumFormatDanFullYear($datum_kraja_licence) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if(!$dani_trajanja)
                <div class="my-4">
                    <div class="bg-red-50 border border-red-500 text-red-500 px-4 py-3 rounded relative my-4 " role="alert">
                        <p class="">Greška!<br />
                        <span class="font-bold block sm:inline">Datum isteka licence mora biti veći od datuma početka!</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <x-heroicon-c-exclamation-triangle class="fill-red-500 h-6 w-6"/>
                        </span>
                        </p>
                    </div>
                </div>
            @endif
            
            <div class="my-4">
                <div class="my-4 border-y py-2 bg-gray-50">
                    <div class="mt-2">
                        <div class="max-w-2xl grid grid-cols-3 gap-2 mt-4 mb-4 ml-4">
                            <div>Cena licence:
                                <div class="px-1 flex bg-white text-center">
                                    <x-jet-input wire:model="produzenje_cena_licence" id="" class="block form-input rounded-none w-28" type="text" />
                                    <span class="flex-1 px-1 pt-3 border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">RSD</span>
                                </div>
                            </div>
                            <div class="px-1 border-x text-center">
                                <p>Preporučena cena:</p>
                                @if($produziLicModalVisible)
                                <p><span class="font-bold">@money($produzenje_cene[0]->dist_cena_din)</span> RSD</p>
                                <p><span class="font-bold">@money($produzenje_cene[0]->dist_cena_eur)</span> EUR</p>
                                @endif
                            </div>
                            <div class="px-1 text-center">
                            <p>Cena Zeta:</p>
                                @if($produziLicModalVisible)
                                <p><span class="font-bold">@money($produzenje_cene[0]->zeta_cena_din)</span> RSD</p>
                                <p><span class="font-bold">@money($produzenje_cene[0]->zeta_cena_eur)</span> EUR</p>
                                @endif
                            </div>
                        </div>
                        @if($produzenje_unete_cene_error != '')
                        <div class="px-4 text-red-600">
                            {{ $produzenje_unete_cene_error }}
                        </div>
                        @endif
                    </div>
                    
                   
                    <div class="max-w-2xl grid grid-cols-5 gap-2 mt-4 mb-4 ml-10 border-t">
                        @foreach(App\Models\LicencaParametar::parametriLicence($produzenje_tip_licence) as $parametar)
                            <div class="px-1 rounded-md text-center">
                                <input id="{{$parametar->id}}" type="checkbox" value="{{$parametar->id}}" wire:model.live="parametri"  class="form-checkbox h-6 w-6 text-blue-500 my-2"><br />
                                <label class="break-words" for="{{$parametar->id}}">{{$parametar->param_opis}}</label>
                            </div>
                        @endforeach
                    </div>
                   
                </div>
            </div>

        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('produziLicModalVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
            <x-jet-danger-button class="ml-2" wire:click="produziLicencu" wire:loading.attr="disabled">
                {{ __('Produži licencu') }}
            </x-jet-danger-button>
        </x-slot>
    </x-jet-dialog-modal>

    {{-- GREŠKA Modal ######################################################### --}}
    <x-jet-dialog-modal wire:model.live="errorModalVisible">
        <x-slot name="title">
            {{ __('Greška') }}
        </x-slot>

        <x-slot name="content">
            <div my-4>
                <div class="bg-red-50 border border-red-500 text-red-500 px-4 py-3 rounded relative my-4 " role="alert">
                    <p class="">Greška!<br />
                    <span class="font-bold block sm:inline">{{ $error_message}}.</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <x-heroicon-c-exclamation-triangle class="fill-red-500 h-6 w-6"/>
                    </span>
                    </p>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('errorModalVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>

        {{-- KOMENTARI MODAL ################################################################### --}}
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

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

</div>
