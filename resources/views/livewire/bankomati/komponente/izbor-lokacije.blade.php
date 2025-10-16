<div class="mt-2 bg-green-100 border-t-4 border-green-500 rounded-b text-green-900 px-4 py-3 shadow-md mb-6" role="alert">
   @if(!$vrsta_lokacije)
        <div class="my-2 flex justify-between">
            @foreach (App\Models\BlokacijaTip::getAll() as $key => $value)
                <button wire:click="vrstaLokacije({{$key}})" class="flex mt-1 -mb-1 bg-yellow-50 text-sm text-gray-700 uppercase border border-green-500 rounded-md p-1.5 hover:bg-gray-700 hover:text-white" >
                     @switch($key)
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
                    <span class="ml-2">{{ $value }}</span>
                </button>
            @endforeach
        </div>
    @else
        <div class="mt-2">
            <p class="font-bold">{{ App\Models\BlokacijaTip::getAll()[$vrsta_lokacije] }}</p>
        </div>
        <div class="mt-2">
                <table class="min-w-full divide-y divide-gray-200 mt-4" style="width: 100% !important">
                    <thead>
                        {{-- search row --}}
                        <tr class="bg-orange-50">
                            <td></td>
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
                    @foreach ($this->lokacijeTipa($vrsta_lokacije) as $value)
                        <tr class="hover:bg-gray-100" wire:click="novaLokacija({{ $value->id }})" >
                        {{--  <tr class="hover:bg-gray-100" wire:click="$set('nova_lokacija', {{ $value->id }})" >  --}}   
                                <td></td>
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
                    {{ $this->lokacijeTipa($vrsta_lokacije)->links() }}
                </div>
        </div>
    @endif
</div>
