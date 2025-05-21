<div class="p-6">
    <div class="flex items-center justify-end px-4 py-3 text-right sm:px-6"> 
        <a class="flex text-green-600 hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-2 py-1 text-center dark:border-gray-600 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-800" href="{{ route( 'managment-distributer-licence') }}" :active="request()->routeIs('managment-distributer-licence')" title="Statistika licenci"><span class="pt-0.5 mr-2">Sve licence</span>
            <svg class="w-6 h-6 text-current dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v15a1 1 0 0 0 1 1h15M8 16l2.5-5.5 3 3L17.273 7 20 9.667"/></svg>
        </a>
    </div>
{{-- The data table --}}
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">
                                    <livewire:komponente.sort-button 
                                        :btn_text="''" 
                                        :wire:key="'id'" 
                                        :orderBy="$orderBy" 
                                        :field="'id'" />
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">
                                    <livewire:komponente.sort-button 
                                        :btn_text="'Naziv'" 
                                        :wire:key="'dname'" 
                                        :orderBy="$orderBy" 
                                        :field="'distributer_naziv'" />
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">Adresa</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">Datum ugovora</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">Datum isteka</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">
                                    <livewire:komponente.sort-button 
                                        :btn_text="'Broj licenci'" 
                                        :wire:key="'lic'" 
                                        :orderBy="$orderBy" 
                                        :field="'broj_licenci_count'" />
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">
                                    <livewire:komponente.sort-button 
                                        :btn_text="'Broj terminala'" 
                                        :wire:key="'term'" 
                                        :orderBy="$orderBy" 
                                        :field="'broj_terminala'" />    
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">  
                        {{-- SEARCH ROW --}}
                            <tr class="bg-orange-50">
                                
                                <td colspan = "2">
                                    <x-jet-input wire:model="searchName" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži ime" />
                                </td>
                                <td>
                                    <x-jet-input wire:model="searchMesto" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži mesto" />
                                </td>
                                <td colspan="2">
                                    <x-jet-input wire:model="searchPib" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži PIB" />
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>

                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr>
                                        <td colspan="2" class="px-2 py-2">{{ $item->distributer_naziv }}</td>
                                        <td class="px-4 py-2">{{ $item->distributer_adresa }} <br /> {{ $item->distributer_zip }} {{ $item->distributer_mesto }}</td>                                         
                                        
                                        <td class="px-2 py-2">{{ App\Http\Helpers::datumFormatDan($item->datum_ugovora) }}</td>
                                        <td class="px-2 py-2">{{ App\Http\Helpers::datumFormatDan($item->datum_kraj_ugovora) }}</td>
                                        <td class="px-2 py-2">
                                            <div class="flex items-center">
                                            <span>{{ $item->broj_licenci_count }}</span>
                                            <svg class="fill-gray-500 w-5 h-5 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M373.1,134.6L253.4,15.3C243.5,5.5,230.2,0,216.3,0H59C26.5,0,0,26.5,0,59v394c0,32.5,26.5,59,59,59h266 c32.5,0,59-26.5,59-59V160.9C384,151.1,380.1,141.6,373.1,134.6z M354.9,151.8h-61.5c-35.8,0-65-29.2-65-65v-59 c2.7,1.3,5.1,3.1,7.3,5.2L354.9,151.8z M359,453c0,9-3.6,17.5-10,24c-6.5,6.5-15,10-24,10H59c-9,0-17.5-3.6-24-10 c-6.5-6.5-10-15-10-24V59c0-9,3.6-17.5,10-24c6.5-6.5,15-10,24-10h144.4v61.8c0,49.6,40.4,90,90,90H359V453z"/><g><path d="M159.9,391.1h111.3v26.3h-141V197.8h29.7V391.1z"/></g></svg>
                                        </td>
                                        
                                        <td class="pl-2 pr-1 py-2 text-right">
                                            <div class="flex items-center">
                                            <span>{{ $item->broj_terminala }}</span>
                                            <svg class="fill-gray-500 w-5 h-5 ml-2" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 512 512"><g><path d="M422.4,254.5c-4.1-27.1-27.3-47.1-55.5-47.1H180v-27.7h69.2c15.3,0,27.7-12.4,27.7-27.7V96.7c0-15.3-12.4-27.7-27.7-27.7 H54.6c-14.5,0-27.7,12.4-27.7,27.7v55.4c0,15.3,13.2,27.7,27.7,27.7h69.2v27.7H75.3c-27.4,0-50.6,20-54.7,47.1L0.9,384 c-0.6,4.1-0.9,8.2-0.9,12.4v60.2C0,487.2,24.8,512,55.4,512h332.3c30.5,0,55.4-24.8,55.4-55.4v-60.2c0-4.2-0.3-8.3-1-12.4 L422.4,254.5z M346.1,255.9c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C325.3,265.1,334.6,255.9,346.1,255.9z M304.6,325.1c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C283.8,334.4,293.1,325.1,304.6,325.1z M263,255.9c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C242.3,265.1,251.5,255.9,263,255.9z M221.5,325.1c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C200.7,334.4,210,325.1,221.5,325.1z M200.7,276.7c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8c0-11.5,9.3-20.8,20.8-20.8 S200.7,265.1,200.7,276.7z M83.1,138.2c-7.6,0-13.8-6.2-13.8-13.8c0-7.6,6.2-13.8,13.8-13.8h138.4c7.6,0,13.8,6.2,13.8,13.8 c0,7.6-6.2,13.8-13.8,13.8H83.1z M138.4,325.1c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8s-20.8-9.3-20.8-20.8 C117.7,334.4,126.9,325.1,138.4,325.1z M96.9,255.9c11.5,0,20.8,9.3,20.8,20.8c0,11.5-9.3,20.8-20.8,20.8 c-11.5,0-20.8-9.3-20.8-20.8C76.1,265.1,85.4,255.9,96.9,255.9z M373.8,456.6H69.2c-7.6,0-13.8-6.2-13.8-13.8 c0-7.6,6.2-13.8,13.8-13.8h304.6c7.6,0,13.8,6.2,13.8,13.8C387.6,450.4,381.4,456.6,373.8,456.6z"/><polygon points="437.3,75.2 437.3,0 386.8,0 386.8,75.2 311.6,75.2 311.6,125.7 386.8,125.7 386.8,200.9 437.3,200.9 437.3,125.7 512.5,125.7 512.5,75.2 "/></g></svg>
                                        </td>
                                        
                                        <td class="">
                                             <a class="flex text-green-600 hover:text-white border border-gray-600 hover:bg-gray-600 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-2 py-1 mr-2 text-center dark:border-gray-600 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-800" href="{{ route( 'managment-distributer-licence', ['id' => $item->id] ) }}" :active="request()->routeIs('managment-distributer-licence', ['id' => $item->id])" title="Statistika licenci">
                                                <svg class="w-6 h-6 text-curent dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v15a1 1 0 0 0 1 1h15M8 16l2.5-5.5 3 3L17.273 7 20 9.667"/></svg>
                                            </a>
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
</div>
