<div class="bg-white rounded shadow-sm p-4 mb-4">
    <h4>Agent Types Distribution</h4>
    <div class="chart-container" style="position: relative; height:250px;">
        <canvas id="agentTypesChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('agentTypesChart').getContext('2d');
    
    const agentTypes = @json($metrics['agent_types'] ?? []);
    const labels = Object.keys(agentTypes).map(type => {
        // Convert snake_case to Title Case
        return type.split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    });
    
    const data = Object.values(agentTypes);
    
    const colors = [
        'rgba(75, 192, 192, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(255, 99, 132, 0.7)',
        'rgba(153, 102, 255, 0.7)',
        'rgba(255, 159, 64, 0.7)',
    ];
    
    new Chart(ctx, {
        type: 'doughnut',
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
            legend: {
                position: 'right',
            }
        }
    });
});
</script>
