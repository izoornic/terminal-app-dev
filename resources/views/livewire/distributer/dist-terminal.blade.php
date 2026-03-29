<div class="p-6">
    <div class="flex justify-between">
        <p></p>
        <a class="flex px-2 py-2 text-sm relative text-gray-800 uppercase border rounded-md hover:bg-gray-700 hover:text-white mb-2" href="{{ route( 'distributer-kampanja' ) }}">
            <x-heroicon-o-megaphone class="w-4 h-4 mr-2" />
           Kampanje
        </a>
    </div>
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
                                <td class="px-1 py-1"><x-jet-input wire:model="searchSB" id="" class="block bg-orange-50 w-full" type="text" placeholder="Serijski broj" /></td>
                                <td class="px-1 py-1">
                                    <x-jet-input wire:model="searchName" id="" class="block bg-orange-50 w-full" type="text" placeholder="Lokacija" /> 
                                </td>
                                <td class="px-1 py-1">
                                    <x-jet-input wire:model="searchPib" id="" class="block bg-orange-50 w-32" type="text" placeholder="PIB" /> 
                                </td>
                                <td class="px-1 py-1" >
                                    <select wire:model="searchStatus" id="" class="block appearance-none bg-orange-50 border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">-- Status --</option>
                                        @foreach (App\Models\TerminalStatusTip::tipoviList() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-1 py-1">
                                    <select wire:model="searchBlackist" id="" class="mx-4 block appearance-none bg-orange-50 border border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">Blacklsta</option>
                                        <option value="2"> Ne</option>
                                        <option value="1"> Da </option>
                                    </select>
                                </td>      
                            </tr>  
                            {{-- SEARCH 2nd ROW --}}    
                            <tr class="bg-orange-50">
                                <td></td>
                                <td class="px-1 py-1"><x-jet-input wire:model="searchKutija" id="" class="block bg-orange-50 w-full" type="text" placeholder="Broj kutije" /></td>
                                <td>
                                    <x-jet-input wire:model="searchCampagin" id="" class="block bg-orange-50 w-full" type="text" placeholder="Kampanja" />
                                </td>
                                <td>
                                    <select wire:model="searchRegion" id="" class="block appearance-none bg-orange-50 border border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                                <option value="">-- Region --</option>
                                            @foreach (App\Models\Region::regioni() as $key => $value)    
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                    </select>
                                </td>
                                <td></td>
                                <td></td>
                            </tr>          
                        </tbody>
                    </table>

                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            {{-- DATA THEAD --}}
                            <tr>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500  tracking-wider"><input type="checkbox" value="1" wire:model="selectAll.1"  class="form-checkbox h-6 w-6 text-blue-500"></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500  tracking-wider">
                                    Serijski broj <br />
                                    <span class=" text-red-400">Kutija</span>
                                </th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">
                                    Lokacija<br />
                                    <span class=" text-green-600">Kampanja</span>
                                </th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">Region</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">Status</th>
                                <th colspan="3" class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider">Ukupno: <span class="font-bold">{{ $data->total() }}</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"> 
                        
                            {{-- DATA TABLE --}}        
                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr @if($loop->even) class="bg-gray-50" @endif >
                                        <td class="px-1 py-1"><input type="checkbox" value="{{ $item->tlid }}" wire:model="selectedTerminals"  class="form-checkbox h-6 w-6 text-blue-500"></td>
                                        <td class="px-1 py-2">
                                            {{ $item->sn }}<br />
                                            <span class="text-red-500 text-xs">{{ $item->broj_kutije }}</span>
                                        </td>
                                        <td class="px-1 py-2">
                                             @if($item->is_duplicate)<span class="text-red-500">*</span>@endif
                                            {{ $item->l_naziv }}&nbsp;{{ $item->l_naziv_sufix }}
                                            @if( $item->campagin_name )<br /><span class="text-xs text-green-600">{{ $item->campagin_name }}</span>@endif
                                        </td>
                                        <td class="px-1 py-2">{{ $item->r_naziv }}</td> 
                                        <td class="px-1 py-2">
                                            <button class="px-2 py-2 text-sm relative text-gray-800 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="statusShowModal({{ $item->tlid}}, {{ $item->statusid }})">
                                                {{ $item->ts_naziv }}
                                            </button>
                                        </td>
                                        <td class="px-1 py-1">
                                            <button class="px-2 py-2 text-sm relative text-gray-800 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="premestiShowModal({{ $item->tlid }}, {{ $item->statusid }})">
                                                {{ __('Premesti') }}
                                            </button>
                                        </td>
                                        <td class="px-1 py-1">
                                            @if($item->blacklist == 1)
                                                <button class="p-2 text-sm relative text-white uppercase border rounded-md bg-gray-700 hover:bg-white hover:text-gray-600" wire:click="blacklistShowModal({{  $item->tlid }})" title="Skloni sa Blckiste">
                                                    <x-icon-blacklist-scull class="fill-current w-5 h-5 ml-1" />
                                                </button>
                                            @else
                                                <button class="p-2 text-sm relative text-gray-800 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="blacklistShowModal({{  $item->tlid }})" title="Dodaj na Blckistu">
                                                    <x-icon-blacklist-scull class="fill-gray-400 w-5 h-5 ml-1" />
                                                </button>
                                            @endif
                                        </td>
                                        <td>
                                            @if($komentariTerminalVisible)
                                            <button class="px-2 py-1 text-sm relative text-gray-600 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="commentsShowModal({{ $item->tlid }})" title="Komentari">
                                                <div class="mx-1 text-lg">{{ $item->br_komentara}}</div>
                                                <x-heroicon-o-chat-bubble-bottom-center-text class="z-10 absolute top-1 right-1 text-gray-400 -mt-1.5 ml-2.5 w-4 h-4"/>
                                            </button>
                                            @endif
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
            
        </div>
        <div class="basis-1/4 text-right mr-6">
            @if(count($selectedTerminals))
                <x-jet-button class="ml-2" wire:click="premestiSelectedShowModal()">
                    {{ __('Premesti') }}
                </x-jet-button>
            @endif
        </div>
    </div>

    {{-- BLAKLIST modal ############################################################## --}}
    <x-jet-dialog-modal wire:model="blacklistFormVisible">
        <x-slot name="title">
            {{ __('Promeni blacklist status terminala') }}
        </x-slot>

        <x-slot name="content">
        @if($blacklistFormVisible)
                <livewire:komponente.terminal-info :terminal_lokacija_id="$modelId" />
                <livewire:komponente.blacklist-add-remove :terminal_lokacija_id="$modelId" />
        @endif
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('blacklistFormVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>

    {{-- PREMESTI Modal ############################################################## --}}
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
                <option value="3">Korisnik terminala</option>
                <option value="4">Distributer</option>
            </select>
           
                @if($plokacijaTip == 3)
                    {{-- KORISNIK terminala search by name --}}
                    <table class="min-w-full divide-y divide-gray-200 mt-4" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"></th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Naziv</th> 
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Mesto</th> 
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"></th> 
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"></th>  
                            </tr>
                            <tr class="bg-orange-50">
                                <td><svg class="mx-auto fill-orange-600 w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M3.853 54.87C10.47 40.9 24.54 32 40 32H472C487.5 32 501.5 40.9 508.1 54.87C514.8 68.84 512.7 85.37 502.1 97.33L320 320.9V448C320 460.1 313.2 471.2 302.3 476.6C291.5 482 278.5 480.9 268.8 473.6L204.8 425.6C196.7 419.6 192 410.1 192 400V320.9L9.042 97.33C-.745 85.37-2.765 68.84 3.854 54.87L3.853 54.87z"/></svg></td>
                                <td><x-jet-input wire:model="searchPLokacijaNaziv" id="" class="block bg-orange-50 w-full" type="text" placeholder="Naziv" /></td>
                                <td><x-jet-input wire:model="searchPlokacijaMesto" id="" class="block bg-orange-50 w-full" type="text" placeholder="Mesto" /></td>
                                <td><x-jet-input wire:model="searchPlokacijaPib" id="" class="block bg-orange-50 w-full" type="text" placeholder="PIB" /></td>
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

                @elseif($plokacijaTip == 4)
                {{-- DISTRIBUTER --}}
                    <x-jet-label for="lokacija" value="{{ __('Izaberi lokaciju') }}" class="mt-4" />
                    <select wire:model="plokacija" id="" class="block appearance-none bg-gray-50 w-full border border-1 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                        <option value="">---</option>
                        @foreach (App\Models\Lokacija::lokacijeDistributera($distId) as $key => $value)    
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
            {{-- KAMPANJA PRODAJE --}}
            @if($plokacijaTip == 3) 
                <div class="mt-4">
                <x-jet-label for="selectedCampagin" value="{{ __('Odaberi kampanju') }}" />
                    <select wire:model="selectedCampagin" id="" class="block appearance-none bg-gray-50 w-full border border-1 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                        <option value="">---</option>
                        <option value="0">Bez kampanje</option>
                        @foreach ($distCampagins as $item)    
                            <option value="{{ $item->id }}">{{ $item->campagin_name }}</option>
                        @endforeach
                    </select>
                    @error('selectedCampagin') <span class="error">{{ $message }}</span> @enderror
                </div> 
            @else

            @endif
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

    {{-- STATUS MODAL #####################################################################--}}
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

            <div class="font-bold">Nova status terminala:</div>
        @endif
            <div class="mt-4">
            <x-jet-label for="terminalStatus" value="{{ __('Novi status terminala') }}" />
                <select wire:model="modalStatus" id="" class="block appearance-none w-full border border-1 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
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

    {{-- KOMENTARI MODAL ############################################################## --}}
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
