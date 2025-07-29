{{-- file: resources/views/filament/pages/attendance/view-attendance-record.blade.php --}}
<x-filament-panels::page>
    {{-- This renders the infolist with all the attendance details --}}
    {{ $this->infolist }}

    {{-- Check if the location data exists before rendering the map section --}}
    @if ($this->getGpsLocation())
        <x-filament::section>
            <x-slot name="heading">
                Clock-In Location (Google Maps)
            </x-slot>

            {{-- The div where the map will be rendered --}}
            <div id="map" style="height: 400px;" class="w-full rounded-lg"></div>
        </x-filament::section>

        {{-- Script to initialize and render the Google Map --}}
        <script>
            function initMap() {
                // Get the location data passed from the PHP class
                const clockInLocation = @json($this->getGpsLocation());
                const workArrangement = @json($this->record->locationType->arrangement_type ?? null);

                // --- NEW: Define the fixed office location and geofence radius ---
                const officeLocation = { lat: -6.2383, lng: 106.9924 }; // Example: Summarecon Mall Bekasi
                const geofenceRadius = 100; // in meters

                if (clockInLocation && clockInLocation.latitude && clockInLocation.longitude) {
                    const mapElement = document.getElementById('map');
                    
                    // Center the map on the provided coordinates
                    const clockInPosition = { lat: clockInLocation.latitude, lng: clockInLocation.longitude };

                    // Create the map instance
                    const map = new google.maps.Map(mapElement, {
                        zoom: 16, // Zoom in a bit more to see the geofence clearly
                        center: workArrangement === 'WFO' ? officeLocation : clockInPosition, // Center on office if WFO
                        mapTypeId: 'roadmap'
                    });

                    // Create a marker for the employee's clock-in location
                    const clockInMarker = new google.maps.Marker({
                        position: clockInPosition,
                        map: map,
                        title: `Clocked in at: ${clockInLocation.latitude}, ${clockInLocation.longitude}`
                    });

                    // --- NEW: If work arrangement is WFO, draw the office location and geofence ---
                    if (workArrangement === 'WFO') {
                        // Add a marker for the office
                        const officeMarker = new google.maps.Marker({
                            position: officeLocation,
                            map: map,
                            title: 'Office Location',
                            icon: {
                                url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png" // Use a different color for the office
                            }
                        });

                        // Add the circular geofence
                        const geofenceCircle = new google.maps.Circle({
                            strokeColor: '#0000FF', // Blue color for the circle outline
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: '#0000FF', // Blue color for the fill
                            fillOpacity: 0.20,
                            map: map,
                            center: officeLocation,
                            radius: geofenceRadius
                        });
                    }
                }
            }

            // Load the Google Maps API script.
            // No API key is included, so it will work for development but show a "For Development Purposes Only" watermark.
            const script = document.createElement('script');
            script.src = "https://maps.googleapis.com/maps/api/js?callback=initMap";
            script.async = true;
            document.head.appendChild(script);
        </script>
    @endif
</x-filament-panels::page>
