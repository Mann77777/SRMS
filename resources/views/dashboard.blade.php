<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .sidebar {
            min-width: 200px;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
            color: white;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar p-3">
        <h4>SRMS</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
            <a class="nav-link" href="{{ route('service-request') }}">Service Request</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Request Status</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Service History</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Notification</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Message</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('home') }}">Profile</a>
            </li>
            <li class="nav-item">
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-link nav-link" style="color: white;">Logout</button>
                </form>
            </li>
        </ul>
    </div>

    <div class="content">
        <h2>Welcome to Your Dashboard!</h2>
        <p>Here you can manage your requests and view your profile.</p>
        <!-- Add more content here as needed -->
    </div>
</body>
</html>
