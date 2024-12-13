<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/myrequest.css') }}">
    <link rel="stylesheet" href="{{ asset('css/navbar-sidebar.css') }}">
    <title>My Requests</title>
</head>
<body>

    @include('layouts.navbar')
    @include('layouts.sidebar')

    <div class="container mt-5">
        <h2 class="text-center">My Requests</h2>

        <!-- Dropdown and Search Bar -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <select id="statusFilter" class="form-control">
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <div class="input-group w-50">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by Service">
                <button class="btn btn-primary" onclick="performSearch()">Search</button>
            </div>
        </div>

        <!-- Request Table -->
        <table class="table table-bordered mt-4">
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
                @foreach($requests as $request)
                <tr>
                    <td>{{ $request->id }}</td>
                    <td>{{ $request->service }}</td>
                    <td>{{ $request->created_at->format('Y-m-d') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $request->status)) }}</td>
                    <td>
                        @if($request->status == 'pending')
                            <form action="{{ route('requests.approve', $request->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-success btn-sm">Approve</button>
                            </form>
                        @endif

                        @if($request->status == 'in_progress')
                            <form action="{{ route('requests.complete', $request->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-primary btn-sm">Complete</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
</html>
