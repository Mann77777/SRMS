<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/profile.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>My Profile</title>
</head>
<body>

  <!-- Include Navbar -->
  @include('layouts.navbar')

    <div class="profile-container">
        <div class="back-button-container">
            <a href="{{ url('/dashboard') }}" class="btn btn-secondary back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        <h2>My Profile</h2>
        <p>Manage and protect your account</p>

        <!-- Display Profile Image -->
        <div class="profile-header">
            <div class="profile-image">
                @if(Auth::user()->profile_image)
                    <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile Image" class="profile-img">
                @else
                    <img src="{{ asset('images/default-avatar.png') }}" alt="Default Profile Image" class="profile-img">
                @endif
            </div>
            <h3 class="username">{{ Auth::user()->name }}</h3>
        </div>
        
        <div class="user-info">
            <p>
                <span class="label">Name:</span>
                <span class="user-data">{{ Auth::user()->name }}</span>
            </p>
            <p>  
                <span class="label">Username:</span>
                <span class="user-data">
                    <span id="username-display">{{ Auth::user()->username }}</span>
                    <input type="text" id="username-input" value="{{ Auth::user()->username }}" style="display:none;">
                    <span id="edit-username" style="color: blue; cursor: pointer; text-decoration: underline; margin-left: 10px;" data-toggle="modal" data-target="#usernameUpdateModal">Edit</span>
                    <span class="save-username-btn" id="save-username-btn" style="display:none; cursor: pointer; color: green; text-decoration: underline; margin-left: 10px;">Save</span>
                </span>
            </p>
            <input type="text" id="username-input" value="{{ Auth::user()->username }}" style="display:none;">
            <span class="save-username-btn" id="save-username-btn" style="display:none; cursor: pointer; color: green; text-decoration: underline;">Save</span>
            
            <p>
                <span class="label">Email:</span>
                <span class="user-data">{{ Auth::user()->email }}</span>
            </p>

            @if(Auth::user()->role === 'Student')
                <p>
                    <span class="label">Student ID:</span>
                    <span class="user-data">{{ Auth::user()->student_id }}</span>
                </p>
                @endif
            <p>
                <span class="label">Role:</span>
                <span class="user-data">{{ Auth::user()->role }}</span>
            </p>

            <p>
                <span class="label">Verification Status:</span>
                <span class="user-data">{{ Auth::user()->verification_status }}</span>
            </p>
        </div>

        <!-- Form for uploading profile image -->
        <form action="{{ route('profile.upload') }}" method="POST" enctype="multipart/form-data" class="profile-upload-form">
            @csrf
            <label for="profile_image">Upload Profile</label>
            <input type="file" name="profile_image" id="profile_image">
           
            <div class="button-container">
                <button type="submit" class="upload-btn" data-toggle="modal" data-target="#uploadProfileImageModal">Upload</button>
            </div>       
        </form>

        <!-- Form for removing profile image -->
        <form action="{{ route('profile.remove') }}" method="POST" class="remove-image-form">
            @csrf
            <div class="button-container">
                <button type="submit" class="remove-image-btn" data-toggle="modal" data-target="#removeProfileImageModal">Remove Image</button>
            </div>
        </form>

        <!-- Password Change Form -->
        <h2 class="changepass-header">Set or Change Password</h2>
        @if ($errors->any())
            <div class="alert alert-danger" style="list-style-type: none; padding-left: 0;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif


        <form action="{{ route('myprofile.setPassword') }}" method="POST" class="password-form">
            @csrf
            <div class="form-group">
                <label for="password" class="label">New Password:</label>
                <input type="password" name="password" id="password" class="input-field" placeholder="Enter New Password" required>
            </div>
            <div class="form-group">
                <label for="password_confirmation" class="label">Confirm Password:</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="input-field" placeholder="Enter Confirm Password" required>
            </div>
            <button type="submit" class="btn">Set Password</button>
        </form>

    </div>

    <!-- Include the modal from the subfolder -->
    @include('users.modals.profile-modal')
    <script src="{{ asset('js/navbar-sidebar.js') }}" defer></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('edit-username').addEventListener('click', function() {
            // Hide the username display and the edit link
            document.getElementById('username-display').style.display = 'none';
            this.style.display = 'none';

            // Show the input field and save link
            document.getElementById('username-input').style.display = 'inline';
            document.getElementById('save-username-btn').style.display = 'inline';
        });

        document.getElementById('save-username-btn').addEventListener('click', function() {
            const newUsername = document.getElementById('username-input').value;

            // AJAX request to save the new username
            fetch('/update-username', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ username: newUsername }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the displayed username and hide input
                    document.getElementById('username-display').textContent = newUsername;
                    document.getElementById('username-display').style.display = 'inline';
                    document.getElementById('edit-username').style.display = 'inline';
                    document.getElementById('username-input').style.display = 'none';
                    document.getElementById('save-username-btn').style.display = 'none';

                    // Show success modal
                    $('#usernameUpdateSuccessModal').modal('show');
                } else {
                    alert('Error updating username.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            // Check if success messages are present
            if ('{{ session('upload_success') }}') {
                $('#uploadSuccessModal').modal('show');
            }
             // Show the confirmation modal when the user clicks "Remove Image"
            $('#removeImageBtn').on('click', function() {
                $('#confirmRemoveImageModal').modal('show');
            });

            // If the session indicates that the image has been removed, show the success modal
            if ('{{ session('image_removed') }}') {
                $('#removeProfileImageModal').modal('show');
            }

            @if(session('password_changed'))
                $('#passwordChangeModal').modal('show');
            @endif
        });

    </script>
</body>
</html>