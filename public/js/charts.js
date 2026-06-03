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
            grid: { color: '#e2e8f0' },
            ticks: { color: '#64748b' }
        },
        x: {
            grid: { color: '#e2e8f0' },
            ticks: { color: '#64748b' }
        }
    }
};

const currencyFormatter = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
const currencyFormatterNoDecimals = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 });

// ============================================================
// Dashboard expense vs income chart
// Data injected via window.dashboardChartData from index.php
// ============================================================
function initDashboardChart() {
    const ctx = document.getElementById('dashboardChart');
    if (!ctx) return;

    const dynamicData = (typeof window.dashboardChartData !== 'undefined') ? window.dashboardChartData : null;

    const labels   = (dynamicData && dynamicData.labels)   ? dynamicData.labels   : ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'];
    const expenses = (dynamicData && dynamicData.expenses)  ? dynamicData.expenses : [0, 0, 0, 0, 0, 0];
    const incomes  = (dynamicData && dynamicData.incomes)   ? dynamicData.incomes  : [0, 0, 0, 0, 0, 0];

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Dépenses',
                data: expenses,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#ef4444',
                pointRadius: 4,
            }, {
                label: 'Revenus',
                data: incomes,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#10b981',
                pointRadius: 4,
            }]
        },
        options: {
            ...chartOptions,
            plugins: {
                ...chartOptions.plugins,
                title: { display: true, text: 'Évolution Mensuelle : Revenus vs Dépenses' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ' + currencyFormatter.format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                ...chartOptions.scales,
                y: {
                    ...chartOptions.scales.y,
                    ticks: {
                        ...chartOptions.scales.y.ticks,
                        callback: function(value) {
                            return currencyFormatterNoDecimals.format(value);
                        }
                    }
                }
            }
        }
    });
}

// ============================================================
// Forecast balance chart
// Data injected via window.forecastChartData from forecasts.php
// ============================================================
function initForecastChart() {
    const ctx = document.getElementById('forecastChart');
    if (!ctx) return;

    const dynamicData = (typeof window.forecastChartData !== 'undefined') ? window.forecastChartData : null;

    if (dynamicData && Array.isArray(dynamicData.labels) && Array.isArray(dynamicData.balances) && dynamicData.labels.length > 0) {
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
                    title: { display: true, text: 'Projection cumulative des soldes' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + currencyFormatter.format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    ...chartOptions.scales,
                    y: {
                        ...chartOptions.scales.y,
                        ticks: {
                            ...chartOptions.scales.y.ticks,
                            callback: function(value) {
                                return currencyFormatterNoDecimals.format(value);
                            }
                        }
                    }
                }
            }
        });
    }
}

// ============================================================
// Account balance doughnut chart
// Data injected via window.accountBalanceChartData from index.php
// ============================================================
function initAccountBalanceChart() {
    const ctx = document.getElementById('accountBalanceChart');
    if (!ctx) return;

    const dynamicData = (typeof window.accountBalanceChartData !== 'undefined') ? window.accountBalanceChartData : null;

    const labels   = (dynamicData && dynamicData.labels)   ? dynamicData.labels   : ['Compte 1'];
    const balances = (dynamicData && dynamicData.balances)  ? dynamicData.balances : [0];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: balances,
                backgroundColor: [
                    '#2563eb', '#10b981', '#f59e0b',
                    '#6366f1', '#ec4899', '#8b5cf6',
                    '#06b6d4', '#84cc16'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                title: { display: true, text: 'Répartition des Comptes' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ' + currencyFormatter.format(context.parsed);
                        }
                    }
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

// Utility function to format currency (JS-side)
function formatCurrency(amount) {
    return currencyFormatter.format(amount);
}

// Utility function to format date (JS-side)
function formatDate(date) {
    return new Intl.DateTimeFormat('fr-FR').format(new Date(date));
}
