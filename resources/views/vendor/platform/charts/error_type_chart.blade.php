<div class="bg-white rounded shadow-sm p-4 mb-4">
    <h5 class="text-muted">Error Types Distribution</h5>
    <div class="chart-container" style="position: relative; height:250px;">
        <canvas id="errorTypeChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('errorTypeChart').getContext('2d');
    
    // Extract data from PHP
    const errorsByType = @json($errorsByType);
    
    // Prepare chart data
    const labels = Object.keys(errorsByType);
    const data = Object.values(errorsByType);
    
    // Colors for different error types
    const colors = {
        'emergency': '#dc3545', // red
        'alert': '#fd7e14',     // orange
        'critical': '#dc3545',  // red
        'error': '#f39c12',     // yellow
        'warning': '#ffc107',   // light yellow
        'notice': '#17a2b8',    // cyan
        'info': '#3498db',      // blue
        'debug': '#6c757d'      // gray
    };
    
    // Map colors to labels
    const backgroundColors = labels.map(label => colors[label] || '#6c757d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            },
            cutout: '70%'
        }
    });
});
</script>
