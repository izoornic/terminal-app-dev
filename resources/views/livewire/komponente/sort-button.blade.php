<button wire:click="sortClick" class="@if($active){{ 'fill-orange-600' }} {{ 'text-orange-600' }}@else {{ 'fill-current' }} {{ 'text-current' }} @endif  flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 focus:outline-none">
        {{ $btn_text }} 
        @if($orderDirection === 'asc')
                <x-icon-sort-down class="float-right w-4 h-4 mr-0 ml-2" />
        @else
                <x-icon-sort-up class="float-right w-4 h-4 mr-0 ml-2" />
        @endif
</button>
   