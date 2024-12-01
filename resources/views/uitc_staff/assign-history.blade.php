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
    <link href="{{ asset('css/assign-history.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Assignment History</title>
</head>
<body>
     <!-- Include Navbar -->
     @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    <div class="content">
        <h1>Assignment History</h1>

        <div class="dropdown-container">
            <!-- Search Bar -->
            <div class="search-container">
                <div class="search-input-wrapper">
                    <input type="text" id="history-search" name="history-search" placeholder="Search history...">
                    <i class="fas fa-search search-icon"></i>
                </div>            
            </div>

            <!-- Date Range Filter -->
            <div class="date-filter">
                <label>From:</label>
                <input type="date" id="date-from" name="date-from">
                <label>To:</label>
                <input type="date" id="date-to" name="date-to">
            </div>

            <!-- Status Filter -->
            <select name="status" id="status">
                <option value="all">All Status</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>

        </div>


        <div class="assignhistory-table-container">
            <h4>Assigned Request List</h4>
            <div class="assignhistory-table-wrapper">
                <table class="assignhistory-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Request Data</th>
                            <th>Date Assigned</th>
                            <th>Date Completed</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>021</td>
                            <td>
                                <strong>Name: </strong>Marielle Verdaluza<br>
                                <strong>Username: </strong>Marielle Verdaluza<br>
                                <strong>Email: </strong>Marielle Verdaluza<br>
                                <strong>Services: </strong>Marielle Verdaluza<br>
                            </td>
                            <td>2024-11-01</td>
                            <td>2024-11-02</td>
                            <td>Completed</td>
                            <td class="b">
                                <button class="btn-edit" title="Edit">View</button>
                                <button class="btn-status" title="Toggle Status">Download</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


    </div>


    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>

</body>
</html>