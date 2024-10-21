<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service History</title>
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
        .history-section {
            max-width: 800px;
            margin: 0 auto;
        }
        .history-section h2 {
            font-size: 24px;
            font-weight: bold;
        }
        .history-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .history-table th, .history-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .history-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .survey-button {
            margin-top: 20px;
            text-align: right;
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
                <a class="nav-link" href="{{ route('service-history') }}">Service History</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Request Status</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Notifications</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Message</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('profile') }}">Profile</a>
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
        <div class="history-section">
            <h2>History</h2>
            <p>Welcome, <strong>name!</strong></p>

            <table class="history-table">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Technician</th>
                        <th>Review and Ratings</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>001</td>
                        <td>Internet Setup</td>
                        <td>2023-09-15</td>
                        <td>10:00 AM</td>
                        <td>John Doe</td>
                        <td><button class="btn btn-secondary">Take a survey</button></td>
                    </tr>
                    <!-- Repeat rows as needed for more requests -->
                </tbody>
            </table>

            <div class="survey-button">
                <button class="btn btn-primary">Take a survey</button>
            </div>
        </div>
    </div>
</body>
</html>
