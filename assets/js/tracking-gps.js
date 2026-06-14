// assets/js/tracking-gps.js

function initMap(lat, lng) {
    // Inisialisasi peta ke koordinat tertentu
    var map = L.map('map').setView([lat, lng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'DITRAS GPS System'
    }).addTo(map);

    // Ikon Marker Mobil
    var carIcon = L.icon({
        iconUrl: '/assets/img/car-marker.png', // Pastikan gambar ini ada
        iconSize: [32, 32]
    });

    L.marker([lat, lng], {icon: carIcon}).addTo(map)
        .bindPopup('Posisi Armada Anda');
}