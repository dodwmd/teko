<div class="bg-white rounded shadow-sm p-4 mb-4">
    <h5 class="text-muted">Error Trend (Last 7 Days)</h5>
    <div class="chart-container" style="position: relative; height:250px;">
        <canvas id="errorTrendChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('errorTrendChart').getContext('2d');
    
    // Extract data from PHP
    const errorTrend = @json($errorTrend);
    const labels = @json($trendLabels);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Errors',
                data: errorTrend,
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgba(220, 53, 69, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
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
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
