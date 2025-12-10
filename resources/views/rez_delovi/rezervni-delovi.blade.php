<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        <span class="float-left mr-2 pr-2">
            <x-heroicon-c-cog class="fill-gray-500 w-6 h-6"/>
        </span>    
            {{ __('Rezervni delovi') }}
        </h2>
    </x-slot>

    <div class="pt-6 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
              @livewire('rdelovi.rezervni-delovi')
            </div>
        </div>
    </div>
    @include('admin.footer')
</x-app-layout>