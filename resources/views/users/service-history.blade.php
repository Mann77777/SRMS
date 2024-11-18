<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/student-request.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Service History</title>
</head>
<body>
    
    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')

    <div class="history-header">
        <h2>History</h2>
        <p>Welcome, <strong>{{ Auth::user()->username }}!</strong></p>
    </div>
 
    <div class="content">
        <div class="history-section">
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

        </div>
    </div>

    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    @stack('scripts') 

</body>
</html>