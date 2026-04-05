<x-app-layout>
    <x-slot name="header">
    <h2 class="flex justify-between mb-0 py-1 font-semibold text-xl text-gray-800 leading-tight">
        <div>
            <span class="float-left mr-2 pr-2">
                <x-icon-terminal class="fill-gray-500 w-6 h-6" />
            </span>
            {{ __('Terminali') }} 
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
            @livewire('distributer.dist-terminal')
            </div>
        </div>
    </div>
    @include('admin.footer')
</x-app-layout>