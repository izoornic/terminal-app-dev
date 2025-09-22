<div class="relative bg-green-100 border-t-4 border-green-500 rounded-b text-green-900 px-4 py-3 shadow-md mb-6" role="alert">
   <div class="flex">
        <div class="py-1">
            <x-heroicon-o-map-pin class="text-current w-6 h-6 mr-2" />
        </div>
        
        <div>
            <div class="">
                <div>
                    <p>Naziv: <span class="font-bold">{{ $data->bl_naziv }}&nbsp;{{ $data->bl_naziv_sufix }}</span></p>
                    <p class="text-sm">PIB: <span class="font-bold">{{ $data->pib }}</span></p>
                    <p class="text-sm">Adresa: <span class="font-bold">{{$data->bl_adresa}}</span></p>
                    <p class="text-sm">Mesto: <span class="font-bold">{{$data->bl_mesto}}</span></p>
                    <p class="text-sm">Region: <span class="font-bold">{{$data->r_naziv}}</span></p>
                    <p class="text-sm">Tip: <span class="font-bold">{{$data->tip}}</span></p>
                    <p class="text-sm">Poslednja promena: <span class="font-bold">{{ App\Http\Helpers::datumFormat($data->updated_at) }}</span></p>    
                </div>
            </div>
        </div>
    </div>
</div>
