document.addEventListener('DOMContentLoaded', function() {
    // Show/hide custom date range based on selection
    document.getElementById('date-filter').addEventListener('change', function() {
        const customDateRange = document.querySelector('.custom-date-range');
        if (this.value === 'custom') {
            customDateRange.style.display = 'flex';
        } else {
            customDateRange.style.display = 'none';
        }
    });
    
    // Initialize charts and load data
    initCharts();
    fetchStaffList();
    loadReportData();
    
    // Event listener for filter application
    document.getElementById('apply-filters').addEventListener('click', function() {
        loadReportData();
    });
    
    // Event listener for excel export
    document.getElementById('export-report').addEventListener('click', function() {
        exportToExcel();
    });
    
    // Event listener for pdf export
    document.getElementById('export-pdf').addEventListener('click', function() {
        exportToPDF();
    });
    
    // Toggle chart view (line/bar)
    document.querySelectorAll('[data-chart-view]').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('[data-chart-view]').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            const chartType = this.getAttribute('data-chart-view');
            updateChartType('monthlyTrendsChart', chartType);
        });
    });
});

// Initialize charts with empty data
function initCharts() {
    // Monthly Trends Chart
    const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
    window.monthlyTrendsChart = new Chart(monthlyTrendsCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Total Requests',
                data: [],
                borderColor: 'rgba(78, 115, 223, 1)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                tension: 0.3,
                fill: true
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
                    display: true,
                    position: 'top'
                }
            }
        }
    });
    
    // Staff Performance Chart
    const staffPerformanceCtx = document.getElementById('staffPerformanceChart').getContext('2d');
    window.staffPerformanceChart = new Chart(staffPerformanceCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Assigned',
                    data: [],
                    backgroundColor: 'rgba(255, 159, 64, 0.7)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Completed',
                    data: [],
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }
            ]
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
                    position: 'top'
                }
            }
        }
    });
    
    // Service Category Chart
    const serviceCategoryCtx = document.getElementById('serviceCategoryChart').getContext('2d');
    window.serviceCategoryChart = new Chart(serviceCategoryCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    'rgba(78, 115, 223, 0.8)',
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(54, 185, 204, 0.8)',
                    'rgba(246, 194, 62, 0.8)',
                    'rgba(231, 74, 59, 0.8)',
                    'rgba(111, 66, 193, 0.8)',
                    'rgba(32, 201, 151, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        padding: 15
                    }
                }
            }
        }
    });
    
    // Service Category Trend Chart
    const serviceCategoryTrendCtx = document.getElementById('serviceCategoryTrendChart').getContext('2d');
    window.serviceCategoryTrendChart = new Chart(serviceCategoryTrendCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: []
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
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        padding: 10
                    }
                }
            }
        }
    });
    
    // Status Distribution Chart
    const statusDistributionCtx = document.getElementById('statusDistributionChart').getContext('2d');
    window.statusDistributionChart = new Chart(statusDistributionCtx, {
        type: 'pie',
        data: {
            labels: ['Pending', 'In Progress', 'Completed', 'Rejected'],
            datasets: [{
                data: [0, 0, 0, 0],
                backgroundColor: [
                    'rgba(246, 194, 62, 0.8)',
                    'rgba(54, 185, 204, 0.8)',
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(231, 74, 59, 0.8)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        padding: 15
                    }
                }
            }
        }
    });
}

// Fetch UITC staff list for the dropdown
function fetchStaffList() {
    // Get the CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch('/admin/get-uitc-staff', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const staffFilter = document.getElementById('staff-filter');
            
            // Clear existing options except the first one
            while (staffFilter.options.length > 1) {
                staffFilter.remove(1);
            }
            
            // Add new options based on the fetched staff list
            data.staff.forEach(staff => {
                const option = document.createElement('option');
                option.value = staff.id;
                option.textContent = staff.name;
                staffFilter.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Error fetching staff list:', error);
    });
}

// Load report data based on selected filters
function loadReportData(page = 1) {
    // Show loading indicators
    showLoadingState();
    
    // Get filter values
    const dateFilter = document.getElementById('date-filter').value;
    const staffFilter = document.getElementById('staff-filter').value;
    const serviceFilter = document.getElementById('service-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    
    // Get custom date range if selected
    let startDate = null;
    let endDate = null;
    if (dateFilter === 'custom') {
        startDate = document.getElementById('start-date').value;
        endDate = document.getElementById('end-date').value;
    }
    
    // Get the CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Make API request
    fetch('/admin/report-data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({
            date_filter: dateFilter,
            staff_id: staffFilter,
            service_category: serviceFilter,
            status: statusFilter,
            start_date: startDate,
            end_date: endDate,
            page: page
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingState();
        
        if (data.success) {
            // Update summary stats
            updateSummaryStats(data.stats);
            
            // Update charts
            updateCharts(data.charts);
            
            // Update detailed table
            updateDetailedTable(data.requests);
            
            // Update staff performance table
            updateStaffPerformanceTable(data.staff_performance);
            
            // Update out-of-specialization section
            updateOutOfSpecSection(data.out_of_spec_requests);
        } else {
            console.error('Failed to load report data:', data.message);
            showErrorMessage('Failed to load report data. Please try again.');
        }
    })
    .catch(error => {
        hideLoadingState();
        console.error('Error loading report data:', error);
        showErrorMessage('Error loading data. Please try again.');
    });
}

