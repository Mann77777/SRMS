<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin_servicerequest.css') }}" rel="stylesheet">

    <title>Admin - Service Request</title>
</head>
<body>
    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
        
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    <div class="content">
        <h1>Service Request</h1>
        <div class="dropdown-container">
            <select id="status" name="status_id">
                <option value="peding">Pending</option>
                <option value="in progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
            <div class="requests-btn">
                <button type="button" class="delete-button" id="delete-btn">Delete</button>
                <button type="button" class="cancel-btn" id="cancel-btn">Cancel</button>
                <button type="button" class="confirm-btn" id="confirm-btn">Confirm</button>
            </div>
        </div>

        <div class="request-table-container">
            <h4>Request List</h4>
            <form action="" id="delete-form">
                <table class="request-table">
                    <thead>
                        <tr>
                            <th class="left"><input type="checkbox" id="select-all"></th>
                            <th>Request ID</th>
                            <th>Request Data</th>
                            <th>Services</th>
                            <th>Request Date & Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>1</td>
                            <td>
                                <strong>First name: </strong><span>Marielle</span><br>
                                <strong>Last name: </strong><span>Verdaluza</span><br>
                                <strong>Email: </strong><span>m@gmail.com</span>
                            </td>
                            <td>
                                <strong>Service: </strong><span>MS Teams</span><br>
                                <strong>UITC Staff: </strong><span>John</span>
                            </td>
                            <td>
                                <strong>Date: </strong><span>2024-11-01</span><br>
                                <strong>Time: </strong><span>9:15 AM</span>
                            </td>
                            <td>Pending</td>
                        </tr>

                        <tr>
                            <td><input type="checkbox"></td>
                            <td>2</td>
                            <td>
                                <strong>First name: </strong><span>Marielle</span><br>
                                <strong>Last name: </strong><span>Verdaluza</span><br>
                                <strong>Email: </strong><span>m@gmail.com</span>
                            </td>
                            <td>
                                <strong>Service: </strong><span>MS Teams</span><br>
                                <strong>UITC Staff: </strong><span>John</span>
                            </td>
                            <td>
                                <strong>Date: </strong><span>2024-11-01</span><br>
                                <strong>Time: </strong><span>9:15 AM</span>
                            </td>
                            <td>Pending</td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>

    </div>
</body>
</html>