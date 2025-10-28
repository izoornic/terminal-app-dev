<div>
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
            <table class="min-w-full divide-y divide-gray-200 mt-4" style="width: 100% !important">
                <thead>
                    {{-- search row --}}
                    <tr class="bg-orange-50">
                        <td></td>
                        <td><x-jet-input wire:model="searchSN" id="" class="block bg-orange-50 w-full" type="text" placeholder="Serijski broj" /></td>
                        <td><x-jet-input wire:model="searchPLokacijaNaziv" id="" class="block bg-orange-50 w-full" type="text" placeholder="Naziv" /></td>
                        <td><x-jet-input wire:model="searchPlokacijaMesto" id="" class="block bg-orange-50 w-full" type="text" placeholder="Mesto" /></td>
                        <td>
                            <select wire:model="searchPlokacijaRegion" id="" class="block appearance-none bg-orange-50 w-full border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="">---</option>
                                    @foreach (App\Models\BankomatRegion::getAll() as $key => $value)    
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                            </select>
                        </td>
                        <td></td>
                    </tr>
                </thead>
                <tbody class="bg-yellow-50 divide-y divide-gray-200"> 
                @foreach ($proizvodi as $value)
                    <tr class="hover:bg-gray-100" wire:click="izabraniProizvod({{ $value->blid }})" >
                    {{--  <tr class="hover:bg-gray-100" wire:click="$set('nova_lokacija', {{ $value->id }})" >  --}}   
                            <td></td>
                            <td>{{ ($value->b_sn) }}</td>
                            <td>
                                @if($value->is_duplicate)<span class="text-red-500">*</span>@endif
                                {{ $value->bl_naziv }}&nbsp;{{ $value->bl_naziv_sufix }}
                            </td>
                            <td>{{ $value->bl_mesto}}</td>
                            <td>{{ $value->r_naziv}}</td>
                            <td></td>
                    </tr>
                @endforeach
                </tbody>
            <table>
            <div class="mt-4">
                {{ $proizvodi->links() }}
            </div>
        </div>  
    @endif
</div>
