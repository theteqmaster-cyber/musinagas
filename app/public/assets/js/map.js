// Musina Gas Leaflet.js Map Handler

function initMusinaMap(latInputId, lngInputId, addressInputId) {
  const mapElement = document.getElementById('leafletMap');
  if (!mapElement) return;

  // Default Musina CBD coordinates
  const defaultLat = -22.3562;
  const defaultLng = 30.0416;

  const latInput = document.getElementById(latInputId);
  const lngInput = document.getElementById(lngInputId);
  const addressInput = document.getElementById(addressInputId);

  const initialLat = parseFloat(latInput.value) || defaultLat;
  const initialLng = parseFloat(lngInput.value) || defaultLng;

  const map = L.map('leafletMap').setView([initialLat, initialLng], 14);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  const pinIcon = L.icon({
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
  });

  const marker = L.marker([initialLat, initialLng], { draggable: true, icon: pinIcon }).addTo(map);

  function updateCoords(lat, lng) {
    latInput.value = lat.toFixed(6);
    lngInput.value = lng.toFixed(6);
    if (!addressInput.value) {
      addressInput.value = `Location (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
    }
  }

  marker.on('dragend', function (e) {
    const coord = marker.getLatLng();
    updateCoords(coord.lat, coord.lng);
  });

  map.on('click', function(e) {
    marker.setLatLng(e.latlng);
    updateCoords(e.latlng.lat, e.latlng.lng);
  });

  // Geolocation Button handler
  const gpsBtn = document.getElementById('btnUseGps');
  if (gpsBtn) {
    gpsBtn.addEventListener('click', () => {
      if (navigator.geolocation) {
        gpsBtn.textContent = '📍 Locating...';
        navigator.geolocation.getCurrentPosition(
          (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            map.setView([lat, lng], 15);
            marker.setLatLng([lat, lng]);
            updateCoords(lat, lng);
            gpsBtn.textContent = '✅ GPS Located';
          },
          (err) => {
            alert('Unable to retrieve location: ' + err.message);
            gpsBtn.textContent = '📍 Use My Current GPS';
          }
        );
      } else {
        alert('Geolocation is not supported by your browser');
      }
    });
  }
}
