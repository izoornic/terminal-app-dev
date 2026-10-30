{{-- Podaci banke (ACQ) o trgovcu: Lokacije ih menjaju, Distributer ih samo prikazuje ($samoPrikaz). --}}
<div class="mt-4 p-3 border border-sky-200 rounded-md bg-sky-50">
    <div class="text-sm font-semibold text-gray-700 mb-2">Podaci banke (ACQ)</div>
    @if ($samoPrikaz ?? false)
        <div class="text-sm">
            MID: <span class="font-bold">{{ $acq_mid ?: '—' }}</span> · MCC: <span class="font-bold">{{ $mcc ?: '—' }}</span> · PTT: <span class="font-bold">{{ $ptt ?: '—' }}</span>
            @if ($precizna_lokacija)
                <div>Precizna lokacija: <span class="font-bold">{{ $precizna_lokacija }}</span></div>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <x-jet-label for="acq_mid" value="ACQ MID" />
                <x-jet-input wire:model="acq_mid" id="acq_mid" class="block mt-1 w-full" type="text" />
                @error('acq_mid') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div>
                <x-jet-label for="mcc" value="MCC" />
                <x-jet-input wire:model="mcc" id="mcc" class="block mt-1 w-full" type="text" />
                @error('mcc') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div>
                <x-jet-label for="ptt" value="PTT" />
                <x-jet-input wire:model="ptt" id="ptt" class="block mt-1 w-full" type="text" />
                @error('ptt') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="mt-4">
            <x-jet-label for="precizna_lokacija" value="Precizna lokacija (adresa ili plus kod)" />
            <x-jet-input wire:model="precizna_lokacija" id="precizna_lokacija" class="block mt-1 w-full" type="text" />
            @error('precizna_lokacija') <span class="error">{{ $message }}</span> @enderror
        </div>
    @endif
</div>
