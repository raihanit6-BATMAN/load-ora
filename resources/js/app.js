import './bootstrap';
import Chart from 'chart.js/auto';
import { createIcons, icons } from 'lucide';

window.Chart = Chart;
window.createIcons = createIcons;
window.lucideIcons = icons;

// Initialize Lucide icons on page load and Livewire updates
function refreshIcons() {
    createIcons({ icons });
}

window.refreshIcons = refreshIcons;

document.addEventListener('DOMContentLoaded', refreshIcons);
document.addEventListener('livewire:navigated', refreshIcons);
document.addEventListener('livewire:load', refreshIcons);
document.addEventListener('livewire:update', refreshIcons);
document.addEventListener('livewire:initialized', () => {
    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            setTimeout(refreshIcons, 50);
        });
    });
});
