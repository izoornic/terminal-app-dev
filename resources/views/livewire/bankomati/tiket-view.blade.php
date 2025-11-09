<div>
    <div class="mx-4 my-4">
        <div class="bg-{{$prioritet->tr_bg_collor}} border-t-4 border-{{$prioritet->btn_collor}} rounded-b text-slate-600 px-4 py-3 shadow-md mb-6 mx-2 my-2" role="alert">
			<div class="flex justify-between"> 
				<div class="flex">
					<div class="py-1">
                        <x-heroicon-o-ticket class="text-{{$prioritet->btn_collor}} w-5 h-5 mr-2" />
                    </div>
					<div>
						<div>Prioritet: <span class="font-bold text-{{$prioritet->btn_collor}}">{{$prioritet->tp_naziv}}</span> &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; 
                            Status: <span class="font-bold text-{{$prioritet->btn_collor}}">{{$tiket->status}}</span></div>
						<table class="min-w-full divide-y divide-gray-200" style="width: 100% !important">
							<tr>
								<td class="px-2">Kreiran:</td>
								<td><span class="font-bold">{{ App\Http\Helpers::datumFormat($tiket->created_at) }}</span></td>
								<td class="px-2 pl-4">
                                    <x-heroicon-o-user-circle class="w-6 h-6 mr-2" />
                                </td>
                                <td>otvorio:</td>
								<td class="px-2"><span class="font-bold">@if($userKreirao != '') {{$userKreirao->name}} @else {{ 'Kreiran online' }} @endif</span></td>
							</tr>
							<tr>
								<td class="px-2">Poslednja promena:</td>
								<td><span class="font-bold">{{ App\Http\Helpers::datumFormat($tiket->updated_at) }}</span></td>
								<td class="px-2 pl-4">
                                    <x-heroicon-o-user-circle class="w-6 h-6 mr-2" />
                                </td>
                                <td>dodeljen:</td>
								<td class="px-2">
                                    @if($userDodeljen)
                                        <span class="font-bold">{{$userDodeljen->name}} </span>
                                        
                                        @if($tiket->status != "Zatvoren" && $role_region['role'] != 'serviser') 
                                        
                                            <x-jet-secondary-button wire:click="dodeliTiketShowModal" title='dodeli'>
                                                @if($userDodeljen->name == '') {{ 'Dodeli tiket' }} 
                                                @else {{ 'promeni' }} 
                                                @endif
                                            </x-jet-secondary-button>
                                            
                                        @endif
                                    @else
                                        <span class="font-bold">Kreiran online</span>
                                        <x-jet-secondary-button wire:click="dodeliTiketShowModal" title='dodeli'>{{ 'Dodeli tiket' }} 
                                        </x-jet-secondary-button>
                                    @endif
                                </td>
							</tr>
						</table>
                        {{-- @if($kreiranOlineInfo)
                        <div class="my-4">  Prijava ime: <span class="font-bold">{{ $kreiranOlineInfo->prijava_ime }}</span> <br />
                                            Prijava telefon: <span class="font-bold"> +{{ $kreiranOlineInfo->prijava_tel }}</span>
                        </div>
                        @endif --}}
                        <div>Kvar: <span class="font-bold text-{{$prioritet->btn_collor}}">{{$kvarTipNaziv}}</div>
                        <div class="my-4 pr-4">Opis: {{$tiket->opis}}</div>
                        <div class="my-4 pr-4">
                        </div>
					</div>
				</div>
        
				<div>
                    @if($tiket->status != "Zatvoren") 
                        <x-jet-danger-button wire:click="zatvoriTiketShowModal" title='Zatvori tiket'>
                            <x-heroicon-s-x-circle class="fill-current w-5 h-5 mr-2" /> zatvori tiket</x-jet-button> 
                    @else 
                        <a href="{{ route( 'bankomat-tiketi' ) }}">
                            <span class="flex-none py-2 px-4 mx-2 font-bold rounded bg-{{$prioritet->tr_bg_collor}} text-{{$prioritet->btn_collor}}">TIKETI</span>
                        </a>
                        <br />
                        <p>Tiket zatvorio: 
                            <span class="font-bold">@if($this->uderZatvorio){{ $this->uderZatvorio->name }} @endif</span>
                        </p>
                    @endif

                    @if($role_region['role'] == 'admin' && $tiket->status == "Zatvoren")
                        <div class="py-4">
                            <x-jet-button wire:click="obrisiTiketShowModal" title='Obrisi tiket'>
                                <x-heroicon-s-trash class="fill-current w-5 h-5 mr-2"/>
                                obriši tiket
                            </x-jet-button>
                        </div>
                    @endif
                </div>
            </div> 
        </div> 
    </div>
    <div class="flex mx-4 my-4">
        <div class="w-2/5 flex-initial mx-2 my-2">
            <livewire:bankomati.komponente.bankomat-info :bankomat_lokacija_id="$bankomat_lokacija_id" :key="time()" />
           
            <div class="border-t-4 border-sky-500 rounded-b text-sky-900 px-4 py-3 shadow-md mb-6" role="alert">
                <div class="flex mt-2 mb-6">
                    <x-icon-history class="fill-current w-4 h-4 mr-0 mr-4 mt-1"/>
                    <span class="font-bold">Istorija proizvoda:</span>
                </div>
                <livewire:bankomati.komponente.bankomat-history :bankomat_lokacija_id="$bankomat_lokacija_id" />
                
            </div>
        </div>
        
        <livewire:bankomati.komponente.tiket-komentari :tiketid="$tikid" :tiket_status="$tiket->status" />
    </div>

    
    {{-- Zatvori tiket MODAL #################################################### --}}
    <x-jet-dialog-modal wire:model="modalZatvoriTiketVisible">
        <x-slot name="title">
        <svg class="fill-current float-left w-6 h-4 mr-4 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 384"><path d="M576,208V128a64,64,0,0,0-64-64H64A64,64,0,0,0,0,128v80a48,48,0,0,1,48,48A48,48,0,0,1,0,304v80a64,64,0,0,0,64,64H512a64.06,64.06,0,0,0,64-64V304a48,48,0,0,1,0-96ZM446.78,370.78l-44,44L288,300,173.22,414.78l-44-44L244,256,129.22,141.22l44-44L288,212,402.78,97.22l44,44L332,256Z" transform="translate(0 -64)"/></svg>
        Zatvori tiket
    </x-slot>

        <x-slot name="content">
            <p class="font-bold my-4">Da li ste sigurni da želite da zatvorite tiket #{{ $tikid }} ?</p>
            <hr/>
            <x-jet-label for="zatvori_komentar" value="Dodaj komentar:" />
            <x-jet-textarea id="zatvori_komentar" type="textarea" class="mt-1 block w-full disabled:opacity-50" wire:model.defer="zatvori_komentar" />
            @error('zatvori_komentar') <span class="error">{{ $message }}</span> @enderror
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalZatvoriTiketVisible')" wire:loading.attr="disabled">
                Otkaži
            </x-jet-secondary-button>
            <x-jet-danger-button class="ml-2" wire:click="closeTiket" wire:loading.attr="disabled">
                Zatvori tiket
            </x-jet-danger-button>         
        </x-slot>
    </x-jet-dialog-modal>

    {{-- Promeni dodeljenog MODAL --}}
    <x-jet-dialog-modal wire:model="modalDodeliTiketVisible">
        <x-slot name="title">
        <svg class="float-left fill-current w-4 h-4 mr-0 mt-1 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Pro 6.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M224 256c70.7 0 128-57.31 128-128s-57.3-128-128-128C153.3 0 96 57.31 96 128S153.3 256 224 256zM274.7 304H173.3C77.61 304 0 381.6 0 477.3c0 19.14 15.52 34.67 34.66 34.67h378.7C432.5 512 448 496.5 448 477.3C448 381.6 370.4 304 274.7 304z"/></svg>
        Dodeli tiket korisniku
        </x-slot>

        <x-slot name="content">
            @if(!$noviDodeljenUserId)
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
                    @error('noviDodeljenUserId') <span class="error">{{ $message }}</span> @enderror
                </div>

            @else
            
                <div class="mt-4">Korisnik:</div>
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
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('modalDodeliTiketVisible')" wire:loading.attr="disabled">
                Otkaži
            </x-jet-secondary-button>
            <x-jet-danger-button class="ml-2" wire:click="changeUser" wire:loading.attr="disabled">
                Dodeli
            </x-jet-danger-button> 
            
        </x-slot>
    </x-jet-dialog-modal>

    
    {{-- Obrisi tiket MODAL --}}
    <x-jet-dialog-modal wire:model="obrisiTiketModalVisible">
        <x-slot name="title">
        <svg class="fill-current float-left w-6 h-4 mr-4 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Pro 6.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M135.2 17.69C140.6 6.848 151.7 0 163.8 0H284.2C296.3 0 307.4 6.848 312.8 17.69L320 32H416C433.7 32 448 46.33 448 64C448 81.67 433.7 96 416 96H32C14.33 96 0 81.67 0 64C0 46.33 14.33 32 32 32H128L135.2 17.69zM31.1 128H416V448C416 483.3 387.3 512 352 512H95.1C60.65 512 31.1 483.3 31.1 448V128zM111.1 208V432C111.1 440.8 119.2 448 127.1 448C136.8 448 143.1 440.8 143.1 432V208C143.1 199.2 136.8 192 127.1 192C119.2 192 111.1 199.2 111.1 208zM207.1 208V432C207.1 440.8 215.2 448 223.1 448C232.8 448 240 440.8 240 432V208C240 199.2 232.8 192 223.1 192C215.2 192 207.1 199.2 207.1 208zM304 208V432C304 440.8 311.2 448 320 448C328.8 448 336 440.8 336 432V208C336 199.2 328.8 192 320 192C311.2 192 304 199.2 304 208z"/></svg> 
        Obriši tiket
    </x-slot>

        <x-slot name="content">
            <p class="font-bold my-4">Da li ste sigurni da želite da obrišete tiket #{{ $tikid }} ?</p>
            <hr/>
            <p class="py-4"><svg class="fill-red-600 float-left w-4 h-4 mr-4 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M256 0C114.6 0 0 114.6 0 256s114.6 256 256 256s256-114.6 256-256S397.4 0 256 0zM232 152C232 138.8 242.8 128 256 128s24 10.75 24 24v128c0 13.25-10.75 24-24 24S232 293.3 232 280V152zM256 400c-17.36 0-31.44-14.08-31.44-31.44c0-17.36 14.07-31.44 31.44-31.44s31.44 14.08 31.44 31.44C287.4 385.9 273.4 400 256 400z"/></svg>Brisanjem tiketa brišete sve dodate komentare.</p> 
            <p><svg class="fill-red-600 float-left w-4 h-4 mr-4 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M256 0C114.6 0 0 114.6 0 256s114.6 256 256 256s256-114.6 256-256S397.4 0 256 0zM232 152C232 138.8 242.8 128 256 128s24 10.75 24 24v128c0 13.25-10.75 24-24 24S232 293.3 232 280V152zM256 400c-17.36 0-31.44-14.08-31.44-31.44c0-17.36 14.07-31.44 31.44-31.44s31.44 14.08 31.44 31.44C287.4 385.9 273.4 400 256 400z"/></svg>Tiket će biti vidljiv u istoriji terminala bez mogućnosti pregleda!</p>
        </x-slot>

        <x-slot name="footer">
        <x-jet-secondary-button wire:click="$toggle('obrisiTiketModalVisible')" wire:loading.attr="disabled">
                Otkaži
            </x-jet-secondary-button>
                <x-jet-danger-button class="ml-2" wire:click="deleteTiket" wire:loading.attr="disabled">
                   Obriši tiket
                </x-jet-danger-button>         
        </x-slot>
    </x-jet-dialog-modal>
</div>
