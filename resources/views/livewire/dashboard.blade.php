<div>
    <div class="py-6 flex flex-wrap">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg h-96 w-96">
                    <div class="shadow-lg rounded-lg overflow-hidden">
                    <canvas class="p-1" id="chartPie"></canvas>
                </div>
            </div>
        </div>
        @if($is_admin)
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg h-96 w-96">
                <div class="p-6"> 
                    <x-jet-nav-link href="{{ route( 'terminali-stanje' ) }}" :active="request()->routeIs('terminali-stanje')" >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="fill-current w-4 h-4 mr-2"><path d="M21 6.375c0 2.692-4.03 4.875-9 4.875S3 9.067 3 6.375 7.03 1.5 12 1.5s9 2.183 9 4.875Z" /><path d="M12 12.75c2.685 0 5.19-.586 7.078-1.609a8.283 8.283 0 0 0 1.897-1.384c.016.121.025.244.025.368C21 12.817 16.97 15 12 15s-9-2.183-9-4.875c0-.124.009-.247.025-.368a8.285 8.285 0 0 0 1.897 1.384C6.809 12.164 9.315 12.75 12 12.75Z" /><path d="M12 16.5c2.685 0 5.19-.586 7.078-1.609a8.282 8.282 0 0 0 1.897-1.384c.016.121.025.244.025.368 0 2.692-4.03 4.875-9 4.875s-9-2.183-9-4.875c0-.124.009-.247.025-.368a8.284 8.284 0 0 0 1.897 1.384C6.809 15.914 9.315 16.5 12 16.5Z" /><path d="M12 20.25c2.685 0 5.19-.586 7.078-1.609a8.282 8.282 0 0 0 1.897-1.384c.016.121.025.244.025.368 0 2.692-4.03 4.875-9 4.875s-9-2.183-9-4.875c0-.124.009-.247.025-.368a8.284 8.284 0 0 0 1.897 1.384C6.809 19.664 9.315 20.25 12 20.25Z" /></svg>

                    {{ __('Stanje terminala') }}
                    </x-jet-nav-link>
                </div>
            </div>
        </div>
        @endif
    </div>

        <!-- Required chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Chart pie -->
<script>
const dataPie = {
    labels: ["Magacin {{ 200 }}", "Instalirani", "Zamenski", "Neispravni"],
    datasets: [
    {
        label: "Terminali",
        data: [{{ $too }}, 50, 100, 150],
        backgroundColor: [
        "rgb(255, 99, 132)",
        "rgb(225, 206, 86)",
        "rgb(153, 102, 255)",
        "rgb(120, 143, 241)",
        ],
        borderColor: "#333",
        borderWidth: 2,
        hoverOffset: 4,
    },
    ],
};

const configPie = {
    type: "pie",
    data: dataPie,
    options: {
        title:{
            display: true,
            text: "Grafik terminala",
        }
    }
};

var chartBar = new Chart(document.getElementById("chartPie"), configPie);
</script>


</div>
