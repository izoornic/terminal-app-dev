<x-app-layout>
    <x-slot name="header">
        <h2 class="flex justify-between mb-0 font-semibold text-xl text-gray-800 leading-tight">
            <div class="flex">
                <span class="mr-2">
                    <x-heroicon-o-wrench-screwdriver class="w-6 h-6 mr-1"/>
                </span>
                Vrste kvara
            </div>
            <div class="flex">
                <div class="flex justify-end mr-4">
                    <a href="{{ route('tiket') }}" class="flex bg-gray-200 text-sm text-gray-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white" title="Tiketi">
                        <x-heroicon-o-arrow-left class="w-5 h-5 ml-1" />
                        <span class="mx-2">Tiketi</span>
                    </a>
                </div>
                <span class="mr-2 pr-2">
                    <livewire:komponente.add-new-item-button
                            btn_name="Nova vrsta kvara"
                            btn_event="newVrstaKvara" />
                </span>
            </div>
        </h2>
    </x-slot>

    <div class="pt-6 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                @livewire('tiket-vrste-kvara')
            </div>
        </div>
    </div>
    @include('admin.footer')
</x-app-layout>