// Show loading state
function showLoadingState() {
    // Show loading spinners or indicators as needed
    document.getElementById('detailed-requests-table').querySelector('tbody').innerHTML = 
        '<tr><td colspan="8" class="text-center"><i class="fas fa-spinner fa-spin mr-2"></i>Loading data...</td></tr>';
    
    // Add loading indicators for other sections if needed
}

// Hide loading state
function hideLoadingState() {
    // Hide loading spinners or indicators as needed
}

// Show error message
function showErrorMessage(message) {
    document.getElementById('detailed-requests-table').querySelector('tbody').innerHTML = 
        `<tr><td colspan="8" class="text-center text-danger">${message}</td></tr>`;
}

// Update summary statistics
function updateSummaryStats(stats) {
    document.getElementById('total-requests').textContent = stats.total_requests;
    document.getElementById('completed-requests').textContent = stats.completed_requests;
    document.getElementById('avg-response-time').textContent = stats.avg_response_time + 'h';
    document.getElementById('completion-rate').textContent = stats.completion_rate + '%';
}

// Update all charts with new data
function updateCharts(chartData) {
    // Update Monthly Trends Chart
    if (chartData.monthly_trends) {
        window.monthlyTrendsChart.data.labels = chartData.monthly_trends.labels;
        window.monthlyTrendsChart.data.datasets[0].data = chartData.monthly_trends.data;
        window.monthlyTrendsChart.update();
    }
    
    // Update Staff Performance Chart
    if (chartData.staff_performance) {
        window.staffPerformanceChart.data.labels = chartData.staff_performance.labels;
        window.staffPerformanceChart.data.datasets[0].data = chartData.staff_performance.assigned;
        window.staffPerformanceChart.data.datasets[1].data = chartData.staff_performance.completed;
        window.staffPerformanceChart.update();
    }
    
    // Update Service Category Chart
    if (chartData.service_categories) {
        window.serviceCategoryChart.data.labels = chartData.service_categories.labels;
        window.serviceCategoryChart.data.datasets[0].data = chartData.service_categories.data;
        window.serviceCategoryChart.update();
    }
    
    // Update Service Category Trend Chart
    if (chartData.service_category_trends) {
        window.serviceCategoryTrendChart.data.labels = chartData.service_category_trends.labels;
        window.serviceCategoryTrendChart.data.datasets = chartData.service_category_trends.datasets;
        window.serviceCategoryTrendChart.update();
    }
    
    // Update Status Distribution Chart
    if (chartData.status_distribution) {
        window.statusDistributionChart.data.labels = chartData.status_distribution.labels;
        window.statusDistributionChart.data.datasets[0].data = chartData.status_distribution.data;
        window.statusDistributionChart.update();
    }
}

// Update chart type (line/bar)
function updateChartType(chartId, newType) {
    if (window[chartId]) {
        window[chartId].config.type = newType;
        window[chartId].update();
    }
}

// Update detailed requests table
function updateDetailedTable(requestsData) {
    const tableBody = document.getElementById('detailed-requests-table').querySelector('tbody');
    const paginationContainer = document.getElementById('pagination-container');
    
    // Clear existing table rows
    tableBody.innerHTML = '';
    
    if (requestsData.data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No requests found matching the selected filters.</td></tr>';
        paginationContainer.innerHTML = '';
        return;
    }
    
    // Add new rows
    requestsData.data.forEach(request => {
        const row = document.createElement('tr');
        
        // Status class
        let statusClass = '';
        switch (request.status) {
            case 'Pending':
                statusClass = 'text-warning';
                break;
            case 'In Progress':
                statusClass = 'text-primary';
                break;
            case 'Completed':
                statusClass = 'text-success';
                break;
            case 'Rejected':
                statusClass = 'text-danger';
                break;
        }
        
        row.innerHTML = `
            <td>${request.id}</td>
            <td>${request.date}</td>
            <td>${request.service_type}</td>
            <td>${request.requester}</td>
            <td>${request.assigned_to}</td>
            <td class="${statusClass}">${request.status}</td>
            <td>${request.response_time}</td>
            <td>${request.completion_time}</td>
        `;
        
        tableBody.appendChild(row);
    });
    
    // Build pagination
    buildPagination(requestsData);
}

