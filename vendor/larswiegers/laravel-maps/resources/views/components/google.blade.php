<style>
    #{{$mapId}} {
        height: 80%;
    }
</style>
<style>
    #{{$mapId}} {
    @if(! isset($attributes['style']))
        height: 80vh;
    @else
        {{ $attributes['style'] }}
    @endif
    }
</style>
<style>
    .b_name {
        color:rgb(0, 0, 0); 
        font-weight: bold; 
        font-size: 1.5em;
        margin-bottom: 5px;
    }
    .details {
        font-family: Arial, sans-serif;
        font-size: 1.2em;
        color: #808080;
    }
    .details .address {
        font-size: 1em;
        color: #555;
        margin-bottom: 10px;
    }
    .details .sn {
        font-size: 1em;
        color: #555;
        margin-bottom: 10px;
    }
    .details .kontakt_osoba {
        margin-top: 10px;
    }
    .details .kontakt_osoba .name {
        font-weight: bold;
        color: #333;
    }
    .details .kontakt_osoba .tel {
        color: #007bff;
        text-decoration: none;
    }
    
</style>

<div id="{{$mapId}}" @if(isset($attributes['class']))
class='{{ $attributes["class"] }}'
        @endif
></div>
<script
        src="https://maps.googleapis.com/maps/api/js?key={{config('maps.google_maps.access_token', null)}}&callback=initMap{{$mapId}}&libraries=&v=3"
        async
></script>

<script>
    let map{{$mapId}} = "";  

    function initMap{{$mapId}}() {
        map{{$mapId}} = new google.maps.Map(document.getElementById("{{$mapId}}"), {
            center: { lat: {{$centerPoint['lat'] ?? $centerPoint[0]}}, lng: {{$centerPoint['long'] ?? $centerPoint[1]}} },
            zoom: {{$zoomLevel}},
            mapTypeId: '{{$mapType}}',
            zoomControl: true
        });

    function addInfoWindow(marker, message) {

        var infoWindow = new google.maps.InfoWindow({
            content: buildContent(message)
        });

        google.maps.event.addListener(marker, 'click', function () {
            infoWindow.open(map{{$mapId}}, marker);
        });
    }

    @if($fitToBounds || $centerToBoundsCenter)
    let bounds = new google.maps.LatLngBounds();
    @endif

    @foreach($markers as $marker)
        var marker{{ $loop->iteration }} = new google.maps.Marker({
            position: {
                lat: {{$marker['lat'] ?? $marker[0]}},
                lng: {{$marker['long'] ?? $marker[1]}}
            },
            map: map{{$mapId}},
            @if(isset($marker['title']))
            title: "{{ $marker['title'] }}",
            @endif
            icon: @if(isset($marker['icon']))"{{ $marker['icon']}}" @else null @endif
        });

        @if(isset($marker['info']))
            addInfoWindow(marker{{ $loop->iteration }}, @json($marker['info']));
        @endif

        @if($fitToBounds || $centerToBoundsCenter)
        bounds.extend({lat: {{$marker['lat'] ?? $marker[0]}},lng: {{$marker['long'] ?? $marker[1]}}});
        @endif

        @if($fitToBounds)
        map{{$mapId}}.fitBounds(bounds);
        @endif        
        @endforeach

        @if($centerToBoundsCenter)
        map{{$mapId}}.setCenter(bounds.getCenter());
        @endif
    }

function buildContent(p_property) {
   let property = JSON.parse(p_property);
   let sn = property.terminal_sn ? property.terminal_sn : '';
   let terminals = sn.split(',').map(term => term.trim()).filter(term => term !== '');
    
    if (terminals.length > 4) {
        terminals = terminals.slice(0, 4); // Limit to 4 SNs
        terminals.push('...'); // Indicate more SNs available
    }
   
  const content = document.createElement("div");

  content.innerHTML = `
    <div class="b_name">
        ${property.p_name}
    </div>
    <div class="details">
        <div class="address">${property.address}</div>

        <div class="sn">
            ${terminals.length > 0 ? 'SN: ' + terminals.map(term => `<span class="sn">${term}</span>`).join(', ') : ''}
        </div>

        <div class="kontakt_osoba">
            <div class="name">${property.ko_name}</div>
            <div class="tel">${property.ko_tel}</div>
        </div>
    </div>
    `;
  return content;
}
</script>
