<div>
    <div class="font-semibold text-xl text-gray-800 ml-4 mt-10"> Statistika licenci - prikaz prema datumu isteka licence<span class="text-gray-600 ml-2">( {{$grafikNaslov}} )</span></div>
    <div>Privremene licence su prikazane u mesecima u kojima ističu kao privremene.</div>
    <div class="flex flex-col">
        <div class="flex items-center justify-between px-4 py-3 text-right sm:px-6">
            <div class="flex font-semibold text-xl">
                <div class="flex ml-2 pr-2 text-red-800">
                    <svg class="fill-red-800 w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M160 80c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-352zM0 272c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 160c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48L0 272zM368 96l32 0c26.5 0 48 21.5 48 48l0 288c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48z"/></svg>
                    Istekle licence {{ $broj_istekilh }}
                </div> 
                <div class="flex ml-4 pr-2 text-emerald-600">
                    <svg class="fill-emerald-600 w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M160 80c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-352zM0 272c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 160c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48L0 272zM368 96l32 0c26.5 0 48 21.5 48 48l0 288c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48z"/></svg>
                    Aktivne licence {{ $broj_aktivnih }}
                </div> 
                <div class="flex ml-4 pr-2 text-yellow-300">
                    <svg class="fill-yellow-300 w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M160 80c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-352zM0 272c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 160c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48L0 272zM368 96l32 0c26.5 0 48 21.5 48 48l0 288c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48z"/></svg>
                    Privremene licence {{ $broj_privremenih }}
                </div> 
            </div>
        </div>
            <div class="">
                <div class="">
                <canvas class="p-1" id="myChart2"></canvas>
            </div>
        </div>
    </div>


    <!-- Required chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        const ctx2 = document.getElementById('myChart2');

        const color_1 = 'rgb(5, 150, 105)'; //bg-emerald-600
        const color_2 = 'rgb(153, 27, 27)'; //bg-red-800
        const color_3 = 'rgb(253, 224, 71)'; //bg-yellow-300

        const labels2 = [];
        const dataset_1 = [];
        const dataset_2 = [];
        const bgcollors2 = [];

        @foreach($data as $item)
            labels2.push("{{ $item->month }} - {{ $item->year }}");
            dataset_1.push("{{ $item->data }}");
            dataset_2.push("{{ $item->privremene }}");
            bgcollors2.push( @if($item->istekla) color_1 @else color_2 @endif );
        @endforeach

        const data2 = {
            labels: labels2,
            datasets: [
                {
                    label: [],
                    data: dataset_1 ,
                    //borderColor: ["rgb(75, 75, 75)"],
                    backgroundColor: bgcollors2,
                    borderWidth: 0,
                    borderRadius: 2,
                    borderSkipped: false,
                },
                {
                    label: [],
                    data: dataset_2 ,
                    //borderColor: ["rgb(75, 75, 75)"],
                    backgroundColor: [color_3],
                    borderWidth: 0,
                    borderRadius: 2,
                    borderSkipped: false,
                }
            ]
        };

        const config2 = {
            type: 'bar',
            data: data2,
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

        var myChart2 = new Chart(ctx2, config2);
        
    </script>
</div>
