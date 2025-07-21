<div class="p-6">
   <div class="text-lg flex mb-4">
      <div class="text-gray-700 mr-2">Mapa novih licenci za poslednja </div>
      <select wire:model="filterMeseciValue" id="" class="block appearance-none border-gray-700 border-1 text-gray-700 py-1 px-2 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
            <option value="">---</option>
            @foreach ($fiterMeseci as $key => $value)    
               <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
      </select>
   </div>
   
    <x-maps-google
        :markers='$pins'  
        :fitToBounds="true"
    ></x-maps-google>
</div>