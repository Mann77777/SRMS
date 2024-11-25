<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/myrequest.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>My Requests</title>
</head>
<body>
   
    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')

    <div class="content">
        <h1>My Request</h1>
        <div class="form-container">
            <div class="dropdown-container">
                <select name="" id="">
                    <option value="pending">Pending</option>
                    <option value="in progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>

                <!-- Search Bar -->
                <div class="search-container">
                    <input type="text" name="" placeholder="Search...">
                    <button class="search-btn" type="button" onclick="performSearch()">Search</button>
                </div>

            </div>
            <div class="request-table-container">
                <form action="">
                    <table class="request-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Service</th>
                                <th>Date Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <td>1</td>
                            <td>Ms Teams</td>
                            <td>2024-11-01</td>
                            <td>Pending</td>
                            <td>
                                <button class="btn-edit">Edit</button>
                                <button class="btn-view">View</button>
                                <button class="btn-delete">Delete</button>
                            </td>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/navbar-sidebar.js') }}" defer></script>

</body>
</html>