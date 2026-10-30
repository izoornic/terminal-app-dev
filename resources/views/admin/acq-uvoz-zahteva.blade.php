<x-app-layout>
    <x-slot name="header">
        <h2 class="flex justify-between mb-0 font-semibold text-xl text-gray-800 leading-tight">
            <div class="flex">
                <span class="mr-2">
                    <x-heroicon-o-document-arrow-up class="w-6 h-6 mr-1"/>
                </span>
                Uvoz zahteva banke
            </div>
            @if (\App\Models\PozicijaPrikazStranica::isRoleHasRightToAccess(auth()->user()->pozicija_tipId, \App\Http\Livewire\TerminalAcq\AcqTerminali::ROUTE_NAME))
                <div class="flex justify-end mr-4">
                    <a href="{{ route(\App\Http\Livewire\TerminalAcq\AcqTerminali::ROUTE_NAME) }}" class="flex bg-gray-200 text-sm text-gray-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white" title="ACQ terminali">
                        <x-heroicon-o-arrow-left class="w-5 h-5 ml-1" />
                        <span class="mx-2">ACQ terminali</span>
                    </a>
                </div>
            @endif
        </h2>
    </x-slot>

    <div class="pt-6 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                @livewire('terminal-acq.uvoz-zahteva')
            </div>
        </div>
    </div>
    @include('admin.footer')
</x-app-layout>
