document.addEventListener('DOMContentLoaded', function() {
    // Fetch user statistics data
    fetch('get_user_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                initializeCharts(data);
            } else {
                console.error('Failed to fetch user statistics:', data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching user statistics:', error);
        });
});

function initializeCharts(data) {
    // User Status Distribution Chart
    new Chart(document.getElementById('userStatusChart'), {
        type: 'pie',
        data: {
            labels: ['Active Users', 'Banned Users'],
            datasets: [{
                data: [data.statusData.active, data.statusData.banned],
                backgroundColor: ['#2ecc71', '#e74c3c']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // User Role Distribution Chart
    new Chart(document.getElementById('userRoleChart'), {
        type: 'pie',
        data: {
            labels: ['Clients', 'Agents'],
            datasets: [{
                data: [data.roleData.clients, data.roleData.agents],
                backgroundColor: ['#3498db', '#9b59b6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // User Growth Over Time Chart
    new Chart(document.getElementById('userGrowthChart'), {
        type: 'line',
        data: {
            labels: data.growthData.labels,
            datasets: [{
                label: 'New Users',
                data: data.growthData.values,
                borderColor: '#2980b9',
                backgroundColor: 'rgba(41, 128, 185, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}