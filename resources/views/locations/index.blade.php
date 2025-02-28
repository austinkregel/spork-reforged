<script
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap&v=weekly"
    defer
></script>
<script>
    function initMap() {
        @php
            $center = [
                43.8237182,-84.7808299
];
        @endphp
        const dispensary = { lat: {{$center[0]}}, lng: {{$center[1]}} };
        const map = new google.maps.Map(document.getElementById("map"), {
            center: dispensary,
            zoom: 6,
        });
        const contentString = '<div id="content"></div>';

        const infowindow = new google.maps.InfoWindow({
            content: contentString,
        });

        @foreach($locations as $dispensary)
        @if ($dispensary->latitude && $dispensary->longitude)
        new google.maps.Marker({
            position: {
                lat: {{ $dispensary->latitude ?? 0}},
                lng: {{ $dispensary->longitude ?? 0}},
            },
            map,
            title: "{{ $dispensary->name}}",
        });
        @endif
        @endforeach
    }
</script>

<div id="map" class="w-full bg-white rounded-t" style="height: 100%"></div>
