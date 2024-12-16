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
            <button type="button" id="add-admin-btn" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Administrator
            </button>

            <!-- Logout Button 
            <button type="button" class="btn logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</button> -->
            
            <!-- Logout Form (Hidden) -->
            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>

        <!-- Add Administrator Form -->
        <div id="add-admin-form-container" style="display: none; margin-top: 20px;">
            <div id="new-admin-form">
                <form action="{{ route('admin.save') }}" method="POST">
                    <h3>Add New Administrator</h3>
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

    <!-- Include the modal from the admin > modal -->
    @include('admin.modal.settings-modal')

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