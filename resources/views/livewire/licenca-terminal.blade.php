<div class="p-6">
    <div class="flex items-center justify-end px-4 py-3 text-right sm:px-6">
        <x-jet-button wire:click="noviTerminalShowModal">
        <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M0 256C0 114.6 114.6 0 256 0C397.4 0 512 114.6 512 256C512 397.4 397.4 512 256 512C114.6 512 0 397.4 0 256zM256 368C269.3 368 280 357.3 280 344V280H344C357.3 280 368 269.3 368 256C368 242.7 357.3 232 344 232H280V168C280 154.7 269.3 144 256 144C242.7 144 232 154.7 232 168V232H168C154.7 232 144 242.7 144 256C144 269.3 154.7 280 168 280H232V344C232 357.3 242.7 368 256 368z"/></svg>
        {{ __('Novi Terminal') }}
        </x-jet-button>
    </div>
    {{-- The data table --}}
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"><input type="checkbox" value="1" wire:model="selectAll.1"  class="form-checkbox h-6 w-6 text-blue-500"></th>
                                <th class="px-1 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Serijski broj</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Kutija</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">m</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Lokacija</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Region</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Tip lokacije</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th colspan="4" class="px-1 py-3 bg-gray-50 text-right text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Ukupno: <span class="font-bold">{{ $data->total() }}</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"> 
                        {{-- SEARCH ROW --}}
                            <tr class="bg-orange-50">
                                <td><svg class="mx-auto fill-orange-600 w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M3.853 54.87C10.47 40.9 24.54 32 40 32H472C487.5 32 501.5 40.9 508.1 54.87C514.8 68.84 512.7 85.37 502.1 97.33L320 320.9V448C320 460.1 313.2 471.2 302.3 476.6C291.5 482 278.5 480.9 268.8 473.6L204.8 425.6C196.7 419.6 192 410.1 192 400V320.9L9.042 97.33C-.745 85.37-2.765 68.84 3.854 54.87L3.853 54.87z"/></svg></td>
                                <td></td>
                                <td><x-jet-input wire:model="searchSB" id="" class="block bg-orange-50 w-full" type="text" placeholder="Serijski broj" /></td>
                                <td><x-jet-input wire:model="searchKutija" id="" class="block bg-orange-50 w-full" type="text" placeholder="Broj kutije" /></td>
                                <td></td>
                                <td>
                                    <x-jet-input wire:model="searchName" id="" class="block bg-orange-50 w-full" type="text" placeholder="Lokacija" /> 
                                </td>
                                <td>
                                    <select wire:model="searchRegion" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                                <option value="">---</option>
                                            @foreach (App\Models\Region::regioni() as $key => $value)    
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select wire:model="searchTipTeminal" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">---</option>
                                        @foreach (App\Models\LokacijaTip::tipoviList() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select wire:model="searchStatus" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">---</option>
                                        @foreach (App\Models\TerminalStatusTip::tipoviList() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td></td>
                                <td colspan="3" class="text-right text-sm pr-4">
                                    <select wire:model="searchBlackist" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="0">Blacklsta</option>
                                        <option value="2"> Ne</option>
                                        <option value="1"> Da </option>
                                    </select>
                                </td>    
                            </tr>  
                            
                            {{--DATA TABLE --}}                   
                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr @if($loop->even) class="bg-gray-50" @endif >
                                        <td class="px-1 py-1"><input type="checkbox" value="{{ $item->tid }}" wire:model="selectedTerminals"  class="form-checkbox h-6 w-6 text-blue-500"></td>
                                        <td class="px-1 py-2">
                                            @if($item->tzlid)
                                                <!--  TERMINAL IMA LICENCU -->
                                                @if($item->tzlid == 1 || $item->tzlid == 3)
                                                    <!-- TERMINAL IMA REGULARNU LICENCU -->
                                                    <a class="flex p-1 cursor-pointer flex border border-stone-500 bg-stone-50 hover:bg-stone-500 text-stone-700 hover:text-white rounded" wire:click="licencaShowModal({{ $item->tlid}}, {{$item->tzlid}})" title="Pregled licence">
                                                        <svg class="fill-current w-5 h-5 ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M373.1,134.6L253.4,15.3C243.5,5.5,230.2,0,216.3,0H59C26.5,0,0,26.5,0,59v394c0,32.5,26.5,59,59,59h266 c32.5,0,59-26.5,59-59V160.9C384,151.1,380.1,141.6,373.1,134.6z M354.9,151.8h-61.5c-35.8,0-65-29.2-65-65v-59 c2.7,1.3,5.1,3.1,7.3,5.2L354.9,151.8z M359,453c0,9-3.6,17.5-10,24c-6.5,6.5-15,10-24,10H59c-9,0-17.5-3.6-24-10 c-6.5-6.5-10-15-10-24V59c0-9,3.6-17.5,10-24c6.5-6.5,15-10,24-10h144.4v61.8c0,49.6,40.4,90,90,90H359V453z"/><g><path d="M159.9,391.1h111.3v26.3h-141V197.8h29.7V391.1z"/></g></svg>
                                                    </a>
                                                @elseif($item->tzlid == 2)
                                                    <!-- TERMINAL IMA SERVISNU LICENCU -->
                                                    <a class="flex p-1 cursor-pointer flex border border-green-800 bg-green-50 text-green-800 hover:bg-stone-500 text-stone-700 hover:text-white rounded" wire:click="licencaShowModal({{ $item->tlid}}, {{$item->tzlid}})" title="Pregled licence" >

                                                        <svg class="fill-current w-5 h-5" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 384 512" style="enable-background:new 0 0 384 512;" xml:space="preserve"><g><path d="M272.2,391.1h111.3v26.3h-141V197.8h29.7L272.2,391.1L272.2,391.1z"/></g><path d="M359,451.2v1.8c0,9-3.6,17.5-10,24c-6.5,6.5-15,10-24,10H59c-9,0-17.5-3.6-24-10c-6.5-6.5-10-15-10-24V59 c0-9,3.6-17.5,10-24c6.5-6.5,15-10,24-10h144.4v61.8c0,49.6,40.4,90,90,90H359v177.4h25V160.9c0-9.8-3.9-19.3-10.9-26.3L253.4,15.3 C243.5,5.5,230.2,0,216.3,0H59C26.5,0,0,26.5,0,59v394c0,32.5,26.5,59,59,59h266c32.5,0,59-26.5,59-59v-1.8H359z M228.4,27.8 c2.7,1.3,5.1,3.1,7.3,5.2l119.2,118.8h-61.5c-35.8,0-65-29.2-65-65V27.8z"/><g><path d="M62.7,374.4c12.7,7.3,32,13.7,52.1,13.7c25.1,0,39.2-11.8,39.2-29.4c0-16.3-10.9-25.9-38.2-35.8 c-35.6-12.6-58.4-31.8-58.4-62.7c0-35.3,29.5-61.9,76.3-61.9c23.4,0,40.5,5,51.7,10.7l-9.4,31.8c-7.8-4.1-22.8-10.2-43.1-10.2 c-25,0-35.6,13.3-35.6,26c0,16.5,12.5,24.3,41.3,35.3c37.5,14.3,55.5,33.1,55.5,64.1c0,34.7-26.1,64.8-81.8,64.8 c-22.8,0-46.5-6.5-58.3-13.7L62.7,374.4z"/></g></svg>
                                                    </a>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-1 py-2">{{ $item->sn }}</td>
                                        <td class="px-1 py-2">{{ $item->broj_kutije }}</td>
                                        <td class="px-1 py-2">{{ $item->model }}</td>
                                        <td class="px-1 py-2">{{ $item->l_naziv }}</td>
                                        <td class="px-1 py-2">{{ $item->r_naziv }}</td> 
                                        <td class="px-1 py-2">
                                            @if($item->distributerId)
                                                <svg class="float-left fill-current w-4 h-4 mr-1 pt-1" xmlns="http://www.w3.org/2000/svg" height="16" width="20" viewBox="0 0 640 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M0 48C0 21.5 21.5 0 48 0H336c26.5 0 48 21.5 48 48V232.2c-39.1 32.3-64 81.1-64 135.8c0 49.5 20.4 94.2 53.3 126.2C364.5 505.1 351.1 512 336 512H240V432c0-26.5-21.5-48-48-48s-48 21.5-48 48v80H48c-26.5 0-48-21.5-48-48V48zM80 224c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V240c0-8.8-7.2-16-16-16H80zm80 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V240c0-8.8-7.2-16-16-16H176c-8.8 0-16 7.2-16 16zm112-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V240c0-8.8-7.2-16-16-16H272zM64 112v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V112c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zM176 96c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V112c0-8.8-7.2-16-16-16H176zm80 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V112c0-8.8-7.2-16-16-16H272c-8.8 0-16 7.2-16 16zm96 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm140.7-67.3c-6.2 6.2-6.2 16.4 0 22.6L521.4 352H432c-8.8 0-16 7.2-16 16s7.2 16 16 16h89.4l-28.7 28.7c-6.2 6.2-6.2 16.4 0 22.6s16.4 6.2 22.6 0l56-56c6.2-6.2 6.2-16.4 0-22.6l-56-56c-6.2-6.2-16.4-6.2-22.6 0z"/></svg>
                                            @endif
                                            {{ $item->lt_naziv }}
                                        </td>
                                        <td class="px-1 py-2">{{ $item->ts_naziv }}</td>
                                        <td class="px-1 py-1">
                                            <button class="text-sm text-gray-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white" wire:click="premestiShowModal({{ $item->tid }}, {{ $item->statusid }})">
                                                {{ __('Premesti') }}
                                            </button>
                                        </td>
                                        <td class="px-1 py-1">
                                            <x-jet-secondary-button class="" wire:click="terminalHistoryShowModal({{ $item->tlid }})" title="Istorija terminala">
                                                <svg class="fill-current w-4 h-4 mr-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M256 0C397.4 0 512 114.6 512 256C512 397.4 397.4 512 256 512C201.7 512 151.2 495 109.7 466.1C95.2 455.1 91.64 436 101.8 421.5C111.9 407 131.8 403.5 146.3 413.6C177.4 435.3 215.2 448 256 448C362 448 448 362 448 256C448 149.1 362 64 256 64C202.1 64 155 85.46 120.2 120.2L151 151C166.1 166.1 155.4 192 134.1 192H24C10.75 192 0 181.3 0 168V57.94C0 36.56 25.85 25.85 40.97 40.97L74.98 74.98C121.3 28.69 185.3 0 255.1 0L256 0zM256 128C269.3 128 280 138.7 280 152V246.1L344.1 311C354.3 320.4 354.3 335.6 344.1 344.1C335.6 354.3 320.4 354.3 311 344.1L239 272.1C234.5 268.5 232 262.4 232 256V152C232 138.7 242.7 128 256 128V128z"/></svg>
                                            </x-jet-button></td>
                                        <td class="px-1 py-1">
                                            @if($item->blacklist == 1)
                                                <x-jet-button class="" wire:click="blacklistShowModal({{  $item->tlid }})" title="Skloni sa Blckiste">
                                                    <svg class="fill-current w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">	<path class="st1" d="M320,464c8.8,0,16-7.2,16-16V160h-80c-17.7,0-32-14.3-32-32V48H64c-8.8,0-16,7.2-16,16v384 c0,8.8,7.2,16,16,16H320z M0,64C0,28.7,28.7,0,64,0h165.5c17,0,33.3,6.7,45.3,18.7l90.5,90.5c12,12,18.7,28.3,18.7,45.3V448 c0,35.3-28.7,64-64,64H64c-35.3,0-64-28.7-64-64V64z"/> <path class="st1" d="M48,238c0,39.8,21.1,75.3,54,98.4c0,0.2,0,0.4,0,0.6v36c0,14.9,12.1,27,27,27h27v-27c0-5,4-9,9-9s9,4,9,9v27 h36v-27c0-5,4-9,9-9s9,4,9,9v27h27c14.9,0,27-12.1,27-27v-36c0-0.2,0-0.4,0-0.6c32.9-23.1,54-58.6,54-98.4	c0-22.2-6.6-43.1-18.1-61.2h-24.5c-40.6,0-75-27.1-86.2-64.1c-5-0.5-10.1-0.7-15.2-0.7C112.5,112,48,168.4,48,238z M138,292	c-19.9,0-36-16.1-36-36s16.1-36,36-36s36,16.1,36,36S157.9,292,138,292z M246,220c19.9,0,36,16.1,36,36s-16.1,36-36,36 s-36-16.1-36-36S226.1,220,246,220z"/></svg>
                                                </x-jet-button>
                                            @else
                                                <x-jet-secondary-button class="ml-2" wire:click="blacklistShowModal({{  $item->tlid }})" title="Dodaj na Blckistu">
                                                    <svg class="fill-current w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M373.1,134.6,253.4,15.3A52.7,52.7,0,0,0,216.3,0H59A59.11,59.11,0,0,0,0,59V453a59.11,59.11,0,0,0,59,59H325a59.11,59.11,0,0,0,59-59V160.9A36.9,36.9,0,0,0,373.1,134.6Zm-18.3,17.1H293.4a65.13,65.13,0,0,1-65-65V27.78A27.29,27.29,0,0,1,235.7,33ZM359,453a34.09,34.09,0,0,1-10,24,33.67,33.67,0,0,1-24,10H59a34.09,34.09,0,0,1-24-10,33.67,33.67,0,0,1-10-24V59A34.09,34.09,0,0,1,35,35,33.67,33.67,0,0,1,59,25H203.4V86.8a90.14,90.14,0,0,0,90,90H359Z"/><path d="M65.79,183.16a160.53,160.53,0,0,1,39.66-4.59c22.37,0,40.23,4.84,52.43,16.34,10.07,9.26,15.53,23.17,15.53,40,0,24.07-13.75,43.14-36.72,52v.87c26.36,6.91,44.3,28.19,44.3,60.16,0,19.29-6.46,35-17,46.4-13.58,15.07-35.71,21.12-62.73,21.12-17.56,0-28.27-1.1-35.48-2.29ZM90.24,279.7h15c23.86,0,43.4-17.34,43.4-42.3,0-23.08-11.81-39.4-42-39.4-7.51,0-12.85.68-16.41,1.74Zm0,114.7A79.63,79.63,0,0,0,106,395.68c29.46,0,49.5-16.13,49.5-47.94,0-33.33-24.05-48.9-51.26-49.14h-14Z"/><path d="M230.58,179.72h24.54V393.17h71.5V414h-96Z"/></svg>
                                                </x-jet-button>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="p-2 text-sm relative text-gray-300 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="commentsShowModal({{ $item->tlid }})" title="Komentari">
                                                <div class="z-10 absolute ml-1 mb-1 text-gray-500 text-lg">{{ $item->br_komentara}}</div>
                                                
                                                <svg class="text-current w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>

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

    {{-- LICENCA MODAL --}}
    <x-jet-dialog-modal wire:model="licencaModalVisible">
        <x-slot name="title">
            LICENCE
        </x-slot>
        <x-slot name="content"> 
            @if($licencaModalVisible)
                <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                    <div class="flex">
                        <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M288 0C305.7 0 320 14.33 320 32V96C320 113.7 305.7 128 288 128H208V160H424.1C456.6 160 483.5 183.1 488.2 214.4L510.9 364.1C511.6 368.8 512 373.6 512 378.4V448C512 483.3 483.3 512 448 512H64C28.65 512 0 483.3 0 448V378.4C0 373.6 .3622 368.8 1.083 364.1L23.76 214.4C28.5 183.1 55.39 160 87.03 160H143.1V128H63.1C46.33 128 31.1 113.7 31.1 96V32C31.1 14.33 46.33 0 63.1 0L288 0zM96 48C87.16 48 80 55.16 80 64C80 72.84 87.16 80 96 80H256C264.8 80 272 72.84 272 64C272 55.16 264.8 48 256 48H96zM80 448H432C440.8 448 448 440.8 448 432C448 423.2 440.8 416 432 416H80C71.16 416 64 423.2 64 432C64 440.8 71.16 448 80 448zM112 216C98.75 216 88 226.7 88 240C88 253.3 98.75 264 112 264C125.3 264 136 253.3 136 240C136 226.7 125.3 216 112 216zM208 264C221.3 264 232 253.3 232 240C232 226.7 221.3 216 208 216C194.7 216 184 226.7 184 240C184 253.3 194.7 264 208 264zM160 296C146.7 296 136 306.7 136 320C136 333.3 146.7 344 160 344C173.3 344 184 333.3 184 320C184 306.7 173.3 296 160 296zM304 264C317.3 264 328 253.3 328 240C328 226.7 317.3 216 304 216C290.7 216 280 226.7 280 240C280 253.3 290.7 264 304 264zM256 296C242.7 296 232 306.7 232 320C232 333.3 242.7 344 256 344C269.3 344 280 333.3 280 320C280 306.7 269.3 296 256 296zM400 264C413.3 264 424 253.3 424 240C424 226.7 413.3 216 400 216C386.7 216 376 226.7 376 240C376 253.3 386.7 264 400 264zM352 296C338.7 296 328 306.7 328 320C328 333.3 338.7 344 352 344C365.3 344 376 333.3 376 320C376 306.7 365.3 296 352 296z"/></svg></div>
                        <div>
                            <p>Terminal: <span class="font-bold">{{$selectedTerminal->sn}}</span> &nbsp;&nbsp;&nbsp; Staus: <span class="font-bold">{{ $selectedTerminal->ts_naziv }}</span></p>
                            <p class="text-sm">Lokacija: <span class="font-bold">{{ $selectedTerminal->l_naziv }}, {{$selectedTerminal->mesto}}</span></p>
                            <p class="text-sm">Model: <span class="font-bold">{{ $selectedTerminal->treminal_model }}</span> | Proizvođač: <span class="font-bold">{{$selectedTerminal->treminal_proizvodjac}}</span></p>
                            <p class="text-sm">Distributer: <span class="font-bold">{{ $selectedTerminal->distributer_naziv }}</span></p>
                            <p class="text-sm">PIB: <span class="font-bold">{{ $selectedTerminal->pib }}</span></p>
                            <p class="text-sm">Poslednja promena: <span class="font-bold">{{ App\Http\Helpers::datumFormat($selectedTerminal->updated_at) }}</span></p>
                        </div>
                    </div>
                </div> 
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

    {{-- ERROR LICENCA --}}
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

    {{-- BLAKLIST modal --}}
    <x-jet-dialog-modal wire:model="modalFormVisible">
        <x-slot name="title">
            {{ __('Promeni blacklist status terminala') }}
        </x-slot>

        <x-slot name="content">
        @if($modalFormVisible)
            @if ($multiSelected)
                <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                    <div class="flex">
                    <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M288 0C305.7 0 320 14.33 320 32V96C320 113.7 305.7 128 288 128H208V160H424.1C456.6 160 483.5 183.1 488.2 214.4L510.9 364.1C511.6 368.8 512 373.6 512 378.4V448C512 483.3 483.3 512 448 512H64C28.65 512 0 483.3 0 448V378.4C0 373.6 .3622 368.8 1.083 364.1L23.76 214.4C28.5 183.1 55.39 160 87.03 160H143.1V128H63.1C46.33 128 31.1 113.7 31.1 96V32C31.1 14.33 46.33 0 63.1 0L288 0zM96 48C87.16 48 80 55.16 80 64C80 72.84 87.16 80 96 80H256C264.8 80 272 72.84 272 64C272 55.16 264.8 48 256 48H96zM80 448H432C440.8 448 448 440.8 448 432C448 423.2 440.8 416 432 416H80C71.16 416 64 423.2 64 432C64 440.8 71.16 448 80 448zM112 216C98.75 216 88 226.7 88 240C88 253.3 98.75 264 112 264C125.3 264 136 253.3 136 240C136 226.7 125.3 216 112 216zM208 264C221.3 264 232 253.3 232 240C232 226.7 221.3 216 208 216C194.7 216 184 226.7 184 240C184 253.3 194.7 264 208 264zM160 296C146.7 296 136 306.7 136 320C136 333.3 146.7 344 160 344C173.3 344 184 333.3 184 320C184 306.7 173.3 296 160 296zM304 264C317.3 264 328 253.3 328 240C328 226.7 317.3 216 304 216C290.7 216 280 226.7 280 240C280 253.3 290.7 264 304 264zM256 296C242.7 296 232 306.7 232 320C232 333.3 242.7 344 256 344C269.3 344 280 333.3 280 320C280 306.7 269.3 296 256 296zM400 264C413.3 264 424 253.3 424 240C424 226.7 413.3 216 400 216C386.7 216 376 226.7 376 240C376 253.3 386.7 264 400 264zM352 296C338.7 296 328 306.7 328 320C328 333.3 338.7 344 352 344C365.3 344 376 333.3 376 320C376 306.7 365.3 296 352 296z"/></svg></div>
                        <div>
                            @foreach ($multiSelectedInfo as $item)
                                <p>Terminal: <span class="font-bold">{{$item->sn}}</span> &nbsp;&nbsp;&nbsp; Staus: <span class="font-bold">{{ $item->ts_naziv }}</span></p>
                                <p class="text-sm">Lokacija: {{ $item->l_naziv }}, {{$item->mesto}}</p><hr />
                            @endforeach
                        </div>
                    </div>
                </div> 
            @else
                <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                    <div class="flex">
                        <div class="flex-none py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M288 0C305.7 0 320 14.33 320 32V96C320 113.7 305.7 128 288 128H208V160H424.1C456.6 160 483.5 183.1 488.2 214.4L510.9 364.1C511.6 368.8 512 373.6 512 378.4V448C512 483.3 483.3 512 448 512H64C28.65 512 0 483.3 0 448V378.4C0 373.6 .3622 368.8 1.083 364.1L23.76 214.4C28.5 183.1 55.39 160 87.03 160H143.1V128H63.1C46.33 128 31.1 113.7 31.1 96V32C31.1 14.33 46.33 0 63.1 0L288 0zM96 48C87.16 48 80 55.16 80 64C80 72.84 87.16 80 96 80H256C264.8 80 272 72.84 272 64C272 55.16 264.8 48 256 48H96zM80 448H432C440.8 448 448 440.8 448 432C448 423.2 440.8 416 432 416H80C71.16 416 64 423.2 64 432C64 440.8 71.16 448 80 448zM112 216C98.75 216 88 226.7 88 240C88 253.3 98.75 264 112 264C125.3 264 136 253.3 136 240C136 226.7 125.3 216 112 216zM208 264C221.3 264 232 253.3 232 240C232 226.7 221.3 216 208 216C194.7 216 184 226.7 184 240C184 253.3 194.7 264 208 264zM160 296C146.7 296 136 306.7 136 320C136 333.3 146.7 344 160 344C173.3 344 184 333.3 184 320C184 306.7 173.3 296 160 296zM304 264C317.3 264 328 253.3 328 240C328 226.7 317.3 216 304 216C290.7 216 280 226.7 280 240C280 253.3 290.7 264 304 264zM256 296C242.7 296 232 306.7 232 320C232 333.3 242.7 344 256 344C269.3 344 280 333.3 280 320C280 306.7 269.3 296 256 296zM400 264C413.3 264 424 253.3 424 240C424 226.7 413.3 216 400 216C386.7 216 376 226.7 376 240C376 253.3 386.7 264 400 264zM352 296C338.7 296 328 306.7 328 320C328 333.3 338.7 344 352 344C365.3 344 376 333.3 376 320C376 306.7 365.3 296 352 296z"/></svg></div>
                        <div class="flex-auto">
                            <p>Terminal: <span class="font-bold">{{$selectedTerminal->sn}}</span> &nbsp;&nbsp;&nbsp; Staus: <span class="font-bold">{{ $selectedTerminal->ts_naziv }}</span></p>
                            <p class="text-sm">Lokacija: {{ $selectedTerminal->l_naziv }}, {{$selectedTerminal->mesto}}</p>
                        </div>
                        <div class="self-end w-8">
                            @if($selectedTerminal->blacklist == 1)
                                <svg class="fill-current w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">	<path class="st1" d="M320,464c8.8,0,16-7.2,16-16V160h-80c-17.7,0-32-14.3-32-32V48H64c-8.8,0-16,7.2-16,16v384 c0,8.8,7.2,16,16,16H320z M0,64C0,28.7,28.7,0,64,0h165.5c17,0,33.3,6.7,45.3,18.7l90.5,90.5c12,12,18.7,28.3,18.7,45.3V448 c0,35.3-28.7,64-64,64H64c-35.3,0-64-28.7-64-64V64z"/> <path class="st1" d="M48,238c0,39.8,21.1,75.3,54,98.4c0,0.2,0,0.4,0,0.6v36c0,14.9,12.1,27,27,27h27v-27c0-5,4-9,9-9s9,4,9,9v27 h36v-27c0-5,4-9,9-9s9,4,9,9v27h27c14.9,0,27-12.1,27-27v-36c0-0.2,0-0.4,0-0.6c32.9-23.1,54-58.6,54-98.4	c0-22.2-6.6-43.1-18.1-61.2h-24.5c-40.6,0-75-27.1-86.2-64.1c-5-0.5-10.1-0.7-15.2-0.7C112.5,112,48,168.4,48,238z M138,292	c-19.9,0-36-16.1-36-36s16.1-36,36-36s36,16.1,36,36S157.9,292,138,292z M246,220c19.9,0,36,16.1,36,36s-16.1,36-36,36 s-36-16.1-36-36S226.1,220,246,220z"/></svg>
                            @else
                                <svg class="fill-current w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M373.1,134.6L253.4,15.3C243.5,5.5,230.2,0,216.3,0H59C26.5,0,0,26.5,0,59v394c0,32.5,26.5,59,59,59h266 c32.5,0,59-26.5,59-59V160.9C384,151.1,380.1,141.6,373.1,134.6z M354.9,151.8h-61.5c-35.8,0-65-29.2-65-65v-59 c2.7,1.3,5.1,3.1,7.3,5.2L354.9,151.8z M359,453c0,9-3.6,17.5-10,24c-6.5,6.5-15,10-24,10H59c-9,0-17.5-3.6-24-10 c-6.5-6.5-10-15-10-24V59c0-9,3.6-17.5,10-24c6.5-6.5,15-10,24-10h144.4v61.8c0,49.6,40.4,90,90,90H359V453z"/><g><path d="M159.9,391.1h111.3v26.3h-141V197.8h29.7V391.1z"/></g></svg>
                            @endif
                        </div>
                    </div>
                </div> 
            @endif
            <div class="font-bold text-xl text-red-500 mb-6">
                {{$canBlacklistErorr}}
            </div>
        @endif
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalFormVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
            @if($canBlacklist)
                    @if($selectedTerminal->blacklist == 1)
                        <x-jet-secondary-button class="ml-2" wire:click="blacklistUpdate" wire:loading.attr="disabled">
                            {{ __('Ukloni sa Blackliste') }}
                        </x-jet-secondary-button>
                    @else
                        <x-jet-button class="ml-2" wire:click="blacklistUpdate" wire:loading.attr="disabled">
                            {{ __('Dodaj na Blacklistu') }}
                        </x-jet-button>
                    @endif
            @endif   
        </x-slot>
    </x-jet-dialog-modal>

    
    {{-- HISTORI Modal --}}
    <x-jet-dialog-modal wire:model="terminalHistoryVisible">
        <x-slot name="title">
            {{ __('Vremenska linija terminala') }}
        </x-slot>
       
        <x-slot name="content">
            @if($terminalHistoryVisible)
                <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                    <div class="flex">
                        <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M288 0C305.7 0 320 14.33 320 32V96C320 113.7 305.7 128 288 128H208V160H424.1C456.6 160 483.5 183.1 488.2 214.4L510.9 364.1C511.6 368.8 512 373.6 512 378.4V448C512 483.3 483.3 512 448 512H64C28.65 512 0 483.3 0 448V378.4C0 373.6 .3622 368.8 1.083 364.1L23.76 214.4C28.5 183.1 55.39 160 87.03 160H143.1V128H63.1C46.33 128 31.1 113.7 31.1 96V32C31.1 14.33 46.33 0 63.1 0L288 0zM96 48C87.16 48 80 55.16 80 64C80 72.84 87.16 80 96 80H256C264.8 80 272 72.84 272 64C272 55.16 264.8 48 256 48H96zM80 448H432C440.8 448 448 440.8 448 432C448 423.2 440.8 416 432 416H80C71.16 416 64 423.2 64 432C64 440.8 71.16 448 80 448zM112 216C98.75 216 88 226.7 88 240C88 253.3 98.75 264 112 264C125.3 264 136 253.3 136 240C136 226.7 125.3 216 112 216zM208 264C221.3 264 232 253.3 232 240C232 226.7 221.3 216 208 216C194.7 216 184 226.7 184 240C184 253.3 194.7 264 208 264zM160 296C146.7 296 136 306.7 136 320C136 333.3 146.7 344 160 344C173.3 344 184 333.3 184 320C184 306.7 173.3 296 160 296zM304 264C317.3 264 328 253.3 328 240C328 226.7 317.3 216 304 216C290.7 216 280 226.7 280 240C280 253.3 290.7 264 304 264zM256 296C242.7 296 232 306.7 232 320C232 333.3 242.7 344 256 344C269.3 344 280 333.3 280 320C280 306.7 269.3 296 256 296zM400 264C413.3 264 424 253.3 424 240C424 226.7 413.3 216 400 216C386.7 216 376 226.7 376 240C376 253.3 386.7 264 400 264zM352 296C338.7 296 328 306.7 328 320C328 333.3 338.7 344 352 344C365.3 344 376 333.3 376 320C376 306.7 365.3 296 352 296z"/></svg></div>
                        <div>
                            <p>Terminal: <span class="font-bold">{{$selectedTerminal->sn}}</span> &nbsp;&nbsp;&nbsp; Staus: <span class="font-bold">{{ $selectedTerminal->ts_naziv }}</span></p>
                            <p class="text-sm">Lokacija: <span class="font-bold">{{ $selectedTerminal->l_naziv }}, {{$selectedTerminal->mesto}}</span></p>
                            <p class="text-sm">Model: <span class="font-bold">{{ $selectedTerminal->treminal_model }}</span> | Proizvođač: <span class="font-bold">{{$selectedTerminal->treminal_proizvodjac}}</span></p>
                            <p class="text-sm">Distributer: <span class="font-bold">{{ $selectedTerminal->distributer_naziv }}</span></p>
                            <p class="text-sm">PIB: <span class="font-bold">{{ $selectedTerminal->pib }}</span></p>
                            <p class="text-sm">Poslednja promena: <span class="font-bold">{{ App\Http\Helpers::datumFormat($selectedTerminal->updated_at) }}</span></p>
                        </div>
                    </div>
                </div> 
                @if(count($historyData))
                    <ol class="relative border-l border-gray-200 dark:border-gray-700">
                        @foreach($historyData as $item)                 
                        <li class="mb-4 ml-6">            
                            <span class="flex absolute -left-3 justify-center items-center w-6 h-6 bg-sky-100 rounded-full ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900">
                                @if($item->tabela == 'tlh')
                                    <svg class="w-3 h-3" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                @else
                                    <svg class="fill-orange-600 w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M128 160H448V352H128V160zM512 64C547.3 64 576 92.65 576 128V208C549.5 208 528 229.5 528 256C528 282.5 549.5 304 576 304V384C576 419.3 547.3 448 512 448H64C28.65 448 0 419.3 0 384V304C26.51 304 48 282.5 48 256C48 229.5 26.51 208 0 208V128C0 92.65 28.65 64 64 64H512zM96 352C96 369.7 110.3 384 128 384H448C465.7 384 480 369.7 480 352V160C480 142.3 465.7 128 448 128H128C110.3 128 96 142.3 96 160V352z"/></svg>
                                @endif
                            </span>
                            @if($item->tabela == 'tlh')
                                <h3 class="flex items-center mb-1 text-lg font-semibold text-gray-900 dark:text-white"> - <span class="bg-sky-100 text-sky-900 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-blue-200 dark:text-blue-800 ml-3">{{ App\Http\Helpers::datumFormat($item->updated_at) }}</span></h3>
                                <p class="mb-0 text-base font-normal text-gray-500 dark:text-gray-400">Status: <span class="font-bold">{{ $item->status_naziv }}</span></p>
                                <p class="mb-2 text-base font-normal text-gray-500 dark:text-gray-400">Lokacija: <span class="font-bold">{{ $item->lokacija }} , {{ $item->mesto }}</span></p>
                            @else
                                <h3 class="flex items-center mb-1 text-lg font-semibold text-gray-900 dark:text-white"> - <span class="bg-sky-100 text-sky-900 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-blue-200 dark:text-blue-800 ml-3">{{ App\Http\Helpers::datumFormat($item->updated_at) }}</span></h3>
                                <p class="mb-0 text-base font-normal text-gray-500 dark:text-gray-400"><span class="font-bold">Tiket #{{ $item->lokacija }}</span> , {{ $item->mesto }} : {{ $item->dodeljen }}</p>
                                <p class="mb-2 text-base font-normal text-gray-500 dark:text-gray-400">Opis kvara: <span class="font-bold">{{ $item->user_ime }}</span></p>
                            @endif
                        </li>
                        @endforeach
                    </ol>
                @endif
            @endif
        </x-slot>

        <x-slot name="footer">
        <x-jet-secondary-button wire:click="$toggle('terminalHistoryVisible')" wire:loading.attr="disabled">
                {{ __('Zatvori') }}
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>

    {{-- PREMESTI Modal --}}
    <x-jet-dialog-modal wire:model="modalConfirmPremestiVisible">
        <x-slot name="title">
            {{ __('Premesti terminal') }}
        </x-slot>

        <x-slot name="content">
        @if($modalConfirmPremestiVisible)
            @if ($multiSelected)
                    <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                        <div class="flex">
                        <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M288 0C305.7 0 320 14.33 320 32V96C320 113.7 305.7 128 288 128H208V160H424.1C456.6 160 483.5 183.1 488.2 214.4L510.9 364.1C511.6 368.8 512 373.6 512 378.4V448C512 483.3 483.3 512 448 512H64C28.65 512 0 483.3 0 448V378.4C0 373.6 .3622 368.8 1.083 364.1L23.76 214.4C28.5 183.1 55.39 160 87.03 160H143.1V128H63.1C46.33 128 31.1 113.7 31.1 96V32C31.1 14.33 46.33 0 63.1 0L288 0zM96 48C87.16 48 80 55.16 80 64C80 72.84 87.16 80 96 80H256C264.8 80 272 72.84 272 64C272 55.16 264.8 48 256 48H96zM80 448H432C440.8 448 448 440.8 448 432C448 423.2 440.8 416 432 416H80C71.16 416 64 423.2 64 432C64 440.8 71.16 448 80 448zM112 216C98.75 216 88 226.7 88 240C88 253.3 98.75 264 112 264C125.3 264 136 253.3 136 240C136 226.7 125.3 216 112 216zM208 264C221.3 264 232 253.3 232 240C232 226.7 221.3 216 208 216C194.7 216 184 226.7 184 240C184 253.3 194.7 264 208 264zM160 296C146.7 296 136 306.7 136 320C136 333.3 146.7 344 160 344C173.3 344 184 333.3 184 320C184 306.7 173.3 296 160 296zM304 264C317.3 264 328 253.3 328 240C328 226.7 317.3 216 304 216C290.7 216 280 226.7 280 240C280 253.3 290.7 264 304 264zM256 296C242.7 296 232 306.7 232 320C232 333.3 242.7 344 256 344C269.3 344 280 333.3 280 320C280 306.7 269.3 296 256 296zM400 264C413.3 264 424 253.3 424 240C424 226.7 413.3 216 400 216C386.7 216 376 226.7 376 240C376 253.3 386.7 264 400 264zM352 296C338.7 296 328 306.7 328 320C328 333.3 338.7 344 352 344C365.3 344 376 333.3 376 320C376 306.7 365.3 296 352 296z"/></svg></div>
                            <div>
                                @foreach ($multiSelectedInfo as $item)
                                    <p>Terminal: <span class="font-bold">{{$item->sn}}</span> &nbsp;&nbsp;&nbsp; Staus: <span class="font-bold">{{ $item->ts_naziv }}</span></p>
                                    <p class="text-sm">Lokacija: {{ $item->l_naziv }}, {{$item->mesto}}</p><hr />
                                @endforeach
                            </div>
                        </div>
                    </div> 
                @else
                    <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                        <div class="flex">
                            <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M288 0C305.7 0 320 14.33 320 32V96C320 113.7 305.7 128 288 128H208V160H424.1C456.6 160 483.5 183.1 488.2 214.4L510.9 364.1C511.6 368.8 512 373.6 512 378.4V448C512 483.3 483.3 512 448 512H64C28.65 512 0 483.3 0 448V378.4C0 373.6 .3622 368.8 1.083 364.1L23.76 214.4C28.5 183.1 55.39 160 87.03 160H143.1V128H63.1C46.33 128 31.1 113.7 31.1 96V32C31.1 14.33 46.33 0 63.1 0L288 0zM96 48C87.16 48 80 55.16 80 64C80 72.84 87.16 80 96 80H256C264.8 80 272 72.84 272 64C272 55.16 264.8 48 256 48H96zM80 448H432C440.8 448 448 440.8 448 432C448 423.2 440.8 416 432 416H80C71.16 416 64 423.2 64 432C64 440.8 71.16 448 80 448zM112 216C98.75 216 88 226.7 88 240C88 253.3 98.75 264 112 264C125.3 264 136 253.3 136 240C136 226.7 125.3 216 112 216zM208 264C221.3 264 232 253.3 232 240C232 226.7 221.3 216 208 216C194.7 216 184 226.7 184 240C184 253.3 194.7 264 208 264zM160 296C146.7 296 136 306.7 136 320C136 333.3 146.7 344 160 344C173.3 344 184 333.3 184 320C184 306.7 173.3 296 160 296zM304 264C317.3 264 328 253.3 328 240C328 226.7 317.3 216 304 216C290.7 216 280 226.7 280 240C280 253.3 290.7 264 304 264zM256 296C242.7 296 232 306.7 232 320C232 333.3 242.7 344 256 344C269.3 344 280 333.3 280 320C280 306.7 269.3 296 256 296zM400 264C413.3 264 424 253.3 424 240C424 226.7 413.3 216 400 216C386.7 216 376 226.7 376 240C376 253.3 386.7 264 400 264zM352 296C338.7 296 328 306.7 328 320C328 333.3 338.7 344 352 344C365.3 344 376 333.3 376 320C376 306.7 365.3 296 352 296z"/></svg></div>
                            <div>
                                <p>Terminal: <span class="font-bold">{{$selectedTerminal->sn}}</span> &nbsp;&nbsp;&nbsp; Staus: <span class="font-bold">{{ $selectedTerminal->ts_naziv }}</span></p>
                                <p class="text-sm">Lokacija: {{ $selectedTerminal->l_naziv }}, {{$selectedTerminal->mesto}}</p>
                            </div>
                        </div>
                    </div> 
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
                    <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M168.3 499.2C116.1 435 0 279.4 0 192C0 85.96 85.96 0 192 0C298 0 384 85.96 384 192C384 279.4 267 435 215.7 499.2C203.4 514.5 180.6 514.5 168.3 499.2H168.3zM192 256C227.3 256 256 227.3 256 192C256 156.7 227.3 128 192 128C156.7 128 128 156.7 128 192C128 227.3 156.7 256 192 256z"/></svg></div>
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

    {{-- NOVI TERMINALI Modal --}}
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

    {{-- KOMENTARI MODAL --}}
    <x-jet-dialog-modal wire:model="modalKomentariVisible">
        <x-slot name="title">
            KOMENTARI
        </x-slot>
        <x-slot name="content"> 
            @if($modalKomentariVisible)
                <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                    <div class="flex">
                        <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M288 0C305.7 0 320 14.33 320 32V96C320 113.7 305.7 128 288 128H208V160H424.1C456.6 160 483.5 183.1 488.2 214.4L510.9 364.1C511.6 368.8 512 373.6 512 378.4V448C512 483.3 483.3 512 448 512H64C28.65 512 0 483.3 0 448V378.4C0 373.6 .3622 368.8 1.083 364.1L23.76 214.4C28.5 183.1 55.39 160 87.03 160H143.1V128H63.1C46.33 128 31.1 113.7 31.1 96V32C31.1 14.33 46.33 0 63.1 0L288 0zM96 48C87.16 48 80 55.16 80 64C80 72.84 87.16 80 96 80H256C264.8 80 272 72.84 272 64C272 55.16 264.8 48 256 48H96zM80 448H432C440.8 448 448 440.8 448 432C448 423.2 440.8 416 432 416H80C71.16 416 64 423.2 64 432C64 440.8 71.16 448 80 448zM112 216C98.75 216 88 226.7 88 240C88 253.3 98.75 264 112 264C125.3 264 136 253.3 136 240C136 226.7 125.3 216 112 216zM208 264C221.3 264 232 253.3 232 240C232 226.7 221.3 216 208 216C194.7 216 184 226.7 184 240C184 253.3 194.7 264 208 264zM160 296C146.7 296 136 306.7 136 320C136 333.3 146.7 344 160 344C173.3 344 184 333.3 184 320C184 306.7 173.3 296 160 296zM304 264C317.3 264 328 253.3 328 240C328 226.7 317.3 216 304 216C290.7 216 280 226.7 280 240C280 253.3 290.7 264 304 264zM256 296C242.7 296 232 306.7 232 320C232 333.3 242.7 344 256 344C269.3 344 280 333.3 280 320C280 306.7 269.3 296 256 296zM400 264C413.3 264 424 253.3 424 240C424 226.7 413.3 216 400 216C386.7 216 376 226.7 376 240C376 253.3 386.7 264 400 264zM352 296C338.7 296 328 306.7 328 320C328 333.3 338.7 344 352 344C365.3 344 376 333.3 376 320C376 306.7 365.3 296 352 296z"/></svg></div>
                        <div>
                            <p>Terminal: <span class="font-bold">{{$selectedTerminal->sn}}</span> &nbsp;&nbsp;&nbsp; Staus: <span class="font-bold">{{ $selectedTerminal->ts_naziv }}</span></p>
                            <p class="text-sm">Lokacija: <span class="font-bold">{{ $selectedTerminal->l_naziv }}, {{$selectedTerminal->mesto}}</span></p>
                            <p class="text-sm">Model: <span class="font-bold">{{ $selectedTerminal->treminal_model }}</span> | Proizvođač: <span class="font-bold">{{$selectedTerminal->treminal_proizvodjac}}</span></p>
                            <p class="text-sm">Distributer: <span class="font-bold">{{ $selectedTerminal->distributer_naziv }}</span></p>
                            <p class="text-sm">PIB: <span class="font-bold">{{ $selectedTerminal->pib }}</span></p>
                        </div>
                    </div>
                </div> 


                <div class="flex mb-4">
                    <div class="py-1"><svg class="fill-slate-500 w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path d="M416 176C416 78.8 322.9 0 208 0S0 78.8 0 176c0 39.57 15.62 75.96 41.67 105.4c-16.39 32.76-39.23 57.32-39.59 57.68c-2.1 2.205-2.67 5.475-1.441 8.354C1.9 350.3 4.602 352 7.66 352c38.35 0 70.76-11.12 95.74-24.04C134.2 343.1 169.8 352 208 352C322.9 352 416 273.2 416 176zM599.6 443.7C624.8 413.9 640 376.6 640 336C640 238.8 554 160 448 160c-.3145 0-.6191 .041-.9336 .043C447.5 165.3 448 170.6 448 176c0 98.62-79.68 181.2-186.1 202.5C282.7 455.1 357.1 512 448 512c33.69 0 65.32-8.008 92.85-21.98C565.2 502 596.1 512 632.3 512c3.059 0 5.76-1.725 7.02-4.605c1.229-2.879 .6582-6.148-1.441-8.354C637.6 498.7 615.9 475.3 599.6 443.7z"/></svg></div> 
                    <div class="w-full">
                        <div class="flex justify-between">
                            <div class="mt-2 font-bold">Komentari:</div>
                            <div class="font-light">{{$selectedTerminalCommentsCount}}</div>
                        </div>
                        @if($selectedTerminalComments->count())
                            @foreach($selectedTerminalComments as $komentar)
                                <div class="py-2">
                                    <div class="flex justify-between">
                                        <div class="flex">
                                            <svg class="float-left fill-slate-600 w-4 h-4 mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224 256c70.7 0 128-57.31 128-128s-57.3-128-128-128C153.3 0 96 57.31 96 128S153.3 256 224 256zM274.7 304H173.3C77.61 304 0 381.6 0 477.3c0 19.14 15.52 34.67 34.66 34.67h378.7C432.5 512 448 496.5 448 477.3C448 381.6 370.4 304 274.7 304z"/></svg>
                                            <div class="font-bold">{{ $komentar->user_name }}</div> 
                                            <div class="text-sm ml-4">{{ App\Http\Helpers::datumFormat($komentar->created_at) }}</div>
                                        </div>
                                        <button wire:click="obrisiKomentar({{$komentar->id}})" class="mr-4 p-1 rounded-lg text-red-500 hover:text-white hover:bg-red-400 cursor-pointer"><svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg></button>
                                    </div>
                                    <div class=" border rounded-lg border-slate-50 bg-slate-200 px-2 py-1 ml-10">{{ $komentar->comment }}</div>
                                </div>
                            @endforeach
                        @endif
                        
                        <div class="mt-4">
                            <x-jet-label for="newKoment" value="{{ __('Dodaj komentar:') }}" />
                            <x-jet-textarea id="newKoment" type="textarea" class="mt-1 block w-full disabled:opacity-50" wire:model.defer="newKoment" />
                            @error('newKoment') <span class="error">{{ $message }}</span> @enderror
                            <x-jet-secondary-button wire:click="posaljiKomentar" wire:loading.attr="disabled" class="mt-2">
                                {{ __('Posalji komentar') }}
                            </x-jet-secondary-button>

                        </div> 
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