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
    <link href="{{ asset('css/assign-request.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Assignment Request</title>
</head>
<body>
    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    <div class="content">
        <h1>Assigned Requests</h1>

        <div class="dropdown-container">
            <!-- Search Bar -->
            <div class="search-container">
                <div class="search-input-wrapper">
                    <input type="text" id="user-search" name="user-search" placeholder="Search users...">
                    <i class="fas fa-search search-icon"></i>
                </div>            
            </div>

            <!-- Status Filter -->
            <select name="status" id="status">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="in progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>

            <!-- Transaction Filter -->
            <select name="status" id="status">
                <option value="all">All Transaction</option>
                <option value="simple">Simple Transaction</option>
                <option value="complex">Complex Transaction</option>
                <option value="highly technical">Highly Technical Transaction</option>
            </select>
        </div>

        <div class="assignreq-table-container">
            <h4>Assigned Request List</h4>
            <div class="assignreq-table-wrapper">
                <table class="assignreq-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Request Details</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Transaction Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignedRequests as $request)
                        <tr>
                            <td>{{ $request->id }}</td>
                            <td>
                            {!! 
                            '<strong>Name:</strong> ' . $request->first_name . ' ' . $request->last_name . '<br>' .
                            '<strong>Student ID:</strong> ' . $request->student_id . '<br>' .
                            '<strong>Service:</strong> ' . $request->service_category 
                            !!}
                        </td>
                        <td>{{ $request->data_type }}</td>
                        <td>
                            <strong>Date:</strong> {{ \Carbon\Carbon::parse($request->created_at)->format('Y-m-d') }}<br>
                            <strong>Time:</strong> {{ \Carbon\Carbon::parse($request->created_at)->format('g:i A') }}
                        </td>
                     <td>

                    <span class="badge 
                        @if($request->status == 'Pending') badge-warning
                        @elseif($request->status == 'In Progress') badge-info
                        @elseif($request->status == 'Completed') badge-success
                        @else badge-secondary
                        @endif">
                        {{ $request->status }}
                    </span>
                </td>
                <td class="btns">
                    <button class="btn-view" onclick="viewRequestDetails({{ $request->id }})">View</button>
                    <button class="btn-complete" onclick="completeRequest({{ $request->id }})">Complete</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="empty-state">
                    <i class="fas fa-inbox fa-3x"></i>
                    <p>No assigned requests found</p>
                </td>
            </tr>
            @endforelse
        </tbody>
</table>
            </div>
        </div>
        
    </div>

    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>

</body>