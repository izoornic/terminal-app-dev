<div class="p-6">
    {{-- The data table --}}
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500"></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Proizvod</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Model</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Proizvođač</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-sm leading-4 font-medium text-gray-500">Opis</th>
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
                                <td class="px-2"><x-jet-input wire:model="searchModel" id="" class="block bg-orange-50 w-full" type="text" placeholder="Model" /></td>
                                <td class="px-2"><x-jet-input wire:model="searchProizvodjac" id="" class="block bg-orange-50 w-full" type="text" placeholder="Proizvođač" /></td>
                                <td class="px-2"><x-jet-input wire:model="searchOpis" id="" class="block bg-orange-50 w-full" type="text" placeholder="Opis" /></td>
                                <td></td>
                            </tr>  
                            <!-- DATA  -->                   
                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr @if($loop->even) class="bg-gray-50" @endif >

                                        <td class="px-2 py-1">
                                            <button class="mt-2 text-sm text-gray-700 uppercase border rounded-md hover:bg-gray-700 hover:text-white" wire:click="showUpdateModal('{{ $item->id }}')">
                                                <x-heroicon-o-pencil-square class="w-5 h-5 mx-2 my-1" />
                                            </button>
                                        </td> 
                                        <td class="px-1 py-2">{{ $item->bp_tip_naziv }}</td>
                                        <td class="px-1 py-2">{{ $item->model }}</td>
                                        <td class="px-1 py-2">{{ $item->proizvodjac }}</td>
                                        <td class="px-1 py-2">{{ $item->opis }}</td>
                                        
                                        <td class="px-1 py-1 text-center">
                                             <button class="text-sm bg-white text-red-600 uppercase border border-red-600 rounded-md p-1.5 hover:bg-red-600 hover:text-white" wire:click="showDeleteModal({{ $item->id }})" title="Obriši">
                                               <x-heroicon-o-trash class="w-4 h-4 mr-0" />
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

    {{-- NOV / IZMENI BANKOMAT ############################################### --}}
    <x-jet-dialog-modal wire:model="newEditModalVisible">
        <x-slot name="title">
            <div class="flex justify-between">
                <div class="flex">
                    <x-heroicon-o-building-office class="w-6 h-6 mr-2"/>
                    @if ($is_edit) Izmeni podatke - {{ $bankomat_model }}
                    @else 
                         Novi uređaj
                    @endif
                </div>

            </div>
        </x-slot>

        <x-slot name="content">
           
            <div class="mt-4">
                <x-jet-label for="bankomat_product_tip" value="Tip uređaja" />
                 @if ($is_edit)
                    <p class="mt-2 text-sm font-bold text-gray-600">{{$tip_uredjaja_naziv}}</p>
                 @else
                    <select wire:model="bankomat_product_tip" id="" class="block appearance-none bg-orange-50 w-full border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                        <option value="">---</option>
                        @foreach (App\Models\BankomatProductTip::getAll() as $key => $value)    
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('bankomat_product_tip') <span class="error">{{ $message }}</span> @enderror
                 @endif
            </div>

            <div class="mt-4">
                <x-jet-label for="bankomat_model" value="Model" />
                <x-jet-input wire:model="bankomat_model" id="" class="block mt-1 w-full" type="text" />
                @error('bankomat_model') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="mt-4">
                <x-jet-label for="bankomat_proizvodjac" value="Proizvođač" />
                <x-jet-input wire:model="bankomat_proizvodjac" id="" class="block mt-1 w-full" type="text" />
                @error('bankomat_proizvodjac') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="mt-4">
                <x-jet-label for="opis" value="Opis" />
                <x-jet-input wire:model="opis" id="" class="block mt-1 w-full" type="text" />
                @error('opis') <span class="error">{{ $message }}</span> @enderror
            </div>

        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('newEditModalVisible')" wire:loading.attr="disabled">
                {{ __('Otkaži') }}
            </x-jet-secondary-button>

            @if ($is_edit)
                <x-jet-button class="ml-2" wire:click="updateBankomatModel" wire:loading.attr="disabled">
                    {{ __('Update') }}
                </x-jet-danger-button>
            @else
                <x-jet-button class="ml-2" wire:click="saveNewBankomatModel" wire:loading.attr="disabled">
                    {{ __('Sačuvaj') }}
                </x-jet-danger-button>
            @endif            
        </x-slot>
    </x-jet-dialog-modal>

    {{-- DELETE ############################################### --}}
    <x-jet-dialog-modal wire:model="deleteModalVisible">
        <x-slot name="title">
            <div class="flex justify-between">
                <div class="flex">
                    <x-heroicon-o-trash class="w-6 h-6 mr-2"/>
                    Obriši bankomat model - {{ $bankomat_model }}
                </div>

            </div>
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                @if($can_not_delete)
                    <h2 class="font-bold text-red-600">Model bankomata je povezan sa jednim ili više bankomata i ne može biti obrisan.</h2>
                @else
                    <h2 class="font-bold text-red-600">Da li ste sigurni da želite da obrišete model bankomata?</h2>
                @endif

                <p class="mt-2 text-sm text-gray-600">Model: <span class="font-bold">{{ $bankomat_model }}</span></p>
                <p class="mt-2 text-sm text-gray-600">Proizvođač: <span class="font-bold">{{ $bankomat_proizvodjac }}</span></p>
                <p class="mt-2 text-sm text-gray-600">Opis: <span class="font-bold">{{ $opis }}</span></p>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('deleteModalVisible')" wire:loading.attr="disabled">
                Otkaži
            </x-jet-secondary-button>
            @if(!$can_not_delete)
            <x-jet-danger-button class="ml-2" wire:click="deleteBankomatModel" wire:loading.attr="disabled">
                Obriši model bankomata
            </x-jet-danger-button>
            @endif
        </x-slot>
    </x-jet-dialog-modal>

</div>