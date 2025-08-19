import './bootstrap';
import Clipboard from "@ryangjchandler/alpine-clipboard"

Alpine.plugin(Clipboard)

// Add to your app.js or a new reporting.js file
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching with smooth transitions
    const tabs = document.querySelectorAll('[role="tab"]');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const target = this.getAttribute('aria-controls');
            document.querySelectorAll('[role="tabpanel"]').forEach(panel => {
                panel.classList.add('hidden');
            });
            document.getElementById(target).classList.remove('hidden');
            
            // Update active tab styling
            tabs.forEach(t => t.classList.remove('border-indigo-500', 'text-indigo-600'));
            this.classList.add('border-indigo-500', 'text-indigo-600');
        });
    });
    
    // Chart hover effects
    document.querySelectorAll('.filament-widget canvas').forEach(canvas => {
        canvas.addEventListener('mousemove', function(e) {
            this.style.transform = 'scale(1.01)';
        });
        canvas.addEventListener('mouseleave', function(e) {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Time period selector functionality
    document.querySelectorAll('.time-period-selector').forEach(select => {
        select.addEventListener('change', function() {
            const widgetId = this.closest('.widget-container').dataset.widgetId;
            // Here you would typically make an AJAX call to update the chart data
            console.log(`Time period changed for ${widgetId} to ${this.value}`);
        });
    });
});