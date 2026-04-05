<div class="p-6">
    {{-- The data table --}}
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 mb-4" style="width: 100% !important">
                        
                        <tbody class="bg-white divide-y divide-gray-200"> 
                        {{-- SEARCH ROW --}}
                            <tr class="bg-orange-50">
                                <td>
                                    <x-heroicon-o-funnel class="mx-auto text-orange-600 w-4 h-4" />
                                </td>
                                <td class="p-1">
                                    <x-jet-input wire:model="searchSB" id="" class="block bg-orange-50 w-full" type="text" placeholder="Serijski broj" />
                                </td>
                                <td class="p-1">
                                    <x-jet-input wire:model="searchName" id="" class="block bg-orange-50 w-full" type="text" placeholder="Lokacija" />
                                </td>
                                <td class="p-1">
                                    <select wire:model="searchTip" id="" class="block appearance-none bg-orange-50 border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">-- Tip lokacije --</option>
                                        @foreach (App\Models\LokacijaTip::tipoviList() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="p-1">
                                    <select wire:model="searchStatus" id="" class="block appearance-none bg-orange-50 border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">-- Status --</option>
                                        @foreach (App\Models\TerminalStatusTip::tipoviList() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-right text-sm pr-4">Ukupno: <span class="font-bold">{{ $data->total() }}</span></td>
                            </tr>
                            {{-- SEARCH 2nd ROW --}}
                            <tr class="bg-orange-50">
                                <td></td>
                                <td class="p-1">
                                    <x-jet-input wire:model="searchKutija" id="" class="block bg-orange-50 w-full" type="text" placeholder="Broj kutije" />
                                </td>
                                <td class="p-1">
                                    <select wire:model="searchRegion" id="" class="block appearance-none bg-orange-50 border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                                <option value="">-- Region --</option>
                                            @foreach (App\Models\Region::regioni() as $key => $value)    
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                    </select>
                                </td>
                                <td class="p-1">
                                    <select wire:model="searchVendor" id="" class="block appearance-none bg-orange-50 border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                            <option value="">-- Vendor --</option>
                                        @foreach (App\Models\TerminalVendor::allList() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="p-1">
                                    <x-jet-input wire:model="searchCampagin" id="" class="block bg-orange-50 w-full" type="text" placeholder="Kampanja" />
                                </td>
                                <td class="p-1">
                                    <x-jet-input wire:model="searchPib" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži PIB" />
                                </td>    
                            </tr>  
                        </tbody>
                    </table>


                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500"><input type="checkbox" value="1" wire:model="selectAll.1"  class="form-checkbox h-6 w-6 text-blue-500"></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">L</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Serijski broj <br /><span class=" text-red-400">Kutija</span></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Lokacija <br /><span class=" text-red-400">Region</span></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Lok. tip <br /><span class="text-gray-800">Vendor</span></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Distributer <br /><span class=" text-green-600">Kampanja</span></th></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Status</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Premesti</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Istorija</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Tiket</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Komentari</th>
                            </tr>
                        </thead>
                            <!-- DATA  -->                   
                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr @if($loop->even) class="bg-gray-50" @endif >
                                        {{-- CHECK FIELD --}}
                                        <td class="px-1 py-1">
                                            <input type="checkbox" value="{{ $item->tlid }}" wire:model="selectedTerminals"  class="form-checkbox h-6 w-6 text-blue-500">
                                        </td>

                                        {{-- LICENCA --}}
                                        <td class="px-1 py-2">
                                            @if($item->blacklist == 1)
                                                <!-- SERVISER VIDI DA JE TERMINAL NA BLACKLISTI -->
                                                <x-icon-blacklist-scull class="fill-current w-5 h-5" />
                                            @elseif($item->tzlid)
                                                <!--  TERMINAL IMA LICENCU -->
                                                @if($item->tzlid == 1 || $item->tzlid == 3)
                                                    <!-- TERMINAL IMA REGULARNU LICENCU -->
                                                    <a class="flex p-1 cursor-pointer flex border border-stone-500 bg-stone-50 hover:bg-stone-500 text-stone-700 hover:text-white rounded max-w-[30px]" wire:click="licencaShowModal({{ $item->tlid}}, {{$item->tzlid}})" title="Pregled licence">
                                                        <x-icon-licenca class="fill-current w-5 h-5" />
                                                    </a>
                                                @elseif($item->tzlid == 2)
                                                    <!-- TERMINAL IMA SERVISNU LICENCU -->
                                                    <a class="flex p-1 cursor-pointer flex border border-green-800 bg-green-50 text-green-800 hover:bg-stone-500 text-stone-700 hover:text-white rounded max-w-[30px]" wire:click="licencaShowModal({{ $item->tlid}}, {{$item->tzlid}})" title="Pregled licence" >
                                                        <x-icon-licenca-servisna class="fill-current w-5 h-5" />
                                                    </a>
                                                @endif
                                            @else
                                                <!-- TERMINAL NEMA LICENCU I MOZE MU SE DODATI SERVISNA LICENCA -->
                                                <a class="flex p-1 cursor-pointer flex border border-sky-600 bg-sky-50 hover:bg-stone-500 text-sky-600 hover:text-white rounded max-w-[30px]" title="Dodaj SERVISNU licencu" wire:click="novaServisnaShowwModal({{ $item->tlid}},  {{$item->tzlid}})">
                                                    <x-icon-licenca-servisna-dodaj class="fill-current w-5 h-5" />
                                                </a>
                                            @endif
                                        </td>
                                        {{-- SN KUTIJA --}}
                                        <td class="px-1 py-2">
                                            {{ $item->sn }} <br />
                                            <span class="text-red-500 text-xs">{{ $item->broj_kutije }}</span>
                                        </td>
                                        {{-- LOKACIJA REGION --}}
                                        <td class="px-1 py-2 text-xs">
                                            @if($item->is_duplicate)<span class="text-red-500">*</span>@endif
                                            {{ $item->l_naziv }}&nbsp;{{ $item->l_naziv_sufix }}<br />
                                            <span class="text-sm text-red-400"> {{ $item->r_naziv }}</span>
                                        </td>
                                        {{-- LOKACIJA TIP --}}
                                        <td class="px-1 py-2 text-center">
                                            
                                            @switch($item->lokacija_tipId)
                                                @case(1)
                                                    {{-- Servisni centar --}}
                                                    <x-heroicon-o-wrench-screwdriver class="text-red-400 mx-auto w-5 h-5" />
                                                @break
                                                @case(2)
                                                    {{-- Magacin --}}
                                                    <x-heroicon-o-building-library class="text-gray-400 mx-auto w-5 h-5"/>
                                                @break
                                                @case(3)
                                                    {{-- Korisnik terminala --}}
                                                    <x-heroicon-o-building-storefront class="text-sky-400 mx-auto w-5 h-5"/>
                                                @break
                                                @case(4)
                                                    {{-- Distributer --}}
                                                    <x-icon-distributer class="fill-emerald-400 mx-auto w-5 h-5" />
                                                @break
                                            @endswitch
                                            
                                            <button class="px-2 py-0.5 text-sm relative text-gray-800 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="vendorShowModal({{ $item->tlid}}, {{ $item->tid}}, {{ $item->vendor_id }})">
                                                @if($item->vendor_name == null) <x-heroicon-o-plus-circle class="w-5 h-5" /> @else {{ $item->vendor_name }} @endif
                                            </button>
                                        </td>
                                        {{-- DISTRIBUTER/ KAMPANJA --}}
                                        <td class="px-1 py-2 text-xs" style="max-width: 200px !important;">
                                            {{ $item->distributer_naziv }}@if( $item->campagin_name )<br /><span class="text-xs text-green-600">{{ $item->campagin_name }}</span>@endif
                                        </td> 
                                        
                                        {{-- STATUS --}}
                                        <td class="px-1 py-2">
                                            <button class="px-2 py-2 text-sm relative text-gray-800 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="statusShowModal({{ $item->tlid}}, {{ $item->statusid }})">
                                                {{ $item->ts_naziv }}
                                            </button>
                                        </td>
                                        {{-- PREMESTI --}}
                                        <td class="px-1 py-1">
                                           <button class="px-2 py-2 text-sm relative text-gray-800 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="premestiShowModal({{ $item->tlid }}, {{ $item->statusid }})">
                                                <x-heroicon-o-arrows-right-left class="fill-current w-4 h-4 mr-0" />
                                            </button>
                                        </td>
                                        {{-- Istorija terminala --}}
                                        <td class="px-1 py-1">
                                            <button class="px-2 py-2 text-sm relative text-gray-800 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="terminalHistoryShowModal({{ $item->tlid }})" title="Istorija terminala">
                                                <x-icon-history class="fill-current w-4 h-4 mr-0" />
                                            </button>
                                        </td>
                                        {{-- Tiket --}}
                                        <td class="px-1 py-1">
                                            <button class="px-2 py-2 text-sm relative text-gray-800 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="newTiketShowModal({{ $item->tlid }})" title="Novi tiket">
                                                <x-icon-ticket-plus class="fill-current w-4 h-4 mr-0" />
                                            </button>
                                        </td>
                                        {{-- Komentari --}}
                                        <td>
                                            <button class="px-2 py-1 text-sm relative text-gray-600 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="commentsShowModal({{ $item->tlid }})" title="Komentari">
                                                <div class="mx-1 text-lg">{{ $item->br_komentara}}</div>
                                                <x-heroicon-o-chat-bubble-bottom-center-text class="z-10 absolute top-1 right-1 text-gray-400 -mt-1.5 ml-2.5 w-4 h-4"/>
                                            </button>
                                        </td>
                                    </tr>
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
    <div class="bg-blue-100 border-t border-b border-blue-500 text-blue-700 px-4 py-3 my-4 flex flex-row" role="alert">
        <div class="basis-1/2"><p class="text-sm">Ukupno izabranih terminala: <span class="font-bold"> {{ count($selectedTerminals) }}</span></p></div>
        <div class="basis-1/4 text-right mr-6">
            @if(count($selectedTerminals))
                <x-jet-button class="ml-2" wire:click="statusSelectedShowModal()">
                    {{ __('Promeni status') }}
                </x-jet-button>
            @endif
        </div>
        <div class="basis-1/4 text-right mr-6">
            @if(count($selectedTerminals))
                <x-jet-button class="ml-2" wire:click="premestiSelectedShowModal()">
                    {{ __('Premesti') }}
                </x-jet-button>
            @endif
        </div>
    </div>
    

    {{-- LICENCA MODAL ##########################################################################--}}
    <x-jet-dialog-modal wire:model="licencaModalVisible">
        <x-slot name="title">
            LICENCE
        </x-slot>
        <x-slot name="content"> 
            @if($licencaModalVisible)
                <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
                
                @foreach ($licencaData as $licenca)
                    <div class="bg-stone-100 border-t-4 border-stone-500 rounded-b text-stone-900 px-4 py-3 shadow-md mb-6" role="alert">
                        <div class="flex">
                            <div class="py-1 px-2"><svg class="fill-current w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M373.1,134.6L253.4,15.3C243.5,5.5,230.2,0,216.3,0H59C26.5,0,0,26.5,0,59v394c0,32.5,26.5,59,59,59h266 c32.5,0,59-26.5,59-59V160.9C384,151.1,380.1,141.6,373.1,134.6z M354.9,151.8h-61.5c-35.8,0-65-29.2-65-65v-59 c2.7,1.3,5.1,3.1,7.3,5.2L354.9,151.8z M359,453c0,9-3.6,17.5-10,24c-6.5,6.5-15,10-24,10H59c-9,0-17.5-3.6-24-10 c-6.5-6.5-10-15-10-24V59c0-9,3.6-17.5,10-24c6.5-6.5,15-10,24-10h144.4v61.8c0,49.6,40.4,90,90,90H359V453z"/><g><path d="M159.9,391.1h111.3v26.3h-141V197.8h29.7V391.1z"/></g></svg></div>
                            <div class="w-full">
                                <p class="font-bold my-2">{{ $licenca->naziv_licence }}</p>
                                @php
                                    $privremena = (isset($licenca->razduzeno)) ? 0 : 1;
                                @endphp
                                @if($licenca->licenca_poreklo == 2)
                                    <p class="text-sm pt-2">Status:  <span class="font-bold text-green-700"> Servisna </span> </p>
                                @elseif($privremena)
                                    <div class="bg-red-100 p-2 w-full">
                                        <p class="text-sm pt-2">Status:  <span class="font-bold text-red-700"> Privremena </span> </p>
                                        <p class="text-sm pt-2">Datum isteka: <span class="font-bold text-red-700">{{ App\Http\Helpers::datumFormatDanFullYear($licenca->datum_kraj) }}</span></p>
                                        <p class="text-sm pt-2">Datum prekoračenja: <span class="font-bold text-red-700">{{ App\Http\Helpers::datumFormatDanFullYear($licenca->datum_prekoracenja) }}</span></p> 
                                        @if(App\Http\Helpers::dateGratherOrEqualThan(App\Http\Helpers::datumKalendarNow(), $licenca->datum_prekoracenja))
                                            <div class="h-12">
                                                <div class="float-right">
                                                    <x-jet-secondary-button class="ml-2 mt-2" wire:click="pomeriPrekoracenjePrivremenoj('{{$licenca->naziv_licence}}', {{$licenca->distributerId}}, {{$licenca->licenca_distributer_cenaId}})" >
                                                        {{ __('Pomeri datum prekoračenja') }}
                                                    </x-jet-secondary-button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="text-sm pt-2 mt-2 font-bold">Predviđeno trajanje:</p>
                                @else
                                    <p class="text-sm pt-2">Status:  <span class="font-bold text-red-700"> Trajna </span> </p>
                                @endif

                                <!-- AKO JE SERVISNA DATUMI IZ TABELE licenca_za_terminals -->
                                @if($licenca->licenca_poreklo == 2)
                                    <p class="text-sm pt-2">Datum isteka: <span class="font-bold text-red-700">{{ App\Http\Helpers::datumFormatDanFullYear($licenca->datum_kraj) }}</span></p>
                                    <p class="text-sm pt-2">Datum prekoračenja: <span class="font-bold text-red-700">{{ App\Http\Helpers::datumFormatDanFullYear($licenca->datum_prekoracenja) }}</span></p>
                                @else
                                    <p class="text-sm pt-2">Datum isteka: <span class="font-bold text-red-700">{{ App\Http\Helpers::datumFormatDanFullYear($licenca->datum_kraj_licence) }}</span></p>
                                    <p class="text-sm pt-2">Datum prekoračenja: <span class="font-bold text-red-700">{{ App\Http\Helpers::datumFormatDanFullYear($licenca->datum_isteka_prekoracenja) }}</span></p>
                                @endif
                                <!-- AKO JE TRAJNA I ISTEKLA PRIKAZI DUGME DODAJ SERVISNU -->
                                 @if(App\Http\Helpers::dateGratherOrEqualThan(App\Http\Helpers::datumKalendarNow(), $licenca->datum_isteka_prekoracenja) && $licenca->licenca_poreklo == 1)
                                    <div class="">
                                        <div class="float-right">
                                            <x-jet-secondary-button class="ml-2 mt-2" wire:click="novaServisnaIzPregleda('{{$licenca->naziv_licence}}', {{$licenca->distributerId}}, {{$licenca->licenca_distributer_cenaId}})" >
                                                {{ __('Dodaj SERVISNU licencu') }}
                                            </x-jet-secondary-button>
                                        </div>
                                    </div>
                                 @endIF
                            </div>
                        </div>
                    </div>
                @endforeach
                
            @endif
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('licencaModalVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>

    {{-- ERROR LICENCA ###################################################################### --}}
    <x-jet-dialog-modal wire:model="modalErorLicencaVisible">
        <x-slot name="title">
            GREŠKA
        </x-slot>
        <x-slot name="content"> 
            <div class="bg-red-50 border border-red-500 text-red-500 px-4 py-3 rounded relative my-4 " role="alert">
                <p class="">Greška!<br />
                <span class="font-bold block sm:inline">
                    @if($licencaError == 'multi')
                        Jedan ili više izabranih terminala imaju licencu! <br />
                        Samo terminali bez licenci mogu da se premeštaju.
                    @elseif($licencaError == 'db')
                        Došlo je do greške! <br />
                        Pokušajte kasnije.
                    @endif
                </span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <svg class="fill-red-500 h-6 w-6 " xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M506.3 417l-213.3-364c-16.33-28-57.54-28-73.98 0l-213.2 364C-10.59 444.9 9.849 480 42.74 480h426.6C502.1 480 522.6 445 506.3 417zM232 168c0-13.25 10.75-24 24-24S280 154.8 280 168v128c0 13.25-10.75 24-23.1 24S232 309.3 232 296V168zM256 416c-17.36 0-31.44-14.08-31.44-31.44c0-17.36 14.07-31.44 31.44-31.44s31.44 14.08 31.44 31.44C287.4 401.9 273.4 416 256 416z"/></svg>
                </span>
                </p>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalErorLicencaVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>


    {{-- STATUS ##############################################################################  --}}
    <x-jet-dialog-modal wire:model="modalFormVisible">
        <x-slot name="title">
            {{ __('Promeni status terminala') }}
        </x-slot>

        <x-slot name="content">
            @if($modalFormVisible)
                @if ($multiSelected)
                    <livewire:komponente.terminal-info :terminal_lokacija_id="0" :multySelectedArray="$selectedTerminals" :multySelected="true"  />
                @else
                    <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
                @endif
                
            @endif
            <div class="mt-4 bg-gray-50 p-4 border-t-4 border-grey-800 rounded-b text-grey-900 shadow-md mb-6" role="alert">
            <div><span class="font-bold">Novi status terminala:</span></div>
                <select wire:model.defer="modalStatus" id="" class="block appearance-none w-full border border-1 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                    @foreach (App\Models\TerminalStatusTip::tipoviList() as $key => $value)    
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('terminalStatus') <span class="error">{{ $message }}</span> @enderror
            </div>  
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalFormVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>

            @if ($modelId || $multiSelected)
                <x-jet-button class="ml-2" wire:click="statusUpdate" wire:loading.attr="disabled">
                    {{ __('Update') }}
                </x-jet-danger-button>
            @else
               <div>Nešto nije u redu?!?</div>
            @endif            
        </x-slot>
    </x-jet-dialog-modal>


    {{-- PREMESTI Modal ############################################################################--}}
    <x-jet-dialog-modal wire:model="modalConfirmPremestiVisible">
        <x-slot name="title">
            {{ __('Premesti terminal') }}
        </x-slot>

        <x-slot name="content">
        @if($modalConfirmPremestiVisible)
            @if ($multiSelected)
                <livewire:komponente.terminal-info :terminal_lokacija_id="0" :multySelectedArray="$selectedTerminals" :multySelected="true"  />
            @else
                <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
            @endif


                <div class="font-bold">Nova lokacija terminala:</div>
        @endif
        @if(!$plokacija)
            <x-jet-label for="tiplokacije" value="{{ __('Izaberi tip lokacije') }}" />
            <select wire:model="plokacijaTip" id="" class="block appearance-none bg-gray-50 w-full border border-1 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                <option value="">---</option>
                @foreach (App\Models\LokacijaTip::tipoviList() as $key => $value)    
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
           
                @if($plokacijaTip == 3 || $plokacijaTip == 4)
                    {{-- KORISNIK ili DISTRIBUTER terminala search by name --}}
                    <table class="min-w-full divide-y divide-gray-200 mt-4" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500"></th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Naziv</th> 
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Mesto</th> 
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Region</th> 
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500"></th>  
                            </tr>
                            <tr class="bg-orange-50">
                                <td><svg class="mx-auto fill-orange-600 w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M3.853 54.87C10.47 40.9 24.54 32 40 32H472C487.5 32 501.5 40.9 508.1 54.87C514.8 68.84 512.7 85.37 502.1 97.33L320 320.9V448C320 460.1 313.2 471.2 302.3 476.6C291.5 482 278.5 480.9 268.8 473.6L204.8 425.6C196.7 419.6 192 410.1 192 400V320.9L9.042 97.33C-.745 85.37-2.765 68.84 3.854 54.87L3.853 54.87z"/></svg></td>
                                <td><x-jet-input wire:model="searchPLokacijaNaziv" id="" class="block bg-orange-50 w-full" type="text" placeholder="Naziv" /></td>
                                <td><x-jet-input wire:model="searchPlokacijaMesto" id="" class="block bg-orange-50 w-full" type="text" placeholder="Mesto" /></td>
                                <td>
                                     <select wire:model="searchPlokacijaRegion" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                                <option value="">---</option>
                                            @foreach (App\Models\Region::regioni() as $key => $value)    
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                    </select>
                                </td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"> 
                        @foreach ($this->lokacijeTipa($plokacijaTip) as $value)
                        <tr class="hover:bg-gray-100" wire:click="$set('plokacija', {{ $value->id }})" >    
                                <td></td>
                                <td>
                                    @if($value->is_duplicate)<span class="text-red-500">*</span>@endif
                                    {{ $value->l_naziv }}&nbsp;{{ $value->l_naziv_sufix }}
                                </td>
                                <td>{{ $value->mesto}}</td>
                                <td>{{ $value->r_naziv}}</td>
                                <td></td>
                        </tr>
                        @endforeach
                        </tbody>
                    <table>
                    <div class="mt-5">
                        {{ $this->lokacijeTipa($plokacijaTip)->links() }}
                    </div>

                @elseif($plokacijaTip != 0 && $plokacijaTip != 3)
                {{-- MAGACIN ILI SEVIS --}}
                    <x-jet-label for="lokacija" value="{{ __('Izaberi lokaciju') }}" class="mt-4" />
                    <select wire:model="plokacija" id="" class="block appearance-none bg-gray-50 w-full border border-1 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                        <option value="">---</option>
                        @foreach (App\Models\Lokacija::lokacijeTipa($plokacijaTip) as $key => $value)    
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                @endif
        @else
            {{-- IZABRAO LOKACIJU MENJAM PRIKAZ --}}
            @php
                $canMoveTerminal = 1;
            @endphp
            <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                <div class="flex">
                    <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M168.3 499.2C116.1 435 0 279.4 0 192C0 85.96 85.96 0 192 0C298 0 384 85.96 384 192C384 279.4 267 435 215.7 499.2C203.4 514.5 180.6 514.5 168.3 499.2H168.3zM192 256C227.3 256 256 227.3 256 192C256 156.7 227.3 128 192 128C156.7 128 128 156.7 128 192C128 227.3 156.7 256 192 256z"/></svg></div>
                    <div>
                    <p class="font-bold">{{ $this->novaLokacija()->l_naziv}}&nbsp;{{ $this->novaLokacija()->l_naziv_sufix }}, {{ $this->novaLokacija()->mesto }}</p>
                    <p class="text-sm">Region: {{ $this->novaLokacija()->r_naziv }}</p>
                    </div>
                </div>
            </div>        
        @endif

            <div class="mt-4">
                <x-jet-label for="pterminalStatus" value="{{ __('Novi status terminala') }}" />
                    <select wire:model="modalStatusPremesti" id="" class="block appearance-none bg-gray-50 w-full border border-1 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                        @foreach (App\Models\TerminalStatusTip::tipoviList() as $key => $value)    
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('pterminalStatus') <span class="error">{{ $message }}</span> @enderror
            </div> 
            
            <div class="mb-6 mt-4">
                <x-jet-label for="date_akcije" value="Datum promene:" />
                <x-jet-input id="date_akcije" type="date" class="mt-1 block" value="{{ $datum_premestanja_terminala }}" wire:model.defer="datum_premestanja_terminala" />
                <x-jet-input-error for="date_akcije" class="mt-2" />
            </div>

        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalConfirmPremestiVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
            @if($canMoveTerminal)
                <x-jet-danger-button class="ml-2" wire:click="moveTerminal" wire:loading.attr="disabled">
                    {{ __('Premesti terminal') }}
                </x-jet-danger-button>
            @endif
        </x-slot>
    </x-jet-dialog-modal>

    
    {{-- HISTORI Modal ################################################################################## --}}
    <x-jet-dialog-modal wire:model="terminalHistoryVisible">
        <x-slot name="title">
            {{ __('Vremenska linija terminala') }}
        </x-slot>
       
        <x-slot name="content">
            @if($terminalHistoryVisible)
                <div class="relative">
                    <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
                    
                    <div class="fixed top-3 right-3">
                        <x-jet-danger-button class="ml-2" wire:click="editTerminalInfoShowModal({{$modelId}})" wire:loading.attr="disabled">
                            <svg class="fill-current w-4 h-4 mr-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M373.1 24.97C401.2-3.147 446.8-3.147 474.9 24.97L487 37.09C515.1 65.21 515.1 110.8 487 138.9L289.8 336.2C281.1 344.8 270.4 351.1 258.6 354.5L158.6 383.1C150.2 385.5 141.2 383.1 135 376.1C128.9 370.8 126.5 361.8 128.9 353.4L157.5 253.4C160.9 241.6 167.2 230.9 175.8 222.2L373.1 24.97zM440.1 58.91C431.6 49.54 416.4 49.54 407 58.91L377.9 88L424 134.1L453.1 104.1C462.5 95.6 462.5 80.4 453.1 71.03L440.1 58.91zM203.7 266.6L186.9 325.1L245.4 308.3C249.4 307.2 252.9 305.1 255.8 302.2L390.1 168L344 121.9L209.8 256.2C206.9 259.1 204.8 262.6 203.7 266.6zM200 64C213.3 64 224 74.75 224 88C224 101.3 213.3 112 200 112H88C65.91 112 48 129.9 48 152V424C48 446.1 65.91 464 88 464H360C382.1 464 400 446.1 400 424V312C400 298.7 410.7 288 424 288C437.3 288 448 298.7 448 312V424C448 472.6 408.6 512 360 512H88C39.4 512 0 472.6 0 424V152C0 103.4 39.4 64 88 64H200z"/></svg>
                        </x-jet-danger-button>
                    </div>
                </div>
                <livewire:komponente.terminal-histroy-component :terminal_lokacija_id="$modelId" />
            @endif
        </x-slot>

        <x-slot name="footer">
        <x-jet-secondary-button wire:click="$toggle('terminalHistoryVisible')" wire:loading.attr="disabled">
                Zatvori
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>

    {{-- NOVI TIKET Modal ##################################################################################--}}
    <x-jet-dialog-modal wire:model="newTiketVisible">
        <x-slot name="title">
            <svg class="fill-current w-6 h-6 mr-2 mt-1 float-left" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 384"><path d="M576,208V128a64,64,0,0,0-64-64H64A64,64,0,0,0,0,128v80a48,48,0,0,1,48,48A48,48,0,0,1,0,304v80a64,64,0,0,0,64,64H512a64.06,64.06,0,0,0,64-64V304a48,48,0,0,1,0-96ZM438,286.5H318.5V406h-61V286.5H138v-61H257.5V106h61V225.5H438Z" transform="translate(0 -64)"/></svg>
            Novi Tiket
        </x-slot>
       
        <x-slot name="content">
        @if($newTiketVisible)
           {{-- Sada proveravamo dali terminal ima otvoren tiket --}}
           @if(App\Models\Tiket::daliTerminalImaOtvorenTiket($modelId))
                {{-- PRIKAZ GRESKE --}}
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative my-4" role="alert">
                    <strong class="font-bold">Greška!</strong>
                    <span class="block sm:inline">Terminal ima aktivan Tiket. Otvoren: {{ App\Http\Helpers::datumFormat(App\Models\Tiket::daliTerminalImaOtvorenTiket($modelId)->created_at) }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <svg class="fill-current h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M506.3 417l-213.3-364c-16.33-28-57.54-28-73.98 0l-213.2 364C-10.59 444.9 9.849 480 42.74 480h426.6C502.1 480 522.6 445 506.3 417zM232 168c0-13.25 10.75-24 24-24S280 154.8 280 168v128c0 13.25-10.75 24-23.1 24S232 309.3 232 296V168zM256 416c-17.36 0-31.44-14.08-31.44-31.44c0-17.36 14.07-31.44 31.44-31.44s31.44 14.08 31.44 31.44C287.4 401.9 273.4 416 256 416z"/></svg>
                    </span>
                </div>
           @else
                <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
                
                <div class="mt-4">
                    <x-jet-label for="opisKvaraList" value="{{ __('Izaberi kvar iz liste') }}" />
                    <select wire:model="opisKvaraList" id="" class="block appearance-none w-full border border-1 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                    <option value="">---</option>    
                        @foreach (App\Models\TiketOpisKvaraTip::opisList() as $key => $value)    
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('opisKvaraList') <span class="error">{{ $message }}</span> @enderror
                </div>  
                <div class="mt-4">
                    <x-jet-label for="opis_kvara" value="{{ __('Opis kvara') }}" />
                    <x-jet-textarea id="opis_kvara" type="textarea" class="mt-1 block w-full disabled:opacity-50" wire:model.defer="opisKvataTxt" />
                    @error('opis_kvara') <span class="error">{{ $message }}</span> @enderror
                </div> 
                @if($userPozicija != 2)
                    <!-- AKO nije koll centar bira servisera -->
                    @if(!$dodeljenUserId)
                    <div class="mt-4">
                        <hr />
                        <p>Dodeli tiket korisniku:</p>
                        <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                            <thead>
                                <tr>
                                    <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500"></th>
                                    <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Ime</th>
                                    <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Lokacija</th> 
                                    <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500">Pozicija</th>   
                                </tr>
                                <tr class="bg-orange-50">
                                    <td><svg class="mx-auto fill-orange-600 w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M3.853 54.87C10.47 40.9 24.54 32 40 32H472C487.5 32 501.5 40.9 508.1 54.87C514.8 68.84 512.7 85.37 502.1 97.33L320 320.9V448C320 460.1 313.2 471.2 302.3 476.6C291.5 482 278.5 480.9 268.8 473.6L204.8 425.6C196.7 419.6 192 410.1 192 400V320.9L9.042 97.33C-.745 85.37-2.765 68.84 3.854 54.87L3.853 54.87z"/></svg></td>
                                    <td><x-jet-input wire:model="searchUserName" id="" class="block bg-orange-50 w-full" type="text" placeholder="Ime" /></td>
                                    <td><x-jet-input wire:model="searchUserLokacija" id="" class="block bg-orange-50 w-full" type="text" placeholder="Lokacija" /></td>
                                    <td><x-jet-input wire:model="searchUserPozicija" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pozicija" /></td>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200"> 
                            @foreach ($this->searchUser() as $value)
                                <tr class="hover:bg-gray-100" wire:click="$set('dodeljenUserId', {{ $value->id }})" >    
                                        <td></td>
                                        <td>{{ $value->name }}</td>
                                        <td>{{ $value->l_naziv}}</td>
                                        <td>{{ $value->naziv}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="mt-5">
                            {{ $this->searchUser() ->links() }}
                        </div>
                    </div>

                    @else
                    
                    <div class="mt-4">Tiket dodeljen korisniku:</div>
                    <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                        <div class="flex">
                            <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224 256c70.7 0 128-57.31 128-128s-57.3-128-128-128C153.3 0 96 57.31 96 128S153.3 256 224 256zM274.7 304H173.3C77.61 304 0 381.6 0 477.3c0 19.14 15.52 34.67 34.66 34.67h378.7C432.5 512 448 496.5 448 477.3C448 381.6 370.4 304 274.7 304z"/></svg></div>
                            <div>
                                <p>Korisnik: <span class="font-bold">{{ $dodeljenUserInfo->name }}</span> &nbsp;&nbsp;&nbsp; Pozicija: <span class="font-bold">{{ $dodeljenUserInfo->naziv }}</span></p>
                                <p class="text-sm">Lokacija: <span class="font-bold">{{ $dodeljenUserInfo->l_naziv }}, {{$dodeljenUserInfo->mesto}}</span></p>
                            </div>
                        </div>
                    </div> 
                    @endif
                @else
                    <!-- KOL centar Samo dva dugmeta  -->
                    <div class="flex mt-4">
                        
                    </div>
                   
                @endif
                <p>Odredi prioritet tiketa:</p>
                <div class="flex mt-4">
                    @foreach (App\Models\TiketPrioritetTip::prList() as $value)
                        @if($prioritetTiketa == $value->id)
                            <span class="flex-none py-2 px-4 mx-2 font-bold rounded bg-{{$prioritetInfo->tr_bg_collor}} text-{{$prioritetInfo->btn_collor}}">{{ $value->tp_naziv }}</span>
                        @else
                            <button wire:click="$set('prioritetTiketa', {{ $value->id }})" class="flex-none bg-{{ $value->btn_collor }} hover:bg-{{$value->btn_hover_collor}} text-white font-bold py-2 px-4 rounded mx-2">
                                {{ $value->tp_naziv }}
                            </button>
                        @endif
                    @endforeach
                </div>
                
                <div>
                    @if($prioritetTiketa)
                        <div class="bg-{{$prioritetInfo->tr_bg_collor}} border border-{{$prioritetInfo->btn_collor}} text-{{$prioritetInfo->btn_collor}} px-4 py-3 rounded relative my-4" role="alert">
                            <p class="">Prioritet tiketa:
                            <span class="font-bold block sm:inline">{{ $prioritetInfo->tp_naziv }}</span><br /> {{ $prioritetInfo->tp_opis }}
                            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                                <svg class="fill-{{$prioritetInfo->btn_collor}} h-6 w-6 " xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M506.3 417l-213.3-364c-16.33-28-57.54-28-73.98 0l-213.2 364C-10.59 444.9 9.849 480 42.74 480h426.6C502.1 480 522.6 445 506.3 417zM232 168c0-13.25 10.75-24 24-24S280 154.8 280 168v128c0 13.25-10.75 24-23.1 24S232 309.3 232 296V168zM256 416c-17.36 0-31.44-14.08-31.44-31.44c0-17.36 14.07-31.44 31.44-31.44s31.44 14.08 31.44 31.44C287.4 401.9 273.4 416 256 416z"/></svg>
                            </span>
                            </p>
                        </div>
                    @endif
                </div>  
            @endif
        @endif
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('newTiketVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
            @if($userPozicija != 2)
                @if($dodeljenUserId && $prioritetTiketa)
                    <x-jet-danger-button class="ml-2" wire:click="createTiket" wire:loading.attr="disabled">
                        {{ __('Sačuvaj') }}
                    </x-jet-danger-button>     
                @endif 
            @else
                @if($prioritetTiketa && !App\Models\Tiket::daliTerminalImaOtvorenTiket($modelId))
                    <x-jet-danger-button class="ml-2" wire:click="createCallCentar(false)" wire:loading.attr="disabled">
                    <svg class="float-left fill-current w-4 h-4 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 384"><path d="M576,208V128a64,64,0,0,0-64-64H64A64,64,0,0,0,0,128v80a48,48,0,0,1,48,48A48,48,0,0,1,0,304v80a64,64,0,0,0,64,64H512a64.06,64.06,0,0,0,64-64V304a48,48,0,0,1,0-96ZM438,286.5H318.5V406h-61V286.5H138v-61H257.5V106h61V225.5H438Z" transform="translate(0 -64)"/></svg>
                        {{ __('Otvori tiket') }}
                    </x-jet-danger-button>
                    <x-jet-secondary-button class="ml-2" wire:click="createCallCentar(true)" wire:loading.attr="disabled">
                    <svg class="float-left fill-current w-4 h-4 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M331.8 224.1c28.29 0 54.88 10.99 74.86 30.97l19.59 19.59c40.01-17.74 71.25-53.3 81.62-96.65c5.725-23.92 5.34-47.08 .2148-68.4c-2.613-10.88-16.43-14.51-24.34-6.604l-68.9 68.9h-75.6V97.2l68.9-68.9c7.912-7.912 4.275-21.73-6.604-24.34c-21.32-5.125-44.48-5.51-68.4 .2148c-55.3 13.23-98.39 60.22-107.2 116.4C224.5 128.9 224.2 137 224.3 145l82.78 82.86C315.2 225.1 323.5 224.1 331.8 224.1zM384 278.6c-23.16-23.16-57.57-27.57-85.39-13.9L191.1 158L191.1 95.99l-127.1-95.99L0 63.1l96 127.1l62.04 .0077l106.7 106.6c-13.67 27.82-9.251 62.23 13.91 85.39l117 117.1c14.62 14.5 38.21 14.5 52.71-.0016l52.75-52.75c14.5-14.5 14.5-38.08-.0016-52.71L384 278.6zM227.9 307L168.7 247.9l-148.9 148.9c-26.37 26.37-26.37 69.08 0 95.45C32.96 505.4 50.21 512 67.5 512s34.54-6.592 47.72-19.78l119.1-119.1C225.5 352.3 222.6 329.4 227.9 307zM64 472c-13.25 0-24-10.75-24-24c0-13.26 10.75-24 24-24S88 434.7 88 448C88 461.3 77.25 472 64 472z"/></svg>
                        {{ __('Dodeli servisu') }}
                    </x-jet-danger-button>
                    <x-jet-danger-button class="ml-2" wire:click="createCallCentarClosedTiket()" wire:loading.attr="disabled">
                    <svg class="float-left fill-current w-4 h-4 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M448 32C483.3 32 512 60.65 512 96V416C512 451.3 483.3 480 448 480H64C28.65 480 0 451.3 0 416V96C0 60.65 28.65 32 64 32H448zM175 208.1L222.1 255.1L175 303C165.7 312.4 165.7 327.6 175 336.1C184.4 346.3 199.6 346.3 208.1 336.1L255.1 289.9L303 336.1C312.4 346.3 327.6 346.3 336.1 336.1C346.3 327.6 346.3 312.4 336.1 303L289.9 255.1L336.1 208.1C346.3 199.6 346.3 184.4 336.1 175C327.6 165.7 312.4 165.7 303 175L255.1 222.1L208.1 175C199.6 165.7 184.4 165.7 175 175C165.7 184.4 165.7 199.6 175 208.1V208.1z"/></svg>   
                        {{ __('Zatvoren tiket') }}
                    </x-jet-danger-button>
                @endif
            @endif
        </x-slot>
    </x-jet-dialog-modal>

    {{-- NOVA SERVISNA LICENCA ####################################################################--}}
    <x-jet-dialog-modal wire:model="novaServisnaModalVisible">
        <x-slot name="title">
            {{ __('Dodaj servisne licence terminalu') }}
        </x-slot>
        <x-slot name="content"> 
            @if($novaServisnaModalVisible)
            <div class="my-4">
            @if(count($licence_za_dodavanje))
                <div class="border-y py-2 bg-gray-50">
                    <p class="ml-4 font-bold">Trajanje licence:</p>
                    <div class="flex justify-between">
                        <div class="pl-4 my-4 flex">
                            <div class="mt-4 px-4">
                                <svg class="fill-blue-500 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M32 0C14.3 0 0 14.3 0 32S14.3 64 32 64V75c0 42.4 16.9 83.1 46.9 113.1L146.7 256 78.9 323.9C48.9 353.9 32 394.6 32 437v11c-17.7 0-32 14.3-32 32s14.3 32 32 32H64 320h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V437c0-42.4-16.9-83.1-46.9-113.1L237.3 256l67.9-67.9c30-30 46.9-70.7 46.9-113.1V64c17.7 0 32-14.3 32-32s-14.3-32-32-32H320 64 32zM288 437v11H96V437c0-25.5 10.1-49.9 28.1-67.9L192 301.3l67.9 67.9c18 18 28.1 42.4 28.1 67.9z"/></svg>
                            </div>
                            <div>
                                <x-jet-label for="datum_pocetka_licence" value="Datum početka licence" />
                                <p>{{ App\Http\Helpers::datumFormatDanFullYear($datum_pocetka_licence) }}</p>
                            </div>
                        </div>
                        <div class="pr-4 mt-4 flex">
                            <div class="mt-4 px-4">
                                <svg class="fill-blue-500 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M32 0C14.3 0 0 14.3 0 32S14.3 64 32 64V75c0 42.4 16.9 83.1 46.9 113.1L146.7 256 78.9 323.9C48.9 353.9 32 394.6 32 437v11c-17.7 0-32 14.3-32 32s14.3 32 32 32H64 320h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V437c0-42.4-16.9-83.1-46.9-113.1L237.3 256l67.9-67.9c30-30 46.9-70.7 46.9-113.1V64c17.7 0 32-14.3 32-32s-14.3-32-32-32H320 64 32zM96 75V64H288V75c0 25.5-10.1 49.9-28.1 67.9L192 210.7l-67.9-67.9C106.1 124.9 96 100.4 96 75z"/></svg>
                            </div>
                            <div>
                                <x-jet-label for="datum_kraja_licence" value="Datum isteka licence" />
                                <p>{{ App\Http\Helpers::datumFormatDanFullYear($datum_kraja_licence) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="my-4">
                <div>
                    @if($nazivZaServisnu)
                        @php
                            $licenca_dodatak = App\Models\LicencaDistributerCena::nazivServisneKojaGaziTrajnu($distId ,$nazivZaServisnu)
                        @endphp
                        <div class="my-4 border-y py-2 bg-gray-50">
                                <input id="licAddM" type="checkbox" value="{{ $licenca_dodatak->id }}" wire:model="licence_za_dodavanje"  class="form-checkbox h-6 w-6 text-blue-500">
                                <span class="font-bold pl-2">{{ $licenca_dodatak->licenca_naziv }}</span>
                                @if(in_array($licenca_dodatak->id, $licence_za_dodavanje))
                                    
                                    <div class="max-w-2xl grid grid-cols-5 gap-2 mt-4 mb-4 ml-10 border-t">
                                        @foreach(App\Models\LicencaParametar::parametriLicence($licenca_dodatak->licenca_tipId) as $parametar)
                                            <div class="px-1 rounded-md text-center">
                                                <input id="{{$parametar->id}}" type="checkbox" value="{{$parametar->id}}" wire:model="parametri"  class="form-checkbox h-6 w-6 text-blue-500 my-2"><br />
                                                <label class="break-words" for="{{$parametar->id}}">{{$parametar->param_opis}}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                    @else
                        @foreach( App\Models\LicencaDistributerCena::naziviNeDodatihLicenci($licence_za_dodavanje, $distId) as $licenca_dodatak)
                            
                            <div class="my-4 border-y py-2 bg-gray-50">
                                <input id="licAddM" type="checkbox" value="{{ $licenca_dodatak->id }}" wire:model="licence_za_dodavanje"  class="form-checkbox h-6 w-6 text-blue-500">
                                <span class="font-bold pl-2">{{ $licenca_dodatak->licenca_naziv }}</span>
                                @if(in_array($licenca_dodatak->id, $licence_za_dodavanje))
                                    
                                    <div class="max-w-2xl grid grid-cols-5 gap-2 mt-4 mb-4 ml-10 border-t">
                                        @foreach(App\Models\LicencaParametar::parametriLicence($licenca_dodatak->licenca_tipId) as $parametar)
                                            <div class="px-1 rounded-md text-center">
                                                <input id="{{$parametar->id}}" type="checkbox" value="{{$parametar->id}}" wire:model="parametri"  class="form-checkbox h-6 w-6 text-blue-500 my-2"><br />
                                                <label class="break-words" for="{{$parametar->id}}">{{$parametar->param_opis}}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
           @endif 
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('novaServisnaModalVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
            @if($novaServisnaModalVisible)
                @if(count($licence_za_dodavanje))
                    <x-jet-button class="ml-2" wire:click="dodajServisnueLicence" wire:loading.attr="disabled">
                        {{ __('Dodaj licence') }}
                    </x-jet-button>
                @endif
            @endif
        </x-slot>
    </x-jet-dialog-modal>

    {{-- PRODUZI PREKORACENJE ###############################################################################--}}
    <x-jet-dialog-modal wire:model="pomeriPrekoracenjeModalVisible">
        <x-slot name="title">
            {{ __('Produzi trajanje privremene licence') }} - {{ $nazivZaServisnu }}
        </x-slot>
        <x-slot name="content"> 
            @if($pomeriPrekoracenjeModalVisible)
            <div class="my-4">
                <div class="border-y py-2 bg-gray-50">
                    <p class="ml-4 font-bold">Trajanje licence:</p>
                    <div class="flex justify-between">
                        <div class="pl-4 my-4 flex">
                            <div class="mt-4 px-4">
                                <svg class="fill-blue-500 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M32 0C14.3 0 0 14.3 0 32S14.3 64 32 64V75c0 42.4 16.9 83.1 46.9 113.1L146.7 256 78.9 323.9C48.9 353.9 32 394.6 32 437v11c-17.7 0-32 14.3-32 32s14.3 32 32 32H64 320h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V437c0-42.4-16.9-83.1-46.9-113.1L237.3 256l67.9-67.9c30-30 46.9-70.7 46.9-113.1V64c17.7 0 32-14.3 32-32s-14.3-32-32-32H320 64 32zM288 437v11H96V437c0-25.5 10.1-49.9 28.1-67.9L192 301.3l67.9 67.9c18 18 28.1 42.4 28.1 67.9z"/></svg>
                            </div>
                            <div>
                                <x-jet-label for="datum_pocetka_licence" value="Datum početka licence" />
                                <p>{{ App\Http\Helpers::datumFormatDanFullYear($licencaZaProduzetak->datum_pocetak) }}</p>
                            </div>
                        </div>
                        <div class="pr-4 mt-4 flex">
                            <div class="mt-4 px-4">
                                <svg class="fill-blue-500 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M32 0C14.3 0 0 14.3 0 32S14.3 64 32 64V75c0 42.4 16.9 83.1 46.9 113.1L146.7 256 78.9 323.9C48.9 353.9 32 394.6 32 437v11c-17.7 0-32 14.3-32 32s14.3 32 32 32H64 320h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V437c0-42.4-16.9-83.1-46.9-113.1L237.3 256l67.9-67.9c30-30 46.9-70.7 46.9-113.1V64c17.7 0 32-14.3 32-32s-14.3-32-32-32H320 64 32zM96 75V64H288V75c0 25.5-10.1 49.9-28.1 67.9L192 210.7l-67.9-67.9C106.1 124.9 96 100.4 96 75z"/></svg>
                            </div>
                            <div>
                                <x-jet-label for="datum_kraja_licence" value="Datum isteka licence" />
                                <p>{{ App\Http\Helpers::datumFormatDanFullYear($licencaZaProduzetak->datum_kraj) }}</p>
                            </div>
                        </div>
                        <div class="pr-4 mt-4 flex">
                            <div class="mt-4 px-4">
                                <svg class="fill-red-500 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M32 0C14.3 0 0 14.3 0 32S14.3 64 32 64V75c0 42.4 16.9 83.1 46.9 113.1L146.7 256 78.9 323.9C48.9 353.9 32 394.6 32 437v11c-17.7 0-32 14.3-32 32s14.3 32 32 32H64 320h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V437c0-42.4-16.9-83.1-46.9-113.1L237.3 256l67.9-67.9c30-30 46.9-70.7 46.9-113.1V64c17.7 0 32-14.3 32-32s-14.3-32-32-32H320 64 32zM96 75V64H288V75c0 25.5-10.1 49.9-28.1 67.9L192 210.7l-67.9-67.9C106.1 124.9 96 100.4 96 75z"/></svg>
                            </div>
                            <div>
                                <x-jet-label for="datum_prekoracenja" value="Datum prekoracenja" />
                                <p>{{ App\Http\Helpers::datumFormatDanFullYear($licencaZaProduzetak->datum_prekoracenja) }}</p>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="border-y py-2 bg-gray-50">
                    
                    <div class="flex justify-between">
                        
                        <div class="pl-4 my-4 flex">
                            <p class="ml-4 font-bold">Produžetak prekoračenja:</p>
                        </div>
                        <div class="pr-4 mt-4 flex">
                            <div class="mt-4 px-4">
                                <svg class="fill-green-500 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M32 0C14.3 0 0 14.3 0 32S14.3 64 32 64V75c0 42.4 16.9 83.1 46.9 113.1L146.7 256 78.9 323.9C48.9 353.9 32 394.6 32 437v11c-17.7 0-32 14.3-32 32s14.3 32 32 32H64 320h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V437c0-42.4-16.9-83.1-46.9-113.1L237.3 256l67.9-67.9c30-30 46.9-70.7 46.9-113.1V64c17.7 0 32-14.3 32-32s-14.3-32-32-32H320 64 32zM96 75V64H288V75c0 25.5-10.1 49.9-28.1 67.9L192 210.7l-67.9-67.9C106.1 124.9 96 100.4 96 75z"/></svg>
                            </div>
                            <div>
                                <x-jet-label for="datum_prekoracenja" value="Novi datum prekoračenja" />
                                <p>{{ App\Http\Helpers::datumFormatDanFullYear($datum_prekoracenja) }}</p>
                            </div>
                        </div>


                    </div>
                </div>
            </div>

            
           @endif 
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('pomeriPrekoracenjeModalVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
            <x-jet-button class="ml-2" wire:click="produziPrekoracenjePrivremenoj" wire:loading.attr="disabled">
                {{ __('Produži trajanje') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>

        {{-- KOMENTARI MODAL ############################################################################--}}
    <x-jet-dialog-modal wire:model="modalKomentariVisible">
        <x-slot name="title">
            KOMENTARI
        </x-slot>
        <x-slot name="content"> 
            @if($modalKomentariVisible)
                <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />

                <div class="flex mb-4">
                        <livewire:komponente.prikazkomentara 
                            :terminalLokacijaId="$modelId"
                            :canEdit="false" />
                </div>
            @endif
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalKomentariVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>

         {{-- EDIT TERMINAL INFO MODAL ###############################################################################--}}
    <x-jet-dialog-modal wire:model="editTerminalInfoVisible">
        <x-slot name="title">
           TERMINAL INFO
        </x-slot>
        <x-slot name="content"> 
            @if($editTerminalInfoVisible)
                <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
                
                <div class="mt-4">
                    <x-jet-label for="info_sn" value="{{ __('Serijski broj') }}" />
                    @if($terminalZaEditImaLicencu)
                        <x-jet-input wire:model="info_sn" id="" class="block mt-1 w-full" type="text" disabled />
                        <p class="text-red-500 text-xs italic">Terminal ima dodeljenu licencu, serijski broj se ne može menjati!</p>    
                    @else
                        <x-jet-input wire:model="info_sn" id="" class="block mt-1 w-full" type="text" />
                        @error('info_sn') <span class="error">{{ $message }}</span> @enderror
                    @endif
                </div>
               

                <div class="mt-4">
                    <x-jet-label for="kutija" value="{{ __('Kutija') }}" />
                    <x-jet-input wire:model="kutija" id="" class="block mt-1 w-full" type="text" />
                    @error('kutija') <span class="error">{{ $message }}</span> @enderror
                </div>
                
            @endif
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('editTerminalInfoVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
            <x-jet-button class="ml-2" wire:click="updateTerminalInfo" wire:loading.attr="disabled">
                    {{ __('Update') }}
                </x-jet-danger-button>
        </x-slot>
    </x-jet-dialog-modal>

        {{-- VENDOR MODAL ##############################################################################  --}}
    <x-jet-dialog-modal wire:model="modalVendorVisible">
        <x-slot name="title">
            {{ __('Promeni vendora') }}
        </x-slot>

        <x-slot name="content">
            @if($modalVendorVisible)
                @if ($multiSelected)
                    <livewire:komponente.terminal-info :terminal_lokacija_id="0" :multySelectedArray="$selectedTerminals" :multySelected="true"  />
                @else
                    <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
                @endif
                
            @endif
            <div class="mt-4 bg-gray-50 p-4 border-t-4 border-grey-800 rounded-b text-grey-900 shadow-md mb-6" role="alert">
            <div><span class="font-bold">Vendor:</span></div>
                <select wire:model.defer="vendor_id" id="" class="block appearance-none w-full border border-1 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                        <option value="">---</option>
                    @foreach (App\Models\TerminalVendor::allList() as $key => $value)    
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('vendor_id') <span class="error">{{ $message }}</span> @enderror
            </div>  
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalVendorVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>

            @if ($modelId || $multiSelected)
                <x-jet-button class="ml-2" wire:click="vendorSave" wire:loading.attr="disabled">
                    {{ __('Sačuvaj') }}
                </x-jet-danger-button>
            @else
               <div>Nešto nije u redu?!?</div>
            @endif            
        </x-slot>
    </x-jet-dialog-modal>

</div>