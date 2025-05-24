<div>

<div class="font-semibold text-xl text-gray-800 ml-4"> Statistika licenci - prikaz prema datumu početka licence</div>
<div>Istekle licence ne ulaze u ukupni broj licenci već su samo dodatak (informacija) o tome koliko je licenci koje su istekle od ukupnog broja licenci koje su dodate/produžene u datom mesecu.<br /></div>
    <div class="flex flex-col mb-10">
        <div class=""> 
            <div class="flex mt-4 mb-4">
                <label class="inline-flex items-center me-5 cursor-pointer">
                    <input type="checkbox" value="produzene" class="sr-only peer" onChange="changeBarVisability('produzene')" checked>
                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-yellow-300 dark:peer-focus:ring-yellow-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-yellow-400 dark:peer-checked:bg-yellow-400"></div>
                </label>
                <a class="font-semibold text-xl cursor-pointer flex ml-2 mr-6 pr-2 text-yellow-400 hover:text-gray-600" wire:click="$emit('chartClicked', 'januar - 2000 - 0')">
                        <svg class="fill-current w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M160 80c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-352zM0 272c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 160c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48L0 272zM368 96l32 0c26.5 0 48 21.5 48 48l0 288c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48z"/></svg>
                        Produžene licence {{ $broj_produzenih }}
                </a>

                <label class="inline-flex items-center me-5 cursor-pointer">
                    <input type="checkbox" value="nove" class="sr-only peer" onChange="changeBarVisability('nove')" checked>
                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-teal-300 dark:peer-focus:ring-teal-800 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-teal-600 dark:peer-checked:bg-teal-600"></div>
                </label>
                <a class="font-semibold text-xl cursor-pointer flex ml-2 mr-6 pr-2 text-emerald-600 hover:text-gray-600" wire:click="$emit('chartClicked', 'januar - 2000 - 1')">
                    <svg class="fill-current w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M160 80c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-352zM0 272c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 160c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48L0 272zM368 96l32 0c26.5 0 48 21.5 48 48l0 288c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48z"/></svg>
                    Nove licence {{ $broj_novih }}
                </a> 

                <label class="inline-flex items-center me-5 cursor-pointer">
                    <input type="checkbox" value="istekle" class="sr-only peer" onChange="changeBarVisability('istekle')" checked>
                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-800 dark:peer-checked:bg-red-800"></div>
                </label>
                <a class="font-semibold text-xl cursor-pointer flex ml-2 mr-6 pr-2 text-red-800 hover:text-gray-600" wire:click="$emit('chartClicked', 'januar - 2000 - 2')">
                    <svg class="fill-current w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M160 80c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-352zM0 272c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 160c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48L0 272zM368 96l32 0c26.5 0 48 21.5 48 48l0 288c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48z"/></svg>
                    Istekle licence {{ $broj_istekilh }}
                </a> 
            </div>
        </div>
            <div class="">
                <div class="">
                <canvas class="p-1" id="NaplataGrafik" ></canvas>
            </div>
        </div>
    </div>

    <!-- Required chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        const ctx = document.getElementById('NaplataGrafik');

        const color3 = 'rgb(5, 150, 105)'; //bg-emerald-600
        const color2 = 'rgb(153, 27, 27)'; //bg-red-800
        const color1 = 'rgb(251, 191, 36)'; //bg-yellow-400

        const labels = [];
        const dataset = [];
        const dataset2 = [];
        const dataset3 = [];
        const bgcollors = [];
        @foreach($data as $item)
            labels.push("{{ $item->month }} - {{ $item->year }}");
            dataset.push("{{ $item->data }}");
            dataset2.push("{{ $item->nove }}");
            dataset3.push("{{ $item->istekla }}");
            //bgcollors.push( @if($item->istekla) color1 @else color2 @endif );                
        @endforeach

        const data = {
            labels: labels,
            datasets: [
                {
                    label: [],
                    data: dataset ,
                    //borderColor: ["rgb(75, 75, 75)"],
                    backgroundColor: [color1],//bgcollors,
                    borderWidth: 0,
                    borderRadius: 2,
                    borderSkipped: false,
                },
                {
                    label: [],
                    data: dataset2 ,
                    //borderColor: ["rgb(75, 75, 75)"],
                    backgroundColor: [color3],
                    borderWidth: 0,
                    borderRadius: 2,
                    borderSkipped: false,
                },
                {
                    label: [],
                    data: dataset3 ,
                    //borderColor: ["rgb(75, 75, 75)"],
                    backgroundColor: [color2],
                    borderWidth: 0,
                    borderRadius: 2,
                    borderSkipped: false,
                }
            ]
        };

        const config = {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                    title: {
                        display: false,
                    }
                },
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true
                    }
                },
            },
        };

        ctx.onclick = function(e) {
            const points = NaplataGrafik.getElementsAtEventForMode(e, 'nearest', { intersect: true }, false);
            if (points.length) {
                const firstPoint = points[0];
                const label = NaplataGrafik.data.labels[firstPoint.index];
                const value = NaplataGrafik.data.datasets[firstPoint.datasetIndex].data[firstPoint.index];
                Livewire.emit('chartClicked', label+'-'+firstPoint.datasetIndex);
                //console.log(label + ': ' + value + ' - ' + firstPoint.datasetIndex);
            }
        };

        var NaplataGrafik = new Chart(ctx, config);

        function changeBarVisability(type) {
            if (type === 'produzene') {
                NaplataGrafik.data.datasets[0].hidden = !NaplataGrafik.data.datasets[0].hidden;
            } else if (type === 'nove') {
                NaplataGrafik.data.datasets[1].hidden = !NaplataGrafik.data.datasets[1].hidden;
            } else if (type === 'istekle') {
                NaplataGrafik.data.datasets[2].hidden = !NaplataGrafik.data.datasets[2].hidden;
            }
            NaplataGrafik.update();
        }
    </script>
</div>
