<div class="p-6">

    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-700 flex items-center">
            <x-heroicon-o-building-library class="w-5 h-5 mr-2 text-sky-500" />
            Pregled proizvoda po lokacijama
        </h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse ($sum_of_products as $naziv => $count)
            <div x-data="{ open: false }" class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">

                {{-- Main location header --}}
                <div class="p-5 flex items-center justify-between cursor-pointer" @click="open = !open">
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide">{{ $naziv }}</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1">{{ $count }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $count == 1 ? 'proizvod' : 'proizvoda' }}</p>
                    </div>
                    <div class="flex flex-col items-center ml-4 gap-2">
                        <x-heroicon-o-building-library class="w-10 h-10 text-sky-300" />
                        <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400 transition-transform duration-200" ::class="{ 'rotate-180': open }" />
                    </div>
                </div>

                {{-- Sub-locations list --}}
                <div x-show="open" x-transition class="border-t border-gray-100 px-5 py-3">
                    @if (!empty($sub_locations_products[$naziv]))
                        <ul class="space-y-2">
                            @foreach ($sub_locations_products[$naziv] as $sub_naziv => $sub_data)
                                <li class="border-b border-gray-50 last:border-0 pb-2">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-700 font-medium">{{ $sub_naziv }}</span>
                                        <span class="font-semibold text-gray-800 ml-2">{{ $sub_data['total'] }}</span>
                                    </div>
                                    @if (!empty($sub_data['tips']))
                                        <ul class="mt-1 ml-2 space-y-0.5">
                                            @foreach ($sub_data['tips'] as $model => $tip_count)
                                                <li class="flex justify-between text-xs text-gray-400">
                                                    <span>{{ $model }}</span>
                                                    <span class="ml-2">{{ $tip_count }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-xs text-gray-400">Nema podlokacija.</p>
                    @endif
                </div>

            </div>
        @empty
            <div class="col-span-4 text-center text-gray-400 py-10">
                Nema lokacija za prikaz.
            </div>
        @endforelse
    </div>

    {{-- Total summary --}}
    @if ($sum_of_products && count($sum_of_products) > 0)
        <div class="mt-6 flex justify-end">
            <div class="bg-gray-50 border border-gray-200 rounded-lg px-6 py-3 flex items-center gap-3">
                <x-heroicon-o-calculator class="w-5 h-5 text-gray-400" />
                <span class="text-sm text-gray-500">Ukupno svih proizvoda:</span>
                <span class="text-lg font-bold text-gray-800">{{ array_sum($sum_of_products) }}</span>
            </div>
        </div>
    @endif

</div>
