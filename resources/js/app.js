import Alpine from 'alpinejs';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import Chart from 'chart.js/auto';

// Fix leaflet default icon asset paths
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

window.Alpine = Alpine;
window.L = L;
window.flatpickr = flatpickr;
window.Chart = Chart;

Alpine.start();
