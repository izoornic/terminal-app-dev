<div class="p-6">
    {{ session('status') }}
    <livewire:komponente.session-flash-message />
    {{-- The data table --}}
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500"></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Tip</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Serijski broj</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Model</th>
                                 <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500"></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Lokacija</th>
                                {{-- <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Adresa</th> --}}
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Region</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Status</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500"></th>
                                <th colspan="2" class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Ukupno: {{ $data->total() }}</th>
                                  
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"> 
                        {{-- SEARCH ROW --}}
                            <tr class="bg-orange-50">
                                <td> <x-heroicon-o-funnel class="mx-auto text-orange-600 w-4 h-4" /> </td>
                                <td>
                                    <select wire:model="searchProductTip" id="" class="block appearance-none bg-orange-50 w-full border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">---</option>
                                        @foreach (App\Models\BankomatProductTip::getAll() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><x-jet-input wire:model="searchSB" id="" class="block bg-orange-50 w-full" type="text" placeholder="Serijski broj" /></td>
                                {{-- <td><x-jet-input wire:model="searchTerminalId" id="" class="block bg-orange-50 w-full" type="text" placeholder="Terminal ID" /></td> --}}
                                <td><x-jet-input wire:model="searchModel" id="" class="block bg-orange-50 w-full" type="text" placeholder="Model" /></td>
                                <td></td>
                                <td><x-jet-input wire:model="searchLokacijaNaziv" id="" class="block bg-orange-50 w-full" type="text" placeholder="Naziv lokacije" /></td>
                                <td>
                                    <select wire:model="searchRegion" id="" class="block appearance-none bg-orange-50 w-full border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                                <option value="">---</option>
                                            @foreach (App\Models\BankomatRegion::getAll() as $key => $value)    
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select wire:model="searchStatus" id="" class="block appearance-none bg-orange-50 w-full border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">---</option>
                                        @foreach (App\Models\BankomatStatusTip::getAll() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select wire:model="searchLocationTip" id="" class="block appearance-none bg-orange-50 w-full border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">---</option>
                                        @foreach (App\Models\BlokacijaTip::getAll() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                
                                <td colspan="2">
                                    <x-jet-input wire:model="searchPib" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži PIB" />
                                </td>
                            </tr>  
                            
                            <!-- DATA  -->                   
                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr @if($loop->even) class="bg-gray-50" @endif >

                                        <td class="px-2 py-1">
                                            <button class="mt-2 text-sm text-gray-700 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="editBankomat('{{ $item->bid }}')">
                                                <x-heroicon-o-pencil-square class="w-5 h-5 mx-2 my-1" />
                                            </button>
                                        </td> 
                                        <td class="px-1 py-2">{{ $item->bp_tip_naziv }}</td>
                                        <td class="px-1 py-2">{{ $item->b_sn }}</td>
                                        
                                        <td class="px-1 py-2">{{ $item->model }}</td>
                                        <td class="pl-1">
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
                                        {{-- <td class="px-1 py-2">{{ $item->bl_adresa }}, {{ $item->bl_mesto  }}</td> --}} 
                                        <td class="px-1 py-2">{{ $item->r_naziv}}</td>
                                        {{-- <td class="px-1 py-2">{{ $item->bl_tip_naziv}}</td> --}}
                                        <td class="px-1 py-2">
                                            <button class="flex text-sm text-gray-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white" wire:click="statusShowModal({{ $item->blid}}, {{ $item->statusid }})">
                                                <x-heroicon-c-arrow-path-rounded-square class="w-4 h-4 mr-2"/>
                                                {{ $item->status_naziv }}
                                            </button>
                                        </td>
                                        <td class="px-1 py-1">
                                            <button class="flex text-sm text-gray-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white" wire:click="premestiShowModal({{ $item->blid }}, {{ $item->statusid }})">
                                                <x-heroicon-o-arrows-right-left class="w-4 h-4 mr-2" />
                                                Premesti
                                            </button>
                                        </td>
                                        <td class="px-1 py-1">
                                             <button class="text-sm text-gray-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white" wire:click="proizvodlHistoryShowModal({{ $item->blid }})" title="Istorija terminala">
                                                <x-icon-history class="fill-current w-4 h-4 mr-0"/>
                                            </button>
                                        </td>
                                        <td class="px-1 py-1">
                                             <button class="text-sm text-gray-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white" wire:click="newTiketShowModal({{ $item->blid }})" title="Novi tiket">
                                                <x-icon-ticket-plus class="fill-current w-4 h-4 mr-0"/>
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

    {{-- NOVI  BANKOMAT ############################################### --}}
    <x-jet-dialog-modal wire:model="modalNewVisible">
        <x-slot name="title">
            <div class="flex justify-between">
                <div class="flex">
                    <x-heroicon-o-tag class="w-6 h-6 mr-2"/>
                    Novi bankomat
                </div>

            </div>
        </x-slot>

        <x-slot name="content">

                @if(!$proizvod_model_tip)
                    <div class="mt-4">
                        <x-jet-label for="proizvod_model_tip" value="Tip proizvoda" />
                        <select wire:model="proizvod_model_tip" id="" class="block appearance-none w-full border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                            <option value="">---</option>
                            @foreach (App\Models\BankomatProductTip::getAll() as $key => $value)    
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                        @error('proizvod_model_tip') <span class="error">{{ $message }}</span> @enderror
                    </div>
                @else
                    <div class="mt-4">
                        <x-jet-label for="proizvod_model_tip" value="Tip proizvoda" />
                        <p class="font-bold">{{ App\Models\BankomatProductTip::getName([$proizvod_model_tip]) }}</p>
                    </div>
                    <div class="mt-4">
                        <x-jet-label for="proizvod_model" value="Model novog proizvoda" />   
                        <select wire:model="proizvod_model" id="" class="block appearance-none w-full bg-gray-100 border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                            <option value="">---</option>
                            @foreach (App\Models\BankomatTip::getAllFromCategory($proizvod_model_tip) as $key => $value)    
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                        @error('proizvod_model') <span class="error">{{ $message }}</span> @enderror
                    </div>  
                @endif

            <div class="mt-4">
                <x-jet-label for="b_sn" value="Serijski broj" />
                <x-jet-input wire:model.defer="b_sn" id="" class="block mt-1 w-full" type="text" />
                @error('b_sn') <span class="error">{{ $message }}</span> @enderror
            </div>

            @if($proizvod_model_tip == 1)
                <div class="mt-4">
                    <x-jet-label for="bankomat_tid" value="Terminal ID" />
                    <x-jet-input wire:model.defer="bankomat_tid" id="" class="block mt-1 w-full" type="text" />
                    @error('bankomat_tid') <span class="error">{{ $message }}</span> @enderror
                </div>
            @endif
                
                <div class="mt-4">
                    <x-jet-label for="bankomat_status" value="Status proizvoda" />   
                    <select wire:model.defer="bankomat_status" id="" class="block appearance-none w-full bg-gray-100 border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                        <option value="">---</option>
                        @foreach (App\Models\BankomatStatusTip::getAll() as $key => $value)    
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('bankomat_status') <span class="error">{{ $message }}</span> @enderror
                </div>
            @if($modalNewVisible)
                <div class="mt-4">
                    <p class="font-bold">Lokacija proizvoda:</p>
                    @if(!$bankomat_lokacija)
                        <livewire:bankomati.komponente.izbor-lokacije :key="$flashKey" comp_index="lokacija" />
                    @else
                        {{-- Izabrao je lokaciju menjam prikaz --}}
                        <livewire:bankomati.komponente.bankomat-lokacija-info :b_lokacija_id="$bankomat_lokacija" />  
                    @endif
                    @error('bankomat_lokacija') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="mt-4">
                    <p class="font-bold">Vlasnik proizvoda:</p>
                    @if(!$vlasnik_proizvoda)
                        <livewire:bankomati.komponente.izbor-lokacije :key="$location_key" comp_index="vlasnik" />
                    @else
                        {{-- Izabrao je lokaciju menjam prikaz --}}
                        <livewire:bankomati.komponente.bankomat-lokacija-info :b_lokacija_id="$vlasnik_proizvoda" />
                    @endif
                    @error('vlasnik_proizvoda') <span class="error">{{ $message }}</span> @enderror
                </div>
                
            @endif
                <div class="mt-4">
                    <x-jet-label for="datum_promene" value="Datum dodavanja proizvoda" />
                    <div class="flex">
                        <x-jet-input id="datum_promene" type="date" class="mt-1 block" value="{{ $datum_promene }}" wire:model="datum_promene" /> <span class="p-2 mt-2">{{ App\Http\Helpers::datumFormatDanFullYear($datum_promene) }}</span>
                    </div>
                    @error('datum_promene') <span class="error">{{ $message }}</span> @enderror
                    {{-- <p class="p-2">{{ App\Http\Helpers::datumFormatDanFullYear($datum_promene) }}</p> --}}
                    @if($datum_promene_error != '')
                        <p class="text-red-500"> {{$datum_promene_error}} </p>
                    @endif
                </div>
            
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalNewVisible')" wire:loading.attr="disabled">
               Otkaži
            </x-jet-secondary-button>

           {{--  @if ($is_edit)
                <x-jet-button class="ml-2" wire:click="updateBankomat" wire:loading.attr="disabled">
                   Update
                </x-jet-danger-button>
            @else --}}
                <x-jet-button class="ml-2" wire:click="saveBankomat" wire:loading.attr="disabled">
                   Sačuvaj
                </x-jet-danger-button>
           {{--  @endif  --}}           
        </x-slot>
    </x-jet-dialog-modal>

    {{-- STATUS MODAL ############################################################### --}}
    <x-jet-dialog-modal wire:model="modalStatusFormVisible">
        <x-slot name="title">
            <div class="flex justify-between">
                <div class="flex">
                    <x-heroicon-c-arrow-path-rounded-square class="w-6 h-6 mr-2"/>
                   Status proizvoda 
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            @if($modalStatusFormVisible)
                {{-- <livewire:bankomati.komponente.bankomat-info :bankomat_lokacija_id="0" :multySelectedArray="$selectedTerminals" :multySelected="$multiSelected" /> --}}
                <livewire:bankomati.komponente.bankomat-info :bankomat_lokacija_id="$modelId" />
            @endif
            <div class="mt-4">
                <x-jet-label for="bankomat_status" value="Status bankomata" />   
                <select wire:model="bankomat_status" id="" class="block appearance-none w-full bg-gray-100 border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                    @foreach (App\Models\BankomatStatusTip::getAll() as $key => $value)    
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('bankomat_status') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="mt-4">
                <x-jet-label for="datum_promene" value="Datum promene" />
                <div class="flex">
                    <x-jet-input id="datum_promene" type="date" class="mt-1 block" value="{{ $datum_promene }}" wire:model="datum_promene" /> <span class="p-2 mt-2">{{ App\Http\Helpers::datumFormatDanFullYear($datum_promene) }}</span>
                </div>
                @error('datum_promene') <span class="error">{{ $message }}</span> @enderror
                @if($datum_promene_error != '')
                    <p class="text-red-500"> {{$datum_promene_error}} </p>
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalStatusFormVisible')" wire:loading.attr="disabled">
                Otkaži
            </x-jet-secondary-button>

            <x-jet-button class="ml-2" wire:click="statusUpdate" wire:loading.attr="disabled">
                Sačuvaj
            </x-jet-button>     
        </x-slot>
    </x-jet-dialog-modal>

    {{-- PREMESTI MODAL ############################################################### --}}
    <x-jet-dialog-modal wire:model="modalPremestiVisible">
        <x-slot name="title">
            <div class="flex justify-between">
                <div class="flex">
                    <x-heroicon-o-arrows-right-left class="w-6 h-6 mr-2"/>
                  Premesti proizvod
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            @if($modalPremestiVisible)
                <livewire:bankomati.komponente.bankomat-info :bankomat_lokacija_id="$modelId" />
                <p class="font-bold">Nova lokacija proizvoda:</p>
                @if(!$nova_lokacija)
                    <livewire:bankomati.komponente.izbor-lokacije :key="$flashKey" comp_index="premesti" />
                @else
                    {{-- Izabrao je lokaciju menjam prikaz --}}
                    <p>Nova lokacija:</p>
                    <livewire:bankomati.komponente.bankomat-lokacija-info :b_lokacija_id="$nova_lokacija" />
                @endif
                @error('nova_lokacija') <span class="error">{{ $message }}</span> @enderror

                <div class="mt-4">
                    <x-jet-label for="bankomat_status" value="Status bankomata" />   
                    <select wire:model="bankomat_status" id="" class="block appearance-none w-full bg-gray-100 border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                        @foreach (App\Models\BankomatStatusTip::getAll() as $key => $value)    
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('bankomat_status') <span class="error">{{ $message }}</span> @enderror
                </div>
            @endif

            <div class="mt-4">
                <x-jet-label for="datum_promene" value="Datum promene" />
                <div class="flex">
                    <x-jet-input id="datum_promene" type="date" class="mt-1 block" value="{{ $datum_promene }}" wire:model="datum_promene" /> <span class="p-2 mt-2">{{ App\Http\Helpers::datumFormatDanFullYear($datum_promene) }}</span>
                </div>
                @error('datum_promene') <span class="error">{{ $message }}</span> @enderror
                @if($datum_promene_error != '')
                    <p class="text-red-500"> {{$datum_promene_error}} </p>
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalPremestiVisible')" wire:loading.attr="disabled">
                Otkaži
            </x-jet-secondary-button>

            <x-jet-button class="ml-2" wire:click="moveBankomat" wire:loading.attr="disabled">
                Premesti
            </x-jet-button>     
        </x-slot>
    </x-jet-dialog-modal>

    {{-- HISTORI Modal ################################################################################## --}}
    <x-jet-dialog-modal wire:model="modalProizvodlHistoryVisible">
        <x-slot name="title">
            <div class="flex">
            <x-icon-history class="fill-current w-6 h-6 mr-2"/>
            Vremenska linija proizvoda
            </div>
        </x-slot>
       
        <x-slot name="content">
            @if($modalProizvodlHistoryVisible)
                <div class="mt-4">
                    <livewire:bankomati.komponente.bankomat-info :bankomat_lokacija_id="$modelId" />
                </div>
                <div class="mt-4">
                    <livewire:bankomati.komponente.bankomat-history :bankomat_lokacija_id="$modelId" />
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalProizvodlHistoryVisible')" wire:loading.attr="disabled">
                Zatvori
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>

    {{-- TIKET Modal ################################################################################## --}}
    <x-jet-dialog-modal wire:model="modalNewTicketVisible">
        <x-slot name="title">
            <div class="flex">
            <x-icon-ticket-plus class="fill-current w-6 h-6 mr-2"/>
            Novi tiket
            </div>
        </x-slot>
       
        <x-slot name="content">
            @if($modalNewTicketVisible)
                <div class="mt-4">
                    <livewire:bankomati.komponente.bankomat-info :bankomat_lokacija_id="$modelId" />
                </div>
                <div class="mt-4">
                    <livewire:bankomati.komponente.bankomat-new-ticket :bankomat_lokacija_id="$modelId" />
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalNewTicketVisible')" wire:loading.attr="disabled">
                Zatvori
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>