// Build pagination controls
function buildPagination(requestsData) {
    const paginationContainer = document.getElementById('pagination-container');
    paginationContainer.innerHTML = '';
    
    if (!requestsData.last_page || requestsData.last_page <= 1) {
        return;
    }
    
    // Previous button
    const prevLi = document.createElement('li');
    prevLi.classList.add('page-item');
    if (requestsData.current_page === 1) {
        prevLi.classList.add('disabled');
    }
    prevLi.innerHTML = `<a class="page-link" href="#" data-page="${requestsData.current_page - 1}">&laquo;</a>`;
    paginationContainer.appendChild(prevLi);
    
    // Page numbers
    let startPage = Math.max(1, requestsData.current_page - 2);
    let endPage = Math.min(requestsData.last_page, startPage + 4);
    
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.classList.add('page-item');
        if (i === requestsData.current_page) {
            li.classList.add('active');
        }
        li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
        paginationContainer.appendChild(li);
    }
    
    // Next button
    const nextLi = document.createElement('li');
    nextLi.classList.add('page-item');
    if (requestsData.current_page === requestsData.last_page) {
        nextLi.classList.add('disabled');
    }
    nextLi.innerHTML = `<a class="page-link" href="#" data-page="${requestsData.current_page + 1}">&raquo;</a>`;
    paginationContainer.appendChild(nextLi);
    
    // Add event listeners to pagination links
    document.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = parseInt(this.getAttribute('data-page'));
            loadReportData(page);
        });
    });
}

// Update staff performance table
function updateStaffPerformanceTable(staffData) {
    const tableBody = document.getElementById('staff-performance-tbody');
    tableBody.innerHTML = '';
    
    if (staffData.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No staff performance data available.</td></tr>';
        return;
    }
    
    // Sort by performance (highest first)
    staffData.sort((a, b) => b.performance - a.performance);
    
    staffData.forEach(staff => {
        const row = document.createElement('tr');
        
        // Determine progress bar color
        let progressClass = 'bg-success';
        if (staff.performance < 70) {
            progressClass = 'bg-danger';
        } else if (staff.performance < 85) {
            progressClass = 'bg-warning';
        }
        
        row.innerHTML = `
            <td>${staff.name}</td>
            <td>${staff.assigned}</td>
            <td>${staff.completed}</td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="mr-2">${staff.performance}%</div>
                    <div class="progress flex-grow-1">
                        <div class="progress-bar ${progressClass}" role="progressbar" style="width: ${staff.performance}%"></div>
                    </div>
                </div>
            </td>
        `;
        
        tableBody.appendChild(row);
    });
}

