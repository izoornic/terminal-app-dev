<div class="p-6">
    <div class="flex items-center justify-between px-4 py-3 text-right sm:px-6">
        <div class="flex font-semibold text-xl">
        <div class="ml-2 pr-2"><svg class="fill-red-600 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path d="M0 48C0 21.5 21.5 0 48 0H336c26.5 0 48 21.5 48 48V232.2c-39.1 32.3-64 81.1-64 135.8c0 49.5 20.4 94.2 53.3 126.2C364.5 505.1 351.1 512 336 512H240V432c0-26.5-21.5-48-48-48s-48 21.5-48 48v80H48c-26.5 0-48-21.5-48-48V48zM80 224c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V240c0-8.8-7.2-16-16-16H80zm80 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V240c0-8.8-7.2-16-16-16H176c-8.8 0-16 7.2-16 16zm112-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V240c0-8.8-7.2-16-16-16H272zM64 112v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V112c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zM176 96c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V112c0-8.8-7.2-16-16-16H176zm80 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V112c0-8.8-7.2-16-16-16H272c-8.8 0-16 7.2-16 16zm96 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm140.7-67.3c-6.2 6.2-6.2 16.4 0 22.6L521.4 352H432c-8.8 0-16 7.2-16 16s7.2 16 16 16h89.4l-28.7 28.7c-6.2 6.2-6.2 16.4 0 22.6s16.4 6.2 22.6 0l56-56c6.2-6.2 6.2-16.4 0-22.6l-56-56c-6.2-6.2-16.4-6.2-22.6 0z"/></svg></div>
        <div class="text-red-600 float-left">{{ $dist_name }}</div>
        </div>
    </div>
    <div class="font-semibold text-xl text-gray-800 ml-4"> Statistika licenci <span class="text-gray-600 ml-2">( Zeta naplata )</span></div>
    <div class="flex flex-col mb-10">
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
                <canvas class="p-1" id="myChart"></canvas>
            </div>
        </div>
    </div>
    <hr />
    <div class="font-semibold text-xl text-gray-800 ml-4 mt-10"> Statistika licenci <span class="text-gray-600 ml-2">( API )</span></div>
    <div class="flex flex-col">
        <div class="flex items-center justify-between px-4 py-3 text-right sm:px-6">
            <div class="flex font-semibold text-xl">
                <div class="flex ml-2 pr-2 text-red-800">
                    <svg class="fill-red-800 w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M160 80c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-352zM0 272c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 160c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48L0 272zM368 96l32 0c26.5 0 48 21.5 48 48l0 288c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48z"/></svg>
                    Istekle licence {{ $broj_istekilh_2 }}
                </div> 
                <div class="flex ml-4 pr-2 text-emerald-600">
                    <svg class="fill-emerald-600 w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M160 80c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-352zM0 272c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 160c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48L0 272zM368 96l32 0c26.5 0 48 21.5 48 48l0 288c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48z"/></svg>
                    Aktivne licence {{ $broj_aktivnih_2 }}
                </div> 
                <div class="flex ml-4 pr-2 text-yellow-300">
                    <svg class="fill-yellow-300 w-6 h-6 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M160 80c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-352zM0 272c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 160c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48L0 272zM368 96l32 0c26.5 0 48 21.5 48 48l0 288c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48z"/></svg>
                    Privremene licence {{ $broj_privremenih_2 }}
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
    <!-- Chart pie -->
    <script>

        const ctx = document.getElementById('myChart');
        const ctx2 = document.getElementById('myChart2');
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
            dataset2.push("{{ $item->privremene }}");
            bgcollors.push( @if($item->istekla) color1 @else color2 @endif );                
        @endforeach

        const labels2 = [];
        const dataset_1 = [];
        const dataset_2 = [];
        const bgcollors2 = [];

        @foreach($data2 as $item)
            labels2.push("{{ $item->month }} - {{ $item->year }}");
            dataset_1.push("{{ $item->data }}");
            dataset_2.push("{{ $item->privremene }}");
            bgcollors2.push( @if($item->istekla) color1 @else color2 @endif );
        @endforeach

        const data = {
            labels: labels,
            datasets: [
                {
                    label: [],
                    data: dataset ,
                    //borderColor: ["rgb(75, 75, 75)"],
                    backgroundColor: bgcollors,
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

        var myChart = new Chart(ctx, config);
        var myChart2 = new Chart(ctx2, config2);
        
    </script>


</div>
