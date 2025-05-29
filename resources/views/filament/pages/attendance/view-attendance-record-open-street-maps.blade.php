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

    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDmPtGDqQ-ylso2VntDxCVjI9c2Sqvr98E&callback=initMap"></script>

    {{-- Full width container with proper spacing --}}
    <div class="w-full max-w-none">
        <div class="flex flex-col gap-y-6 lg:flex-row lg:gap-x-6">
            {{-- Left Column: Infolist (Record Details) --}}
            <div class="lg:w-80 lg:flex-shrink-0"> {{-- Fixed width instead of percentage --}}
                {{ $this->infolist }}
            </div>

            {{-- Right Column: Map - Takes remaining space --}}
            <div class="flex-1 min-w-0"> {{-- flex-1 takes remaining space, min-w-0 allows shrinking --}}
                @php
                    $location = $this->getGpsLocation();
                @endphp

                @if ($location && $location['latitude'] && $location['longitude'])
                    <div class="w-full rounded-lg border border-gray-300 dark:border-gray-700 shadow-sm">
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                Location on Map
                            </h3>
                            {{-- Map container with explicit width --}}
                            <div id="mapId" style="height: 400px; width: 100%;" class="rounded"></div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var lat = {{ $location['latitude'] }};
                            var lon = {{ $location['longitude'] }};

                            if (document.getElementById('mapId') && typeof L !== 'undefined') {
                                var map = L.map('mapId').setView([lat, lon], 15);

                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                }).addTo(map);

                                L.marker([lat, lon]).addTo(map)
                                    .bindPopup('Attendance Location')
                                    .openPopup();

                                // Force map to resize after container is fully rendered
                                setTimeout(function() {
                                    map.invalidateSize();
                                }, 100);
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
    </div>

</x-filament-panels::page>
