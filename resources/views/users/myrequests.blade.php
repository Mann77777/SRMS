<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/myrequest.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>My Requests</title>
</head>
<body>

    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')

    <div class="content">
        <h1>My Request</h1>
        <div class="form-container">
            <div class="dropdown-container">
                <select name="" id="">
                    <option value="pending">Pending</option>
                    <option value="in progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>

                <!-- Search Bar -->
                <div class="search-container">
                    <input type="text" name="" placeholder="Search...">
                    <button class="search-btn" type="button" onclick="performSearch()">Search</button>
                </div>

            </div>
            <div class="request-table-container">
                <form action="">
                    <table class="request-table">
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
                            @foreach ($requests as $request)
                                <tr>
                                    <td>{{ $request['id'] }}</td>
                                    <td>{{ $request['service'] }}</td>
                                    <td>{{ $request['date'] }}</td>
                                    <td>{{ $request['status'] }}</td>
                                    <td>
                                        <button class="btn-edit" data-id="{{ $request['id'] }}" data-type="{{ $request['type'] }}">Edit</button>
                                        <button class="btn-view" data-id="{{ $request['id'] }}" data-type="{{ $request['type'] }}">View</button>
                                        <button class="btn-delete" data-id="{{ $request['id'] }}" data-type="{{ $request['type'] }}">Delete</button>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/navbar-sidebar.js') }}" defer></script>
   <script>
        document.addEventListener('DOMContentLoaded', () => {
            const table = document.querySelector('.request-table');

            table.addEventListener('click', (e)=>{
                 const requestId = e.target.getAttribute('data-id');
                  const requestType = e.target.getAttribute('data-type');

                if(e.target.classList.contains('btn-edit')){
                  window.location.href = `/editrequest/${requestType}/${requestId}`;
                }else if(e.target.classList.contains('btn-view')){
                     window.location.href = `/viewrequest/${requestType}/${requestId}`;
                }else if(e.target.classList.contains('btn-delete')){
                      window.location.href = `/deleterequest/${requestType}/${requestId}`;
                }
            })
        });

    </script>
</body>
</html>