console.log('🌍 Initializing filter_mapforusers...');
document.addEventListener('DOMContentLoaded', function () {
    const mapContainer = document.getElementById('worldmap');

    if (!mapContainer) {
        console.error('Map container #worldmap not found.');
        return;
    }

    console.log('Leaflet loaded:', typeof L !== 'undefined');
    console.log('Initializing map...');

    const map = L.map('worldmap').setView([20, 0], 2); // center of the world

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(map);

    // Get locations from the HTML element
    const raw = document.getElementById('map-pins-data').textContent;
    const pins = JSON.parse(raw);

    locations.forEach(loc => {
        L.marker([loc.lat, loc.lng]).addTo(map).bindPopup(loc.city);
    });
});