// Update out-of-specialization section
function updateOutOfSpecSection(requestsData) {
    const container = document.getElementById('out-of-spec-container');
    container.innerHTML = '';
    
    if (!requestsData || requestsData.length === 0) {
        container.innerHTML = `
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> No out-of-specialization assignments detected during this period.
            </div>
        `;
        return;
    }
    
    // Create tags for common service categories
    const getCategoryTag = (category) => {
        let tagClass = 'tag-misc';
        
        if (category.includes('Computer') || category.includes('Hardware') || category.includes('Printer')) {
            tagClass = 'tag-hardware';
        } else if (category.includes('Software') || category.includes('Application')) {
            tagClass = 'tag-software';
        } else if (category.includes('Internet') || category.includes('Network') || category.includes('Connection')) {
            tagClass = 'tag-network';
        } else if (category.includes('Email') || category.includes('Account') || category.includes('Password')) {
            tagClass = 'tag-account';
        }
        
        return `<span class="service-tag ${tagClass}">${category}</span>`;
    };
    
    let html = '<div class="alert alert-warning mb-3">';
    html += '<i class="fas fa-exclamation-triangle"></i> ';
    html += `<strong>${requestsData.length} requests</strong> are assigned to staff outside their primary expertise.`;
    html += '</div>';
    
    html += '<div class="list-group">';
    requestsData.forEach(request => {
        html += `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${request.id}</strong>: ${getCategoryTag(request.service_type)}
                    </div>
                    <span class="badge badge-secondary">${request.staff_name}</span>
                </div>
                <small class="text-muted">Primary expertise: ${request.primary_expertise}</small>
            </div>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
}


function exportToExcel() {
    // Show loading spinner
    const exportBtn = document.getElementById('export-report');
    const originalContent = exportBtn.innerHTML;
    exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
    exportBtn.disabled = true;
    
    // Get filter values
    const dateFilter = document.getElementById('date-filter').value;
    const staffId = document.getElementById('staff-filter').value;
    const serviceCategory = document.getElementById('service-filter').value;
    const status = document.getElementById('status-filter').value;
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    
    // Get CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Create form data
    const formData = new FormData();
    formData.append('_token', token);
    formData.append('date_filter', dateFilter);
    formData.append('staff_id', staffId);
    formData.append('service_category', serviceCategory);
    formData.append('status', status);
    if (startDate) formData.append('start_date', startDate);
    if (endDate) formData.append('end_date', endDate);
    
    // Use fetch to download the file
    fetch('/admin/export-report', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.blob();
    })
    .then(blob => {
        // Create a URL for the blob
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = 'UITC_Report_' + new Date().toISOString().slice(0,10) + '.xlsx';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    })
    .catch(error => {
        console.error('Error downloading file:', error);
        alert('Error downloading file. Please try again.');
    })
    .finally(() => {
        // Restore button state
        exportBtn.innerHTML = originalContent;
        exportBtn.disabled = false;
    });
}

function exportToPDF() {
    // Show loading spinner
    const exportBtn = document.getElementById('export-pdf');
    const originalContent = exportBtn.innerHTML;
    exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
    exportBtn.disabled = true;
    
    // Get filter values
    const dateFilter = document.getElementById('date-filter').value;
    const staffId = document.getElementById('staff-filter').value;
    const serviceCategory = document.getElementById('service-filter').value;
    const status = document.getElementById('status-filter').value;
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    
    // Get CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Create form data
    const formData = new FormData();
    formData.append('_token', token);
    formData.append('date_filter', dateFilter);
    formData.append('staff_id', staffId);
    formData.append('service_category', serviceCategory);
    formData.append('status', status);
    if (startDate) formData.append('start_date', startDate);
    if (endDate) formData.append('end_date', endDate);
    
    // Use fetch to download the file
    fetch('/admin/export-pdf', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.blob();
    })
    .then(blob => {
        // Create a URL for the blob
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = 'UITC_Report_' + new Date().toISOString().slice(0,10) + '.pdf';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    })
    .catch(error => {
        console.error('Error downloading file:', error);
        alert('Error downloading file. Please try again.');
    })
    .finally(() => {
        // Restore button state
        exportBtn.innerHTML = originalContent;
        exportBtn.disabled = false;
    });
}function exportToExcel() {
    // Show loading spinner
    const exportBtn = document.getElementById('export-report');
    const originalContent = exportBtn.innerHTML;
    exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
    exportBtn.disabled = true;
    
    // Get filter values
    const dateFilter = document.getElementById('date-filter').value;
    const staffId = document.getElementById('staff-filter').value;
    const serviceCategory = document.getElementById('service-filter').value;
    const status = document.getElementById('status-filter').value;
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    
    // Get the CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Create form for direct file download
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/export-report'; // Match the route path
    form.style.display = 'none';
    document.body.appendChild(form);
    
    // Add CSRF token
    const csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_token';
    csrfField.value = token;
    form.appendChild(csrfField);
    
    // Add filter values
    const addField = (name, value) => {
        if (value) {
            const field = document.createElement('input');
            field.type = 'hidden';
            field.name = name;
            field.value = value;
            form.appendChild(field);
        }
    };
    
    addField('date_filter', dateFilter);
    addField('staff_id', staffId);
    addField('service_category', serviceCategory);
    addField('status', status);
    addField('start_date', startDate);
    addField('end_date', endDate);
    
    // Submit form for direct download
    form.submit();
    
    // Clean up
    setTimeout(() => {
        document.body.removeChild(form);
        exportBtn.innerHTML = originalContent;
        exportBtn.disabled = false;
    }, 2000);
}

function exportToPDF() {
    // Show loading spinner
    const exportBtn = document.getElementById('export-pdf');
    const originalContent = exportBtn.innerHTML;
    exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
    exportBtn.disabled = true;
    
    // Get filter values
    const dateFilter = document.getElementById('date-filter').value;
    const staffId = document.getElementById('staff-filter').value;
    const serviceCategory = document.getElementById('service-filter').value;
    const status = document.getElementById('status-filter').value;
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    
    // Get the CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Create form for direct file download
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/export-pdf'; // Match the route path
    form.style.display = 'none';
    document.body.appendChild(form);
    
    // Add CSRF token
    const csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_token';
    csrfField.value = token;
    form.appendChild(csrfField);
    
    // Add filter values
    const addField = (name, value) => {
        if (value) {
            const field = document.createElement('input');
            field.type = 'hidden';
            field.name = name;
            field.value = value;
            form.appendChild(field);
        }
    };
    
    addField('date_filter', dateFilter);
    addField('staff_id', staffId);
    addField('service_category', serviceCategory);
    addField('status', status);
    addField('start_date', startDate);
    addField('end_date', endDate);
    
    // Submit form for direct download
    form.submit();
    
    // Clean up
    setTimeout(() => {
        document.body.removeChild(form);
        exportBtn.innerHTML = originalContent;
        exportBtn.disabled = false;
    }, 2000);
}