<div class="p-6">
    <div class="flex items-center justify-between px-4 py-3 text-right sm:px-6">
        <div class="flex font-semibold text-xl">
            <div class="ml-2 pr-2"><svg class="fill-red-600 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path d="M0 48C0 21.5 21.5 0 48 0H336c26.5 0 48 21.5 48 48V232.2c-39.1 32.3-64 81.1-64 135.8c0 49.5 20.4 94.2 53.3 126.2C364.5 505.1 351.1 512 336 512H240V432c0-26.5-21.5-48-48-48s-48 21.5-48 48v80H48c-26.5 0-48-21.5-48-48V48zM80 224c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V240c0-8.8-7.2-16-16-16H80zm80 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V240c0-8.8-7.2-16-16-16H176c-8.8 0-16 7.2-16 16zm112-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V240c0-8.8-7.2-16-16-16H272zM64 112v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V112c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zM176 96c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V112c0-8.8-7.2-16-16-16H176zm80 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V112c0-8.8-7.2-16-16-16H272c-8.8 0-16 7.2-16 16zm96 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm140.7-67.3c-6.2 6.2-6.2 16.4 0 22.6L521.4 352H432c-8.8 0-16 7.2-16 16s7.2 16 16 16h89.4l-28.7 28.7c-6.2 6.2-6.2 16.4 0 22.6s16.4 6.2 22.6 0l56-56c6.2-6.2 6.2-16.4 0-22.6l-56-56c-6.2-6.2-16.4-6.2-22.6 0z"/></svg></div>
            <div class="text-red-600">{{ $dist_name }}</div>
        </div>
        <div class="flex">
            <x-jet-secondary-button wire:click="downloadExcel" wire:loading.attr="disabled" class="ml-2">
                <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M64 0C28.7 0 0 28.7 0 64L0 448c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-288-128 0c-17.7 0-32-14.3-32-32L224 0 64 0zM256 0l0 128 128 0L256 0zM155.7 250.2L192 302.1l36.3-51.9c7.6-10.9 22.6-13.5 33.4-5.9s13.5 22.6 5.9 33.4L221.3 344l46.4 66.2c7.6 10.9 5 25.8-5.9 33.4s-25.8 5-33.4-5.9L192 385.8l-36.3 51.9c-7.6 10.9-22.6 13.5-33.4 5.9s-13.5-22.6-5.9-33.4L162.7 344l-46.4-66.2c-7.6-10.9-5-25.8 5.9-33.4s25.8-5 33.4 5.9z"/></svg>
                {{ __('Preuzmi Excel') }}
            </x-jet-secondary-button>
            <div class="ml-2">
                <button data-tooltip-target="tooltip-default" type="button" class="mt-1">
                    <svg class="fill-green-500 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336l24 0 0-64-24 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l48 0c13.3 0 24 10.7 24 24l0 88 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-80 0c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg>
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
                                    <svg class="mx-auto fill-red-400 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">	<path class="st1" d="M320,464c8.8,0,16-7.2,16-16V160h-80c-17.7,0-32-14.3-32-32V48H64c-8.8,0-16,7.2-16,16v384 c0,8.8,7.2,16,16,16H320z M0,64C0,28.7,28.7,0,64,0h165.5c17,0,33.3,6.7,45.3,18.7l90.5,90.5c12,12,18.7,28.3,18.7,45.3V448 c0,35.3-28.7,64-64,64H64c-35.3,0-64-28.7-64-64V64z"/> <path class="st1" d="M48,238c0,39.8,21.1,75.3,54,98.4c0,0.2,0,0.4,0,0.6v36c0,14.9,12.1,27,27,27h27v-27c0-5,4-9,9-9s9,4,9,9v27 h36v-27c0-5,4-9,9-9s9,4,9,9v27h27c14.9,0,27-12.1,27-27v-36c0-0.2,0-0.4,0-0.6c32.9-23.1,54-58.6,54-98.4	c0-22.2-6.6-43.1-18.1-61.2h-24.5c-40.6,0-75-27.1-86.2-64.1c-5-0.5-10.1-0.7-15.2-0.7C112.5,112,48,168.4,48,238z M138,292	c-19.9,0-36-16.1-36-36s16.1-36,36-36s36,16.1,36,36S157.9,292,138,292z M246,220c19.9,0,36,16.1,36,36s-16.1,36-36,36 s-36-16.1-36-36S226.1,220,246,220z"/></svg>
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
                                    <x-jet-input wire:model="searchTerminalSn" id="" class="block bg-orange-50 w-48" type="text" placeholder="Serijski broj" />
                                </td>
                                <td colspan="2">
                                    <x-jet-input wire:model="searchMesto" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži mesto, adresu, naziv" />
                                </td>
                                <td>
                                    <select wire:model="searchTipLicence" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="0">---</option>
                                        @foreach (App\Models\LicencaDistributerCena::LicenceDistributera($distId) as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                        <option value="1000">Bez licence</option>
                                    </select>
                                </td>
                                <td>
                                    <select wire:model="searchNenaplativ" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="0">---</option>
                                        <option value="1">Da</option>
                                    </select>
                                </td>
                                <td colspan="2">
                                    <x-jet-input wire:model="searchPib" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži PIB" />
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

                                    @php
                                        $item->month_diff = App\Http\Helpers::monthDifference($item->datum_kraj_licence);
                                    @endphp
                                    <tr>
                                        <td>
                                            @if($item->zaduzeno)
                                                @if($item->razduzeno && $item->razduzeno > 0)
                                                    <svg class="fill-green-500 w-5 h-5 mt-0.5 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"/></svg>
                                                @else
                                                    <svg class="fill-red-500 h-4 w-4 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M506.3 417l-213.3-364c-16.33-28-57.54-28-73.98 0l-213.2 364C-10.59 444.9 9.849 480 42.74 480h426.6C502.1 480 522.6 445 506.3 417zM232 168c0-13.25 10.75-24 24-24S280 154.8 280 168v128c0 13.25-10.75 24-23.1 24S232 309.3 232 296V168zM256 416c-17.36 0-31.44-14.08-31.44-31.44c0-17.36 14.07-31.44 31.44-31.44s31.44 14.08 31.44 31.44C287.4 401.9 273.4 416 256 416z"/></svg>
                                                @endif
                                            @endif
                                            @if($item->nenaplativ)
                                                <svg class="mx-auto fill-red-400 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">	<path class="st1" d="M320,464c8.8,0,16-7.2,16-16V160h-80c-17.7,0-32-14.3-32-32V48H64c-8.8,0-16,7.2-16,16v384 c0,8.8,7.2,16,16,16H320z M0,64C0,28.7,28.7,0,64,0h165.5c17,0,33.3,6.7,45.3,18.7l90.5,90.5c12,12,18.7,28.3,18.7,45.3V448 c0,35.3-28.7,64-64,64H64c-35.3,0-64-28.7-64-64V64z"/> <path class="st1" d="M48,238c0,39.8,21.1,75.3,54,98.4c0,0.2,0,0.4,0,0.6v36c0,14.9,12.1,27,27,27h27v-27c0-5,4-9,9-9s9,4,9,9v27 h36v-27c0-5,4-9,9-9s9,4,9,9v27h27c14.9,0,27-12.1,27-27v-36c0-0.2,0-0.4,0-0.6c32.9-23.1,54-58.6,54-98.4	c0-22.2-6.6-43.1-18.1-61.2h-24.5c-40.6,0-75-27.1-86.2-64.1c-5-0.5-10.1-0.7-15.2-0.7C112.5,112,48,168.4,48,238z M138,292	c-19.9,0-36-16.1-36-36s16.1-36,36-36s36,16.1,36,36S157.9,292,138,292z M246,220c19.9,0,36,16.1,36,36s-16.1,36-36,36 s-36-16.1-36-36S226.1,220,246,220z"/></svg>
                                            @endif
                                        </td>
                                        <td class="px-2 py-2">  
                                            @if($item->isDuplicate)
                                                <span class=""> <svg class="fill-red-400 w-5 h-5 ml-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 410.1 512"><path d="M401.3,327.3L270.8,196.8c-11.7-11.7-30.7-11.7-42.4,0c-11.7,11.7-11.7,30.7,0,42.4l78.3,78.3H117.1 c-0.8,0-1.6,0-2.5,0.1c-0.2,0-25.7,1.2-40.9-12.8c-9.2-8.5-13.7-21.7-13.7-40.3V31.7c0-16.6-13.4-30-30-30S0,15.1,0,31.7v232.9 c0,44.6,18,70.5,33.1,84.4c27.9,25.7,63.9,28.8,79.3,28.8c2.4,0,4.4-0.1,5.7-0.2h190.5l-80.2,80.2c-11.7,11.7-11.7,30.7,0,42.4 c5.9,5.9,13.5,8.8,21.2,8.8s15.4-2.9,21.2-8.8l130.5-130.5C413,358,413,339,401.3,327.3z"/></svg></span> 
                                            @else
                                                {{ $item->sn }}
                                            @endif 
                                        </td>
                                        <td class="px-2 py-2">
                                            @if($item->isDuplicate)
                                                <span class=""> <svg class="fill-red-400 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l370.7 0-73.4 73.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l128-128z"/></svg></span> 
                                            @else
                                                {{ $item->l_naziv }}<br />{{ $item->adresa }}, {{ $item->mesto }}
                                            @endif   
                                        </td>
                                        <td>
                                            @if(!$item->isDuplicate)
                                                <div class="flex align-middle">
                                                    
                                                    
                                                    @if($item->latitude != '' && $item->longitude != '') 
                                                    <span class="mt-2">
                                                        <a href="{{ App\Ivan\HelperFunctions::createGmapLink($item->latitude, $item->longitude) }}" target="_blank"> <svg class="fill-sky-800 w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M408 120C408 174.6 334.9 271.9 302.8 311.1C295.1 321.6 280.9 321.6 273.2 311.1C241.1 271.9 168 174.6 168 120C168 53.73 221.7 0 288 0C354.3 0 408 53.73 408 120zM288 152C310.1 152 328 134.1 328 112C328 89.91 310.1 72 288 72C265.9 72 248 89.91 248 112C248 134.1 265.9 152 288 152zM425.6 179.8C426.1 178.6 426.6 177.4 427.1 176.1L543.1 129.7C558.9 123.4 576 135 576 152V422.8C576 432.6 570 441.4 560.9 445.1L416 503V200.4C419.5 193.5 422.7 186.7 425.6 179.8zM150.4 179.8C153.3 186.7 156.5 193.5 160 200.4V451.8L32.91 502.7C17.15 508.1 0 497.4 0 480.4V209.6C0 199.8 5.975 190.1 15.09 187.3L137.6 138.3C140 152.5 144.9 166.6 150.4 179.8H150.4zM327.8 331.1C341.7 314.6 363.5 286.3 384 255V504.3L192 449.4V255C212.5 286.3 234.3 314.6 248.2 331.1C268.7 357.6 307.3 357.6 327.8 331.1L327.8 331.1z"/></svg></a>
                                                    </span> 
                                                    @endif
                                            
                                                <x-jet-secondary-button class="px-1" onclick="copyToCliboard('{{$item->adresa}}', '{{$item->mesto}}')" wire:click="showLatLogModal({{ $item->lokid }})" title="Dodaj koordinate" >
                                                        
                                                    <svg class="fill-sky-800 w-4 h-4" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 299.4 432.7" style="enable-background:new 0 0 299.4 432.7;" xml:space="preserve"><g id="Layer_2"><g><path d="M149.7,0C67.2,0,0,67.2,0,149.7c0,45.1,27.5,81.1,59.4,122.7c26,34,55.6,72.6,76.4,124.2l14.6,36.1l13.4-36.5 c16.1-43.9,44.2-80.8,71.3-116.4c33.1-43.5,64.3-84.5,64.3-130.1C299.4,67.2,232.2,0,149.7,0z M211.2,261.6 c-21,27.6-44.2,58-61.8,92.7c-20.2-40-44.4-71.5-66.2-100.1C53.5,215.4,30,184.7,30,149.7C30,83.7,83.7,30,149.7,30 s119.7,53.7,119.7,119.7C269.4,185.1,242.4,220.6,211.2,261.6z"/><path d="M206.5,136.3h-41.8V94.5c0-8.3-6.7-15-15-15s-15,6.7-15,15v41.8H92.9c-8.3,0-15,6.7-15,15s6.7,15,15,15h41.8v41.8 c0,8.3,6.7,15,15,15s15-6.7,15-15v-41.8h41.8c8.3,0,15-6.7,15-15S214.8,136.3,206.5,136.3z"/></g></g></svg>
                                                </x-jet-secondary-button>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-2 py-2">
                                            @if($item->isDuplicate) 
                                                <span class="float-left pr-2"><svg class="fill-red-400 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M240 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H32c-17.7 0-32 14.3-32 32s14.3 32 32 32H176V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H384c17.7 0 32-14.3 32-32s-14.3-32-32-32H240V80z"/></svg></span>
                                            @endif
                                            {{ $item->licenca_naziv }}
                                        </td>  
                                        <td td class="px-1 py-1">
                                            @if($item->nenaplativ > 0)
                                                    <svg class="mx-auto fill-red-400 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">	<path class="st1" d="M320,464c8.8,0,16-7.2,16-16V160h-80c-17.7,0-32-14.3-32-32V48H64c-8.8,0-16,7.2-16,16v384 c0,8.8,7.2,16,16,16H320z M0,64C0,28.7,28.7,0,64,0h165.5c17,0,33.3,6.7,45.3,18.7l90.5,90.5c12,12,18.7,28.3,18.7,45.3V448 c0,35.3-28.7,64-64,64H64c-35.3,0-64-28.7-64-64V64z"/> <path class="st1" d="M48,238c0,39.8,21.1,75.3,54,98.4c0,0.2,0,0.4,0,0.6v36c0,14.9,12.1,27,27,27h27v-27c0-5,4-9,9-9s9,4,9,9v27 h36v-27c0-5,4-9,9-9s9,4,9,9v27h27c14.9,0,27-12.1,27-27v-36c0-0.2,0-0.4,0-0.6c32.9-23.1,54-58.6,54-98.4	c0-22.2-6.6-43.1-18.1-61.2h-24.5c-40.6,0-75-27.1-86.2-64.1c-5-0.5-10.1-0.7-15.2-0.7C112.5,112,48,168.4,48,238z M138,292	c-19.9,0-36-16.1-36-36s16.1-36,36-36s36,16.1,36,36S157.9,292,138,292z M246,220c19.9,0,36,16.1,36,36s-16.1,36-36,36 s-36-16.1-36-36S226.1,220,246,220z"/></svg>
                                            @endif
                                        </td> 
                                        
                                        <td class="px-2 py-2">@if($item->datum_pocetka_licence != '') {{ App\Http\Helpers::datumFormatDan($item->datum_pocetka_licence) }} @endif</td>
                                        <td>
                                            <div class="float-right">
                                            @if($item->month_diff !='')
                                                @if($item->month_diff == 0)
                                                    <svg class="fill-red-300 w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M32 0C14.3 0 0 14.3 0 32S14.3 64 32 64V75c0 42.4 16.9 83.1 46.9 113.1L146.7 256 78.9 323.9C48.9 353.9 32 394.6 32 437v11c-17.7 0-32 14.3-32 32s14.3 32 32 32H64 320h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V437c0-42.4-16.9-83.1-46.9-113.1L237.3 256l67.9-67.9c30-30 46.9-70.7 46.9-113.1V64c17.7 0 32-14.3 32-32s-14.3-32-32-32H320 64 32zM96 75V64H288V75c0 25.5-10.1 49.9-28.1 67.9L192 210.7l-67.9-67.9C106.1 124.9 96 100.4 96 75z"/></svg>
                                                @elseif($item->month_diff < 0)
                                                    <svg class="fill-red-600 w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M128 0c13.3 0 24 10.7 24 24V64H296V24c0-13.3 10.7-24 24-24s24 10.7 24 24V64h40c35.3 0 64 28.7 64 64v16 48V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V192 144 128C0 92.7 28.7 64 64 64h40V24c0-13.3 10.7-24 24-24zM400 192H48V448c0 8.8 7.2 16 16 16H384c8.8 0 16-7.2 16-16V192zm-95 89l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/></svg>
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
                                                <svg class="fill-red-500 w-5 h-5 mr-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 0C114.6 0 0 114.6 0 256s114.6 256 256 256s256-114.6 256-256S397.4 0 256 0zM256 128c17.67 0 32 14.33 32 32c0 17.67-14.33 32-32 32S224 177.7 224 160C224 142.3 238.3 128 256 128zM296 384h-80C202.8 384 192 373.3 192 360s10.75-24 24-24h16v-64H224c-13.25 0-24-10.75-24-24S210.8 224 224 224h32c13.25 0 24 10.75 24 24v88h16c13.25 0 24 10.75 24 24S309.3 384 296 384z"/></svg>
                                                </x-jet-secondary-button>
                                            @endif
                                        </td>
                                        <td class="px-1 py-1">
                                            @if(!$item->isDuplicate)
                                               <button class="p-2 text-sm relative text-gray-300 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="commentsShowModal({{ $item->id }})" title="Komentari">
                                                    <div class="z-10 absolute ml-1 mb-1 text-gray-500 text-lg font-bold">{{ $item->br_komentara}}</div>

                                                    <svg class="text-current w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
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

    {{-- The Delete Modal --}}
    <x-jet-dialog-modal wire:model="modalConfirmDeleteVisible">
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

    {{-- The Terminal Info Modal --}}
    <x-jet-dialog-modal wire:model="modalTerminalInfoVisible">
        <x-slot name="title">
            <svg class="float-left fill-red-500 w-4 h-4 mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M256 0C114.6 0 0 114.6 0 256s114.6 256 256 256s256-114.6 256-256S397.4 0 256 0zM256 128c17.67 0 32 14.33 32 32c0 17.67-14.33 32-32 32S224 177.7 224 160C224 142.3 238.3 128 256 128zM296 384h-80C202.8 384 192 373.3 192 360s10.75-24 24-24h16v-64H224c-13.25 0-24-10.75-24-24S210.8 224 224 224h32c13.25 0 24 10.75 24 24v88h16c13.25 0 24 10.75 24 24S309.3 384 296 384z"/></svg>
                Info
        </x-slot>
        <x-slot name="content">
            @if($modalTerminalInfoVisible)
            <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                <div class="flex">
                    <div class="flex-none py-1">
                        <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M288 0C305.7 0 320 14.33 320 32V96C320 113.7 305.7 128 288 128H208V160H424.1C456.6 160 483.5 183.1 488.2 214.4L510.9 364.1C511.6 368.8 512 373.6 512 378.4V448C512 483.3 483.3 512 448 512H64C28.65 512 0 483.3 0 448V378.4C0 373.6 .3622 368.8 1.083 364.1L23.76 214.4C28.5 183.1 55.39 160 87.03 160H143.1V128H63.1C46.33 128 31.1 113.7 31.1 96V32C31.1 14.33 46.33 0 63.1 0L288 0zM96 48C87.16 48 80 55.16 80 64C80 72.84 87.16 80 96 80H256C264.8 80 272 72.84 272 64C272 55.16 264.8 48 256 48H96zM80 448H432C440.8 448 448 440.8 448 432C448 423.2 440.8 416 432 416H80C71.16 416 64 423.2 64 432C64 440.8 71.16 448 80 448zM112 216C98.75 216 88 226.7 88 240C88 253.3 98.75 264 112 264C125.3 264 136 253.3 136 240C136 226.7 125.3 216 112 216zM208 264C221.3 264 232 253.3 232 240C232 226.7 221.3 216 208 216C194.7 216 184 226.7 184 240C184 253.3 194.7 264 208 264zM160 296C146.7 296 136 306.7 136 320C136 333.3 146.7 344 160 344C173.3 344 184 333.3 184 320C184 306.7 173.3 296 160 296zM304 264C317.3 264 328 253.3 328 240C328 226.7 317.3 216 304 216C290.7 216 280 226.7 280 240C280 253.3 290.7 264 304 264zM256 296C242.7 296 232 306.7 232 320C232 333.3 242.7 344 256 344C269.3 344 280 333.3 280 320C280 306.7 269.3 296 256 296zM400 264C413.3 264 424 253.3 424 240C424 226.7 413.3 216 400 216C386.7 216 376 226.7 376 240C376 253.3 386.7 264 400 264zM352 296C338.7 296 328 306.7 328 320C328 333.3 338.7 344 352 344C365.3 344 376 333.3 376 320C376 306.7 365.3 296 352 296z"/></svg>
                    </div>
                    <div class="flex-auto">
                        <p>Serijski broj: <span class="font-bold"> {{ $terminalInfo->sn }}</span>, Kutija: {{ $terminalInfo->broj_kutije }}</p>
                        <p class="text-sm">Status: <span class="font-bold">{{ $terminalInfo->ts_naziv }}</span></p>
                    </div>
                    <div class="self-end w-8">
                        @if($terminalInfo->blacklist == 1)
                            <svg class="fill-current w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">	<path class="st1" d="M320,464c8.8,0,16-7.2,16-16V160h-80c-17.7,0-32-14.3-32-32V48H64c-8.8,0-16,7.2-16,16v384 c0,8.8,7.2,16,16,16H320z M0,64C0,28.7,28.7,0,64,0h165.5c17,0,33.3,6.7,45.3,18.7l90.5,90.5c12,12,18.7,28.3,18.7,45.3V448 c0,35.3-28.7,64-64,64H64c-35.3,0-64-28.7-64-64V64z"/> <path class="st1" d="M48,238c0,39.8,21.1,75.3,54,98.4c0,0.2,0,0.4,0,0.6v36c0,14.9,12.1,27,27,27h27v-27c0-5,4-9,9-9s9,4,9,9v27 h36v-27c0-5,4-9,9-9s9,4,9,9v27h27c14.9,0,27-12.1,27-27v-36c0-0.2,0-0.4,0-0.6c32.9-23.1,54-58.6,54-98.4	c0-22.2-6.6-43.1-18.1-61.2h-24.5c-40.6,0-75-27.1-86.2-64.1c-5-0.5-10.1-0.7-15.2-0.7C112.5,112,48,168.4,48,238z M138,292	c-19.9,0-36-16.1-36-36s16.1-36,36-36s36,16.1,36,36S157.9,292,138,292z M246,220c19.9,0,36,16.1,36,36s-16.1,36-36,36 s-36-16.1-36-36S226.1,220,246,220z"/></svg>
                        @else
                            <svg class="fill-current w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M373.1,134.6L253.4,15.3C243.5,5.5,230.2,0,216.3,0H59C26.5,0,0,26.5,0,59v394c0,32.5,26.5,59,59,59h266 c32.5,0,59-26.5,59-59V160.9C384,151.1,380.1,141.6,373.1,134.6z M354.9,151.8h-61.5c-35.8,0-65-29.2-65-65v-59 c2.7,1.3,5.1,3.1,7.3,5.2L354.9,151.8z M359,453c0,9-3.6,17.5-10,24c-6.5,6.5-15,10-24,10H59c-9,0-17.5-3.6-24-10 c-6.5-6.5-10-15-10-24V59c0-9,3.6-17.5,10-24c6.5-6.5,15-10,24-10h144.4v61.8c0,49.6,40.4,90,90,90H359V453z"/><g><path d="M159.9,391.1h111.3v26.3h-141V197.8h29.7V391.1z"/></g></svg>
                        @endif
                    </div>
                </div>
            </div> 
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
                    <svg class="fill-red-500 h-6 w-6 " xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M506.3 417l-213.3-364c-16.33-28-57.54-28-73.98 0l-213.2 364C-10.59 444.9 9.849 480 42.74 480h426.6C502.1 480 522.6 445 506.3 417zM232 168c0-13.25 10.75-24 24-24S280 154.8 280 168v128c0 13.25-10.75 24-23.1 24S232 309.3 232 296V168zM256 416c-17.36 0-31.44-14.08-31.44-31.44c0-17.36 14.07-31.44 31.44-31.44s31.44 14.08 31.44 31.44C287.4 401.9 273.4 416 256 416z"/></svg>
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
     <x-jet-dialog-modal wire:model="latLogVisible">
        <x-slot name="title">
        <svg class="float-left fill-sky-800 w-6 h-6 mx-auto" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 299.4 432.7" style="enable-background:new 0 0 299.4 432.7;" xml:space="preserve"><g id="Layer_2"><g><path d="M149.7,0C67.2,0,0,67.2,0,149.7c0,45.1,27.5,81.1,59.4,122.7c26,34,55.6,72.6,76.4,124.2l14.6,36.1l13.4-36.5 c16.1-43.9,44.2-80.8,71.3-116.4c33.1-43.5,64.3-84.5,64.3-130.1C299.4,67.2,232.2,0,149.7,0z M211.2,261.6 c-21,27.6-44.2,58-61.8,92.7c-20.2-40-44.4-71.5-66.2-100.1C53.5,215.4,30,184.7,30,149.7C30,83.7,83.7,30,149.7,30 s119.7,53.7,119.7,119.7C269.4,185.1,242.4,220.6,211.2,261.6z"/><path d="M206.5,136.3h-41.8V94.5c0-8.3-6.7-15-15-15s-15,6.7-15,15v41.8H92.9c-8.3,0-15,6.7-15,15s6.7,15,15,15h41.8v41.8 c0,8.3,6.7,15,15,15s15-6.7,15-15v-41.8h41.8c8.3,0,15-6.7,15-15S214.8,136.3,206.5,136.3z"/></g></g></svg>
            {{ __('Koordinate') }}
        </x-slot>

        <x-slot name="content">
            @if($latLogVisible)
                <div>Lokacija:</div>
                    <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                        <div class="flex">
                            <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M168.3 499.2C116.1 435 0 279.4 0 192C0 85.96 85.96 0 192 0C298 0 384 85.96 384 192C384 279.4 267 435 215.7 499.2C203.4 514.5 180.6 514.5 168.3 499.2H168.3zM192 256C227.3 256 256 227.3 256 192C256 156.7 227.3 128 192 128C156.7 128 128 156.7 128 192C128 227.3 156.7 256 192 256z"/></svg></div>
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
                            <x-jet-input wire:model="latLogValue" id="" class="block mt-1 w-full" type="text" />
                                @error('lat_value') <span class="error">{{ $message }}</span> @enderror
                                @error('long_value') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex-2 ml-4 mt-4 pt-2">
                            <x-jet-danger-button class="ml-2" wire:click="removeLatLog" wire:loading.attr="disabled">
                                <svg class="fill-current w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M135.2 17.69C140.6 6.848 151.7 0 163.8 0H284.2C296.3 0 307.4 6.848 312.8 17.69L320 32H416C433.7 32 448 46.33 448 64C448 81.67 433.7 96 416 96H32C14.33 96 0 81.67 0 64C0 46.33 14.33 32 32 32H128L135.2 17.69zM31.1 128H416V448C416 483.3 387.3 512 352 512H95.1C60.65 512 31.1 483.3 31.1 448V128zM111.1 208V432C111.1 440.8 119.2 448 127.1 448C136.8 448 143.1 440.8 143.1 432V208C143.1 199.2 136.8 192 127.1 192C119.2 192 111.1 199.2 111.1 208zM207.1 208V432C207.1 440.8 215.2 448 223.1 448C232.8 448 240 440.8 240 432V208C240 199.2 232.8 192 223.1 192C215.2 192 207.1 199.2 207.1 208zM304 208V432C304 440.8 311.2 448 320 448C328.8 448 336 440.8 336 432V208C336 199.2 328.8 192 320 192C311.2 192 304 199.2 304 208z"/></svg>
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

     <script>
        function copyToCliboard(adresa, mesto) {
            navigator.clipboard.writeText(adresa+ ", "+ mesto);
        } 
     </script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</div>