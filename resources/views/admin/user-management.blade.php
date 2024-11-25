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
    <link href="{{ asset('css/user-management.css') }}" rel="stylesheet">
    <title>Admin - User Management</title>
</head>
<body>
    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    <div class="content">
        <h1>User Management</h1>
        <div class="form-container">
            <div class="dropdown-container">
                <select name="user_role" id="role">
                    <option value="student">Student</option>
                    <option value="facult-staff">Faculty & Staff</option>
                </select>

                <!-- Search Bar -->
                <div class="search-container">
                    <input type="text" id="user-search" name="user-search" placeholder="Search users...">
                </div>
            </div>

        <div class="user-table-container">
            <h4>Users List</h4>
            <form action="">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Users Data</th>
                            <th>Role</th>
                            <th>Account Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>
                                <strong>First name: </strong><span>Marielle</span><br>
                                <strong>Last name: </strong><span>Verdaluza</span><br>
                                <strong>Email: </strong><span>m@gmail.com</span>
                            </td>
                            <td>Student</td>
                            <td>
                                <strong>Date: </strong><span></span><br>
                                <strong>Time: </strong><span></span>
                            </td>
                            <td>
                                <button class="btn-edit">Edit</button>
                                <button class="btn-view">View</button>
                                <button class="btn-delete">Delete</button>
                            </td>
                        </tr>

                        <tr>
                            <td>1</td>
                            <td>
                                <strong>First name: </strong><span>Marielle</span><br>
                                <strong>Last name: </strong><span>Verdaluza</span><br>
                                <strong>Email: </strong><span>marielle.verdaluza@tup.edu.ph</span>
                            </td>
                            <td>Student</td>
                            <td>
                                <strong>Date: </strong><span>2024-11-11</span><br>
                                <strong>Time: </strong>07:59 PM<span></span>
                            </td>
                            <td>
                                <button class="btn-edit">Edit</button>
                                <button class="btn-view">View</button>
                                <button class="btn-delete">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
</body>
</html>