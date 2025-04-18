<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin-report.css') }}" rel="stylesheet">
    <title>Report</title>
</head>
<body>
    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h1 class="mt-4 mb-4">Service Request Reports</h1>
            
            <!-- Filter Controls -->
            <div class="report-section filter-controls">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date-filter"><strong>Date Range</strong></label>
                            <select class="form-control" id="date-filter">
                                <option value="current_month">Current Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="last_3_months">Last 3 Months</option>
                                <option value="last_6_months">Last 6 Months</option>
                                <option value="year_to_date">Year to Date</option>
                                <option value="last_year">Last Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="staff-filter"><strong>UITC Staff</strong></label>
                            <select class="form-control" id="staff-filter">
                                <option value="all">All Staff</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="service-filter"><strong>Service Category</strong></label>
                            <select class="form-control" id="service-filter">
                                <option value="all">All Categories</option>
                                <option value="create">Create MS Office/TUP Email</option>
                                <option value="reset_email_password">Reset Email Password</option>
                                <option value="reset_tup_web_password">Reset TUP Web Password</option>
                                <option value="reset_ers_password">Reset ERS Password</option>
                                <option value="computer_repair_maintenance">Computer Repair</option>
                                <option value="printer_repair_maintenance">Printer Repair</option>
                                <option value="biometrics_enrollement">Biometrics Enrollment</option>
                                <option value="install_application">Install Application/Software</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status-filter"><strong>Status</strong></label>
                            <select class="form-control" id="status-filter">
                                <option value="all">All Statuses</option>
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row custom-date-range" style="display:none;">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start-date">Start Date</label>
                            <input type="date" class="form-control" id="start-date">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="end-date">End Date</label>
                            <input type="date" class="form-control" id="end-date">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12 text-right">
                        <button class="btn btn-primary" id="apply-filters">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <button class="export-btn ml-2" id="export-report">
                            <i class="fas fa-file-export"></i> Export Report
                        </button>
                        <!-- <button class="export-btn ml-2" id="export-pdf">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button> -->
                    </div>
                </div>
            </div>
            
            <!-- Summary Stats Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <h3 id="total-requests">0</h3>
                        <p>Total Requests</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card green">
                        <h3 id="completed-requests">0</h3>
                        <p>Completed Requests</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card yellow">
                        <h3 id="avg-response-time">0h</h3>
                        <p>Avg. Response Time</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card purple">
                        <h3 id="completion-rate">0%</h3>
                        <p>Completion Rate</p>
                    </div>
                </div>
            </div>
            
            <!-- Monthly Request Trends -->
            <div class="report-section">
                <div class="section-header">
                    <h2>Monthly Request Trends</h2>
                    <div class="chart-actions">
                        <button class="btn btn-sm btn-outline-secondary active" data-chart-view="line">Line</button>
                        <button class="btn btn-sm btn-outline-secondary" data-chart-view="bar">Bar</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="monthlyTrendsChart" height="380"></canvas>
                </div>
            </div>
            
            <!-- Staff Performance Section -->
            <div class="report-section">
                <div class="section-header">
                    <h2>UITC Staff Performance</h2>
                </div>
                <div class="chart-container" style="height: auto;">
                    <div class="row">
                        <div class="col-md-7">
                            <canvas id="staffPerformanceChart" height="300"></canvas>
                        </div>
                        <div class="col-md-5">
                            <h4>Top Performing Staff</h4>
                            <div class="table-responsive mt-3">
                                <table class="staff-performance-table">
                                    <thead>
                                        <tr>
                                            <th>Staff Name</th>
                                            <th>Assigned</th>
                                            <th>Completed</th>
                                            <th>Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody id="staff-performance-tbody">
                                        <tr>
                                            <td>Loading...</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Service Category Distribution -->
            <div class="report-section">
                <div class="section-header">
                    <h2>Service Category Distribution</h2>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="serviceCategoryChart" height="300"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="serviceCategoryTrendChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Request Status Distribution 
            <div class="report-section">
                <div class="section-header">
                    <h2>Request Status Distribution</h2>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="statusDistributionChart" height="300"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container" style="height: auto; padding: 20px;">
                            <h4>Out-of-Specialization Requests</h4>
                            <p class="text-muted">Requests assigned to staff outside their primary expertise area</p>
                            <div id="out-of-spec-container" class="mt-4">
                                <div class="alert alert-secondary">
                                    <i class="fas fa-info-circle"></i> Filter data to see out-of-specialization assignments
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            
            <!-- Detailed Request Table -->
            <div class="report-section">
                <div class="section-header">
                    <h2>Detailed Request Data</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped" id="detailed-requests-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Date</th>
                                <th>Service Type</th>
                                <th>Requester</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th>Response Time</th>
                                <th>Completion Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center">Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center" id="pagination-container">
                            <!-- Pagination will be generated here -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
    <script src="{{ asset('js/admin-report.js') }}"></script>
</body>
</html>