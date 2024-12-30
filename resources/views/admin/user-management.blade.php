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

            <!-- Status Filter -->
            <select name="status" id="status">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="pending_verification">Pending Verification</option>
                <option value="verified">Verified</option>
            </select>

            <!-- Search Bar -->
            <div class="search-container">
                <div class="search-input-wrapper">
                    <input type="text" id="user-search" name="user-search" placeholder="Search users...">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div class="bulk-actions">
              <!--  <button class="btn-export" id="export-csv">
                    <i class="fas fa-file-export"></i> Export CSV
                </button> -->
                <button class="btn-delete" id="bulk-delete">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>
        </div>

        <div class="user-table-container">
            <h4>Users List</h4>
            <div id="users-table-wrapper">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
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
                            <td><input type="checkbox" class="user-select" value="{{ $user->id }}"></td>
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
                                <strong>Date: </strong>{{ $user->created_at->format('Y-m-d') }}<br>
                                <strong>Time: </strong>{{ $user->created_at->format('h:i A') }}
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
                                <button class="btn-edit" title="Edit" data-id="{{ $user->id }}">Edit</button>
                                <button class="btn-status" title="Toggle Status" data-id="{{ $user->id }}">Status</button>
                                <button class="btn-reset" title="Reset Password" data-id="{{ $user->id }}">Reset</button>
                                <button class="btn-delete" title="Delete" data-id="{{ $user->id }}">Delete</button>
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