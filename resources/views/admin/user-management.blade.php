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
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/user-management.css') }}" rel="stylesheet">
    <title>Admin - User Management</title>
</head>
<body>
    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    <div class="user-content">
        <div class="user-header">
            <h1>User Management</h1>
        </div>
        
        <div class="top-controls">
            <!-- Add User Button -->
            <button class="btn btn-primary add-user-btn" data-toggle="modal" data-target="#addUserModal">
                <i class="fas fa-plus"></i> Add User
            </button>
        </div>
        
        <div class="dropdown-container">
            <!-- Role Filter -->
            <select name="user_role" id="role">
                <option value="all">All Users</option>
                <option value="student">Student</option>
                <option value="faculty">Faculty & Staff</option>
            </select>

            <!-- Status Filter 
            <select name="status" id="status">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="pending_verification">Pending Verification</option>
                <option value="verified">Verified</option>
            </select> -->

            <!-- Search Bar -->
            <div class="search-container">
                <div class="search-input-wrapper">
                    <input type="text" id="user-search" name="user-search" placeholder="Search users...">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>

            <!-- Bulk Actions 
            <div class="bulk-actions">
                <button class="btn-export" id="export-csv">
                    <i class="fas fa-file-export"></i> Export CSV
                </button> 
                <button class="btn-delete" id="bulk-delete">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div> -->
        </div>

        <div class="user-table-container">
            <h4>Users List</h4>
            <div id="users-table-wrapper">
                <table class="users-table">
                    <thead>
                        <tr>
                            <!-- <th><input type="checkbox" id="select-all"></th> -->
                            <th>ID</th>
                            <th>User Data</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Account Created</th>
                            <th>Verification Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        @foreach ($users as $user)
                        <tr>
                            <!-- <td><input type="checkbox" class="user-select" value="{{ $user->id }}"></td> -->
                            <td>{{ $user->id }}</td>
                            <td>
                            <strong>Name: </strong>{{ $user->name }}<br>
                            <strong>Username: </strong>{{ $user->username }}<br>
                            <strong>Email: </strong>{{ isset($user->email) ? $user->email : $user->username }} <br>
                            @if($user->role === 'Student')
                                <strong>Student ID: </strong>{{ $user->student_id ?? 'Not Assigned' }}
                            @endif

                            </td>
                            <td>{{ ucfirst($user->role) }}</td>
                            <td>
                                <span class="status-badge {{ $user->status ?? 'active' }}">
                                    {{ $user->status ?? 'Active' }}
                                </span>
                            </td>
                            <td>
                                {{ $user->created_at->format('M d, Y') }}<br>
                                {{ $user->created_at->format('h:i A') }}
                            </td>

                            <td>
                                @if($user->role === 'Student')
                                    @if(!$user->email_verified_at)
                                        <span class="status-badge pending">Email Unverified</span>
                                    @elseif(!$user->student_id)
                                        <span class="status-badge pending">Details Required</span>
                                    @elseif(!$user->admin_verified)
                                        <span class="status-badge pending">Pending Verification</span>
                                        <button class="btn-verify" title="Verify Student" data-id="{{ $user->id }}">Verify</button>
                                    @else
                                        <span class="status-badge verified">Verified</span>
                                    @endif
                                @elseif($user->role === 'Faculty & Staff')
                                    @if(!$user->email_verified_at)
                                        <span class="status-badge pending">Email Unverified</span>
                                    @elseif(!$user->admin_verified)
                                        <span class="status-badge pending">Pending Verification</span>
                                        <button class="btn-verify-faculty" title="Verify Faculty/Staff" data-id="{{ $user->id }}">Verify</button>
                                    @else
                                        <span class="status-badge verified">Verified</span>
                                    @endif
                                @else
                                    <span class="status-badge">N/A</span>
                                @endif
                            </td>

                            <td class="b">
                                <button class="btn-edit" title="Edit" data-id="{{ $user->id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5z"/>
                                    </svg> Edit
                                </button>

                                <button class="btn-status" title="Toggle Status" data-id="{{ $user->id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9 8c0 1 1 1 1 1h5.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.544-3.393C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4Zm9.886-3.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382l.045-.148ZM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z"/>
                                    </svg> Status
                                </button>

                                <button class="btn-reset" title="Reset Password" data-id="{{ $user->id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                                        <path d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                                    </svg> Reset
                                </button>

                                <button class="btn-delete" title="Delete" data-id="{{ $user->id }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                    </svg> Delete
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Include the modal from the admin > modal -->
    @include('admin.modal.usermanagement-modal')
    @include('admin.modal.verify-student')
    @include('admin.modal.verify-facultystaff')
    <!-- Add jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script src="{{ asset('js/user-management.js') }}"></script>
</body>
</html>