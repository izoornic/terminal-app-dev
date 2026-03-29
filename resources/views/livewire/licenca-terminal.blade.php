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
                                <td><x-heroicon-o-funnel class="mx-auto text-orange-600 w-4 h-4" /></td>
                                <td class="p-1">
                                    <x-jet-input wire:model="searchSB" id="" class="block bg-orange-50 w-full" type="text" placeholder="Serijski broj" />
                                </td>
                                <td class="p-1">
                                    <x-jet-input wire:model="searchNazivLokacije" id="" class="block bg-orange-50 w-full" type="text" placeholder="Lokacija" /> 
                                </td>
                                <td class="p-1">
                                    <select wire:model="searchTipLokacije" id="" class="block appearance-none bg-orange-50 w-full border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">-- Tip lokacije --</option>
                                        @foreach (App\Models\LokacijaTip::tipoviList() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="p-1">
                                    <select wire:model="searchStatus" id="" class="block appearance-none bg-orange-50 w-full border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">-- Status--</option>
                                        @foreach (App\Models\TerminalStatusTip::tipoviList() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="p-1">
                                    <select wire:model="searchBlackist" id="" class="block appearance-none bg-orange-50 w-full border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">Blacklsta</option>
                                        <option value="2"> Ne</option>
                                        <option value="1"> Da </option>
                                    </select>
                                </td>    
                            </tr> 
                            {{-- SEARCH 2nd ROW --}} 
                            <tr class="bg-orange-50">
                                <td></td>
                                <td class="p-1">
                                    <x-jet-input wire:model="searchKutija" id="" class="block bg-orange-50 w-full" type="text" placeholder="Broj kutije" />
                                </td>
                                <td class="p-1">
                                    <select wire:model="searchRegion" id="" class="block appearance-none bg-orange-50 w-full border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                                <option value="">--Region--</option>
                                            @foreach (App\Models\Region::regioni() as $key => $value)    
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                    </select>
                                </td>
                                <td class="p-1">
                                    <x-jet-input wire:model="searchCampagin" id="" class="block bg-orange-50 w-full" type="text" placeholder="Kampanja" />
                                </td>
                                <td class="p-1"> </td>
                                <td class="p-1">
                                    <x-jet-input wire:model="searchPib" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži PIB" />
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">
                                    <input type="checkbox" value="1" wire:model="selectAll.1"  class="form-checkbox h-6 w-6 text-blue-500">
                                </th>
                                <th class="px-1 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider"></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">Serijski broj<br /><span class=" text-red-400">Kutija</span></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">Model</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">Lokacija<br /><span class=" text-red-400">Region</span></th></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">Tip</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">Distributer<br /><span class=" text-green-600">Kutija</span></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">Status</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider"></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider"></th>
                                <th colspan="3" class="px-1 py-3 bg-gray-50 text-right text-xs leading-4 font-medium text-gray-500 tracking-wider">Ukupno: <span class="font-bold">{{ $data->total() }}</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"> 
                        
                            {{--DATA TABLE --}}                   
                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr @if($loop->even) class="bg-gray-50" @endif >
                                        <td class="px-1 py-1"><input type="checkbox" value="{{ $item->tlid }}" wire:model="selectedTerminals"  class="form-checkbox h-6 w-6 text-blue-500"></td>
                                        <td class="px-1 py-2">
                                            @if($item->tzlid)
                                                <!--  TERMINAL IMA LICENCU -->
                                                @if($item->tzlid == 1 || $item->tzlid == 3)
                                                    <!-- TERMINAL IMA REGULARNU LICENCU -->
                                                    <a class="flex p-1 cursor-pointer flex border border-stone-500 bg-stone-50 hover:bg-stone-500 text-stone-700 hover:text-white rounded" wire:click="licencaShowModal({{ $item->tlid}}, {{$item->tzlid}})" title="Pregled licence">
                                                        <x-icon-licenca class="fill-current w-5 h-5  ml-1" />
                                                    </a>
                                                @elseif($item->tzlid == 2)
                                                    <!-- TERMINAL IMA SERVISNU LICENCU -->
                                                    <a class="flex p-1 cursor-pointer flex border border-green-800 bg-green-50 text-green-800 hover:bg-stone-500 text-stone-700 hover:text-white rounded" wire:click="licencaShowModal({{ $item->tlid}}, {{$item->tzlid}})" title="Pregled licence" >
                                                        <x-icon-licenca-servisna class="fill-current w-5 h-5 ml-1" />
                                                    </a>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-1 py-2">
                                            {{ $item->sn }}<br />
                                            <span class="text-red-500 text-xs">{{ $item->broj_kutije }}</span>
                                        </td>
                                        <td class="px-1 py-2">{{ $item->model }}</td>
                                        <td class="px-1 py-2">
                                            @if($item->is_duplicate)<span class="text-red-500">*</span>@endif
                                            {{ $item->l_naziv }}&nbsp;{{ $item->l_naziv_sufix }}
                                            <br />
                                            <span class="text-red-500 text-xs">{{ $item->r_naziv }}</span>
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
                                        </td>
                                        {{-- DISTRIBUTER/ KAMPANJA --}}
                                        <td class="px-1 py-2" style="max-width: 200px !important;">
                                            {{ $item->distributer_naziv }}@if( $item->campagin_name )<br /><span class="text-xs text-green-600">{{ $item->campagin_name }}</span>@endif
                                        </td>     
                                        {{-- STATUS --}}
                                        <td class="px-1 py-2">{{ $item->ts_naziv }}</td>
                                        {{-- PREMESTI --}}
                                        <td class="px-1 py-1">
                                            <button class="flex text-sm text-gray-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white" wire:click="premestiShowModal({{ $item->tlid }}, {{ $item->statusid }})">
                                                <x-heroicon-o-arrows-right-left class="fill-current w-4 h-4 mr-1" />
                                                {{ __('Premesti') }}
                                            </button>
                                        </td>
                                        {{-- HISTORY --}}
                                        <td class="px-1 py-1">
                                            <button class="px-2 py-2 text-sm relative text-gray-800 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="terminalHistoryShowModal({{ $item->tlid }})" title="Istorija terminala">
                                                <x-icon-history class="fill-current w-4 h-4 mr-0" />
                                            </button>
                                        </td>
                                        <td class="px-1 py-1">
                                             @if($item->blacklist == 1)
                                                <button class="p-2 text-sm relative text-white uppercase border rounded-md bg-gray-700 hover:bg-white hover:text-gray-600" wire:click="blacklistShowModal({{  $item->tlid }})" title="Skloni sa Blckiste">
                                                    <x-icon-blacklist-scull class="fill-current w-5 h-5 ml-1" />
                                                </button>
                                            @else
                                                <button class="px-2 py-2 text-sm relative text-gray-800 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="blacklistShowModal({{  $item->tlid }})" title="Dodaj na Blckistu">
                                                    <x-icon-blacklist-scull class="fill-gray-400 w-5 h-5 ml-1" />
                                                </button>
                                            @endif
                                        </td>
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

    <div class="bg-blue-100 border-t border-b border-blue-500 text-blue-700 px-4 py-3 my-4 flex flex-row">
        <div class="basis-1/3"><p class="text-sm">Ukupno izabranih terminala: <span class="font-bold"> {{ count($selectedTerminals) }}</span></p></div>
        <div class="basis-1/3 text-right mr-6">
           
        </div>
        <div class="basis-1/3 text-center mr-6">
            @if(count($selectedTerminals))
                <x-jet-button class="ml-2" wire:click="premestiSelectedShowModal()">
                    {{ __('Premesti') }}
                </x-jet-button>
            @endif
        </div>
    </div>

    {{-- LICENCA MODAL ########################################################################--}}
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

    {{-- ERROR LICENCA #################################################################################--}}
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

    {{-- BLAKLIST modal ################################################################################--}}
    <x-jet-dialog-modal wire:model="modalFormVisible">
        <x-slot name="title">
            {{ __('Promeni blacklist status terminala') }}
        </x-slot>

        <x-slot name="content">
        @if($modalFormVisible)
            <div class="relative">
                <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
                
            </div>
            <livewire:komponente.blacklist-add-remove :terminal_lokacija_id="$modelId" />
        @endif
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalFormVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>

    
    {{-- HISTORI Modal ##################################################################################--}}
    <x-jet-dialog-modal wire:model="terminalHistoryVisible">
        <x-slot name="title">
            {{ __('Vremenska linija terminala') }}
        </x-slot>
       
        <x-slot name="content">
            @if($terminalHistoryVisible)
                <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
                <livewire:komponente.terminal-histroy-component :terminal_lokacija_id="$modelId" />
            @endif
        </x-slot>

        <x-slot name="footer">
        <x-jet-secondary-button wire:click="$toggle('terminalHistoryVisible')" wire:loading.attr="disabled">
                {{ __('Zatvori') }}
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>

    {{-- PREMESTI Modal ###########################################################################################--}}
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
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"></th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Naziv</th> 
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Mesto</th> 
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Region</th> 
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"></th>  
                            </tr>
                            <tr class="bg-orange-50">
                                <td><svg class="mx-auto fill-orange-600 w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M3.853 54.87C10.47 40.9 24.54 32 40 32H472C487.5 32 501.5 40.9 508.1 54.87C514.8 68.84 512.7 85.37 502.1 97.33L320 320.9V448C320 460.1 313.2 471.2 302.3 476.6C291.5 482 278.5 480.9 268.8 473.6L204.8 425.6C196.7 419.6 192 410.1 192 400V320.9L9.042 97.33C-.745 85.37-2.765 68.84 3.854 54.87L3.853 54.87z"/></svg></td>
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
                                <td>{{ $value->l_naziv }}</td>
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
                    <p class="font-bold">{{ $this->novaLokacija()->l_naziv}}, {{ $this->novaLokacija()->mesto }}</p>
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

    {{-- NOVI TERMINALI Modal ################################################################# --}}
    <x-jet-dialog-modal wire:model="modalNoviTerminalVisible">
        <x-slot name="title">
            {{ __('Dodaj novi terminal') }}
        </x-slot>

        <x-slot name="content">
            <p class="text-xl font-bold uppercase text-red-600 flex" >
                <svg class="fill-current w-4 h-4 mt-1 mr-2" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 512 512"><g><path d="M422.4,254.5c-4.1-27.1-27.3-47.1-55.5-47.1H180v-27.7h69.2c15.3,0,27.7-12.4,27.7-27.7V96.7c0-15.3-12.4-27.7-27.7-27.7 H54.6c-14.5,0-27.7,12.4-27.7,27.7v55.4c0,15.3,13.2,27.7,27.7,27.7h69.2v27.7H75.3c-27.4,0-50.6,20-54.7,47.1L0.9,384 c-0.6,4.1-0.9,8.2-0.9,12.4v60.2C0,487.2,24.8,512,55.4,512h332.3c30.5,0,55.4-24.8,55.4-55.4v-60.2c0-4.2-0.3-8.3-1-12.4 L422.4,254.5z M346.1,255.9c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C325.3,265.1,334.6,255.9,346.1,255.9z M304.6,325.1c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C283.8,334.4,293.1,325.1,304.6,325.1z M263,255.9c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C242.3,265.1,251.5,255.9,263,255.9z M221.5,325.1c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C200.7,334.4,210,325.1,221.5,325.1z M200.7,276.7c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8c0-11.5,9.3-20.8,20.8-20.8 S200.7,265.1,200.7,276.7z M83.1,138.2c-7.6,0-13.8-6.2-13.8-13.8c0-7.6,6.2-13.8,13.8-13.8h138.4c7.6,0,13.8,6.2,13.8,13.8 c0,7.6-6.2,13.8-13.8,13.8H83.1z M138.4,325.1c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C117.7,334.4,126.9,325.1,138.4,325.1z M96.9,255.9c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8 c-11.5,0-20.8-9.3-20.8-20.8C76.1,265.1,85.4,255.9,96.9,255.9z M373.8,456.6H69.2c-7.6,0-13.8-6.2-13.8-13.8 c0-7.6,6.2-13.8,13.8-13.8h304.6c7.6,0,13.8,6.2,13.8,13.8C387.6,450.4,381.4,456.6,373.8,456.6z"/><polygon points="437.3,75.2 437.3,0 386.8,0 386.8,75.2 311.6,75.2 311.6,125.7 386.8,125.7 386.8,200.9 437.3,200.9 437.3,125.7 512.5,125.7 512.5,75.2 "/></g></svg>
                    Novi terminal
            </p>

            <div>Lokacija na koju se dodaje terminali:</div>
            <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                <div class="flex">
                    <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M168.3 499.2C116.1 435 0 279.4 0 192C0 85.96 85.96 0 192 0C298 0 384 85.96 384 192C384 279.4 267 435 215.7 499.2C203.4 514.5 180.6 514.5 168.3 499.2H168.3zM192 256C227.3 256 256 227.3 256 192C256 156.7 227.3 128 192 128C156.7 128 128 156.7 128 192C128 227.3 156.7 256 192 256z"/></svg></div>
                        <div>
                            <p class="font-bold">Centralni servis, Oblakovska 23a</p>
                            <p class="text-sm">Region: Beograd</p>
                        </div>
                </div>
            </div> 

            <div class="mt-4">
                <x-jet-label for="noviSN" value="{{ __('Serijski broj') }}" />
                <x-jet-input wire:model="noviSN" id="" class="block mt-1 w-full" type="text" />
                @error('noviSN') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <x-jet-label for="noviKutijaNO" value="{{ __('Broj kutije') }}" />
                <x-jet-input wire:model="noviKutijaNO" id="" class="block mt-1 w-full" type="text" />
                @error('noviKutijaNO') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="my-6">
                <select wire:model="new_terminal_tip" id="" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                    <option value="0">Model terminala</option>
                    @foreach ( App\Models\TerminalTip::tipoviList() as $key => $value)    
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="my-6">
                <select wire:model="t_status" id="" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                    <option value="0">Status terminala</option>
                    @foreach ( App\Models\TerminalStatusTip::tipoviList() as $key => $value)    
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6">
                <x-jet-label for="date_akcije" value="Datum promene:" />
                <x-jet-input id="date_akcije" type="date" class="mt-1 block" value="{{ $datum_dodavanja_terminala }}" wire:model.defer="datum_dodavanja_terminala" />
                <x-jet-input-error for="date_akcije" class="mt-2" />
            </div>
            @if($errAddMsg != '')
            {{-- PRIKAZ GRESKE --}}
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative my-4" role="alert">
                    <strong class="font-bold">Greška!</strong>
                    <span class="block sm:inline">{{ $errAddMsg }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <svg class="fill-current h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M506.3 417l-213.3-364c-16.33-28-57.54-28-73.98 0l-213.2 364C-10.59 444.9 9.849 480 42.74 480h426.6C502.1 480 522.6 445 506.3 417zM232 168c0-13.25 10.75-24 24-24S280 154.8 280 168v128c0 13.25-10.75 24-23.1 24S232 309.3 232 296V168zM256 416c-17.36 0-31.44-14.08-31.44-31.44c0-17.36 14.07-31.44 31.44-31.44s31.44 14.08 31.44 31.44C287.4 401.9 273.4 416 256 416z"/></svg>
                    </span>
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalNoviTerminalVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
            <x-jet-danger-button class="ml-2" wire:click="noviTerminalAdd" wire:loading.attr="disabled">
                {{ __('Dodaj terminal') }}
            </x-jet-danger-button>
        </x-slot>
    </x-jet-dialog-modal>

    {{-- KOMENTARI MODAL #################################################################  --}}
    <x-jet-dialog-modal wire:model="modalKomentariVisible">
        <x-slot name="title">
            KOMENTARI
        </x-slot>
        <x-slot name="content"> 
            @if($modalKomentariVisible)
                <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
                {{-- KOMENTARI --}}
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
</div>