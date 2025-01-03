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
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addStaffModal">
                <i class="fas fa-plus"></i> Add New Staff
            </button>
        </div>

        <div class="row staff-list">
          @forelse($staff as $staffMember)
                <div class="col-md-4 mb-4">
                    <div class="staff-card">
                        <div class="staff-image">
                            <img src="{{ $staffMember->profile_image ? asset('storage/' . $staffMember->profile_image) : asset('images/default-avatar.png') }}" 
                                alt="{{ $staffMember->name }}'s Profile Image" 
                                class="img-fluid rounded-circle">
                        </div>
                        <div class="staff-details">
                            <p><strong>Name:</strong> {{ $staffMember->name }}</p>
                            <p><strong>Username:</strong> {{ $staffMember->username }}</p>
                            <p><strong>Availability Status:</strong> {{ ucfirst(str_replace('_', ' ', $staffMember->availability_status)) }}</p>

                            <div class="staff-actions">
                                <button class="btn btn-sm btn-primary edit-staff" data-toggle="modal" data-target="#editStaffModal"
                                        data-id="{{ $staffMember->id }}" data-name="{{ $staffMember->name }}"
                                        data-username="{{ $staffMember->username }}" data-status="{{ $staffMember->availability_status }}">
                                    <i class="fas fa-edit"></i> Edit
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
                  <form id="editStaffForm" action="" method="POST">
                         @csrf
                         @method('PUT')
                        <input type="hidden" id="editStaffId" name="staff_id">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" id="editStaffName" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" name="username"  id="editStaffUsername" required>
                        </div>
                        <div class="form-group">
                            <label>Availability Status</label>
                             <select class="form-control" name="availability_status" id="editStaffStatus" required>
                                <option value="available">Available</option>
                                <option value="busy">Busy</option>
                                <option value="on_leave">On Leave</option>
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

    

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>

    <script>

    $(document).ready(function() {
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

        $('.delete-staff').on('click', function(){
           var staffId = $(this).data('id');
           var $button = $(this);

            if (confirm('Are you sure you want to delete this staff member?')) {
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