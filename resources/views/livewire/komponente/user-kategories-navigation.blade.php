<div class="flex">
    @foreach(App\Models\PozicijaKategoriy::orderBy('menu_order')->get() as $kategorija)
        @if($kategorija->id == $selectedKatId)
            <div class="px-4 py-2 m-1 text-sm text-grey-300 border-b-2 border-indigo-400">{{ $kategorija->kat_name }}</div>
        @else
            <button wire:click="$emit('izaberiKategoriju', {{ $kategorija->id }})" class="px-4 py-2 m-1 text-sm text-grey-200 hover:border-b-2 hover:border-gray-200">
                {{ $kategorija->kat_name }}
            </button>
        @endif
    @endforeach
</div>
