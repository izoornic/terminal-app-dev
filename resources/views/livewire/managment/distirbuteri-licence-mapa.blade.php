<div class="p-6">
   <div class="text-lg flex mb-2">
      <div class="text-gray-700 mr-2">Mapa novih licenci za poslednja </div>
      <select wire:model="filterMeseciValue" id="" class="block appearance-none border-gray-700 border-1 text-gray-700 py-1 px-2 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
            <option value="">---</option>
            @foreach ($fiterMeseci as $key => $value)    
               <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
      </select>
   </div>
   <div class="flex items-center justify-between px-4 py-3 text-right sm:px-6 bg-slate-100">
      <div class="flex">
         <div class="text-lg mr-2">Legenda</div>
         @foreach($terminal_count_colors_ico as $key => $ico)
            <div class="flex ml-4"><img width="20px" src="{{$ico}}" /><span class="mt-1">&nbsp; @if ($key <= 5) =@else < @endif {{$key}}</span></div>
         @endforeach
      </div>
   </div>
    <x-maps-google
        :markers='$pins'  
        :fitToBounds="true"
    ></x-maps-google>
</div>