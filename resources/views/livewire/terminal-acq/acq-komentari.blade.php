<div class="w-full text-sm">
    <div class="flex justify-between">
        <div class="font-bold">Komentari TID-a {{ $acq->acq_tid }}</div>
        <div class="font-light">{{ $acq->comments->count() }}</div>
    </div>

    @if ($greska)
        <div class="mt-2 p-2 rounded-md border border-red-300 bg-red-50 text-red-700">{{ $greska }}</div>
    @endif

    @forelse ($acq->comments as $komentar)
        <div class="py-2" wire:key="acq-komentar-{{ $komentar->id }}">
            <div class="flex justify-between">
                <div class="flex">
                    <x-heroicon-s-user class="w-4 h-4 mr-2 mt-0.5 text-slate-600" />
                    <div class="font-bold">{{ $komentar->user?->name ?? '—' }}</div>
                    <div class="ml-4">{{ App\Http\Helpers::datumFormat($komentar->created_at) }}</div>
                </div>
                @if (App\Actions\TerminalAcq\TerminalAcqKomentari::mozeObrisati($komentar, auth()->user(), $mozeUpravljati))
                    <button type="button" wire:click="obrisi({{ $komentar->id }})" wire:confirm="Obrisati komentar?" class="mr-4 p-1 rounded-lg text-red-500 hover:text-white hover:bg-red-400" title="Obriši komentar">
                        <x-heroicon-o-trash class="w-4 h-4" />
                    </button>
                @endif
            </div>
            <div class="border rounded-lg border-slate-50 bg-slate-200 px-2 py-1 ml-6 mt-1">{!! nl2br(e($komentar->comment)) !!}</div>
        </div>
    @empty
        <div class="py-2 text-gray-500">Nema komentara.</div>
    @endforelse

    <div class="mt-3">
        <x-jet-label for="noviKomentar" value="Dodaj komentar:" />
        <x-jet-textarea id="noviKomentar" class="mt-1 block w-full" wire:model="noviKomentar" />
        @error('noviKomentar') <span class="text-red-600">{{ $message }}</span> @enderror
        <x-jet-secondary-button wire:click="posalji" wire:loading.attr="disabled" class="mt-2">Pošalji komentar</x-jet-secondary-button>
    </div>

    @if ($acq->terminalLokacija)
        <div class="mt-6 pt-4 border-t border-gray-200">
            <div class="font-bold mb-1">Komentari uređaja {{ $acq->terminal?->sn }} <span class="font-normal text-gray-500">(samo pregled; menjaju se na stranicama Licence i Distributer)</span></div>
            <livewire:komponente.prikazkomentara :terminalLokacijaId="$acq->terminalLokacija->id" :canEdit="false" :wire:key="'uredjaj-komentari-'.$acq->terminalLokacija->id" />
        </div>
    @endif
</div>
