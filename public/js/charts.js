// Chart.js configuration and helper functions for Budgie

// Initialize Chart.js with default options
Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif';
Chart.defaults.color = '#64748b';

// Common chart options
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: '#e2e8f0',
            },
            ticks: {
                color: '#64748b',
            }
        },
        x: {
            grid: {
                color: '#e2e8f0',
            },
            ticks: {
                color: '#64748b',
            }
        }
    }
};

// Dashboard expense vs income chart
function initDashboardChart() {
    const ctx = document.getElementById('dashboardChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Expenses',
                data: [1200, 1900, 3000, 5000, 2000, 3000],
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4
            }, {
                label: 'Income',
                data: [2000, 2000, 2000, 2000, 2000, 2000],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            ...chartOptions,
            plugins: {
                ...chartOptions.plugins,
                title: {
                    display: true,
                    text: 'Monthly Expenses vs Income'
                }
            }
        }
    });
}

// Forecast chart
function initForecastChart() {
    const ctx = document.getElementById('forecastChart');
    if (!ctx) return;

    let dynamicData = null;
    if (ctx.dataset.chart) {
        try {
            dynamicData = JSON.parse(ctx.dataset.chart);
        } catch (error) {
            console.error('Unable to parse forecast chart data', error);
        }
    }

    if (dynamicData && Array.isArray(dynamicData.labels) && Array.isArray(dynamicData.balances)) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dynamicData.labels,
                datasets: [{
                    label: 'Solde projeté',
                    data: dynamicData.balances,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.15)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#2563eb',
                    pointRadius: 4,
                }]
            },
            options: {
                ...chartOptions,
                plugins: {
                    ...chartOptions.plugins,
                    title: {
                        display: true,
                        text: 'Projection cumulative des soldes'
                    }
                }
            }
        });
        return;
    }

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Current', 'Jan 2025', 'Feb 2025', 'Mar 2025', 'Apr 2025', 'May 2025'],
            datasets: [{
                label: 'Account Balance',
                data: [5000, 5200, 5400, 5600, 5800, 6000],
                backgroundColor: '#2563eb',
                borderRadius: 4
            }]
        },
        options: {
            ...chartOptions,
            plugins: {
                ...chartOptions.plugins,
                title: {
                    display: true,
                    text: 'Projected Account Balances'
                }
            }
        }
    });
}

// Account balance pie chart
function initAccountBalanceChart() {
    const ctx = document.getElementById('accountBalanceChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Checking', 'Savings', 'Investment'],
            datasets: [{
                data: [3000, 2000, 5000],
                backgroundColor: [
                    '#2563eb',
                    '#10b981',
                    '#f59e0b'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'Account Distribution'
                }
            }
        }
    });
}

// Initialize all charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initDashboardChart();
    initForecastChart();
    initAccountBalanceChart();
});

// Utility function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
}

// Utility function to format date
function formatDate(date) {
    return new Intl.DateTimeFormat('fr-FR').format(new Date(date));
}
