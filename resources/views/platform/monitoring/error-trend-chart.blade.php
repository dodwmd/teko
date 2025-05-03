<div class="bg-white rounded shadow-sm p-4 mb-4">
    <h5>Error Trend (Last 7 Days)</h5>
    <div class="chart-container" style="position: relative; height:250px;">
        <canvas id="errorTrendChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('errorTrendChart').getContext('2d');
    
    const errorsByDay = @json($errorsByDay);
    const dates = errorsByDay.map(item => item.date);
    const counts = errorsByDay.map(item => item.count);
    
    // Generate the last 7 days dates to ensure we have all days represented
    const allDates = [];
    const allCounts = [];
    
    for (let i = 6; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        const dateString = date.toISOString().split('T')[0]; // YYYY-MM-DD format
        
        allDates.push(dateString);
        
        // Find count for this date or use 0
        const found = errorsByDay.find(item => item.date === dateString);
        allCounts.push(found ? found.count : 0);
    }
    
    // Format dates for display
    const formattedDates = allDates.map(date => {
        const d = new Date(date);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: formattedDates,
            datasets: [{
                label: 'Error Count',
                data: allCounts,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1,
                fill: true,
                pointBackgroundColor: 'rgba(255, 99, 132, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function(tooltipItems) {
                            return tooltipItems[0].label;
                        },
                        label: function(context) {
                            const errors = context.raw;
                            return `${errors} ${errors === 1 ? 'error' : 'errors'}`;
                        }
                    }
                }
            }
        }
    });
    
    // Track chart view in Google Analytics
    if (typeof window.trackEvent === 'function') {
        window.trackEvent('Monitoring', 'View', 'Error Trend Chart');
    }
});
</script>
