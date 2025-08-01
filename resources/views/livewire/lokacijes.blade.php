<div class="p-6">
    <div class="flex items-center justify-end px-4 py-3 text-right sm:px-6">
        <x-jet-button wire:click="createShowModal">
        <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M0 256C0 114.6 114.6 0 256 0C397.4 0 512 114.6 512 256C512 397.4 397.4 512 256 512C114.6 512 0 397.4 0 256zM256 368C269.3 368 280 357.3 280 344V280H344C357.3 280 368 269.3 368 256C368 242.7 357.3 232 344 232H280V168C280 154.7 269.3 144 256 144C242.7 144 232 154.7 232 168V232H168C154.7 232 144 242.7 144 256C144 269.3 154.7 280 168 280H232V344C232 357.3 242.7 368 256 368z"/></svg>
        {{ __('Nova Lokacija') }}
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
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="$set('orderBy', 'uid')"><svg class="@if ($orderBy == 'uid') {{ 'fill-orange-600' }}  @else {{ 'fill-current' }} @endif w-4 h-4 mr-0 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M320 224H416c17.67 0 32-14.33 32-32s-14.33-32-32-32h-95.1c-17.67 0-32 14.33-32 32S302.3 224 320 224zM320 352H480c17.67 0 32-14.33 32-32s-14.33-32-32-32h-159.1c-17.67 0-32 14.33-32 32S302.3 352 320 352zM320 96h32c17.67 0 31.1-14.33 31.1-32s-14.33-32-31.1-32h-32c-17.67 0-32 14.33-32 32S302.3 96 320 96zM544 416h-223.1c-17.67 0-32 14.33-32 32s14.33 32 32 32H544c17.67 0 32-14.33 32-32S561.7 416 544 416zM192.4 330.7L160 366.1V64.03C160 46.33 145.7 32 128 32S96 46.33 96 64.03v302L63.6 330.7c-6.312-6.883-14.94-10.38-23.61-10.38c-7.719 0-15.47 2.781-21.61 8.414c-13.03 11.95-13.9 32.22-1.969 45.27l87.1 96.09c12.12 13.26 35.06 13.26 47.19 0l87.1-96.09c11.94-13.05 11.06-33.31-1.969-45.27C224.6 316.8 204.4 317.7 192.4 330.7z"/></svg></button>
                                </th>
                                <th class="bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase"></th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="$set('orderBy', 'name')">NAZIV <svg class="@if ($orderBy == 'name') {{ 'fill-orange-600' }}  @else {{ 'fill-current' }} @endif float-right w-4 h-4 mr-0 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M320 224H416c17.67 0 32-14.33 32-32s-14.33-32-32-32h-95.1c-17.67 0-32 14.33-32 32S302.3 224 320 224zM320 352H480c17.67 0 32-14.33 32-32s-14.33-32-32-32h-159.1c-17.67 0-32 14.33-32 32S302.3 352 320 352zM320 96h32c17.67 0 31.1-14.33 31.1-32s-14.33-32-31.1-32h-32c-17.67 0-32 14.33-32 32S302.3 96 320 96zM544 416h-223.1c-17.67 0-32 14.33-32 32s14.33 32 32 32H544c17.67 0 32-14.33 32-32S561.7 416 544 416zM192.4 330.7L160 366.1V64.03C160 46.33 145.7 32 128 32S96 46.33 96 64.03v302L63.6 330.7c-6.312-6.883-14.94-10.38-23.61-10.38c-7.719 0-15.47 2.781-21.61 8.414c-13.03 11.95-13.9 32.22-1.969 45.27l87.1 96.09c12.12 13.26 35.06 13.26 47.19 0l87.1-96.09c11.94-13.05 11.06-33.31-1.969-45.27C224.6 316.8 204.4 317.7 192.4 330.7z"/></svg></button>
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="$set('orderBy', 'mesto')">MESTO <svg class="@if ($orderBy == 'mesto') {{ 'fill-orange-600' }}  @else {{ 'fill-current' }} @endif float-right w-4 h-4 mr-0 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M320 224H416c17.67 0 32-14.33 32-32s-14.33-32-32-32h-95.1c-17.67 0-32 14.33-32 32S302.3 224 320 224zM320 352H480c17.67 0 32-14.33 32-32s-14.33-32-32-32h-159.1c-17.67 0-32 14.33-32 32S302.3 352 320 352zM320 96h32c17.67 0 31.1-14.33 31.1-32s-14.33-32-31.1-32h-32c-17.67 0-32 14.33-32 32S302.3 96 320 96zM544 416h-223.1c-17.67 0-32 14.33-32 32s14.33 32 32 32H544c17.67 0 32-14.33 32-32S561.7 416 544 416zM192.4 330.7L160 366.1V64.03C160 46.33 145.7 32 128 32S96 46.33 96 64.03v302L63.6 330.7c-6.312-6.883-14.94-10.38-23.61-10.38c-7.719 0-15.47 2.781-21.61 8.414c-13.03 11.95-13.9 32.22-1.969 45.27l87.1 96.09c12.12 13.26 35.06 13.26 47.19 0l87.1-96.09c11.94-13.05 11.06-33.31-1.969-45.27C224.6 316.8 204.4 317.7 192.4 330.7z"/></svg></button>
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"> 
                                    <button wire:click="$set('orderBy', 'region')">REGION <svg class="@if ($orderBy == 'region') {{ 'fill-orange-600' }}  @else {{ 'fill-current' }} @endif float-right w-4 h-4 mr-0 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M320 224H416c17.67 0 32-14.33 32-32s-14.33-32-32-32h-95.1c-17.67 0-32 14.33-32 32S302.3 224 320 224zM320 352H480c17.67 0 32-14.33 32-32s-14.33-32-32-32h-159.1c-17.67 0-32 14.33-32 32S302.3 352 320 352zM320 96h32c17.67 0 31.1-14.33 31.1-32s-14.33-32-31.1-32h-32c-17.67 0-32 14.33-32 32S302.3 96 320 96zM544 416h-223.1c-17.67 0-32 14.33-32 32s14.33 32 32 32H544c17.67 0 32-14.33 32-32S561.7 416 544 416zM192.4 330.7L160 366.1V64.03C160 46.33 145.7 32 128 32S96 46.33 96 64.03v302L63.6 330.7c-6.312-6.883-14.94-10.38-23.61-10.38c-7.719 0-15.47 2.781-21.61 8.414c-13.03 11.95-13.9 32.22-1.969 45.27l87.1 96.09c12.12 13.26 35.06 13.26 47.19 0l87.1-96.09c11.94-13.05 11.06-33.31-1.969-45.27C224.6 316.8 204.4 317.7 192.4 330.7z"/></svg></button>
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    <button wire:click="$set('orderBy', 'tip')">TIP <svg class="@if ($orderBy == 'tip') {{ 'fill-orange-600' }}  @else {{ 'fill-current' }} @endif float-right w-4 h-4 mr-0 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M320 224H416c17.67 0 32-14.33 32-32s-14.33-32-32-32h-95.1c-17.67 0-32 14.33-32 32S302.3 224 320 224zM320 352H480c17.67 0 32-14.33 32-32s-14.33-32-32-32h-159.1c-17.67 0-32 14.33-32 32S302.3 352 320 352zM320 96h32c17.67 0 31.1-14.33 31.1-32s-14.33-32-31.1-32h-32c-17.67 0-32 14.33-32 32S302.3 96 320 96zM544 416h-223.1c-17.67 0-32 14.33-32 32s14.33 32 32 32H544c17.67 0 32-14.33 32-32S561.7 416 544 416zM192.4 330.7L160 366.1V64.03C160 46.33 145.7 32 128 32S96 46.33 96 64.03v302L63.6 330.7c-6.312-6.883-14.94-10.38-23.61-10.38c-7.719 0-15.47 2.781-21.61 8.414c-13.03 11.95-13.9 32.22-1.969 45.27l87.1 96.09c12.12 13.26 35.06 13.26 47.19 0l87.1-96.09c11.94-13.05 11.06-33.31-1.969-45.27C224.6 316.8 204.4 317.7 192.4 330.7z"/></svg></button>
                                </th>
                                <th colspan="2" class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">PIB</th>
                                
                                <th class="bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase"></th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">  
                            {{-- SEARCH ROW --}}
                            <tr class="bg-orange-50">
                                <td><svg class="mx-auto fill-orange-600 w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M3.853 54.87C10.47 40.9 24.54 32 40 32H472C487.5 32 501.5 40.9 508.1 54.87C514.8 68.84 512.7 85.37 502.1 97.33L320 320.9V448C320 460.1 313.2 471.2 302.3 476.6C291.5 482 278.5 480.9 268.8 473.6L204.8 425.6C196.7 419.6 192 410.1 192 400V320.9L9.042 97.33C-.745 85.37-2.765 68.84 3.854 54.87L3.853 54.87z"/></svg></td>
                                <td></td>
                                <td>
                                    <x-jet-input wire:model="searchName" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži ime" />
                                </td>
                                <td>
                                    <x-jet-input wire:model="searchMesto" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži mesto" />
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
                                    <select wire:model="searchTip" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">---</option>
                                        @foreach (App\Models\LokacijaTip::tipoviList() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td colspan="2">
                                    <x-jet-input wire:model="searchPib" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži PIB" />
                                </td>
                                <td colspan="2" class="text-right text-sm pr-4">Ukupno: <span class="font-bold">{{ $data->total() }}</span></td>
                            </tr>
                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <x-jet-secondary-button class="btn btn-blue" wire:click="updateShowModal({{ $item->id }})">
                                            <svg class="fill-current w-4 h-4 mr-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M373.1 24.97C401.2-3.147 446.8-3.147 474.9 24.97L487 37.09C515.1 65.21 515.1 110.8 487 138.9L289.8 336.2C281.1 344.8 270.4 351.1 258.6 354.5L158.6 383.1C150.2 385.5 141.2 383.1 135 376.1C128.9 370.8 126.5 361.8 128.9 353.4L157.5 253.4C160.9 241.6 167.2 230.9 175.8 222.2L373.1 24.97zM440.1 58.91C431.6 49.54 416.4 49.54 407 58.91L377.9 88L424 134.1L453.1 104.1C462.5 95.6 462.5 80.4 453.1 71.03L440.1 58.91zM203.7 266.6L186.9 325.1L245.4 308.3C249.4 307.2 252.9 305.1 255.8 302.2L390.1 168L344 121.9L209.8 256.2C206.9 259.1 204.8 262.6 203.7 266.6zM200 64C213.3 64 224 74.75 224 88C224 101.3 213.3 112 200 112H88C65.91 112 48 129.9 48 152V424C48 446.1 65.91 464 88 464H360C382.1 464 400 446.1 400 424V312C400 298.7 410.7 288 424 288C437.3 288 448 298.7 448 312V424C448 472.6 408.6 512 360 512H88C39.4 512 0 472.6 0 424V152C0 103.4 39.4 64 88 64H200z"/></svg>
                                            </x-jet-button>
                                        </td> 
                                        <td>@switch($item->tipid)
                                                @case(1)
                                                    <svg class="fill-red-600 w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M331.8 224.1c28.29 0 54.88 10.99 74.86 30.97l19.59 19.59c40.01-17.74 71.25-53.3 81.62-96.65c5.725-23.92 5.34-47.08 .2148-68.4c-2.613-10.88-16.43-14.51-24.34-6.604l-68.9 68.9h-75.6V97.2l68.9-68.9c7.912-7.912 4.275-21.73-6.604-24.34c-21.32-5.125-44.48-5.51-68.4 .2148c-55.3 13.23-98.39 60.22-107.2 116.4C224.5 128.9 224.2 137 224.3 145l82.78 82.86C315.2 225.1 323.5 224.1 331.8 224.1zM384 278.6c-23.16-23.16-57.57-27.57-85.39-13.9L191.1 158L191.1 95.99l-127.1-95.99L0 63.1l96 127.1l62.04 .0077l106.7 106.6c-13.67 27.82-9.251 62.23 13.91 85.39l117 117.1c14.62 14.5 38.21 14.5 52.71-.0016l52.75-52.75c14.5-14.5 14.5-38.08-.0016-52.71L384 278.6zM227.9 307L168.7 247.9l-148.9 148.9c-26.37 26.37-26.37 69.08 0 95.45C32.96 505.4 50.21 512 67.5 512s34.54-6.592 47.72-19.78l119.1-119.1C225.5 352.3 222.6 329.4 227.9 307zM64 472c-13.25 0-24-10.75-24-24c0-13.26 10.75-24 24-24S88 434.7 88 448C88 461.3 77.25 472 64 472z"/></svg>
                                                @break
                                                @case(2)
                                                    <svg class="fill-gray-500 w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M0 488V171.3C0 145.2 15.93 121.6 40.23 111.9L308.1 4.753C315.7 1.702 324.3 1.702 331.9 4.753L599.8 111.9C624.1 121.6 640 145.2 640 171.3V488C640 501.3 629.3 512 616 512H568C554.7 512 544 501.3 544 488V223.1C544 206.3 529.7 191.1 512 191.1H128C110.3 191.1 96 206.3 96 223.1V488C96 501.3 85.25 512 72 512H24C10.75 512 0 501.3 0 488zM152 512C138.7 512 128 501.3 128 488V432H512V488C512 501.3 501.3 512 488 512H152zM128 336H512V400H128V336zM128 224H512V304H128V224z"/></svg>
                                                @break
                                                @case(3)
                                                <svg class="fill-sky-600 w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M495.5 223.2C491.6 223.7 487.6 224 483.4 224C457.4 224 434.2 212.6 418.3 195C402.4 212.6 379.2 224 353.1 224C327 224 303.8 212.6 287.9 195C272 212.6 248.9 224 222.7 224C196.7 224 173.5 212.6 157.6 195C141.7 212.6 118.5 224 92.36 224C88.3 224 84.21 223.7 80.24 223.2C24.92 215.8-1.255 150.6 28.33 103.8L85.66 13.13C90.76 4.979 99.87 0 109.6 0H466.4C476.1 0 485.2 4.978 490.3 13.13L547.6 103.8C577.3 150.7 551 215.8 495.5 223.2H495.5zM499.7 254.9C503.1 254.4 508 253.6 512 252.6V448C512 483.3 483.3 512 448 512H128C92.66 512 64 483.3 64 448V252.6C67.87 253.6 71.86 254.4 75.97 254.9L76.09 254.9C81.35 255.6 86.83 256 92.36 256C104.8 256 116.8 254.1 128 250.6V384H448V250.7C459.2 254.1 471.1 256 483.4 256C489 256 494.4 255.6 499.7 254.9L499.7 254.9z"/></svg>
                                                @break
                                            @endswitch
                                        </td>

                                        <td class="px-4 py-2">{{ $item->l_naziv }}</td>
                                        <td class="px-4 py-2">{{ $item->mesto }}</td>
                                        <td class="px-4 py-2">{{ $item->r_naziv }}</td> 
                                        <td class="px-4 py-2">{{ $item->lt_naziv }}</td> 
                                        <td class="px-2 py-2">
                                            @if($item->kontakt)
                                            <x-jet-secondary-button wire:click="showKontaktOsobaModal({{ $item->id }})" title="Kontakt osoba">
                                                <svg class="fill-current w-6 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 448"><defs><style>.a{fill:#fff;}</style></defs><path d="M512,0H64A64,64,0,0,0,0,64V384a64,64,0,0,0,64,64H512a64,64,0,0,0,64-64V64A64,64,0,0,0,512,0Z"/><circle class="a" cx="186.65" cy="137.79" r="86.21"/><path class="a" d="M382.28,317.58h133a25,25,0,0,0,24.94-24.94V76.51a25,25,0,0,0-24.94-24.94h-133a24.94,24.94,0,0,0-24.93,24.94V292.64A24.94,24.94,0,0,0,382.28,317.58Zm83.13-24.94H431.69c-4.1,0-7.84-3.74-7.84-8.31a8.34,8.34,0,0,1,8.31-8.32h33.25c4.57,0,8.31,3.74,8.31,7.85A8.45,8.45,0,0,1,465.41,292.64ZM390.6,84.82H507V251.08H390.6Z"/><path class="a" d="M57.33,396.43H316a21.61,21.61,0,0,0,21.55-21.55A107.77,107.77,0,0,0,229.76,267.11H143.54A107.76,107.76,0,0,0,35.77,374.88,21.59,21.59,0,0,0,57.33,396.43Z"/></svg>
                                            </x-jet-secondary-button>
                                            @endif
                                        </td>

                                        <td class="px-2 py-2 flex">
                                                @if($item->latitude != '' && $item->longitude != '') <a class="pt-2 mr-2" href="{{ App\Ivan\HelperFunctions::createGmapLink($item->latitude, $item->longitude) }}" target="_blank"> <svg class="fill-sky-800 w-4 h-4 mx-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M408 120C408 174.6 334.9 271.9 302.8 311.1C295.1 321.6 280.9 321.6 273.2 311.1C241.1 271.9 168 174.6 168 120C168 53.73 221.7 0 288 0C354.3 0 408 53.73 408 120zM288 152C310.1 152 328 134.1 328 112C328 89.91 310.1 72 288 72C265.9 72 248 89.91 248 112C248 134.1 265.9 152 288 152zM425.6 179.8C426.1 178.6 426.6 177.4 427.1 176.1L543.1 129.7C558.9 123.4 576 135 576 152V422.8C576 432.6 570 441.4 560.9 445.1L416 503V200.4C419.5 193.5 422.7 186.7 425.6 179.8zM150.4 179.8C153.3 186.7 156.5 193.5 160 200.4V451.8L32.91 502.7C17.15 508.1 0 497.4 0 480.4V209.6C0 199.8 5.975 190.1 15.09 187.3L137.6 138.3C140 152.5 144.9 166.6 150.4 179.8H150.4zM327.8 331.1C341.7 314.6 363.5 286.3 384 255V504.3L192 449.4V255C212.5 286.3 234.3 314.6 248.2 331.1C268.7 357.6 307.3 357.6 327.8 331.1L327.8 331.1z"/></svg></a> @endif
                                                    
                                                    <x-jet-secondary-button class="px-1" onclick="copyToCliboard('{{$item->adresa}}', '{{$item->mesto}}')" wire:click="showLatLogModal({{ $item->id }})" title="Dodaj koordinate" >
                                                        
                                                    <svg class="fill-sky-800 w-4 h-4 mx-auto" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 299.4 432.7" style="enable-background:new 0 0 299.4 432.7;" xml:space="preserve"><g id="Layer_2"><g><path d="M149.7,0C67.2,0,0,67.2,0,149.7c0,45.1,27.5,81.1,59.4,122.7c26,34,55.6,72.6,76.4,124.2l14.6,36.1l13.4-36.5 c16.1-43.9,44.2-80.8,71.3-116.4c33.1-43.5,64.3-84.5,64.3-130.1C299.4,67.2,232.2,0,149.7,0z M211.2,261.6 c-21,27.6-44.2,58-61.8,92.7c-20.2-40-44.4-71.5-66.2-100.1C53.5,215.4,30,184.7,30,149.7C30,83.7,83.7,30,149.7,30 s119.7,53.7,119.7,119.7C269.4,185.1,242.4,220.6,211.2,261.6z"/><path d="M206.5,136.3h-41.8V94.5c0-8.3-6.7-15-15-15s-15,6.7-15,15v41.8H92.9c-8.3,0-15,6.7-15,15s6.7,15,15,15h41.8v41.8 c0,8.3,6.7,15,15,15s15-6.7,15-15v-41.8h41.8c8.3,0,15-6.7,15-15S214.8,136.3,206.5,136.3z"/></g></g></svg>
                                                </x-jet-secondary-button>

                                               
                                        </td>  
                                        <td>
                                            <x-jet-secondary-button wire:click="addTerminalShowModal({{ $item->id }})" title="Dodaj terminale na lokaciju">
                                            <svg class="fill-current w-4 h-4" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 512 512"><g><path d="M422.4,254.5c-4.1-27.1-27.3-47.1-55.5-47.1H180v-27.7h69.2c15.3,0,27.7-12.4,27.7-27.7V96.7c0-15.3-12.4-27.7-27.7-27.7 H54.6c-14.5,0-27.7,12.4-27.7,27.7v55.4c0,15.3,13.2,27.7,27.7,27.7h69.2v27.7H75.3c-27.4,0-50.6,20-54.7,47.1L0.9,384 c-0.6,4.1-0.9,8.2-0.9,12.4v60.2C0,487.2,24.8,512,55.4,512h332.3c30.5,0,55.4-24.8,55.4-55.4v-60.2c0-4.2-0.3-8.3-1-12.4 L422.4,254.5z M346.1,255.9c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C325.3,265.1,334.6,255.9,346.1,255.9z M304.6,325.1c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C283.8,334.4,293.1,325.1,304.6,325.1z M263,255.9c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C242.3,265.1,251.5,255.9,263,255.9z M221.5,325.1c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C200.7,334.4,210,325.1,221.5,325.1z M200.7,276.7c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8c0-11.5,9.3-20.8,20.8-20.8 S200.7,265.1,200.7,276.7z M83.1,138.2c-7.6,0-13.8-6.2-13.8-13.8c0-7.6,6.2-13.8,13.8-13.8h138.4c7.6,0,13.8,6.2,13.8,13.8 c0,7.6-6.2,13.8-13.8,13.8H83.1z M138.4,325.1c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C117.7,334.4,126.9,325.1,138.4,325.1z M96.9,255.9c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8 c-11.5,0-20.8-9.3-20.8-20.8C76.1,265.1,85.4,255.9,96.9,255.9z M373.8,456.6H69.2c-7.6,0-13.8-6.2-13.8-13.8 c0-7.6,6.2-13.8,13.8-13.8h304.6c7.6,0,13.8,6.2,13.8,13.8C387.6,450.4,381.4,456.6,373.8,456.6z"/><polygon points="437.3,75.2 437.3,0 386.8,0 386.8,75.2 311.6,75.2 311.6,125.7 386.8,125.7 386.8,200.9 437.3,200.9 437.3,125.7 512.5,125.7 512.5,75.2 "/></g></svg>
                                            </x-jet-secondary-button>
                                        </td>                                     
                                        <td>
                                        <x-jet-secondary-button class="mx-2" wire:click="deleteShowModal({{ $item->id }})" title="Info">
                                                    <svg class="fill-red-500 w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 0C114.6 0 0 114.6 0 256s114.6 256 256 256s256-114.6 256-256S397.4 0 256 0zM256 128c17.67 0 32 14.33 32 32c0 17.67-14.33 32-32 32S224 177.7 224 160C224 142.3 238.3 128 256 128zM296 384h-80C202.8 384 192 373.3 192 360s10.75-24 24-24h16v-64H224c-13.25 0-24-10.75-24-24S210.8 224 224 224h32c13.25 0 24 10.75 24 24v88h16c13.25 0 24 10.75 24 24S309.3 384 296 384z"/></svg>
                                                </x-jet-button>
                                            <!-- @if($item->ima_user || $item->ima_terminala) -->
                                                
                                           <!-- @else
                                                <x-jet-danger-button class="mx-2" wire:click="deleteShowModal({{ $item->id }})" title="Obriši lokaciju">
                                                <svg class="fill-current w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M135.2 17.69C140.6 6.848 151.7 0 163.8 0H284.2C296.3 0 307.4 6.848 312.8 17.69L320 32H416C433.7 32 448 46.33 448 64C448 81.67 433.7 96 416 96H32C14.33 96 0 81.67 0 64C0 46.33 14.33 32 32 32H128L135.2 17.69zM31.1 128H416V448C416 483.3 387.3 512 352 512H95.1C60.65 512 31.1 483.3 31.1 448V128zM111.1 208V432C111.1 440.8 119.2 448 127.1 448C136.8 448 143.1 440.8 143.1 432V208C143.1 199.2 136.8 192 127.1 192C119.2 192 111.1 199.2 111.1 208zM207.1 208V432C207.1 440.8 215.2 448 223.1 448C232.8 448 240 440.8 240 432V208C240 199.2 232.8 192 223.1 192C215.2 192 207.1 199.2 207.1 208zM304 208V432C304 440.8 311.2 448 320 448C328.8 448 336 440.8 336 432V208C336 199.2 328.8 192 320 192C311.2 192 304 199.2 304 208z"/></svg>
                                                </x-jet-button>
                                            @endif -->
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
    
    {{-- ERROR LICENCA #########################################--}}
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

    {{-- NOVA / IZMENI LOKACIJU MODAL ############################################### --}}
    <x-jet-dialog-modal wire:model="modalFormVisible">
        <x-slot name="title">
            @if ($isUpdate) {{ __('Izmeni podatke - ') }}{{ $l_naziv }}
            @else {{ __('Nova lokacija') }} @endif
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                <x-jet-label for="l_naziv" value="{{ __('Naziv lokacije') }}" />
                <x-jet-input wire:model="l_naziv" id="" class="block mt-1 w-full" type="text" />
                @error('l_naziv') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <x-jet-label for="lokacija_tipId" value="{{ __('Vrsta lokacije') }}" />
                <select wire:model="lokacija_tipId" id="" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                    <option value="">Izaberi vrstu</option>
                    @foreach (App\Models\LokacijaTip::tipoviList() as $key => $value)    
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('lokacija_tipId') <span class="error">{{ $message }}</span> @enderror
            </div>  
            <div class="mt-4">
                <x-jet-label for="mesto" value="{{ __('Mesto') }}" />
                <x-jet-input wire:model="mesto" id="" class="block mt-1 w-full" type="text" />
                @error('mesto') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <x-jet-label for="adresa" value="{{ __('Adresa') }}" />
                <x-jet-input wire:model="adresa" id="" class="block mt-1 w-full" type="text" />
                @error('adresa') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <x-jet-label for="pib" value="{{ __('PIB') }}" />
                <x-jet-input wire:model="pib" id="" class="block mt-1 w-full" type="text" />
                @error('pib') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <x-jet-label for="mb" value="{{ __('Matični broj') }}" />
                <x-jet-input wire:model="mb" id="" class="block mt-1 w-full" type="text" />
                @error('mb') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <x-jet-label for="email" value="{{ __('e-mail') }}" />
                @if ($email_is_set)
                <p class="pl-4 font-bold">{{$email}}</p>
                @else
                    <x-jet-input wire:model="email" id="" class="block mt-1 w-full" type="text" />
                    @error('email') <span class="error">{{ $message }}</span> @enderror
                @endif
            </div>
            <div class="mt-4">
                <x-jet-label for="latitude" value="{{ __('Latitude') }}" />
                <x-jet-input wire:model="latitude" id="" class="block mt-1 w-full" type="text" />
                @error('latitude') <span class="error">{{ $message }}</span> @enderror
            </div>  
            <div class="mt-4">
                <x-jet-label for="longitude" value="{{ __('Longitude') }}" />
                <x-jet-input wire:model="longitude" id="" class="block mt-1 w-full" type="text" />
                @error('longitude') <span class="error">{{ $message }}</span> @enderror
            </div>      
            <div class="mt-4">
                <x-jet-label for="regionId" value="{{ __('Region') }}" />
                <select wire:model="regionId" id="" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                  <option value="">Odaberi region</option>
                    @foreach (App\Models\Region::regioni() as $key => $value)    
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('regionId') <span class="error">{{ $message }}</span> @enderror
            </div> 
            {{-- Kontakt osoba --}}
            @if($lokacija_tipId == 3)
                <div class="mt-6">
                    <hr />
                    <p>
                        <svg class="float-left fill-current w-4 h-4 mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 448"><defs><style>.a{fill:#fff;}</style></defs><path d="M512,0H64A64,64,0,0,0,0,64V384a64,64,0,0,0,64,64H512a64,64,0,0,0,64-64V64A64,64,0,0,0,512,0Z"/><circle class="a" cx="186.65" cy="137.79" r="86.21"/><path class="a" d="M382.28,317.58h133a25,25,0,0,0,24.94-24.94V76.51a25,25,0,0,0-24.94-24.94h-133a24.94,24.94,0,0,0-24.93,24.94V292.64A24.94,24.94,0,0,0,382.28,317.58Zm83.13-24.94H431.69c-4.1,0-7.84-3.74-7.84-8.31a8.34,8.34,0,0,1,8.31-8.32h33.25c4.57,0,8.31,3.74,8.31,7.85A8.45,8.45,0,0,1,465.41,292.64ZM390.6,84.82H507V251.08H390.6Z"/><path class="a" d="M57.33,396.43H316a21.61,21.61,0,0,0,21.55-21.55A107.77,107.77,0,0,0,229.76,267.11H143.54A107.76,107.76,0,0,0,35.77,374.88,21.59,21.59,0,0,0,57.33,396.43Z"/></svg>
                        Kontakt osoba:
                    </p>
                    <div class="mt-4">
                        <x-jet-label for="nameKo" value="{{ __('Ime') }}" />
                        <x-jet-input wire:model="nameKo" id="" class="block mt-1 w-full" type="text" />
                        @error('nameKo') <span class="error">{{ $message }}</span> @enderror
                    </div> 
                    <div class="mt-4">
                        <x-jet-label for="telKo" value="{{ __('Broj telefona') }}" />
                        <div class="mt-4 flex rounded-md shadow-sm mb-4">
                    
							<span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-m">
								+381
							</span>
							<input wire:model="telKo" class="form-input flex-1 block w-full rounded-none rounded-r-md transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
							@error('telKo') <span class="error">{{ $message }}</span>@enderror
						</div> 
					</div>
				</div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalFormVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>

            @if ($isUpdate)
                <x-jet-button class="ml-2" wire:click="update" wire:loading.attr="disabled">
                    {{ __('Update') }}
                </x-jet-danger-button>
            @else
                <x-jet-button class="ml-2" wire:click="create" wire:loading.attr="disabled">
                    {{ __('Sačuvaj') }}
                </x-jet-danger-button>
            @endif            
        </x-slot>
    </x-jet-dialog-modal>

    {{-- The Delete or INFO Modal --}}
    <x-jet-dialog-modal wire:model="modalConfirmDeleteVisible">
        <x-slot name="title">
            @if($deletePosible)
                <svg class="float-left fill-red-700 w-4 h-4 mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!-- Font Awesome Pro 5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) --><path d="M569.517 440.013C587.975 472.007 564.806 512 527.94 512H48.054c-36.937 0-59.999-40.055-41.577-71.987L246.423 23.985c18.467-32.009 64.72-31.951 83.154 0l239.94 416.028zM288 354c-25.405 0-46 20.595-46 46s20.595 46 46 46 46-20.595 46-46-20.595-46-46-46zm-43.673-165.346l7.418 136c.347 6.364 5.609 11.346 11.982 11.346h48.546c6.373 0 11.635-4.982 11.982-11.346l7.418-136c.375-6.874-5.098-12.654-11.982-12.654h-63.383c-6.884 0-12.356 5.78-11.981 12.654z"/></svg>
                Brisanje
            @else
                <svg class="float-left fill-red-500 w-4 h-4 mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M256 0C114.6 0 0 114.6 0 256s114.6 256 256 256s256-114.6 256-256S397.4 0 256 0zM256 128c17.67 0 32 14.33 32 32c0 17.67-14.33 32-32 32S224 177.7 224 160C224 142.3 238.3 128 256 128zM296 384h-80C202.8 384 192 373.3 192 360s10.75-24 24-24h16v-64H224c-13.25 0-24-10.75-24-24S210.8 224 224 224h32c13.25 0 24 10.75 24 24v88h16c13.25 0 24 10.75 24 24S309.3 384 296 384z"/></svg>
                Info
            @endif
        </x-slot>

        <x-slot name="content">
            @if($modalConfirmDeleteVisible)
                <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                    <div class="flex">
                        <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M168.3 499.2C116.1 435 0 279.4 0 192C0 85.96 85.96 0 192 0C298 0 384 85.96 384 192C384 279.4 267 435 215.7 499.2C203.4 514.5 180.6 514.5 168.3 499.2H168.3zM192 256C227.3 256 256 227.3 256 192C256 156.7 227.3 128 192 128C156.7 128 128 156.7 128 192C128 227.3 156.7 256 192 256z"/></svg></div>
                            <div>
                                <p><span class="font-bold">{{ $odabranaLokacija->l_naziv}}</span>, Adresa: <span class="font-bold">{{ $odabranaLokacija->adresa}}</span></p>
                                <p>Mesto: <span class="font-bold">{{ $odabranaLokacija->mesto }}</span>, Region: <span class="font-bold">{{ $odabranaLokacija->r_naziv }}</span></p>
                                <p>PIB: <span class="font-bold">{{ $odabranaLokacija->pib }}</span></p>
                                <p>e-mail: <span class="font-bold">{{ $odabranaLokacija->email }}</span></p>
                            </div>
                    </div>
                </div> 
            @endif
            @if($deletePosible)
                {{ __('Da li ste sigurni da želite da obrišete lokaciju?') }}
            @else 
                <p>Lokacija je povezana sa:</p>
                @foreach ($this->locationUsers($modelId) as $key => $value) 
                    @switch($key)
                        @case('users')
                            @if(count($value))
                                <p>Korisnicima:</p>
                            @endif
                        @break
                        @case('terminal')
                            <p>Ukupno terminala:</p>
                        @break
                        
                        @case('sparepart')
                            <p>Rezervnim delovima:</p>
                        @break
                        
                        @case('tiket')
                            <p>Tiketima:</p>
                        @break
                    @endswitch
                

                    @foreach($value as $name)
                         <span class="ml-6 font-bold">{{ $name }} </span><br/>
                    @endforeach
                @endforeach
            @endif
            @if($modalConfirmDeleteVisible)
                @if($odabranaLokacija->lokacija_tipId == 3)
                    @if(!$deletePosible)
                        <p>Terminali:</p>
                    @endif
                    @foreach($terminaliList as $termin)
                        <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                            <div class="flex">
                                <div class="flex-none py-1">
                                    <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M288 0C305.7 0 320 14.33 320 32V96C320 113.7 305.7 128 288 128H208V160H424.1C456.6 160 483.5 183.1 488.2 214.4L510.9 364.1C511.6 368.8 512 373.6 512 378.4V448C512 483.3 483.3 512 448 512H64C28.65 512 0 483.3 0 448V378.4C0 373.6 .3622 368.8 1.083 364.1L23.76 214.4C28.5 183.1 55.39 160 87.03 160H143.1V128H63.1C46.33 128 31.1 113.7 31.1 96V32C31.1 14.33 46.33 0 63.1 0L288 0zM96 48C87.16 48 80 55.16 80 64C80 72.84 87.16 80 96 80H256C264.8 80 272 72.84 272 64C272 55.16 264.8 48 256 48H96zM80 448H432C440.8 448 448 440.8 448 432C448 423.2 440.8 416 432 416H80C71.16 416 64 423.2 64 432C64 440.8 71.16 448 80 448zM112 216C98.75 216 88 226.7 88 240C88 253.3 98.75 264 112 264C125.3 264 136 253.3 136 240C136 226.7 125.3 216 112 216zM208 264C221.3 264 232 253.3 232 240C232 226.7 221.3 216 208 216C194.7 216 184 226.7 184 240C184 253.3 194.7 264 208 264zM160 296C146.7 296 136 306.7 136 320C136 333.3 146.7 344 160 344C173.3 344 184 333.3 184 320C184 306.7 173.3 296 160 296zM304 264C317.3 264 328 253.3 328 240C328 226.7 317.3 216 304 216C290.7 216 280 226.7 280 240C280 253.3 290.7 264 304 264zM256 296C242.7 296 232 306.7 232 320C232 333.3 242.7 344 256 344C269.3 344 280 333.3 280 320C280 306.7 269.3 296 256 296zM400 264C413.3 264 424 253.3 424 240C424 226.7 413.3 216 400 216C386.7 216 376 226.7 376 240C376 253.3 386.7 264 400 264zM352 296C338.7 296 328 306.7 328 320C328 333.3 338.7 344 352 344C365.3 344 376 333.3 376 320C376 306.7 365.3 296 352 296z"/></svg>
                                </div>
                                <div class="flex-auto">
                                    <p>Serijski broj: <span class="font-bold"> {{ $termin->sn }}</span>, Kutija: {{ $termin->broj_kutije }}</p>
                                    <p class="text-sm">Status: <span class="font-bold">{{ $termin->ts_naziv }}</span></p>
                                    @if($termin->distributer_naziv)
                                        <p class="text-sm">Distributer: <span class="font-bold">{{ $termin->distributer_naziv }}</span></p>
                                    @endif
                                </div>
                                <div class="self-end w-8">
                                    @if($termin->blacklist == 1)
                                        <svg class="fill-current w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">	<path class="st1" d="M320,464c8.8,0,16-7.2,16-16V160h-80c-17.7,0-32-14.3-32-32V48H64c-8.8,0-16,7.2-16,16v384 c0,8.8,7.2,16,16,16H320z M0,64C0,28.7,28.7,0,64,0h165.5c17,0,33.3,6.7,45.3,18.7l90.5,90.5c12,12,18.7,28.3,18.7,45.3V448 c0,35.3-28.7,64-64,64H64c-35.3,0-64-28.7-64-64V64z"/> <path class="st1" d="M48,238c0,39.8,21.1,75.3,54,98.4c0,0.2,0,0.4,0,0.6v36c0,14.9,12.1,27,27,27h27v-27c0-5,4-9,9-9s9,4,9,9v27 h36v-27c0-5,4-9,9-9s9,4,9,9v27h27c14.9,0,27-12.1,27-27v-36c0-0.2,0-0.4,0-0.6c32.9-23.1,54-58.6,54-98.4	c0-22.2-6.6-43.1-18.1-61.2h-24.5c-40.6,0-75-27.1-86.2-64.1c-5-0.5-10.1-0.7-15.2-0.7C112.5,112,48,168.4,48,238z M138,292	c-19.9,0-36-16.1-36-36s16.1-36,36-36s36,16.1,36,36S157.9,292,138,292z M246,220c19.9,0,36,16.1,36,36s-16.1,36-36,36 s-36-16.1-36-36S226.1,220,246,220z"/></svg>
                                    @else
                                        <svg class="fill-current w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M373.1,134.6L253.4,15.3C243.5,5.5,230.2,0,216.3,0H59C26.5,0,0,26.5,0,59v394c0,32.5,26.5,59,59,59h266 c32.5,0,59-26.5,59-59V160.9C384,151.1,380.1,141.6,373.1,134.6z M354.9,151.8h-61.5c-35.8,0-65-29.2-65-65v-59 c2.7,1.3,5.1,3.1,7.3,5.2L354.9,151.8z M359,453c0,9-3.6,17.5-10,24c-6.5,6.5-15,10-24,10H59c-9,0-17.5-3.6-24-10 c-6.5-6.5-10-15-10-24V59c0-9,3.6-17.5,10-24c6.5-6.5,15-10,24-10h144.4v61.8c0,49.6,40.4,90,90,90H359V453z"/><g><path d="M159.9,391.1h111.3v26.3h-141V197.8h29.7V391.1z"/></g></svg>
                                    @endif
                                </div>
                            </div>
                        </div> 
                    @endforeach
                @endif
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalConfirmDeleteVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
            @if($deletePosible)
                <x-jet-danger-button class="ml-2" wire:click="delete" wire:loading.attr="disabled">
                    {{ __('Obriši lokaciju') }}
                </x-jet-danger-button>
            @endif
        </x-slot>
    </x-jet-dialog-modal>


    {{-- ADD TERMINAL MODAL ########################################################### --}}
    <x-jet-dialog-modal wire:model="modalAddTerminalVisible" id="adTer">
        <x-slot name="title">
          Dodaj / premesti terminal
        </x-slot>
        
        <x-slot name="content">
            @if($modalAddTerminalVisible)
                <div>Lokacija na koju se dodaju terminali:</div>
                    <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                        <div class="flex">
                            <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M168.3 499.2C116.1 435 0 279.4 0 192C0 85.96 85.96 0 192 0C298 0 384 85.96 384 192C384 279.4 267 435 215.7 499.2C203.4 514.5 180.6 514.5 168.3 499.2H168.3zM192 256C227.3 256 256 227.3 256 192C256 156.7 227.3 128 192 128C156.7 128 128 156.7 128 192C128 227.3 156.7 256 192 256z"/></svg></div>
                            <div>
                                <p class="font-bold">{{ $odabranaLokacija->l_naziv}}, {{ $odabranaLokacija->mesto }}</p>
                                <p class="text-sm">Region: {{ $odabranaLokacija->r_naziv }}</p>
                                <p class="text-sm">Tip: {{ $odabranaLokacija->lt_naziv }}</p> 
                            </div>
                    </div>
                </div> 
            @endif 

            {{-- RADIO BUTTONS  --}}
            <div  class="flex justify-between mb-6">
                <div class="form-check mx-4">
                    <input wire:model="addingType" class="form-check-input appearance-none rounded-full h-4 w-4 border border-gray-300 bg-white checked:bg-blue-600 checked:border-blue-600 focus:outline-none transition duration-200 mt-1 align-top bg-no-repeat bg-center bg-contain float-left mr-2 cursor-pointer" type="radio" name="flexRadioDefault" id="flexRadioDefault1" value="location">
                    <label class="form-check-label inline-block text-gray-800" for="flexRadioDefault1">
                        Premesti sa druge lokacije
                    </label>
                </div>
                <div class="form-check mx-4">
                    <input wire:model="addingType" class="form-check-input appearance-none rounded-full h-4 w-4 border border-gray-300 bg-white checked:bg-blue-600 checked:border-blue-600 focus:outline-none transition duration-200 mt-1 align-top bg-no-repeat bg-center bg-contain float-left mr-2 cursor-pointer" type="radio" name="flexRadioDefault" id="flexRadioDefault2"  value="addNew" >
                    <label class="form-check-label inline-block text-gray-800" for="flexRadioDefault2" value="new">
                        Dodaj novi terminal
                    </label>
                </div>
            </div>

                @if($addingType == 'location') 
                {{-- DODAVANJE SA LOKACIJE --}}
                    @if(!$p_lokacijaId)
                        <div class="mb-6"> 
                            <select wire:model="p_lokacija_tipId" id="" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                <option value="">Izaberi vrstu lokacije</option>
                                @foreach (App\Models\LokacijaTip::tipoviList() as $key => $value)    
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($p_lokacija_tipId)
                            <div>Izaberi lokaciju:</div>
                            @if($p_lokacija_tipId == 3)
                                {{-- Korsnik terminala search by name --}}
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
                                    @foreach ($this->lokacijeTipa($p_lokacija_tipId) as $value)
                                    <tr class="hover:bg-gray-100" wire:click="$set('p_lokacijaId', {{ $value->id }})" >    
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
                                    {{ $this->lokacijeTipa($p_lokacija_tipId)->links() }}
                                </div>
                            @else
                                {{-- MAGACIN ILI SEVIS --}}
                                <div class="mb-6">
                                    <select wire:model="p_lokacijaId" id="" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">Izaberi lokaciju</option>
                                        @foreach (App\Models\Lokacija::lokacijeTipa( $p_lokacija_tipId ) as $key => $value)    
                                            @if($key != $modelId) <option value="{{ $key }}">{{ $value }}</option> @endif
                                        @endforeach
                                    </select>
                                </div>

                            @endif
                        @endif
                    @else
                    {{-- IZABRAO Je LOKACIJU --}}
                        @if($this->terminaliZaLokaciju($p_lokacijaId)->count())
                            {{-- IZABRAO dobru LOKACIJU MENJAM PRIKAZ --}}
                            <div>Lokacija sa koje se uzimaju terminali:</div>
                            <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                                <div class="flex">
                                    <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M168.3 499.2C116.1 435 0 279.4 0 192C0 85.96 85.96 0 192 0C298 0 384 85.96 384 192C384 279.4 267 435 215.7 499.2C203.4 514.5 180.6 514.5 168.3 499.2H168.3zM192 256C227.3 256 256 227.3 256 192C256 156.7 227.3 128 192 128C156.7 128 128 156.7 128 192C128 227.3 156.7 256 192 256z"/></svg></div>
                                        <div>
                                            <p class="font-bold">{{ $lokacijaSaKojeUzima->l_naziv}}, {{ $lokacijaSaKojeUzima->mesto }}</p>
                                            <p class="text-sm">Region: {{ $lokacijaSaKojeUzima->r_naziv }}</p>
                                        </div>
                                </div>
                            </div> 
                            
                            <table class="min-w-full divide-y divide-gray-200 mb-6" style="width: 100% !important">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox" value="{{ $selectAllValue }}" wire:model="selectAll.{{ $this->modelId }}"  class="form-checkbox h-6 w-6 text-blue-500">
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Serijski broj</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Broj kutije</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Model</tr>
                            </thead>
                            <tr>
                                <td></td>
                                <td><x-jet-input wire:model="searchSN" id="" class="block bg-orange-50 w-full" type="text" placeholder="Seriski broj" /></td>
                                <td><x-jet-input wire:model="searchBK" id="" class="block bg-orange-50 w-full" type="text" placeholder="Broj kutije" /></td>
                                <td></td>
                            </tr>
                            <tbody>
                                @foreach($this->terminaliZaLokaciju($p_lokacijaId, $searchSN, $searchBK) as $item)
                                    <tr>
                                        <td><input type="checkbox" value="{{ $item->tid }}" wire:model="selsectedTerminals"  class="form-checkbox h-6 w-6 text-blue-500"></td>
                                        <td> {{ $item->sn }}</td>
                                        <td> {{ $item->broj_kutije }}</td>
                                        <td>{{ $item->model }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mb-6">
                            {{ $this->terminaliZaLokaciju($p_lokacijaId, $searchSN, $searchBK)->links() }} 
                        </div>
                        <div class="mb-6">
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

                        <div class="bg-blue-100 border-t border-b border-blue-500 text-blue-700 px-4 py-3" role="alert">
                            <p class="text-sm">Ukupno terminala: <span class="font-bold"> {{ count($selsectedTerminals) }}</span></p>
                        </div>
                        
                        @else
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <strong class="font-bold">Greška!</strong>
                                <span class="block sm:inline">Na izabranoj lokaciji nema terminala.</span>
                                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                                    <svg class="fill-current h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M506.3 417l-213.3-364c-16.33-28-57.54-28-73.98 0l-213.2 364C-10.59 444.9 9.849 480 42.74 480h426.6C502.1 480 522.6 445 506.3 417zM232 168c0-13.25 10.75-24 24-24S280 154.8 280 168v128c0 13.25-10.75 24-23.1 24S232 309.3 232 296V168zM256 416c-17.36 0-31.44-14.08-31.44-31.44c0-17.36 14.07-31.44 31.44-31.44s31.44 14.08 31.44 31.44C287.4 401.9 273.4 416 256 416z"/></svg>
                                </span>
                            </div>
                        @endif
                    @endif
                @else
                {{-- DODAVANJE NOVOG TERMINALA NA LOKACIJU --}}
                    <p class="text-xl font-bold uppercase text-red-600 flex" >
                        <svg class="fill-current w-4 h-4 mt-1 mr-2" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 512 512"><g><path d="M422.4,254.5c-4.1-27.1-27.3-47.1-55.5-47.1H180v-27.7h69.2c15.3,0,27.7-12.4,27.7-27.7V96.7c0-15.3-12.4-27.7-27.7-27.7 H54.6c-14.5,0-27.7,12.4-27.7,27.7v55.4c0,15.3,13.2,27.7,27.7,27.7h69.2v27.7H75.3c-27.4,0-50.6,20-54.7,47.1L0.9,384 c-0.6,4.1-0.9,8.2-0.9,12.4v60.2C0,487.2,24.8,512,55.4,512h332.3c30.5,0,55.4-24.8,55.4-55.4v-60.2c0-4.2-0.3-8.3-1-12.4 L422.4,254.5z M346.1,255.9c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C325.3,265.1,334.6,255.9,346.1,255.9z M304.6,325.1c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C283.8,334.4,293.1,325.1,304.6,325.1z M263,255.9c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C242.3,265.1,251.5,255.9,263,255.9z M221.5,325.1c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C200.7,334.4,210,325.1,221.5,325.1z M200.7,276.7c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8c0-11.5,9.3-20.8,20.8-20.8 S200.7,265.1,200.7,276.7z M83.1,138.2c-7.6,0-13.8-6.2-13.8-13.8c0-7.6,6.2-13.8,13.8-13.8h138.4c7.6,0,13.8,6.2,13.8,13.8 c0,7.6-6.2,13.8-13.8,13.8H83.1z M138.4,325.1c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C117.7,334.4,126.9,325.1,138.4,325.1z M96.9,255.9c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8 c-11.5,0-20.8-9.3-20.8-20.8C76.1,265.1,85.4,255.9,96.9,255.9z M373.8,456.6H69.2c-7.6,0-13.8-6.2-13.8-13.8 c0-7.6,6.2-13.8,13.8-13.8h304.6c7.6,0,13.8,6.2,13.8,13.8C387.6,450.4,381.4,456.6,373.8,456.6z"/><polygon points="437.3,75.2 437.3,0 386.8,0 386.8,75.2 311.6,75.2 311.6,125.7 386.8,125.7 386.8,200.9 437.3,200.9 437.3,125.7 512.5,125.7 512.5,75.2 "/></g></svg>
                        Novi terminal
                    </p>
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

                @endif
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
            <x-jet-secondary-button wire:click="$toggle('modalAddTerminalVisible')" wire:loading.attr="disabled">
                    {{ __('Otkaži') }}
            </x-jet-secondary-button>
            <x-jet-danger-button class="ml-2" wire:click="addTerminal" wire:loading.attr="disabled">
                    {{ __('Dodaj terminal') }}
            </x-jet-danger-button>
        </x-slot>
     </x-jet-dialog-modal>

     {{-- KONTAKT OSOBA MODAL ##########################################################--}}
     <x-jet-dialog-modal wire:model="kontaktOsobaVisible">
        <x-slot name="title">
        <svg class="float-left fill-gray-500 w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 448"><defs><style>.a{fill:#fff;}</style></defs><path d="M512,0H64A64,64,0,0,0,0,64V384a64,64,0,0,0,64,64H512a64,64,0,0,0,64-64V64A64,64,0,0,0,512,0Z"/><circle class="a" cx="186.65" cy="137.79" r="86.21"/><path class="a" d="M382.28,317.58h133a25,25,0,0,0,24.94-24.94V76.51a25,25,0,0,0-24.94-24.94h-133a24.94,24.94,0,0,0-24.93,24.94V292.64A24.94,24.94,0,0,0,382.28,317.58Zm83.13-24.94H431.69c-4.1,0-7.84-3.74-7.84-8.31a8.34,8.34,0,0,1,8.31-8.32h33.25c4.57,0,8.31,3.74,8.31,7.85A8.45,8.45,0,0,1,465.41,292.64ZM390.6,84.82H507V251.08H390.6Z"/><path class="a" d="M57.33,396.43H316a21.61,21.61,0,0,0,21.55-21.55A107.77,107.77,0,0,0,229.76,267.11H143.54A107.76,107.76,0,0,0,35.77,374.88,21.59,21.59,0,0,0,57.33,396.43Z"/></svg>
            {{ __('Kontakt osoba') }}
        </x-slot>

        <x-slot name="content">
            @if($kontaktOsobaVisible)
                <div>Lokacija:</div>
                    <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                        <div class="flex">
                            <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M168.3 499.2C116.1 435 0 279.4 0 192C0 85.96 85.96 0 192 0C298 0 384 85.96 384 192C384 279.4 267 435 215.7 499.2C203.4 514.5 180.6 514.5 168.3 499.2H168.3zM192 256C227.3 256 256 227.3 256 192C256 156.7 227.3 128 192 128C156.7 128 128 156.7 128 192C128 227.3 156.7 256 192 256z"/></svg></div>
                            <div>
                                <p class="font-bold">{{ $odabranaLokacija->l_naziv}}, {{ $odabranaLokacija->mesto }}</p>
                                <p class="text-sm">Region: {{ $odabranaLokacija->r_naziv }}</p>
                            </div>
                    </div>
                </div> 
                <div>Kontakt osoba:</div>
                    <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                        <div class="flex">
                            <div class="py-1">
                            <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 448"><defs><style>.a{fill:#fff;}</style></defs><path d="M512,0H64A64,64,0,0,0,0,64V384a64,64,0,0,0,64,64H512a64,64,0,0,0,64-64V64A64,64,0,0,0,512,0Z"/><circle class="a" cx="186.65" cy="137.79" r="86.21"/><path class="a" d="M382.28,317.58h133a25,25,0,0,0,24.94-24.94V76.51a25,25,0,0,0-24.94-24.94h-133a24.94,24.94,0,0,0-24.93,24.94V292.64A24.94,24.94,0,0,0,382.28,317.58Zm83.13-24.94H431.69c-4.1,0-7.84-3.74-7.84-8.31a8.34,8.34,0,0,1,8.31-8.32h33.25c4.57,0,8.31,3.74,8.31,7.85A8.45,8.45,0,0,1,465.41,292.64ZM390.6,84.82H507V251.08H390.6Z"/><path class="a" d="M57.33,396.43H316a21.61,21.61,0,0,0,21.55-21.55A107.77,107.77,0,0,0,229.76,267.11H143.54A107.76,107.76,0,0,0,35.77,374.88,21.59,21.59,0,0,0,57.33,396.43Z"/></svg>
                            </div>
                            <div>
                                <p class="font-bold">{{ $kontaktOsobaInfo->name }}</p>
                                <p class="text-sm"> {{ $kontaktOsobaInfo->tel }}</p>
                            </div>
                    </div>
                </div> 

            @endif 
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('kontaktOsobaVisible')" wire:loading.attr="disabled">
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
     <script>
        function copyToCliboard(adresa, mesto) {
            navigator.clipboard.writeText(adresa+ ", "+ mesto);
        } 
     </script>
</div>
