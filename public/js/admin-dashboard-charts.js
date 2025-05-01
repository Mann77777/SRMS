/**
 * Admin Dashboard Charts
 * This file handles all chart functionality for the admin dashboard
 */

// Chart color palette
const chartColors = {
    blue: {
        primary: 'rgba(54, 162, 235, 1)',
        light: 'rgba(54, 162, 235, 0.2)'
    },
    green: {
        primary: 'rgba(75, 192, 192, 1)',
        light: 'rgba(75, 192, 192, 0.7)'
    },
    orange: {
        primary: 'rgba(255, 159, 64, 1)',
        light: 'rgba(255, 159, 64, 0.7)'
    },
    yellow: {
        primary: 'rgba(255, 206, 86, 1)',
        light: 'rgba(255, 206, 86, 0.6)'
    },
    red: {
        primary: 'rgba(255, 99, 132, 1)',
        light: 'rgba(255, 99, 132, 0.6)'
    },
    purple: {
        primary: 'rgba(153, 102, 255, 1)',
        light: 'rgba(153, 102, 255, 0.6)'
    }
};

document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit to ensure data attributes are set
    setTimeout(() => {
        // Only initialize charts if we're on admin dashboard and the user is an admin
        if (document.getElementById('requestStatisticsChart')) {
            console.log('Initializing charts from admin-dashboard-charts.js');
            initializeCharts();
        }
        
        // Add event listeners for filters
        setupEventListeners();
    }, 100);
});

/**
 * Initialize all charts on the dashboard
 */
function initializeCharts() {
    initRequestStatisticsChart();
    initRequestsOverTimeChart();
    initAppointmentsByStaffChart();
}

/**
 * Initialize the Request Statistics Chart
 */
function initRequestStatisticsChart() {
    const ctx = document.getElementById('requestStatisticsChart');
    if (!ctx) {
        console.error('Request Statistics Chart element not found');
        return;
    }
    
    // Get data from data attributes or use default values
    const totalRequests = parseInt(ctx.dataset.totalRequests) || 0;
    const weekRequests = parseInt(ctx.dataset.weekRequests) || 0;
    const monthRequests = parseInt(ctx.dataset.monthRequests) || 0;
    const yearRequests = parseInt(ctx.dataset.yearRequests) || 0;
    const overdueRequests = parseInt(ctx.dataset.overdueRequests || 0); // Add overdue requests

    
    console.log('Statistics Chart Data:', {
        totalRequests, weekRequests, monthRequests, yearRequests
    });
    
    // Check if chart is already initialized and destroy it
    let chartInstance = Chart.getChart(ctx);
    if (chartInstance) {
        chartInstance.destroy();
    }
    
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Total Requests', 'This Week', 'This Month', 'This Year'],
            datasets: [{
                data: [totalRequests, weekRequests, monthRequests, yearRequests],
                backgroundColor: [
                    chartColors.yellow.light,
                    chartColors.green.light,
                    chartColors.red.light,
                    chartColors.blue.light
                ],
                borderColor: [
                    chartColors.yellow.primary,
                    chartColors.green.primary,
                    chartColors.red.primary,
                    chartColors.blue.primary
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 2,
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
                }
            }
        }
    });
}

/**
 * Initialize the Requests Over Time Chart
 */
function initRequestsOverTimeChart() {
    const ctx = document.getElementById('requestsOverTimeChart');
    if (!ctx) {
        console.error('Requests Over Time Chart element not found');
        return;
    }
    
    try {
        // Get data from the element's data attributes
        const labels = JSON.parse(ctx.dataset.labels || '[]');
        const values = JSON.parse(ctx.dataset.values || '[]');
        
        console.log('Time Chart Data:', { labels, values });
        
        // Check if chart is already initialized and destroy it
        let chartInstance = Chart.getChart(ctx);
        if (chartInstance) {
            chartInstance.destroy();
        }
        
        window.requestsOverTimeChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Requests',
                    data: values,
                    fill: false,
                    backgroundColor: chartColors.blue.light,
                    borderColor: chartColors.blue.primary,
                    tension: 0.1,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: chartColors.blue.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
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
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });
    } catch (e) {
        console.error('Error initializing time chart:', e);
    }
}

/**
 * Initialize the Appointments by UITC Staff Chart
 */
function initAppointmentsByStaffChart() {
    const ctx = document.getElementById('appointmentsByStaffChart');
    if (!ctx) {
        console.error('Appointments By Staff Chart element not found');
        return;
    }
    
    try {
        // Get data from the element's data attributes
        const staffNames = JSON.parse(ctx.dataset.staffNames || '[]');
        const assignedCounts = JSON.parse(ctx.dataset.assignedCounts || '[]');
        const completedCounts = JSON.parse(ctx.dataset.completedCounts || '[]');
        
        console.log('Full Staff Names:', staffNames);
        
        // If no data, just return
        if (staffNames.length === 0) {
            console.warn('No staff data available for chart');
            return;
        }
        
        // No need to shorten names - use the full names
        // This ensures we display complete staff names
        
        // Check if chart is already initialized and destroy it
        let chartInstance = Chart.getChart(ctx);
        if (chartInstance) {
            chartInstance.destroy();
        }
        
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: staffNames, // Use full names
                datasets: [
                    {
                        label: 'Assigned',
                        data: assignedCounts,
                        backgroundColor: chartColors.orange.light,
                        borderColor: chartColors.orange.primary,
                        borderWidth: 1,
                        barPercentage: 0.8,
                        categoryPercentage: 0.7
                    },
                    {
                        label: 'Completed',
                        data: completedCounts,
                        backgroundColor: chartColors.green.light,
                        borderColor: chartColors.green.primary,
                        borderWidth: 1,
                        barPercentage: 0.8,
                        categoryPercentage: 0.7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 3,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            padding: 10,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            title: function(tooltipItems) {
                                // Show full name on tooltip
                                return staffNames[tooltipItems[0].dataIndex] || 'Unknown';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    },
                    x: {
                        // Improve how x-axis labels are displayed
                        ticks: {
                            font: {
                                size: 10
                            },
                            callback: function(value, index) {
                                // If the name is too long, truncate it
                                const name = this.getLabelForValue(index);
                                if (name && name.length > 10) {
                                    return name.substr(0, 10) + '...';
                                }
                                return name;
                            }
                        }
                    }
                }
            }
        });
    } catch (e) {
        console.error('Error initializing staff chart:', e);
    }
}

/**
 * Set up event listeners for chart filters
 */
function setupEventListeners() {
    // Time period filters for Requests Over Time chart
    document.querySelectorAll('.time-filter').forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons and add to clicked button
            document.querySelectorAll('.time-filter').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            const period = this.dataset.period;
            fetchTimeSeriesData(period);
        });
    });
}

/**
 * Fetch new time series data based on selected period
 * @param {string} period - The selected time period
 */
function fetchTimeSeriesData(period) {
    // Get the CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    console.log('Fetching time series data for period:', period);
    
    // Make an AJAX request to get new data
    fetch('/admin/dashboard/time-series-data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ period: period })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Time series data received:', data);
        
        // Update the chart with new data
        if (window.requestsOverTimeChart) {
            window.requestsOverTimeChart.data.labels = data.labels;
            window.requestsOverTimeChart.data.datasets[0].data = data.values;
            window.requestsOverTimeChart.update();
        } else {
            console.error('requestsOverTimeChart not found');
        }
    })
    .catch(error => {
        console.error('Error fetching time series data:', error);
    });
}