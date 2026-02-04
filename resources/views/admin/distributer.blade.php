<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <div>
            <h2 class="flex font-semibold text-xl text-gray-800 leading-tight">
                <x-icon-distributer class="fill-gray-500 w-6 h-6 mr-2"/>  
                {{ __('Distributeri') }} 
            </h2>
            </div>
            <div class="mr-8">
                    <!-- <a href="{{ route( 'distributeri' ) }}" :active="request()->routeIs('distributeri')"  class= "@if(request()->routeIs('distributeri')) border-b-2 border-sky-600 @else text-gray-500 @endif text-sm mr-8" >
                        {{ __('Distributeri') }}
                    </a>

                    <a href="{{ route( 'distributeri' ) }}" :active="request()->routeIs('distributeri')"  class= "@if(request()->routeIs('distributeri2')) border-b-2 border-sky-600 @else text-gray-500 @endif text-sm text-gray-500 mr-8" >
                         {{ __('Distributeri2') }}
                    </a> -->
            </div>
        </div>
    </x-slot>

    <div class="pt-6 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
              @livewire('distributeri')
            </div>
        </div>
    </div>
    @include('admin.footer')
</x-app-layout>