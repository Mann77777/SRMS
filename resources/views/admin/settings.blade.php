<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/settings.css') }}" rel="stylesheet">
    <title>Admin - Settings</title>
</head>
<body>
    <!-- Include Navbar -->
    @include('layouts.admin-navbar')
    
    <!-- Include Sidebar -->
    @include('layouts.admin-sidebar')

    <div class="settings-content">
        <div class="settings-header">
            <h1>Settings</h1>
        </div>

        <div class="settings-btn" id="button-container">
            <!-- Add Administrator Button -->
            <button type="button" id="add-admin-btn" class="btn btn-primary">Add Administrator</button>

            <!-- Logout Button -->
            <button type="button" class="btn logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</button>
            
            <!-- Logout Form (Hidden) -->
            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>

        <!-- Add Administrator Form -->
        <div id="add-admin-form-container" style="display: none; margin-top: 20px;">
            <div id="new-admin-form">
                <form action="{{ route('admin.save') }}" method="POST">
                    @csrf
                    <div>
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" placeholder="Name" required>
                    </div>
                    <div>
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Username" required>
                          
                    </div>
                    <div>
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="form-buttons"> 
                        <button type="submit" class="save-btn">Save</button>
                        <button type="button" id="cancel-btn" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Combined Error Modal -->
    <div class="modal" id="combinedErrorModal" tabindex="-1" role="dialog" aria-labelledby="combinedErrorModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="combinedErrorModalLabel">Error</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Check for username and password validation errors -->
                    @if($errors->has('username') && $errors->has('password'))
                        <p>The username has already been taken. Please choose another one.</p>
                        <p>The password must be at least 8 characters long.</p>
                    @elseif($errors->has('username'))
                        <p>The username has already been taken. Please choose another one.</p>
                    @elseif($errors->has('password'))
                        <p>The password must be at least 8 characters long.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Success</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    The administrator has been added successfully!
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
        // Check if there are validation errors for username and/or password
        @if($errors->has('username') || $errors->has('password'))
            $('#combinedErrorModal').modal('show'); // Show the combined error modal
        @endif

        // Show the success modal if the session contains a success message
        @if(session('success'))
            $('#successModal').modal('show');
        @endif

        // Handle the Add Administrator Button
        document.getElementById('add-admin-btn').addEventListener('click', function () {
            document.getElementById('add-admin-form-container').style.display = 'flex';  // Show the form
        });

        // Handle the Cancel Button
        document.getElementById('cancel-btn').addEventListener('click', function () {
            document.getElementById('add-admin-form-container').style.display = 'none';  // Hide the form
        });
    });
    </script>
</body>
</html>
