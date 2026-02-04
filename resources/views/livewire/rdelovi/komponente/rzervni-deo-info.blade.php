<div class="relative bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
   <div class="flex">
        <div class="py-1">
            <x-heroicon-c-cog class="fill-current w-6 h-6 mr-2"/>
        </div>
        <div class="">
            <div>
                <p>Naziv: <span class="font-bold">{{$rzervniDeo->naziv}}</span> &nbsp;&nbsp;&nbsp; Šifra: <span class="font-bold">{{ $rzervniDeo->sifra  }}</span></p>
                <p class="text-sm">Opis: <span class="font-bold">{{ $rzervniDeo->opis }}</span></p> 
                <p class="text-sm">Kategorija: <span class="font-bold">{{$rzervniDeo->kategorija}}</span></p>
                <p class="text-sm">Cena: <span class="font-bold">{{ $rzervniDeo->cena }}</span> RSD</span></p>
                <p class="text-sm">Jedinica mere: <span class="font-bold">{{ $rzervniDeo->jedinica_mere }}</span></p>
                <p class="text-sm">Minimalna količina: <span class="font-bold">{{ $rzervniDeo->min_kolicina }}</span></p>    
            </div>
        </div>
    </div>
</div>