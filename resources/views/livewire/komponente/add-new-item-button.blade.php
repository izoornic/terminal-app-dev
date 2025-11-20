<div class="p-0 m-0 flex justify-end">
    <button class="flex bg-gray-200 text-sm text-gray-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white" wire:click="btnClick" title="{{ $btn_name }}">
        <x-heroicon-o-plus-circle class="w-5 h-5 ml-1" />
        <span class="mx-2">{{ $btn_name }}</span>
    </button>
</div>