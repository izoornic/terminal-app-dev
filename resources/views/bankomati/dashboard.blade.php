<x-app-layout>
    <x-slot name="header">
        <h2 class="flex justify-between mb-0 font-semibold text-xl text-gray-800 leading-tight">
            <div class="flex">
                <x-heroicon-o-home class="w-6 h-6 mr-2" />
                   Početna
            </div>
        </h2>
    </x-slot>

    <div class="pt-6 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            @livewire('bankomati.dashboard')
            </div>
        </div>
    </div>
    @include('admin.footer')
</x-app-layout>