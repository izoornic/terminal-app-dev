<x-app-layout>
    <x-slot name="header">
    <h2 class="flex justify-between mb-0 py-1 font-semibold text-xl text-gray-800 leading-tight">
        <div>
            <span class="float-left mr-2">
                <x-heroicon-o-megaphone class="w-6 h-6" />
            </span>
            {{ __('Kampanje') }} 
        </div>
        <div class="max-h-6" >
            <span class="mr-2 pr-2"> 
                <livewire:komponente.add-new-item-button 
                        btn_name="Nova kampanja"
                        btn_event="newCampagin" />
            </span>
        </div>
        <div>
            <span class="mr-2 pr-2 text-sky-600"> 
                <x-icon-distributer class="float-left fill-sky-600 w-6 h-6 mr-2"/>
                {{ App\Models\LicencaDistributerTip::DistributerNameByUserId(auth()->user()->id) }}
            </span>
        </div>
    </h2>
    </x-slot>

    <div class="pt-6 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            @livewire('distributer.dist-campagin')
            </div>
        </div>
    </div>
    @include('admin.footer')
</x-app-layout>