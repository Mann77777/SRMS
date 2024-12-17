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
            <div class="col-md-4 mb-4">
                <div class="staff-card">
                    <div class="staff-image">
                        <img src="{{ asset('images/default-avatar.png') }}" alt="Default Profile Image">
                    </div>
                    <div class="staff-details">
                        <p><strong>Name:</strong> Staff 1</p>
                        <p><strong>Username:</strong> staff1</p>
                        <p><strong>Availability Status:</strong> Active</p>

                        <div class="staff-actions">
                            <button class="btn btn-sm btn-primary edit-staff" data-id="">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger delete-staff" data-id="">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


      <!-- Add Staff Modal -->
      <div class="modal fade" id="addStaffModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Staff</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addStaffForm">
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
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveNewStaff()">Save Staff</button>
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
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editStaffForm">
                        <input type="hidden" id="editStaffId">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" id="editStaffName" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" id="editStaffUsername" required>
                        </div>
                        <div class="form-group">
                            <label>Availability Status</label>
                            <select class="form-control" id="editStaffStatus" required>
                                <option value="available">Available</option>
                                <option value="busy">Busy</option>
                                <option value="on_leave">On Leave</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveEditedStaff()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>

    <script>
         document.addEventListener('DOMContentLoaded', function() {
            const staffList = document.querySelector('.staff-list');
            
            // Event delegation for edit buttons
            staffList.addEventListener('click', function(e) {
                const editButton = e.target.closest('.edit-staff');
                if (editButton) {
                    const staffCard = editButton.closest('.staff-card');
                    const staffName = staffCard.querySelector('p:nth-child(1)').textContent.replace('Name: ', '').trim();
                    const staffUsername = staffCard.querySelector('p:nth-child(2)').textContent.replace('Username: ', '').trim();
                    const staffStatus = staffCard.querySelector('p:nth-child(3)').textContent.replace('Availability Status: ', '').trim().toLowerCase();

                    // Populate edit modal
                    document.getElementById('editStaffId').value = editButton.getAttribute('data-id');
                    document.getElementById('editStaffName').value = staffName;
                    document.getElementById('editStaffUsername').value = staffUsername;
                    
                    // Set the correct status in dropdown
                    const statusDropdown = document.getElementById('editStaffStatus');
                    statusDropdown.value = staffStatus === 'active' ? 'available' : staffStatus;

                    // Show the edit modal
                    $('#editStaffModal').modal('show');
                }
            });
        });

        function saveEditedStaff() {
            const staffId = document.getElementById('editStaffId').value;
            const staffName = document.getElementById('editStaffName').value;
            const staffUsername = document.getElementById('editStaffUsername').value;
            const staffStatus = document.getElementById('editStaffStatus').value;

            // AJAX call to update staff
            $.ajax({
                url: '/update-staff/' + staffId,
                method: 'POST',
                data: {
                    name: staffName,
                    username: staffUsername,
                    status: staffStatus,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Update staff card
                    const staffCard = document.querySelector(`.edit-staff[data-id="${staffId}"]`).closest('.staff-card');
                    staffCard.querySelector('p:nth-child(1)').innerHTML = `<strong>Name:</strong> ${staffName}`;
                    staffCard.querySelector('p:nth-child(2)').innerHTML = `<strong>Username:</strong> ${staffUsername}`;
                    staffCard.querySelector('p:nth-child(3)').innerHTML = `<strong>Availability Status:</strong> ${staffStatus.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}`;

                    // Close modal
                    $('#editStaffModal').modal('hide');
                    
                    // Optional: Show success message
                    alert('Staff updated successfully');
                },
                error: function(xhr) {
                    // Handle error
                    alert('Error updating staff: ' + xhr.responseText);
                }
            });
        }
    </script>
</body>
</html>