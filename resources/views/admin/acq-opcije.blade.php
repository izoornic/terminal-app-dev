<x-app-layout>
    <x-slot name="header">
        <h2 class="flex justify-between mb-0 font-semibold text-xl text-gray-800 leading-tight">
            <div class="flex">
                <span class="mr-2">
                    <x-heroicon-o-adjustments-horizontal class="w-6 h-6 mr-1"/>
                </span>
                Opcije ACQ terminala
            </div>
            <div class="flex">
                @if (\App\Models\PozicijaPrikazStranica::isRoleHasRightToAccess(auth()->user()->pozicija_tipId, \App\Http\Livewire\TerminalAcq\AcqTerminali::ROUTE_NAME))
                    <div class="flex justify-end mr-4">
                        <a href="{{ route(\App\Http\Livewire\TerminalAcq\AcqTerminali::ROUTE_NAME) }}" class="flex bg-gray-200 text-sm text-gray-700 uppercase border rounded-md p-1.5 hover:bg-gray-700 hover:text-white" title="ACQ terminali">
                            <x-heroicon-o-arrow-left class="w-5 h-5 ml-1" />
                            <span class="mx-2">ACQ terminali</span>
                        </a>
                    </div>
                @endif
                <span class="mr-2 pr-2">
                    <livewire:komponente.add-new-item-button
                            btn_name="Nova opcija"
                            btn_event="novaAcqOpcija" />
                </span>
            </div>
        </h2>
    </x-slot>

    <div class="pt-6 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                @livewire('terminal-acq.acq-opcije')
            </div>
        </div>
    </div>
    @include('admin.footer')
</x-app-layout>
