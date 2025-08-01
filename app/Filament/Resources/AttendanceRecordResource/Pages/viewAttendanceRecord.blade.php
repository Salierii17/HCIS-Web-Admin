<x-filament-panels::page
    @class([
        'filament-resources-view-record-page',
    ])
>
    {{-- Include Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>

    {{-- Include Leaflet JavaScript --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>

    <div class="flex flex-col gap-y-6 md:flex-row md:gap-x-6">
        {{-- Left Column: Infolist (Record Details) --}}
        <div class="md:w-2/3 lg:w-3/4"> {{-- Adjust width as needed --}}
            {{ $this->infolist }}
        </div>

        {{-- Right Column: Map --}}
        <div class="md:w-1/3 lg:w-1/4"> {{-- Adjust width as needed --}}
            @php
                $location = $this->getGpsLocation();
            @endphp

            @if ($location && $location['latitude'] && $location['longitude'])
                <div class="rounded-lg border border-gray-300 dark:border-gray-700 shadow-sm">
                    <div class="p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            Location on Map
                        </h3>
                        <div id="mapId" style="height: 400px;" class="rounded"></div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var lat = {{ $location['latitude'] }};
                        var lon = {{ $location['longitude'] }};

                        if (document.getElementById('mapId') && typeof L !== 'undefined') {
                            var map = L.map('mapId').setView([lat, lon], 15); // 15 is zoom level

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                            }).addTo(map);

                            L.marker([lat, lon]).addTo(map)
                                .bindPopup('Attendance Location')
                                .openPopup();
                        } else {
                            console.error('Map container or Leaflet library not found.');
                        }
                    });
                </script>
            @else
                <div class="p-4 rounded-lg border border-gray-300 dark:border-gray-700 shadow-sm">
                    <p class="text-gray-500 dark:text-gray-400">No GPS coordinates available for this record to display on map.</p>
                </div>
            @endif
        </div>
    </div>

</x-filament-panels::page>
