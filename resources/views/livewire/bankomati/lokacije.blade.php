<div class="p-6">
    {{-- The data table --}}
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">
                                       <livewire:komponente.sort-button 
                                        :btn_text="''" 
                                        :wire:key="'id'" 
                                        :orderBy="$orderBy" 
                                        :field="'id'" />
                                </th>
                                <th colspan="2" class="px-6 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500 uppercase">
                                        <livewire:komponente.sort-button 
                                        :btn_text="'Naziv'" 
                                        :wire:key="'naziv'" 
                                        :orderBy="$orderBy" 
                                        :field="'blokacijas.bl_naziv'" />
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">
                                    <livewire:komponente.sort-button 
                                        :btn_text="'Mesto'" 
                                        :wire:key="'adresa'" 
                                        :orderBy="$orderBy" 
                                        :field="'blokacijas.bl_mesto'" />
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">
                                    <livewire:komponente.sort-button 
                                        :btn_text="'Region'" 
                                        :wire:key="'region'" 
                                        :orderBy="$orderBy" 
                                        :field="'bankomat_regions.r_naziv'" />
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500 tracking-wider">Tip lokacije</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500"></th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Mapa</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Pin</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Info</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">  
                            {{-- SEARCH ROW --}}
                            <tr class="bg-orange-50">
                                <td> <x-heroicon-o-funnel class="mx-auto text-orange-600 w-4 h-4" /> </td>
                                <td colspan="2">
                                    <x-jet-input wire:model="searchName" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži ime" />
                                </td>
                                <td>
                                    <x-jet-input wire:model="searchMesto" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži mesto" />
                                </td>
                                <td>
                                    <select wire:model="searchRegion" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                                <option value="">---</option>
                                            @foreach (App\Models\BankomatRegion::getAll() as $key => $value)    
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                    </select>
                                </td>

                                <td>
                                    <div class="flex">
                                        <button id="states-button" data-dropdown-toggle="dropdown-states" class="shrink-0 z-10 inline-flex items-center py-2.5 px-4 text-sm font-medium text-center text-gray-500 border border-gray-300 rounded-s-lg hover:bg-white focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700 dark:text-white dark:border-gray-600" type="button">
                                            @if($searchTip)
                                                @switch($searchTip)
                                                    @case(1)
                                                        <x-heroicon-o-wrench-screwdriver class="text-red-600 w-4 h-4 mr-2" />
                                                    @break
                                                    @case(2)
                                                        <x-heroicon-o-building-library class="text-gray-600 w-4 h-4 mr-2"/>
                                                    @break
                                                    @case(3)
                                                        <x-heroicon-o-building-storefront class="text-sky-600 w-4 h-4 mr-2"/>
                                                    @break
                                                @endswitch   
                                                <span class="mr-2">{{ App\Models\BlokacijaTip::where('id', $searchTip)->first()->bl_tip_naziv }}</span>
                                            @else
                                               <span class="mx-2"> --- &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                            @endif
                                             <x-icon-o-down class="w-2.5 h-2.5 ml-2" />
                                        </button>
                                    <div id="dropdown-states" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700">
                                        <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="states-button">
                                             <li>
                                                <button wire:click="setFilterTip('')" type="button" class="inline-flex w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white">
                                                    <div class="inline-flex items-center">
                                                        --- 
                                                </button>
                                            </li>
                                            @foreach (App\Models\BlokacijaTip::getAll() as $key => $value)    
                                                <li>
                                                    <button wire:click="setFilterTip({{ $key }})" type="button" class="inline-flex w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white">
                                                        <div class="inline-flex items-center">
                                                            @switch($key)
                                                                @case(1)
                                                                    <x-heroicon-o-wrench-screwdriver class="text-red-600 w-4 h-4 mr-2" />
                                                                @break
                                                                @case(2)
                                                                    <x-heroicon-o-building-library class="text-gray-600 w-4 h-4 mr-2"/>
                                                                @break
                                                                @case(3)
                                                                    <x-heroicon-o-building-storefront class="text-sky-600 w-4 h-4 mr-2"/>
                                                                @break
                                                            @endswitch          
                                                            {{ $value }}
                                                        </div>
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>

                               {{--  <td>
                                    <select wire:model="searchTip" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">---</option>
                                        @foreach (App\Models\BlokacijaTip::getAll() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td> --}}
                                <td></td>
                                <td colspan="2">
                                    <x-jet-input wire:model="searchPib" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži PIB" />
                                </td>
                                <td class="text-right text-sm pr-4">Ukupno: <span class="font-bold">{{ $data->total() }}</span></td>
                            </tr>

                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr>
                                        <td class="px-2 py-1">
                                            <button class="mt-2 text-sm text-gray-700 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="editLocation('{{ $item->id }}')">
                                                <x-heroicon-o-pencil-square class="w-5 h-5 mx-2 my-1" />
                                            </button>
                                        </td> 
                                        <td>
                                            @switch($item->tipid)
                                                @case(1)
                                                    <x-heroicon-o-wrench-screwdriver class="text-red-400 w-4 h-4" />
                                                @break
                                                @case(2)
                                                    <x-heroicon-o-building-library class="text-gray-400 w-4 h-4"/>
                                                @break
                                                @case(3)
                                                    <x-heroicon-o-building-storefront class="text-sky-400 w-4 h-4"/>
                                                @break
                                            @endswitch
                                        </td>

                                        <td class="px-1 py-2">
                                            @if($item->is_duplicate)<span class="text-red-500">*</span>@endif
                                            {{ $item->bl_naziv }}&nbsp;{{ $item->bl_naziv_sufix }}
                                        </td>
                                        <td class="px-1 py-2">{{ $item->bl_adresa }}, {{ $item->bl_mesto }}</td>
                                        <td class="px-4 py-2">{{ $item->r_naziv }}</td> 
                                        <td class="px-4 py-2">{{ $item->lt_naziv }}</td> 
                                        <td class="px-2 pt-1">
                                            @if($item->ime)
                                                <button class="mt-1 -mb-1 text-sm text-gray-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white"  wire:click="showKontaktOsobaModal({{ $item->id }})" title="Kontakt">
                                                    <x-heroicon-o-user-card class="w-8 h-8 -m-1 -mb-3" />
                                                </button>
                                            @else
                                                <button class="mt-1 -mb-1 text-sm text-gray-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white"  wire:click="showKontaktOsobaModal({{ $item->id }})" title="Dodaj kontakt">
                                                    <x-heroicon-o-user-plus class="w-4 h-4 mx-2" />
                                                </button>
                                            @endif
                                        </td>

                                        <td class="px-2 pt-2 flex">
                                                @if($item->latitude != '' && $item->longitude != '') 
                                                    <a class="my-auto text-sky-800 border rounded-md border-sky-800 pt-2 hover:bg-sky-800 hover:text-white" href="{{ App\Ivan\HelperFunctions::createGmapLink($item->latitude, $item->longitude) }}" target="_blank">
                                                    <x-heroicon-o-map class="w-6 h-6 mx-2 mb-1 -mt-1"/>
                                                   </a> 
                                                @endif 
                                        </td>  
                                        <td>
                                            <button class="text-sky-800  border rounded-md border-sky-800 pt-2 mr-2 hover:bg-sky-800 hover:text-white" onclick="copyToCliboard('{{$item->bl_adresa}}', '{{$item->bl_mesto}}')" wire:click="showLatLogModal({{ $item->id }})" title="Dodaj koordinate" >
                                                        <x-heroicon-o-pin-plus class="w-6 h-6 mx-2 mb-1 -mt-1" />
                                            </button>
                                        </td>                                     
                                        <td>
                                            <button class="text-red-800  border rounded-md border-red-800 px-1 py-1 hover:bg-red-800 hover:text-white" wire:click="infoShowModal({{ $item->id }})" title="Info">
                                                <x-heroicon-o-information-circle class="w-6 h-6 mx-1"/>
                                            <button>
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


    {{-- NOVA / IZMENI LOKACIJU MODAL ############################################### --}}
    <x-jet-dialog-modal wire:model="modalNewEditVisible">
        <x-slot name="title">
            <div class="flex justify-between">
                <div class="flex">
                    <x-heroicon-o-building-office class="w-6 h-6 mr-2"/>
                    @if ($is_edit) Izmeni podatke - {{ $bl_naziv }}
                    @else 
                         Nova @if ($is_sublocation) podlokacija @else  lokacija @endif 
                    @endif
                </div>
                <div>
                    @if($is_edit && $blokacija_tip_id == 3 && !$is_duplicate)
                        <x-jet-secondary-button wire:click="dodajPodlokaciju" wire:loading.attr="disabled">
                            <x-heroicon-o-plus-circle class="w-4 h-4 mr-2"/>
                            Dodaj podlokaciju
                        </x-jet-secondary-button>
                    @endif
                </div>

            </div>
        </x-slot>

        <x-slot name="content">
            @if($pib_count > 1 && !$is_duplicate)
                <div class="bg-yellow-50 border border-yellow-500 text-yellow-700 px-4 py-3 rounded relative my-4 " role="alert">
                    <p class="">Pažnja!<br />
                    <span class="font-bold block sm:inline">
                        Postoji više lokacija sa istim PIB-om. Ukoliko promenite Naziv ili PIB oni će biti promenjeni na svim lokacijama.
                    </span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <svg class="text-yellow-500 h-6 w-6 " xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                    </span>
                    </p>
                </div>
            @endif
            @if ($is_edit && $is_duplicate || $is_sublocation)
                <x-jet-label for="l_naziv_sufix" value="{{ __('Sufix naziva') }}" />
                <div class="mt-4 flex rounded-md shadow-sm mb-4">
                    
                    <span class="inline-flex items-center py-2 px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-100 text-gray-500 text-m">
                        {{ $bl_naziv }}
                    </span>
                    <input wire:model="bl_naziv_sufix" class="form-input flex-1 block w-full pl-2 border border-l-0 border-gray-300 rounded-none rounded-r-md transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
                    @error('bl_naziv_sufix') <span class="error">{{ $message }}</span>@enderror
                </div> 
            @else 
                <div class="mt-4">
                    <x-jet-label for="bl_naziv" value="Naziv lokacije" />
                    <x-jet-input wire:model="bl_naziv" id="" class="block mt-1 w-full" type="text" />
                    @error('bl_naziv') <span class="error">{{ $message }}</span> @enderror
                </div>
            @endif

            <div class="mt-4">
                <x-jet-label for="blokacija_tip_id" value="Vrsta lokacije" />
                    @if($is_edit || $is_sublocation)
                        <p class="ml-4"><strong>{{ $blokacija_tip }}</strong></p>
                    @else
                        <select wire:model="blokacija_tip_id" id="" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                            <option value="">Izaberi vrstu lokacije</option>
                            @foreach (App\Models\BlokacijaTip::getAll() as $key => $value)    
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                        @error('blokacija_tip_id') <span class="error">{{ $message }}</span> @enderror
                    @endif
            </div>  
            <div class="mt-4">
                <x-jet-label for="bl_mesto" value="Mesto" />
                <x-jet-input wire:model="bl_mesto" id="" class="block mt-1 w-full" type="text" />
                @error('bl_mesto') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <x-jet-label for="bl_adresa" value="Adresa" />
                <x-jet-input wire:model="bl_adresa" id="" class="block mt-1 w-full" type="text" />
                @error('bl_adresa') <span class="error">{{ $message }}</span> @enderror
            </div>
            
            <div class="mt-4">
                <x-jet-label for="pib" value="PIB" />
                @if($is_edit && $is_duplicate || $is_sublocation)
                    <p class="ml-4"><strong>{{ $pib }}</strong></p>
                @else
                    <x-jet-input wire:model="pib" id="" class="block mt-1 w-full" type="text" />
                    @error('pib') <span class="error">{{ $message }}</span> @enderror
                @endif
            </div>
        
            <div class="mt-4">
                <x-jet-label for="mb" value="Matični broj" />
                <x-jet-input wire:model="mb" id="" class="block mt-1 w-full" type="text" />
                @error('mb') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <x-jet-label for="email" value="e-mail" />
                <x-jet-input wire:model="email" id="" class="block mt-1 w-full" type="text" />
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <x-jet-label for="latitude" value="Latitude" />
                <x-jet-input wire:model="latitude" id="" class="block mt-1 w-full" type="text" />
                @error('latitude') <span class="error">{{ $message }}</span> @enderror
            </div>  
            <div class="mt-4">
                <x-jet-label for="longitude" value="Longitude" />
                <x-jet-input wire:model="longitude" id="" class="block mt-1 w-full" type="text" />
                @error('longitude') <span class="error">{{ $message }}</span> @enderror
            </div>      
            <div class="mt-4">
                <x-jet-label for="bankomat_region_id" value="Region" />
                <select wire:model="bankomat_region_id" id="" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                  <option value="">Odaberi region</option>
                    @foreach (App\Models\BankomatRegion::getAll() as $key => $value)    
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('bankomat_region_id') <span class="error">{{ $message }}</span> @enderror
            </div> 
            {{-- Kontakt osoba --}}
                <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mt-6 mb-6">
                    <hr />
                    <p class="flex">
                        <x-heroicon-o-user-card class="mt-1 w-8 h-8" />
                        Kontakt osoba:
                    </p>
                    <div class="mt-4">
                        <x-jet-label for="kontakt_name" value="Ime" />
                        <x-jet-input wire:model="kontakt_name" id="" class="block mt-1 w-full" type="text" />
                        @error('kontakt_name') <span class="error">{{ $message }}</span> @enderror
                    </div> 
                    <div class="mt-4">
                        <x-jet-label for="kontakt_tel" value="Broj telefona" />
                        <div class="mt-4 flex rounded-md shadow-sm mb-4">
                            <x-jet-input wire:model="kontakt_tel" id="" class="block mt-1 w-full" type="text" />
							{{-- <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-m">
								+381
							</span>
							<input wire:model="kontakt_tel" class="form-input flex-1 block w-full rounded-none rounded-r-md transition duration-150 ease-in-out sm:text-sm sm:leading-5" /> --}}
							@error('kontakt_tel') <span class="error">{{ $message }}</span>@enderror
						</div> 
					</div>
                    <div class="mt-4">
                        <x-jet-label for="kontakt_email" value="e-mail" />
                        <x-jet-input wire:model="kontakt_email" id="" class="block mt-1 w-full" type="text" />
                        @error('kontakt_email') <span class="error">{{ $message }}</span> @enderror
                    </div>
				</div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalNewEditVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>

            @if ($is_edit)
                <x-jet-button class="ml-2" wire:click="updateLocation" wire:loading.attr="disabled">
                    {{ __('Update') }}
                </x-jet-danger-button>
            @else
                <x-jet-button class="ml-2" wire:click="saveNewLocation" wire:loading.attr="disabled">
                    {{ __('Sačuvaj') }}
                </x-jet-danger-button>
            @endif            
        </x-slot>
    </x-jet-dialog-modal>

    {{-- KONTAKT OSOBA MODAL ##########################################################--}}
     <x-jet-dialog-modal wire:model="kontaktOsobaVisible">
        <x-slot name="title">
            <div class="flex">
            <x-heroicon-o-user-card class="text-gray-600 w-8 h-8 mt-1 mr-2" />
            Kontakt osoba
            </div>
        </x-slot>

        <x-slot name="content">
            @if($kontaktOsobaVisible)
                
                    <livewire:komponente.bankomat-lokacija-info :b_lokacija_id="$modelId" />
                    <livewire:bankomati.komponente.kontakt-osobe :b_lokacija_id="$modelId" />        
            @endif 
        </x-slot>
        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('kontaktOsobaVisible')" wire:loading.attr="disabled">
                    Otkaži
            </x-jet-secondary-button>
        </x-slot>
     </x-jet-dialog-modal>


        {{-- The Delete or INFO Modal ############################################################# --}}
    <x-jet-dialog-modal wire:model="modalLokacijaInfoVisible">
        <x-slot name="title">
            <div class="flex">
                <x-heroicon-o-information-circle class="w-6 h-6 mx-1"/>
                Info
            </div>
        </x-slot>

        <x-slot name="content">
            @if($modalLokacijaInfoVisible)
                <livewire:komponente.bankomat-lokacija-info :b_lokacija_id="$modelId" />
            @endif
            @if($deletePosible)
                {{ __('Da li ste sigurni da želite da obrišete lokaciju?') }}
            @else 
             @if($modalLokacijaInfoVisible)
                <p>Lokacija je povezana sa:</p>
                @foreach ($lokacijaSadrzi as $key => $value) 
                    @switch($key)
                        @case('korisnici')
                            @if(count($value))
                                <p>Korisnicima:</p>
                            @endif
                        @break
                        @case('bankomati')
                            <p>Ukupno bankomata:</p>
                        @break
                    @endswitch
                
                    @foreach($value as $name)
                         <span class="ml-6 font-bold">{{ $name }} </span><br/>
                    @endforeach
                @endforeach
             @endif
            @endif
            @if($modalLokacijaInfoVisible)
                @if($odabranaLokacija->blokacija_tip_id == 3)
                    @if(!$deletePosible)
                        <livewire:komponente.bankomat-info :bankomat_lokacija_id="0" :multySelectedArray="$lokacijaTerminals" :multySelected="true" />
                    @endif
                @endif
            @endif 
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalLokacijaInfoVisible')" wire:loading.attr="disabled">
                Otkaži
            </x-jet-secondary-button>
            @if($deletePosible)
                <x-jet-danger-button class="ml-2" wire:click="deleteLocation" wire:loading.attr="disabled">
                    Obriši lokaciju
                </x-jet-danger-button>
            @endif
        </x-slot>
    </x-jet-dialog-modal>

     {{-- LAT LOG MODAL ##########################################################--}}
     <x-jet-dialog-modal wire:model="latLogVisible">
        <x-slot name="title">
            <div class="flex">
            <x-heroicon-o-pin-plus class="w-6 h-6 mr-2" />
           Koordinate</div>
        </x-slot>

        <x-slot name="content">
            @if($latLogVisible)
                <livewire:komponente.bankomat-lokacija-info :b_lokacija_id="$modelId" />
                <div class="mt-4">
                    <div>Koordinate:</div>
                    <div class="flex">
                        <div class="flex-1">
                            <x-jet-label for="latLogValue" value="{{ __('Lat, Long') }}" />
                            <x-jet-input wire:model="latLogValue" id="" class="block mt-1 w-full" type="text" />
                                @error('latLogValue') <span class="error">{{ $message }}</span> @enderror
                                @error('lat_value') <span class="error">{{ $message }}</span> @enderror
                                @error('long_value') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex-2 ml-4 mt-4 pt-2">
                            <x-jet-danger-button class="ml-2" wire:click="removeLatLog" wire:loading.attr="disabled">
                                <x-heroicon-o-trash class="w-6 h-6" />
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
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

</div>
