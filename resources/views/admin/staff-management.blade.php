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
    <link href="{{ asset('css/staff-management.css') }}" rel="stylesheet">
    <title>Admin - Staff Management</title>
</head>
<body>

    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    <div class="staff-content">
        <div class="staff-header">
            <h1>Staff Management</h1>
        </div>
        <div class="staff-btn" id="button-container">
            <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#addStaffModal">
                <i class="fas fa-plus"></i> Add New Staff
            </button>

            <button type="button" class="btn btn-secondary toggle-inactive">
                <i class="fas fa-eye"></i> {{ $showInactive ? 'Hide Inactive Staff' : 'Show Inactive Staff' }}
            </button>
        </div>

        <div class="row staff-list">
          @forelse($staff as $staffMember)
                <div class="col-md-4 mb-4 staff-card-container" data-status="{{ $staffMember->availability_status }}">
                    <div class="staff-card {{ $staffMember->availability_status === 'inactive' ? 'inactive-staff' : '' }}">
                        <div class="status-badge">
                            <span class="badge {{ $staffMember->availability_status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                {{ ucfirst($staffMember->availability_status) }}
                            </span>
                        </div>
                        <div class="staff-image">
                            <img src="{{ $staffMember->profile_image ? asset('storage/' . $staffMember->profile_image) : asset('images/default-avatar.png') }}" 
                                alt="{{ $staffMember->name }}'s Profile Image" 
                                class="img-fluid rounded-circle">
                        </div>
                        <div class="staff-details">
                            <p><strong>Name:</strong> {{ $staffMember->name }}</p>
                            <p><strong>Username:</strong> {{ $staffMember->username }}</p>
                            
                            <div class="staff-actions">
                                <button class="btn btn-sm btn-primary edit-staff" data-toggle="modal" data-target="#editStaffModal"
                                        data-id="{{ $staffMember->id }}" data-name="{{ $staffMember->name }}"
                                        data-username="{{ $staffMember->username }}" data-status="{{ $staffMember->availability_status }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-{{ $staffMember->availability_status === 'active' ? 'warning' : 'success' }} toggle-status" 
                                        data-id="{{ $staffMember->id }}" data-status="{{ $staffMember->availability_status }}">
                                    <i class="fas fa-{{ $staffMember->availability_status === 'active' ? 'ban' : 'check' }}"></i> 
                                    {{ $staffMember->availability_status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button class="btn btn-sm btn-danger delete-staff" data-id="{{ $staffMember->id }}">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
           @empty
            <p> No staff members found.</p>
            @endforelse
        </div>
    </div>


      <!-- Add Staff Modal -->
      <div class="modal fade" id="addStaffModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Staff</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addStaffForm" action="{{ route('staff.store') }}" method="POST">
                      @csrf
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                         <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Staff</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Staff Modal -->
    <div class="modal fade" id="editStaffModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Staff Member</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body">
                <form id="editStaffForm" action="" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="editStaffId" name="staff_id">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" id="editStaffName" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" name="username" id="editStaffUsername" required>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="availability_status" id="editStaffStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" >Save Changes</button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');

    *{
        font-family: "Montserrat", sans-serif;
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    
    body{
        margin: 0;
        padding: 0;
        padding-top: 10px;
        font-family: "Montserrat", sans-serif;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        background-color: #f8f9fa;
        height: 130vh;
    }   
    
    .staff-content {
        margin-top: 8%;
        margin-left: 23%;
        padding: 20px;
    }
    
    .staff-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    
    .staff-header h1 {
        font-size: 2rem;
        font-weight: 650;
        text-transform: uppercase;
    }
    
    .staff-btn {
        margin-bottom: 30px;
    }
    
    .staff-btn .btn-primary {
        padding: 10px 14px;
        font-size: 16px;
        font-weight: 500;
    }
    
    .staff-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        position: relative;
    }
    
    .staff-card:hover {
        transform: scale(1.05);
    }
    
    .staff-image {
        height: 250px;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }
    
    .staff-image img {
        width: 200px;
        height: 200px;
        object-fit: cover;
        border-radius: 50%;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .staff-details {
        padding: 15px;
        text-align: left;
    }
    
    .staff-details p {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
    }
    
    .staff-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 15px;
    }
    
    .staff-actions .btn {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .inactive-staff {
        opacity: 0.7;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    
    .badge-success {
        background-color: #28a745;
        color: white;
    }
    
    .badge-secondary {
        background-color: #6c757d;
        color: white;
    }
    
    /* New styles for status badge */
    .status-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
    }
    
    .status-badge .badge {
        font-size: 14px;
        padding: 5px 10px;
        border-radius: 20px;
    }
    </style>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>

    <script>
    $(document).ready(function() {
        // Toggle between showing active only and all staff
        $('.toggle-inactive').on('click', function() {
            var currentUrl = window.location.href;
            if (currentUrl.includes('staff-management/all')) {
                window.location.href = '/staff-management'; // Show only active
            } else {
                window.location.href = '/staff-management/all'; // Show all
            }
        });
        
        // Handle the edit modal data
        $('#editStaffModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var staffId = button.data('id');
            var staffName = button.data('name');
            var staffUsername = button.data('username');
            var staffStatus = button.data('status');

            var modal = $(this);
            modal.find('#editStaffForm').attr('action', '/admin/staff/' + staffId +'/update')
            modal.find('#editStaffId').val(staffId);
            modal.find('#editStaffName').val(staffName);
            modal.find('#editStaffUsername').val(staffUsername);
            modal.find('#editStaffStatus').val(staffStatus);
        });

        // Handle status toggle
        $('.toggle-status').on('click', function() {
            var staffId = $(this).data('id');
            var $button = $(this);
            var currentStatus = $button.data('status');
            var statusText = currentStatus === 'active' ? 'deactivate' : 'activate';
            
            if (confirm('Are you sure you want to ' + statusText + ' this UITC Staff member?')) {
                $.ajax({
                    url: '/admin/staff/' + staffId + '/change-status',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if(response.success) {
                            // Update UI to reflect new status
                            var newStatus = response.new_status;
                            var $container = $button.closest('.staff-card-container');
                            var $card = $button.closest('.staff-card');
                            var $statusBadge = $container.find('.badge');
                            
                            // Update data attributes and classes
                            $button.data('status', newStatus);
                            $container.attr('data-status', newStatus);
                            
                            // Update card styling
                            if (newStatus === 'inactive') {
                                $card.addClass('inactive-staff');
                                $statusBadge.removeClass('badge-success').addClass('badge-secondary');
                                $button.removeClass('btn-warning').addClass('btn-success');
                                $button.html('<i class="fas fa-check"></i> Activate');
                            } else {
                                $card.removeClass('inactive-staff');
                                $statusBadge.removeClass('badge-secondary').addClass('badge-success');
                                $button.removeClass('btn-success').addClass('btn-warning');
                                $button.html('<i class="fas fa-ban"></i> Deactivate');
                            }
                            
                            // Update status text
                            $statusBadge.text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                            
                            alert(response.message);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred while changing staff status');
                        console.error(xhr.responseText);
                    }
                });
            }
        });

        // Keep existing delete-staff handler but change text to indicate permanent deletion
        $('.delete-staff').on('click', function(){
            var staffId = $(this).data('id');
            var $button = $(this);

            if (confirm('Are you sure you want to PERMANENTLY delete this UITC Staff member? This action cannot be undone.')) {
                $.ajax({
                    url: '/admin/staff/' + staffId,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if(response.success){
                            $button.closest('.col-md-4').remove();
                            alert(response.message);
                        }else{
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred while deleting this staff member')
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    });
    </script>
</body>
</html>