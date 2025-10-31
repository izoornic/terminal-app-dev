<div>
    @if($tiket_exists)
        <div class="bg-red-50 border-t-4 border-red-500 rounded-b text-red-600 px-4 py-3 shadow-md mb-6 mx-2 my-2" role="alert">
            <p class="font-bold text-lg">Upozorenje!</p> 
            <p class="text-lg">Za odabrani proizvod već postoji otvoren tiket!</p>
            <div class="my-4 mx-4 flex" >
                <a class="flex bg-red-500 text-white font-bold px-2 py-1 hover:bg-white hover:text-red-600 rounded" href="{{ route( 'bankomat-tiketview', ['id' => $tiket_exists->id] ) }}" title="{{ __('Pregled') }}" :active="request()->routeIs('bankomat-tiketview', ['id' => $tiket_exists->id] )">
                    <x-heroicon-o-ticket class="w-4 h-4 mr-2 mt-0.5" />
                    Tiket #{{ $tiket_exists->id }}
                </a>
            </div>
        </div>
    @else
        <div class="mt-4">
            <x-jet-label for="vrsta_kvara" value="Vrsta kvara:" />   
            <select wire:model="vrsta_kvara" id="" class="block appearance-none w-full bg-gray-100 border border-1 border-gray-300 rounded-md text-gray-700 py-3 px-4 pr-8 round leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                    <option value="">---</option>
                @foreach (App\Models\BankomatTiketKvarTip::getAll($productTipId) as $key => $value)    
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
                    <option value="1000">Ostalo</option>
            </select>
            @error('vrsta_kvara') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div class="mt-4">
            <x-jet-label for="opis_kvara" value="Opis kvara" />
            <x-jet-textarea id="opis_kvara" type="textarea" class="mt-1 block w-full disabled:opacity-50" wire:model.defer="opis_kvara" />
            @error('opis_kvara') <span class="error">{{ $message }}</span> @enderror
        </div> 

        @if($userPozicija == 9 || $userPozicija == 10)
            <!-- Admin i Šef servisa dodeljuju tiket sebi ili serviseru -->
            @if(!$dodeljenUserId)
            <div class="mt-4">
                <hr />
                <p>Dodeli tiket korisniku:</p>
                <table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
                    <thead>
                        <tr>
                            <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"></th>
                            <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Ime</th>
                            <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Lokacija</th> 
                            <th class="px-3 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Pozicija</th>   
                        </tr>
                        <tr class="bg-orange-50">
                            <td><svg class="mx-auto fill-orange-600 w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M3.853 54.87C10.47 40.9 24.54 32 40 32H472C487.5 32 501.5 40.9 508.1 54.87C514.8 68.84 512.7 85.37 502.1 97.33L320 320.9V448C320 460.1 313.2 471.2 302.3 476.6C291.5 482 278.5 480.9 268.8 473.6L204.8 425.6C196.7 419.6 192 410.1 192 400V320.9L9.042 97.33C-.745 85.37-2.765 68.84 3.854 54.87L3.853 54.87z"/></svg></td>
                            <td><x-jet-input wire:model="searchUserName" id="" class="block bg-orange-50 w-full" type="text" placeholder="Ime" /></td>
                            <td><x-jet-input wire:model="searchUserLokacija" id="" class="block bg-orange-50 w-full" type="text" placeholder="Lokacija" /></td>
                            <td><x-jet-input wire:model="searchUserPozicija" id="" class="block bg-orange-50 w-full" type="text" placeholder="Pozicija" /></td>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200"> 
                        @foreach ($this->searchUser() as $value)
                            <tr class="hover:bg-gray-100" wire:click="setDodeljenUserInfo('{{ $value->id }}')" >    
                                    <td></td>
                                    <td>{{ $value->name }}</td>
                                    <td>{{ $value->bl_naziv}}</td>
                                    <td>{{ $value->naziv}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-5">
                    {{ $this->searchUser() ->links() }}
                </div>
                @error('dodeljenUserId') <span class="error">{{ $message }}</span> @enderror
            </div>

            @else
            
            <div class="mt-4">Tiket dodeljen korisniku:</div>
            <div class="bg-sky-100 border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                <div class="flex">
                    <div class="py-1"><svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224 256c70.7 0 128-57.31 128-128s-57.3-128-128-128C153.3 0 96 57.31 96 128S153.3 256 224 256zM274.7 304H173.3C77.61 304 0 381.6 0 477.3c0 19.14 15.52 34.67 34.66 34.67h378.7C432.5 512 448 496.5 448 477.3C448 381.6 370.4 304 274.7 304z"/></svg></div>
                    <div>
                        <p>Korisnik: <span class="font-bold">{{ $dodeljenUserInfo->name }}</span> &nbsp;&nbsp;&nbsp; Pozicija: <span class="font-bold">{{ $dodeljenUserInfo->naziv }}</span></p>
                        <p class="text-sm">Lokacija: <span class="font-bold">{{ $dodeljenUserInfo->bl_naziv }}, {{$dodeljenUserInfo->bl_mesto}}</span></p>
                    </div>
                </div>
            </div> 
            @endif
        @else
            <!-- SERVISER SAM SEBI DODELJUJE  -->
            <div class="flex mt-4">
                <div>
                    <p>Korisnik: <span class="font-bold">{{ $dodeljenUserInfo->name }}</span> &nbsp;&nbsp;&nbsp; Pozicija: <span class="font-bold">{{ $dodeljenUserInfo->naziv }}</span></p>
                    <p class="text-sm">Lokacija: <span class="font-bold">{{ $dodeljenUserInfo->bl_naziv }}, {{$dodeljenUserInfo->bl_mesto}}</span></p>
                </div>
            </div>
            
        @endif

        <p>Odredi prioritet tiketa:</p>
        <div class="flex mt-4">
            @foreach (App\Models\BankomatTiketPrioritetTip::prList() as $value)
                @if($prioritetTiketa == $value->id)
                    <span class="flex-none py-2 px-4 mx-2 font-bold rounded bg-{{$value->tr_bg_collor}} text-{{$value->btn_collor}}">{{ $value->btpt_naziv }}</span>
                @else
                    <button wire:click="setPrioritetInfo(' {{ $value->id }} ')" class="flex-none bg-{{ $value->btn_collor }} hover:bg-{{$value->btn_hover_collor}} text-white font-bold py-2 px-4 rounded mx-2">
                        {{ $value->btpt_naziv }}
                    </button>
                @endif
            @endforeach
        </div>
        
        <div>
            @if($prioritetTiketa)
                <div class="bg-{{$prioritetInfo->tr_bg_collor}} border border-{{$prioritetInfo->btn_collor}} text-{{$prioritetInfo->btn_collor}} px-4 py-3 rounded relative my-4" role="alert">
                    <p class="">Prioritet tiketa:
                    <span class="font-bold block sm:inline">{{ $prioritetInfo->btpt_naziv }}</span><br /> {{ $prioritetInfo->btpt_opis }}
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <x-heroicon-c-exclamation-triangle class="fill-{{$prioritetInfo->btn_collor}} h-6 w-6 " />
                    </span>
                    </p>
                </div>
            @endif
            @error('prioritetTiketa') <span class="error">{{ $message }}</span> @enderror
        </div> 
        @if ($dodeljenUserId && $prioritetTiketa)
            <hr class="my-4" />
            <div class="flex justify-center items-center">
                
                <button class="flex text-lg font-bold bg-sky-100 text-sky-900 uppercase border border-sky-900 rounded-md p-1.5 hover:bg-gray-700 hover:text-white" wire:click="newTicket()">
                    <x-icon-ticket-plus class="fill-current w-6 h-6 ml-2"/>
                    <span class="mx-2">Kreiraj tiket</span>
                </button>
            </div>
            
        @endif

    @endif
</div>
