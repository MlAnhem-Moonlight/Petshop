document.addEventListener('DOMContentLoaded', function() {
    // Chart color function
    function getRandomColors(count) {
        const colors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#5a5c69', '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
        ];
        return colors.slice(0, count);
    }

    // Initialize charts only if elements exist
    if (document.getElementById('ordersLocationChart')) {
        // Orders by Location Chart
        new Chart(document.getElementById('ordersLocationChart'), {
            // ... existing chart configuration ...
        });
    }

    // ... rest of your chart initializations ...
});