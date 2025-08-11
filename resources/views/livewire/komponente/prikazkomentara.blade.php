<div class="w-full">
    <div class="py-1">
            <svg class="fill-slate-500 w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path d="M416 176C416 78.8 322.9 0 208 0S0 78.8 0 176c0 39.57 15.62 75.96 41.67 105.4c-16.39 32.76-39.23 57.32-39.59 57.68c-2.1 2.205-2.67 5.475-1.441 8.354C1.9 350.3 4.602 352 7.66 352c38.35 0 70.76-11.12 95.74-24.04C134.2 343.1 169.8 352 208 352C322.9 352 416 273.2 416 176zM599.6 443.7C624.8 413.9 640 376.6 640 336C640 238.8 554 160 448 160c-.3145 0-.6191 .041-.9336 .043C447.5 165.3 448 170.6 448 176c0 98.62-79.68 181.2-186.1 202.5C282.7 455.1 357.1 512 448 512c33.69 0 65.32-8.008 92.85-21.98C565.2 502 596.1 512 632.3 512c3.059 0 5.76-1.725 7.02-4.605c1.229-2.879 .6582-6.148-1.441-8.354C637.6 498.7 615.9 475.3 599.6 443.7z"/></svg>
    </div> 
    <div>
        <div class="flex justify-between">
            <div class="mt-2 font-bold">Komentari: </div>
            <div class="font-light">{{$selectedTerminalComments->count()}}</div>
        </div>
        @if($selectedTerminalComments->count())
            @foreach($selectedTerminalComments as $komentar)
                <div class="py-2">
                    <div class="flex justify-between">
                        <div class="flex">
                            <svg class="float-left fill-slate-600 w-4 h-4 mr-2 mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224 256c70.7 0 128-57.31 128-128s-57.3-128-128-128C153.3 0 96 57.31 96 128S153.3 256 224 256zM274.7 304H173.3C77.61 304 0 381.6 0 477.3c0 19.14 15.52 34.67 34.66 34.67h378.7C432.5 512 448 496.5 448 477.3C448 381.6 370.4 304 274.7 304z"/></svg>
                            <div class="font-bold">{{ $komentar->user_name }}</div> 
                            <div class="text-sm ml-4">{{ App\Http\Helpers::datumFormat($komentar->created_at) }}</div>
                        </div>
                        @if($canEdit)
                            <button wire:click="obrisiKomentar({{$komentar->id}})" class="mr-4 p-1 rounded-lg text-red-500 hover:text-white hover:bg-red-400 cursor-pointer"><svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg></button>
                        @endif
                    </div>
                    <div class=" border rounded-lg border-slate-50 bg-slate-200 px-2 py-1 ml-10">{!! nl2br(e($komentar->comment)) !!}</div>
                </div>
            @endforeach
        @endif
    </div> 
    
    @if($canEdit)
        <div class="mt-4">
            <x-jet-label for="newKoment" value="{{ __('Dodaj komentar:') }}" />
            <x-jet-textarea id="newKoment" type="textarea" class="mt-1 block w-full disabled:opacity-50" wire:model.defer="newKoment" />
            @error('newKoment') <span class="error">{{ $message }}</span> @enderror
            <x-jet-secondary-button wire:click="posaljiKomentar" wire:loading.attr="disabled" class="mt-2">
                {{ __('Posalji komentar') }}
            </x-jet-secondary-button>
        </div> 
    @endif
</div>
