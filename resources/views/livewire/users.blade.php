<div class="p-6">
    <div class="flex items-center justify-end px-4 py-3 text-right sm:px-6">
        <x-jet-button wire:click="createShowModal" >
            <!--! CREATE NEW USER -->
            <x-heroicon-o-user-plus class="w-5 h-5 mr-0 ml-1"/>
        <span class="ml-4">Novi korisnik</span>    
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
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase">
                                    <livewire:komponente.sort-button 
                                        :btn_text="''" 
                                        :wire:key="'id'" 
                                        :orderBy="$orderBy" 
                                        :field="'id'" />
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    <livewire:komponente.sort-button 
                                        :btn_text="'Ime'" 
                                        :wire:key="'name'" 
                                        :orderBy="$orderBy" 
                                        :field="'name'" />
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    <livewire:komponente.sort-button 
                                        :btn_text="'Lokacija'" 
                                        :wire:key="'lokacija'" 
                                        :orderBy="$orderBy" 
                                        :field="'lokacija'" />
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">Radni odnos</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    <livewire:komponente.sort-button 
                                        :btn_text="'Pozicija'" 
                                        :wire:key="'pozicija'" 
                                        :orderBy="$orderBy" 
                                        :field="'pozicija'" />
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    <livewire:komponente.sort-button 
                                        :btn_text="'Status'" 
                                        :wire:key="'status'" 
                                        :orderBy="$orderBy" 
                                        :field="'status'" />
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">  
                            {{-- SEARCH ROW --}}
                            <tr class="bg-orange-50">
                                <td>
                                    <x-heroicon-o-funnel class="mx-auto text-orange-600 w-4 h-4" />
                                </td>
                                <td>
                                    <x-jet-input wire:model="searchName" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretrazi ime" />
                                </td>
                                <td>
                                    <x-jet-input wire:model="searchLokacija" id="" class="block bg-orange-50 w-full" type="text" placeholder="Lokacija" />
                                </td>
                                <td></td>
                                <td>
                                    <select wire:model="searchPozicija" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                            <option value="">---</option>
                                        @foreach ($this->pozizicijeKategorije() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select wire:model="searchRStatus" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">---</option>
                                        @foreach (App\Models\RadniStatusTip::userRadniStatusList() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td></td>
                            </tr>
                        
                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr>
                                        <td class="px-6 py-2">
                                            @if($item->naziv != 'Obrisan')
                                            <x-jet-secondary-button class="btn btn-blue" wire:click="updateShowModal({{ $item->id }})">
                                                <x-heroicon-o-pencil-square class="w-5 h-5 m-0 p-0" />
                                            </x-jet-button>
                                            @endif
                                        </td> 
                                        <td class="px-6 py-2">{{ $item->name }}</td>
                                        <td class="px-6 py-2">{{ $item->l_naziv }} - {{ $item->mesto }}</td> 
                                        <td class="px-6 py-2">{{ $item->ro_naziv }}</td>
                                        <td class="px-6 py-2">
                                            @if($item->naziv == 'Obrisan')
                                                <span class="text-red-600 font-bold"> {{ $item->naziv }}</span>
                                            @elseif($item->naziv == 'Distributer')
                                                <span class="text-sky-600"> {{ $item->naziv }}</span>
                                            @else
                                                {{ $item->naziv }}
                                            @endif
                                        </td>  
                                        
                                        <td class="px-6 py-2">
                                            @if($item->naziv == 'Obrisan')
                                                ---
                                            @elseif($item->naziv == 'Distributer')
                                                @if($item->id == 29)
                                                    <x-jet-secondary-button wire:click="promeniDistirbuteraShowModal({{ $item->id }})">
                                                        Dist
                                                    </x-jet-secondary-button>
                                                @else
                                                    ---
                                                @endif
                                            @else
                                                <x-jet-secondary-button wire:click="updateShowRadniStatusModal({{ $item->id }})">
                                                    {{ $item->rs_naziv }}
                                                </x-jet-secondary-button></td> 
                                            @endif
                                        <td class="px-6 py-2 flex justify-end">
                                            @if($item->naziv != 'Obrisan')
                                                <x-jet-danger-button class="ml-2" wire:click="deleteShowModal({{ $item->id }})">
                                                    <x-heroicon-o-trash class="w-5 h-5 mr-0" />
                                                </x-jet-danger-button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else 
                                <tr>
                                    <td class="px-6 py-4 text-sm whitespace-no-wrap" colspan="7">No Results Found</td>
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


    {{-- Promeni DISTRIDUTERA test useru ########################################################### --}}
    <x-jet-dialog-modal wire:model="promeniDitributeraModalVisible">
        <x-slot name="title">
        </x-slot>
        <x-slot name="content">
            <div class="mt-4">
                <x-jet-label for="testUserDistributer" value="Radni status" />
                <select wire:model="testUserDistributer" id="" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                    @foreach (App\Models\LicencaDistributerTip::testUserDistributerList() as $key => $value)    
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('testUserDistributer') <span class="error">{{ $message }}</span> @enderror
            </div>      
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('promeniDitributeraModalVisible')" wire:loading.attr="disabled">
                Otkaži
            </x-jet-secondary-button>

            <x-jet-danger-button class="ml-2" wire:click="promeniDistributera" wire:loading.attr="disabled">
                Promeni
            </x-jet-danger-button>    
        </x-slot>
    </x-jet-dialog-modal>


    {{-- ADD EDIT USER MODAL ########################################################################  --}}
    <x-jet-dialog-modal wire:model="modalFormVisible">
        <x-slot name="title">
           @if ($modelId) Izmeni podatke
           @else Novi korisnik @endif
        </x-slot>
        <x-slot name="content">
            <div class="mt-4">
                <x-jet-label for="name" value="Ime" />
                <x-jet-input wire:model="name" id="" class="block mt-1 w-full" type="text" />
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </div>  
            @if (!$modelId) 
                <div class="mt-4">
                    <x-jet-label for="email" value="E-mail" />
                    <x-jet-input wire:model="email" id="" class="block mt-1 w-full" type="text" />
                    @error('email') <span class="error">{{ $message }}</span> @enderror
                </div>  

                <div class="mt-4">
                    <x-jet-label for="password" value="Password" />
                    <x-jet-input wire:model="password" id="" class="block mt-1 w-full" type="text" />
                    @error('password') <span class="error">{{ $message }}</span> @enderror
                </div>  
            @else
                <br />
                <x-jet-label value="{{ $email }}" />
            @endif

            <div class="mt-4">
                <x-jet-label for="telegramId" value="Telegram ID" />
                <x-jet-input wire:model="telegramId" id="" class="block mt-1 w-full" type="text" />
                @error('telegramId') <span class="error">{{ $message }}</span> @enderror
            </div> 
            <div class="mt-4">
                <x-jet-label for="tel" value="Broj telefona" />
               <div class="mt-4 flex rounded-md shadow-sm">
               
                  <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-m">
                     +381
                  </span>
                  <input wire:model="tel" class="form-input flex-1 block w-full rounded-none rounded-r-md transition duration-150 ease-in-out sm:text-sm sm:leading-5" >
                  @error('tel') <span class="error">{{ $message }}</span>@enderror
               </div>
            </div> 
                 
            <div class="mt-4">
                @if($modelId && $pozicijaId == 8)
                    <label>Pozicija</label>
                    <div class="bg-gray-100 mb-2 border-b-4 border-black-200 p-1.5">
                        <p class="font-bold">Distributer</p>
                    </div>
                    <div class="flex items-center mt-4">
                        <label class="inline-flex items-center me-5 cursor-pointer">
                            <input type="checkbox" value="1" wire:model="vidi_komentare_na_terminalu" class="sr-only peer" >
                            <div class="relative w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-teal-300 dark:peer-focus:ring-teal-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-teal-600 dark:peer-checked:bg-teal-600"></div>
                        </label>
                        <span class="text-gray-700 ml-4">Vidi komentare na terminalu</span>

                    </div>
                @else
                    <x-jet-label for="pozicijaId" value="Pozicija" />
                    <select wire:model="pozicijaId" id="pozicija" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                            <option value="">-- Odaberi poziciju -- </option>
                        @foreach ($this->pozizicijeKategorije() as $key => $value)  {{-- (App\Models\PozicijaTip::userRoleList() as $key => $value) --}}    
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('pozicijaId') <span class="error">{{ $message }}</span> @enderror
                @endif
            </div>
            <br />
            
            <div class="mt-4">

            @if($pozicijaId == 8)
                @php
                    $radniOdnosId = 3;
                @endphp
            <label> Lokacija: </label>
                @if(!$lokacijaId)
                {{-- Novi korisnik je Ditributer  --}}
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
                    @foreach ($this->lokacijeTipa() as $value)
                    <tr class="hover:bg-gray-100" wire:click="$set('lokacijaId', {{ $value->id }})" >    
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
                    {{ $this->lokacijeTipa()->links() }}
                </div>
                @else
                    {{-- IZABRAO LOKACIJU MENJAM PRIKAZ --}}
                    <div class="bg-gray-100 mb-2 border-b-4 border-black-200 p-1.5">
                        <p class="font-bold">{{ $this->izabranaLokacija()->l_naziv}}, {{ $this->izabranaLokacija()->mesto }}</p>
                        <p class="text-sm">Region: {{ $this->izabranaLokacija()->r_naziv }}</p>
                        <p>&nbsp;</p>
                        <p>Distributer:</p>
                        <p class="font-bold">{{ $this->izabranaLokacija()->distributer_naziv}}</p>
                        <p class="text-sm">Mesto: {{ $this->izabranaLokacija()->distributer_mesto }}</p>
                    </div>
                    @if(!$this->izabranaLokacija()->distId)
                    <div class="bg-red-50 border border-red-500 text-red-500 px-4 py-3 rounded relative my-4 " role="alert">
                        <p class="">UPOZORENJE!<br />
                            <span class="font-bold block sm:inline">Korisnik neće biti sačuvan!</span>
                            <span><br />Lokacija koju ste izabrali nije povezana sa Distributerom! <br />Pre dodavanja korisnika potrebno je da Menadžer licenci poveže lokaciju sa Distributerom!  </span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                            <svg class="fill-red-500 h-6 w-6 " xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M506.3 417l-213.3-364c-16.33-28-57.54-28-73.98 0l-213.2 364C-10.59 444.9 9.849 480 42.74 480h426.6C502.1 480 522.6 445 506.3 417zM232 168c0-13.25 10.75-24 24-24S280 154.8 280 168v128c0 13.25-10.75 24-23.1 24S232 309.3 232 296V168zM256 416c-17.36 0-31.44-14.08-31.44-31.44c0-17.36 14.07-31.44 31.44-31.44s31.44 14.08 31.44 31.44C287.4 401.9 273.4 416 256 416z"/></svg>
                        </span>
                        </p>
                    </div>
                    @endif
                    <p>&nbsp;</p>

                @endif
            @elseif($selectedKatId == 4)
                {{-- Novi korisnik je iz Bankomat divizije  --}}
                <x-jet-label for="lokacijaId" value="Lokacija" />
                <select wire:model="lokacijaId" id="lokacija" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                        <option value="">-- Odaberi lokaciju -- </option>
                    @foreach (App\Models\Blokacija::lokacijeTipa() as $lokacija)    
                        <option value="{{ $lokacija->id }}">{{ $lokacija->bl_naziv }} - {{$lokacija->bl_mesto}}</option>
                    @endforeach
                </select>
                @error('lokacijaId') <span class="error">{{ $message }}</span> @enderror
            @else
                <x-jet-label for="lokacijaId" value="Lokacija" />
                <select wire:model="lokacijaId" id="lokacija" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                        <option value="">-- Odaberi lokaciju -- </option>
                    @foreach (App\Models\Lokacija::userLokacijeList() as $key => $value)    
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('lokacijaId') <span class="error">{{ $message }}</span> @enderror
            @endif
            </div>
            <br />
            
            @if($pozicijaId != 8)
            <div class="mt-4">
                <x-jet-label for="radniOdnosId" value="Radni odnos" />
                <select wire:model="radniOdnosId" id="radniodnos" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                        <option value="">-- Odaberi radni odnos --</option>
                    @foreach (App\Models\RadniOdnosTip::userRadniOdnosList() as $key => $value)    
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('radniOdnosId') <span class="error">{{ $message }}</span> @enderror
            </div> 
            @endif   
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalFormVisible')" wire:loading.attr="disabled">
                Otkaži
            </x-jet-secondary-button>

            @if ($modelId)
                <x-jet-danger-button class="ml-2" wire:click="update" wire:loading.attr="disabled">
                    Update
                </x-jet-danger-button>
            @else
                <x-jet-danger-button class="ml-2" wire:click="create" wire:loading.attr="disabled">
                    Sačuvaj
                </x-jet-danger-button>
            @endif            
        </x-slot>
    </x-jet-dialog-modal>

    {{-- Modal Form Radni Srtatus --}}
    <x-jet-dialog-modal wire:model="modalRadniStatusVisible">
        <x-slot name="title">
            <svg class="float-left fill-current w-4 h-4 mr-0 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Pro 6.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M224 256c70.7 0 128-57.31 128-128s-57.3-128-128-128C153.3 0 96 57.31 96 128S153.3 256 224 256zM274.7 304H173.3C77.61 304 0 381.6 0 477.3c0 19.14 15.52 34.67 34.66 34.67h378.7C432.5 512 448 496.5 448 477.3C448 381.6 370.4 304 274.7 304z"/></svg> 
            <span class="ml-4">{{ $name }}</span>
            <br />
        </x-slot>
        <x-slot name="content">          
            <div class="mt-4">
                <x-jet-label for="radniStatusId" value="Radni status" />
                <select wire:model="radniStatusId" id="" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                    @foreach (App\Models\RadniStatusTip::userRadniStatusList() as $key => $value)    
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('radniStatusId') <span class="error">{{ $message }}</span> @enderror
            </div>      
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalRadniStatusVisible')" wire:loading.attr="disabled">
                Otkaži
            </x-jet-secondary-button>
            <x-jet-button class="ml-2" wire:click="updateRadniStatus" wire:loading.attr="disabled">
                Sačuvaj
            </x-jet-danger-button>
        </x-slot>
    </x-jet-dialog-modal>

    {{-- The Delete Modal --}}
    <x-jet-dialog-modal wire:model="modalConfirmDeleteVisible">
        <x-slot name="title">
        <svg class="float-left fill-red-700 w-4 h-4 mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!-- Font Awesome Pro 5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) --><path d="M569.517 440.013C587.975 472.007 564.806 512 527.94 512H48.054c-36.937 0-59.999-40.055-41.577-71.987L246.423 23.985c18.467-32.009 64.72-31.951 83.154 0l239.94 416.028zM288 354c-25.405 0-46 20.595-46 46s20.595 46 46 46 46-20.595 46-46-20.595-46-46-46zm-43.673-165.346l7.418 136c.347 6.364 5.609 11.346 11.982 11.346h48.546c6.373 0 11.635-4.982 11.982-11.346l7.418-136c.375-6.874-5.098-12.654-11.982-12.654h-63.383c-6.884 0-12.356 5.78-11.981 12.654z"/></svg>
            Obriši korisnika - {{ $name }}
        </x-slot>

        <x-slot name="content">
            Da li ste sigurni da želite da obrišete ovog korisnika?
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalConfirmDeleteVisible')" wire:loading.attr="disabled">
                Otkaži
            </x-jet-secondary-button>

            <x-jet-danger-button class="ml-2" wire:click="delete" wire:loading.attr="disabled">
                Obriši korisnika
            </x-jet-danger-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>