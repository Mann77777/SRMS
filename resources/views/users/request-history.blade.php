<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/service-history.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Service History</title>
</head>
<body>
    
    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')


 
    <div class="content">
        <h1>History</h1>
    <!--    <p>Welcome, <strong>{{ Auth::user()->username }}!</strong></p> -->
       
        <div class="history-table-container">
            <form action="">
                <table class="history-table">
                    <thead>
                       <tr>
                            <th>Request ID</th>
                            <th>Service</th>
                            <th>Assigned Staff</th>
                            <th>Date Submitted</th>
                            <th>Date Completed</th>
                            <th>Survey</th>
                       </tr>
                    </thead>
                    <tbody>
                        @forelse($completedRequests as $request)
                        <tr>
                            <td>{{ $request->id }}</td>
                            <td>{{ $request->service_category }}</td>
                            <td>
                                {{ $request->assignedUITCStaff ? $request->assignedUITCStaff->name : 'N/A' }}
                            </td>                            
                            <td>
                                <span>{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y') }}</span><br>
                                <span>{{ \Carbon\Carbon::parse($request->created_at)->format('h:i A') }}</span>
                            </td>
                            <td>
                               <span>{{ \Carbon\Carbon::parse($request->updated_at)->format('M d, Y') }}</span><br>
                                <span>{{ \Carbon\Carbon::parse($request->updated_at)->format('h:i A') }}</span>
                            </td>
                            <td>
                                <a href="{{ route('service.survey', $request->id) }}" class="btn btn-primary">Take a Survey</a>
                            </td>
                           
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No completed requests found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    @stack('scripts') 

</body>
</html>