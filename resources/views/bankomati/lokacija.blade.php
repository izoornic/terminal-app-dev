<x-app-layout>
    <x-slot name="header">
        <h2 class="flex justify-between mb-0 font-semibold text-xl text-gray-800 leading-tight">
            <div class="flex">
                    <x-heroicon-o-map-pin class="text-current w-6 h-6 mr-2" />
                   Lokacije

            </div>
            <div class="max-h-6" >
                <span class="mr-2 pr-2"> 
                    <livewire:komponente.add-new-item-button 
                            btn_name="Nova lokacija"
                            btn_event="newLocation" />
                </span>
            </div>
        </h2>
    </x-slot>

    <div class="pt-6 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            @livewire('bankomati.lokacije')
            </div>
        </div>
    </div>
    @include('admin.footer')
</x-app-layout>