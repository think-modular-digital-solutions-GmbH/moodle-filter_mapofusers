console.log('🌍 Initializing filter_mapforusers...');
document.addEventListener('DOMContentLoaded', function () {
    const mapContainer = document.getElementById('worldmap');

    if (!mapContainer) {
        console.error('Map container #worldmap not found.');
        return;
    }

    console.log('Leaflet loaded:', typeof L !== 'undefined');
    console.log('Initializing map...');

    // Initialize the map
    const map = L.map('worldmap', {
        ...window.mapofusersConfig
    });

    // Get grid.
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(map);

    // Get locations from the HTML element
    const rawContainer = document.getElementById('map-pins-data');
    if (rawContainer) {
        try {
            const raw = rawContainer.textContent;
            const pins = JSON.parse(raw);

            pins.forEach(pin => {
                console.log('🌍 Adding marker for :', pin.name , ' at :', pin.location);

                // Create custom icon if image is provided
                const icon = pin.image ? L.icon({
                    iconUrl: pin.image,
                    iconSize: [25, 41],       // adjust as needed
                    iconAnchor: [12, 41],     // bottom center
                    popupAnchor: [0, -41]     // above the marker
                }) : undefined;

                // Create marker with or without custom icon
                const markerOptions = icon ? { icon } : {};
                const marker = L.marker([pin.lat, pin.lng], markerOptions).addTo(map);

                marker.bindPopup(pin.label);
            });

        } catch (e) {
            console.error('Could not parse map pin data:', e);
        }
    } else {
        console.warn('Map pins data container #map-pins-data not found.');
    }
});
