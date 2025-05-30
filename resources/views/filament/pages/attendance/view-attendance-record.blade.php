<x-filament-panels::page
    @class([
        'filament-resources-view-record-page',
    ])
>
    <div class="flex flex-col gap-y-6 md:flex-row md:gap-x-6">
        {{-- Left Column: Infolist (Record Details) - 1 part --}}
        <div class="md:w-1/3">
            {{ $this->infolist }}
        </div>

        {{-- Right Column: Map - 2 parts --}}
        <div class="md:w-2/3">
            @php
                $location = $this->getGpsLocation();
            @endphp

            @if ($location && isset($location['latitude']) && isset($location['longitude']))
                <div class="rounded-lg border border-gray-300 dark:border-gray-700 shadow-sm">
                    <div class="p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            Location on Map
                        </h3>
                        <div id="mapId" style="height: 400px;" class="rounded"></div>
                    </div>
                </div>
            @else
                <div class="p-4 rounded-lg border border-gray-300 dark:border-gray-700 shadow-sm">
                    <p class="text-gray-500 dark:text-gray-400">No GPS coordinates available for this record to display on map.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- NEW: Google Maps Inline Bootstrap Loader --}}
    {{-- Replace YOUR_API_KEY with your actual key or use config('services.google.maps_api_key') --}}
    <script>
      (g => {
        var h, a, k, p = "The Google Maps JavaScript API",
          c = "google",
          l = "importLibrary",
          q = "__ib__",
          m = document,
          b = window;
        b = b[c] || (b[c] = {});
        var d = b.maps || (b.maps = {}),
          r = new Set,
          e = new URLSearchParams,
          u = () => h || (h = new Promise(async (f, n) => {
            await (a = m.createElement("script"));
            e.set("libraries", [...r] + ""); // Dynamically add libraries, 'marker' will be added below
            e.set("key", "{{ config('services.google.maps_api_key', 'YOUR_API_KEY') }}"); // Use config or direct key
            e.set("solution_channel", "GMP_QB_locatorplus_v6_cA"); // Optional tracking
            a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
            d[q] = f;
            a.onerror = () => h = n(Error(p + " could not load."));
            a.nonce = m.querySelector("script[nonce]")?.nonce || "";
            m.head.append(a)
          }));
        d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n))
      })
      ([]);
    </script>
    @if ($location && isset($location['latitude']) && isset($location['longitude']))
    <script>
        let map; // Declare map variable in a broader scope if needed elsewhere
        async function initMap() {
            const lat = parseFloat({{ $location['latitude'] }});
            const lon = parseFloat({{ $location['longitude'] }});
            const mapLocation = { lat: lat, lng: lon };
            const mapDiv = document.getElementById('mapId');
            if (!mapDiv) {
                console.error('Map container #mapId not found.');
                return;
            }
            try {
                // Import necessary libraries
                const { Map } = await google.maps.importLibrary("maps");
                const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
                map = new Map(mapDiv, {
                    zoom: 15,
                    center: mapLocation,
                    mapId: 'YOUR_MAP_ID' // Optional: Replace with your Map ID for cloud-based map styling
                });
                const marker = new AdvancedMarkerElement({
                    map: map,
                    position: mapLocation,
                    title: 'Attendance Location'
                });
            } catch (error) {
                console.error("Error loading Google Maps libraries or initializing map:", error);
            }
        }
        // Call initMap when the page is ready
        // Using DOMContentLoaded as a fallback, but the inline loader should handle library readiness
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            initMap();
        } else {
            document.addEventListener('DOMContentLoaded', initMap);
        }
    </script>
    @endif
</x-filament-panels::page>