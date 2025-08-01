<x-filament-panels::page
    @class([
        'filament-resources-view-record-page',
    ])
>
    {{-- Include Leaflet CSS in head --}}
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
         integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
         crossorigin=""/>
    @endpush

    <div class="w-full max-w-none">
        <div class="flex flex-col gap-y-6 lg:flex-row lg:gap-x-6">
            {{-- Left Column: Infolist (Record Details) --}}
            <div class="lg:w-80 lg:flex-shrink-0">
                {{ $this->infolist }}
            </div>

            {{-- Right Column: Map --}}
            <div class="flex-1 min-w-0">
                @php
                    $location = $this->getGpsLocation();
                @endphp

                @if ($location && $location['latitude'] && $location['longitude'])
                    <div class="w-full rounded-lg border border-gray-300 dark:border-gray-700 shadow-sm">
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                Location on Map
                            </h3>
                            <div
                                x-data="attendanceMap({{ $location['latitude'] }}, {{ $location['longitude'] }})"
                                x-init="initMap()"
                                id="mapId"
                                style="height: 400px; width: 100%;"
                                class="rounded"
                            ></div>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-lg border border-gray-300 dark:border-gray-700 shadow-sm">
                        <p class="text-gray-500 dark:text-gray-400">No GPS coordinates available for this record to display on map.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Include Leaflet JavaScript and Alpine component --}}
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
         integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
         crossorigin=""></script>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('attendanceMap', (lat, lon) => ({
                    map: null,
                    latitude: lat,
                    longitude: lon,
                    mapId: 'mapId',

                    initMap() {
                        // Wait for Leaflet to be available
                        if (typeof L === 'undefined') {
                            setTimeout(() => this.initMap(), 100);
                            return;
                        }

                        // Wait for container to be ready
                        const container = this.$el;
                        if (!container) {
                            setTimeout(() => this.initMap(), 100);
                            return;
                        }

                        // Clear any existing map
                        if (this.map) {
                            this.map.remove();
                        }

                        try {
                            // Initialize map
                            this.map = L.map(container).setView([this.latitude, this.longitude], 15);

                            // Add tile layer
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                            }).addTo(this.map);

                            // Add marker
                            L.marker([this.latitude, this.longitude])
                                .addTo(this.map)
                                .bindPopup('Attendance Location')
                                .openPopup();
                            // Force resize
                            setTimeout(() => {
                                if (this.map) {
                                    this.map.invalidateSize();
                                }
                            }, 100);
                            console.log('Map initialized successfully with Alpine.js');
                        } catch (error) {
                            console.error('Error initializing map:', error);
                            setTimeout(() => this.initMap(), 500);
                        }
                    },
                    // Cleanup when component is destroyed
                    destroy() {
                        if (this.map) {
                            this.map.remove();
                            this.map = null;
                        }
                    }
                }))
            });
        </script>
    @endpush

</x-filament-panels::page>
