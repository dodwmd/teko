<div class="bg-white rounded shadow-sm p-4 mb-4">
    <h5>Errors by Type</h5>
    <div class="chart-container" style="position: relative; height:250px;">
        <canvas id="errorTypesChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('errorTypesChart').getContext('2d');
    
    const errorsByType = @json($errorsByType);
    const labels = errorsByType.map(item => item.error_type || 'Unknown');
    const data = errorsByType.map(item => item.count);
    
    const colors = [
        'rgba(255, 99, 132, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)',
    ];
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors.slice(0, data.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    
    // Track chart view in Google Analytics
    if (typeof window.trackEvent === 'function') {
        window.trackEvent('Monitoring', 'View', 'Error Types Chart');
    }
});
</script>
