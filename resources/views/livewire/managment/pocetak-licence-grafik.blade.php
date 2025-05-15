<div>
<div class="font-semibold text-xl text-gray-800 ml-4"> Statistika licenci - prikaz prema datumu isteka licence <span class="text-gray-600 ml-2">( {{$grafikNaslov}} )</span></div>
<div>Privremene licence su prikazane u mesecima u kojima će isteći trajna licenca (aktivna) kada privremene licence budu razdužene od strane Zeta Systema i postanu trajne. Sve privremene licence traju od dana izdavanja do kraja kalendarskog meseca, plus period prekoračenja.</div>
    <div class="flex flex-col mb-10">
        <div class="flex items-center justify-between px-4 py-3 text-right sm:px-6">
            <div class="flex font-semibold text-xl">
                <!-- <div class="flex ml-2 pr-2 text-red-800">
                    <svg class="fill-red-800 w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M160 80c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-352zM0 272c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 160c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48L0 272zM368 96l32 0c26.5 0 48 21.5 48 48l0 288c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48z"/></svg>
                    Istekle licence {{ $broj_istekilh }}
                </div>  -->
                <div class="flex ml-4 pr-2 text-emerald-600">
                    <svg class="fill-emerald-600 w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M160 80c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-352zM0 272c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 160c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48L0 272zM368 96l32 0c26.5 0 48 21.5 48 48l0 288c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48z"/></svg>
                    Produžene licence {{ $broj_istekilh }}
                </div> 
                <div class="flex ml-4 pr-2 text-yellow-300">
                    <svg class="fill-yellow-300 w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M160 80c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-352zM0 272c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 160c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48L0 272zM368 96l32 0c26.5 0 48 21.5 48 48l0 288c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48z"/></svg>
                    Nove licence {{ $broj_privremenih }}
                </div> 
            </div>
        </div>
            <div class="">
                <div class="">
                <canvas class="p-1" id="NaplataGrafik"></canvas>
            </div>
        </div>
    </div>

    <!-- Required chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        const ctx = document.getElementById('NaplataGrafik');

        const color1 = 'rgb(5, 150, 105)'; //bg-emerald-600
        const color2 = 'rgb(153, 27, 27)'; //bg-red-800
        const color3 = 'rgb(253, 224, 71)'; //bg-yellow-300

        const labels = [];
        const dataset = [];
        const dataset2 = [];
        const bgcollors = [];
        @foreach($data as $item)
            labels.push("{{ $item->month }} - {{ $item->year }}");
            dataset.push("{{ $item->data }}");
            dataset2.push("{{ $item->nove }}");
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

        var NaplataGrafik = new Chart(ctx, config);
    </script>
</div>
