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
    <link href="{{ asset('css/request-form-management.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Request Form Management</title>
</head>
<body>

    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    <div class="reqform-content">
        <div class="reqform-header">
            <h1>Request Form Management</h1>
        </div>

        <div class="reqform-btn">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addReqFormModal">
                <i class="fas fa-plus"></i> Add New Request Form
            </button>
        </div>

        <!-- Add this above the request form table 
        <div class="filter-section mb-3">
            <label for="userTypeFilter">Filter by User Type:</label>
            <select id="userTypeFilter" class="form-control">
                <option value="">All Request Forms</option>
                <option value="student">Student Request Forms</option>
                <option value="faculty_staff">Faculty & Staff Request Forms</option>
            </select>
        </div> -->

        <div class="dropdown-container">
            <!-- User Type Filter -->
            <select name="userTypeFilter" id="userTypeFilter">
                <option value="">All Request Forms</option>
                <option value="student">Student Request Forms</option>
                <option value="faculty_staff">Faculty & Staff Request Forms</option>
            </select>
        </div>


        <div class="reqform-table-container mt-4">
            <h4>Request Form</h4>
            <div id="reqform-table-wrapper">
                <table class="table table-striped" id="requestFormTable">
                        <thead>
                            <tr>
                                <th>Form Name</th>
                                <th>Description</th>
                                <th>User Type</th>
                                <th>Options</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="requestFormTableBody">
                            <!-- Request forms will be dynamically populated here -->
                        </tbody>
                    </table>
            </div>
        </div>

    </div>

            <!-- Add Request Form Modal -->
            <div class="modal fade" id="addReqFormModal" tabindex="-1" role="dialog" aria-labelledby="addReqFormModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addReqFormModalLabel">Create New Request Form</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="addRequestFormForm">
                                <div class="form-group">
                                    <label for="formName">Form Name</label>
                                    <input type="text" class="form-control" id="formName" required>
                                </div>
                                <div class="form-group">
                                    <label for="formDescription">Description</label>
                                    <textarea class="form-control" id="formDescription" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="userType">User Type</label>
                                    <select class="form-control" id="userType" name="userType">
                                        <option value="student">Student</option>
                                        <option value="faculty_staff">Faculty & Staff</option>
                                    </select>
                                </div>
                                <div id="formFieldsContainer">
                                    <h6>Options</h6>
                                    <div id="dynamicOptions">
                                        <!-- Dynamic options will be added here -->
                                    </div>
                                    <button type="button" class="btn btn-secondary mt-2" id="addOptionBtn">
                                        <i class="fas fa-plus"></i> Add Option
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="saveRequestFormBtn">Save Request Form</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    <!-- Required Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script src="{{ asset('js/request-form-management.js') }}"></script>
</body>
</html>