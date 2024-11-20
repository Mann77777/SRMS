<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Dashboard</title>
</head>
<body>

    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')

    <!-- HERO SECTION -->
    <section class="hero">
        <?php
        date_default_timezone_set('Asia/Manila'); // Set your timezone
        $hour = date('H'); // Get the current hour in 24-hour format

        // Determine the greeting based on the current hour
        if ($hour >= 5 && $hour < 12) {
            $greeting = "Good Morning";
        } elseif ($hour >= 12 && $hour < 18) {
            $greeting = "Good Afternoon";
        } else {
            $greeting = "Good Evening";
        }
        ?>

        <h1><?php echo $greeting . ", " . Auth::user()->username . "!"; ?></h1>

        <!-- Role-based Buttons -->
        <!--
        <div class="button-container">
            @if(Auth::user()->role === 'Student')
                <button onclick="window.location.href='/student-request'" class="btn-primary">Request Student Service</button>
                <button onclick="window.location.href='/student-status'" class="btn-secondary">Check Status</button>
            @elseif(Auth::user()->role === 'Faculty & Staff')
                <button onclick="window.location.href='/faculty-service'" class="btn-primary">Request Faculty & Staff Services</button>
                <button onclick="window.location.href='/faculty-status'" class="btn-secondary">Check Status</button>
            @endif
        </div>
        -->
</section>


    <section class="container my-4">
        <div class="row">
            <!-- Total Requests Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Requests</h5>
                        <p class="card-text h1">{{ $totalRequests ?? 0 }}</p>
                        <a href="">View</a>
                    </div>
                </div>
            </div>

            <!-- Pending Requests Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Pending Requests</h5>
                        <p class="card-text h1">{{ $pendingRequests ?? 0 }}</p>
                        <a href="">View</a>
                    </div>
                </div>
            </div>

            <!-- Completed Requests Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Completed Requests</h5>
                        <p class="card-text h1">{{ $completedRequests ?? 0 }}</p>
                        <a href="">View</a>
                    </div>
                </div>
            </div>  
    </section>


    <!-- Recent Requests Section -->
     <div class="recentreq-table-container">
        <h4>Recent Request</h4>
        <form action="">
            <div class="recentreq-table">
                <table class="recentreq-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Service</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>MS Teams</td>
                            <td>2024-11-01</td>
                            <td>Pending</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
     </div>
    
    <button class="btn-primary" onclick="window.location.href='{{ url('/submit-request') }}'">Submit Request</button>

    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
   
    <script src="https://cdn.jsdelivr.net/npm/botman-web-widget@0/build/js/widget.js"></script>

    <script>
        var botmanWidget = {
            aboutText: 'Powered by BotMan',
            introMessage: "Welcome to SRMS"
        };
    </script>
 
</body>
</html>
