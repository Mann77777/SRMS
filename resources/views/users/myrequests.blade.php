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


    <div class="header-myrequest" style="margin-top: 100px;">
    <h1>My Requests</h1>
    <!-- Search and Filter -->
    <div class="search-filter-container d-flex mb-3">
        <div class="filter-container">
            <select class="form-control" id="filter-status" onchange="filterRequests()">
                <option value="">Filter by Status</option>
                <option value="pending">Pending</option>
                <option value="in progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
        <div class="search-bar-container d-flex align-items-center">
            <input type="text" class="form-control" id="search-bar" placeholder="Search requests..." onkeyup="performSearch()">
            <button class="btn btn-primary" type="button" onclick="performSearch()">Search</button>
        </div>
    </div>

    <!-- Requests Table -->
    <section class="request my-4">
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Request ID</th>
                    <th>Date Submitted</th>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Last Update</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="requests-table">
                <tr>
                    <td>001</td>
                    <td>2024-11-01</td>
                    <td>Library Access</td>
                    <td>Pending</td>
                    <td>2024-11-02</td>
                    <td>
                        <button class="btn btn-info btn-sm">View</button>
                        <button class="btn btn-danger btn-sm">Cancel</button>
                    </td>
                </tr>
                <tr>
                    <td>002</td>
                    <td>2024-11-02</td>
                    <td>IT Support</td>
                    <td>In Progress</td>
                    <td>2024-11-03</td>
                    <td>
                        <button class="btn btn-info btn-sm">View</button>
                        <button class="btn btn-danger btn-sm">Cancel</button>
                    </td>
                </tr>
                <tr>
                    <td>003</td>
                    <td>2024-11-03</td>
                    <td>Counseling Session</td>
                    <td>Completed</td>
                    <td>2024-11-04</td>
                    <td>
                        <button class="btn btn-info btn-sm">View</button>
                        <button class="btn btn-danger btn-sm">Cancel</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>
</div>

    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    @stack('scripts')
    <script>
        function filterRequests() {
            const filterValue = document.getElementById("filter-status").value.toLowerCase();
            const rows = document.querySelectorAll("#requests-table tr");
            
            rows.forEach(row => {
                const statusCell = row.cells[3]; // Index 3 is for the Status column
                if (statusCell) {
                    const statusText = statusCell.textContent.toLowerCase();
                    if (filterValue === "" || statusText === filterValue) {
                        row.style.display = ""; // Show row
                    } else {
                        row.style.display = "none"; // Hide row
                    }
                }
            });
            performSearch(); // Apply search after filtering
        }

        function performSearch() {
            const query = document.getElementById('search-bar').value.toLowerCase();
            const rows = document.querySelectorAll("#requests-table tr");
            
            rows.forEach(row => {
                // Check if the row is currently visible before searching
                if (row.style.display !== "none") {
                    const cells = row.getElementsByTagName('td');
                    let matchFound = false;
                    
                    for (let i = 0; i < cells.length; i++) {
                        const cellText = cells[i].textContent.toLowerCase();
                        if (cellText.includes(query)) {
                            matchFound = true;
                            break; // Exit the loop as we found a match
                        }
                    }
                    
                    if (matchFound) {
                        row.style.display = ""; // Show row if it matches the search
                    } else {
                        row.style.display = "none"; // Hide row if it doesn't match
                    }
                }
            });
        }
    </script>

</body>
</html>