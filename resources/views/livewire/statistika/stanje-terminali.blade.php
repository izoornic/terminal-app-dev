<div class="p-6">
    {{-- The data table --}}
    <div class="flex mx-4 my-4">
    <div class="w-3/5 flex-initial mx-2 my-2">
        <div class="mb-4">
            <h1 class="text-2xl font-semibold text-gray-900">Stanje terminala</h1>
        </div>
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                        <thead>
                            <tr>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider max-w-sm"></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"></th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider text-center">Ukupno terminala</th>
                                <th class="px-1 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider text-center">@if($searchLokacijaTip) Ukupno tip @endif</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"> 
                        {{-- SEARCH ROW --}}
                            <tr class="bg-orange-50">
                                <td></td>
                                <td>
                                    <select wire:model="searchLokacijaTip" id="" class="block appearance-none bg-orange-50 border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                        <option value="" class="text-gray-500"> Vrsta lokacije</option>
                                        @foreach (App\Models\LokacijaTip::tipoviList() as $key => $value)    
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select wire:model="searchTerminalTip" id="" class="block appearance-none bg-orange-50 border border-0 text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                                                <option value="" class="text-gray-500"> Tip terminala</option>
                                            @foreach (App\Models\TerminalTip::tipoviList() as $key => $value)    
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                    </select>
                                </td>                             
                                <td></td>  
                            </tr>  
                            <!-- DATA  -->                   
                            @if ($data->count())
                                @foreach ($data as $item)
                                    <tr>
                                        <td style="max-width: 400px !important;" class="px-4 py-2">{{ $item->lokacija_naziv }}</td>
                                        <td class="px-1 py-2">{{ $item->mesto }}</td>
                                        <td style="max-width: 150px !important;" class="px-1 py-2 text-center font-bold">{{ $item->total }}</td>
                                        <td class="px-1 py-2"></td> 
                                    </tr>
                                    @if($item->modeli->count())
                                        @foreach ($item->modeli as $model)
                                            <tr class="bg-gray-50">
                                                <td class="px-4 py-2"></td>
                                                <td class="px-1 py-2">{{ $model->model }}</td>
                                                <td class="px-1 py-2">{{ $model->proizvodjac }}</td>
                                                <td class="px-1 py-2 text-center font-bold">{{ $model->total }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
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
    <div class="w-2/5 flex-initial mx-2 my-2">
        <div class="mb-4">
            <h2 class="pl-4 text-2xl font-semibold text-gray-500"><svg class="fill-red-400 w-6 h-6 float-left mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 0C114.6 0 0 114.6 0 256s114.6 256 256 256s256-114.6 256-256S397.4 0 256 0zM256 128c17.67 0 32 14.33 32 32c0 17.67-14.33 32-32 32S224 177.7 224 160C224 142.3 238.3 128 256 128zM296 384h-80C202.8 384 192 373.3 192 360s10.75-24 24-24h16v-64H224c-13.25 0-24-10.75-24-24S210.8 224 224 224h32c13.25 0 24 10.75 24 24v88h16c13.25 0 24 10.75 24 24S309.3 384 296 384z"/></svg> Info</h2>
            <div>
                <p class="pl-4 text-gray-500">{{ $filterInfo }}</p>
            </div>
        </div>
       
    </div>
    </div>
    
</div>