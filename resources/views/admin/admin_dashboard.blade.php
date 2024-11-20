<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="{{ asset('css/admin_dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Admin Dashboard</title>
</head>
<body>

    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')
    
    <div class="content">
        <h1>Dashboard</h1>
        <div class="button-container">
            @if(Auth::guard('admin')->user()->role === 'Admin')
            @elseif(Auth::guard('admin')->user()->role === 'Technician')
            @endif
        </div>
    </div>

    <section class="container my-4">
        <div class="row">
            @if(Auth::guard('admin')->user()->role === 'Admin')
                <!-- Request Receive -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Request Receive</h5>
                            <p class="card-text h1">{{ $requestReceive ?? 0 }}</p>
                            <a href="">View</a>
                        </div>
                    </div>
                 </div>

                <!-- Assign Request -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Assign Request</h5>
                            <p class="card-text h1">{{ $assignRequest ?? 0 }}</p>
                            <a href="">View</a>
                        </div>
                    </div>
                </div>

                <!-- Services Completed -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Services Completed</h5>
                            <p class="card-text h1">{{ $servicesCompleted ?? 0 }}</p>
                            <a href="">View</a>
                        </div>
                    </div>
                </div>

                <!-- Assign UITC Staff -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Assign UITC Staff</h5>
                            <p class="card-text h1">{{ $assignStaff ?? 0 }}</p>
                            <a href="">View</a>
                        </div>
                    </div>
                </div>

                <!-- Survey Ratings -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Survey Ratings</h5>
                            <p class="card-text h1">{{ $surveyRatings ?? 0 }}</p>
                            <a href="">View</a>
                        </div>
                    </div>
                </div>
            @elseif(Auth::guard('admin')->user()->role === 'Technician')
                <!-- Assigned Requests -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Assigned Requests</h5>
                            <p class="card-text h1">{{ $assignedRequests ?? 0 }}</p>
                            <a href="">View</a>
                        </div>
                    </div>
                </div>

                <!-- Services Completed -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Services Completed</h5>
                            <p class="card-text h1">{{ $servicesCompleted ?? 0 }}</p>
                            <a href="">View</a>
                        </div>
                    </div>
                </div>

                <!-- Survey Ratings -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Survey Ratings</h5>
                            <p class="card-text h1">{{ $surveyRatings ?? 0 }}</p>
                            <a href="">View</a>
                        </div>
                    </div>
                </div>
                 @endif
        </div>
    </section>

    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
