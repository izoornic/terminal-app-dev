<div class="p-6">
    <livewire:komponente.session-flash-message />
    {{-- The data table --}}
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">BR:</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Tip</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Datum</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Status</th>
                                 <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Lokacija</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Mesto</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500"><x-heroicon-o-chat-bubble-left-right class="w-5 h-5" /></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Prioritet</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Ukupno: {{ $data->total() }}</th>
                                  
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
                                <td></td>
                                <td>
                                    <select wire:model="searchStatus" id="" class="block appearance-none bg-orange-50 w-full border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="Aktivan">Aktivan</option>
                                        <option value="Otvoren">Otvoren</option>
                                        <option value="Dodeljen">Dodeljen</option>
                                        <option value="Zatvoren">Zatvoren</option>
                                        <option value="Svi">Svi tiketi</option>
                                    </select>
                                </td>
                                <td>
                                    <x-jet-input wire:model="searchLokacijaNaziv" id="" class="block bg-orange-50 w-full" type="text" placeholder="Naziv lokacije" />
                                </td>
                                <td><x-jet-input wire:model="searchMesto" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pretraži mesto" /></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            
                            <!-- DATA  -->                   
                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr @if($loop->even) class="bg-gray-50" @endif >

                                        <td class="px-2 py-1">{{ $item->id }}</td> 
                                        <td class="px-1 py-2">{{ $item->bp_tip_naziv }}</td>
                                        <td class="px-1 py-2">{{ App\Http\Helpers::datumFormatDan($item->created_at) }}</td>
                                        <td class="px-1 py-2">{{ $item->status }}</br><span class="text-sm">{{ $item->name }}</span></td>
                                        <td class="px-1 py-2">
                                             @if($item->is_duplicate)<span class="text-red-500">*</span>@endif
                                                <span>{{ $item->bl_naziv }}&nbsp;{{ $item->bl_naziv_sufix }}</span>
                                                <br />
                                                <span class="text-sm text-red-400">  @if($item->btkt_naziv){{ $item->btkt_naziv }}@else Ostalo @endif</span> 
                                        </td>
                                        <td class="pl-1">
                                             {{ $item->bl_mesto }}<br /><span class="text-sm">{{ $item->r_naziv }}</span>
                                        </td>
                                        <td class="px-1 py-2">
                                            <div class="z-10 absolute ml-1 text-lg">{{ $item->br_komentara}}</div>
                                            <x-heroicon-o-chat-bubble-bottom-center-text class="text-gray-400 -mt-1 ml-2 w-3 h-3"/>
                                        </td> 
                                        <td class="px-1 py-2">
                                           <span class="flex-none py-2 px-4 mx-2 font-bold rounded bg-{{$item->tr_bg_collor}} text-{{$item->btn_collor}}" >{{ $item->btpt_naziv }}</span>
                                        </td>
                                        <td class="px-1 py-1">
                                           
                                            <a class="flex text-sm text-gray-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white" href="{{ route( 'bankomat-tiketview', ['id' => $item->id] ) }}" :active="request()->routeIs('bankomat-tiketview', ['id' => $item->id])">
                                               <x-heroicon-o-ticket class="w-4 h-4 mr-2 mt-0.5" />
                                                Pregled
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

    {{-- NOVI tiket MODAL #################################################### --}}
    <x-jet-dialog-modal wire:model="newTicketShowModal">
        <x-slot name="title">
        <x-icon-ticket-plus class="fill-current w-6 h-6 mr-2"/>
            Novi tiket
    </x-slot>

        <x-slot name="content">
           @if(!$bankomat_lokacija_id)
                <livewire:bankomati.komponente.izbor-proizvoda :key="time()"/>
           @else
                <livewire:bankomati.komponente.bankomat-info :bankomat_lokacija_id="$bankomat_lokacija_id" />
                <livewire:bankomati.komponente.bankomat-new-ticket :bankomat_lokacija_id="$bankomat_lokacija_id" />
           @endif
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('newTicketShowModal')" wire:loading.attr="disabled">
                    Otkaži
            </x-jet-secondary-button>    
        </x-slot>
    </x-jet-dialog-modal>
</div>
