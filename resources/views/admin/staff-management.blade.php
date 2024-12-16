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
    // Add event listeners for edit buttons
    document.querySelectorAll('.edit-staff').forEach(button => {
        button.addEventListener('click', function() {
            // Find the closest staff card
            const staffCard = this.closest('.staff-card');
            
            // Extract current values
            const nameElement = staffCard.querySelector('p:nth-child(1)');
            const usernameElement = staffCard.querySelector('p:nth-child(2)');
            
            // Get the current values, removing the "Name: " and "Username: " prefixes
            const currentName = nameElement.textContent.replace('Name: ', '').trim();
            const currentUsername = usernameElement.textContent.replace('Username: ', '').trim();
            
            // Populate the edit modal
            document.getElementById('editStaffName').value = currentName;
            document.getElementById('editStaffUsername').value = currentUsername;
            
            // Show the edit modal
            $('#editStaffModal').modal('show');
        });
    });
});

function saveEditedStaff() {
    // Get values from the edit modal
    const newName = document.getElementById('editStaffName').value;
    const newUsername = document.getElementById('editStaffUsername').value;
    const newStatus = document.getElementById('editStaffStatus').value;
    
    // Update the staff card in the UI
    const staffCard = document.querySelector('.staff-card');
    if (staffCard) {
        const nameElement = staffCard.querySelector('p:nth-child(1)');
        const usernameElement = staffCard.querySelector('p:nth-child(2)');
        
        // Update name and username
        nameElement.innerHTML = `<strong>Name:</strong> ${newName}`;
        usernameElement.innerHTML = `<strong>Username:</strong> ${newUsername}`;
    }
    
    // Close the modal
    $('#editStaffModal').modal('hide');
}
    </script>
</body>
</html>