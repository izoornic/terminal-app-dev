<div class="bg-slate-100 border-t-4 border-slate-500 rounded-b text-slate-900 px-4 py-3 shadow-md mb-6 w-3/5 flex-initial mx-2 my-2" role="alert">
    <div class="flex">
        <div class="py-1">
            <x-heroicon-o-chat-bubble-left-right class="text-slate-500 w-6 h-6 mr-2"/>
        </div> 
        <div class="w-full">
            <div class="flex justify-between">
                <div class="mt-2 font-bold">Komentari:</div>
                <div class="font-light">{{$komentari->count()}}</div>
            </div>
            @if($komentari->count())
                @foreach($komentari as $komentar)
                    <div class="py-2">
                        
                        <div class="flex justify-between">
                            <div class="flex">
                                <x-heroicon-o-user class="text-slate-600 w-4 h-4 mr-2 mt-1"/>
                                
                                <span class="font-bold">{{ $komentar->name }}</span> <span class="text-sm ml-4">{{ App\Http\Helpers::datumFormat($komentar->created_at) }}</span>
                            </div>
                            @if($user_id == $komentar->user_id && $tiket_status != "Zatvoren")
                                <button wire:click="deleteKomentar({{ $komentar->id }})" class="mr-4 p-1 rounded-lg text-red-500 hover:text-white hover:bg-red-400 cursor-pointer">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            @endif
                        </div>
                        <div class="border rounded-lg border-slate-50 bg-slate-200 px-2 py-1 ml-10">
                            {!! nl2br(e ($komentar->komentar )) !!}
                        </div>
                    </div>
                @endforeach
            @endif
            
            <div class="mt-4">
            @if($tiket_status != "Zatvoren")
                <x-jet-label for="novi_komentar" value="{{ __('Dodaj komentar:') }}" />
                <x-jet-textarea id="novi_komentar" type="textarea" class="mt-1 block w-full disabled:opacity-50" wire:model.defer="novi_komentar" />
                @error('novi_komentar') <span class="error">{{ $message }}</span> @enderror
                <br />
                <x-jet-secondary-button wire:click="posaljiKomentar" wire:loading.attr="disabled" class="mt-2">
                    Posalji komentar
                </x-jet-secondary-button>
            @endif
            </div> 
        </div>
    </div>
</div> 