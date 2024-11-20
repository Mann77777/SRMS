<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/admin_myprofile.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">

    <title>My Profile</title>
</head>
<body>

    <!-- Include Navbar -->
    @include('layouts.admin-navbar')

    <div class="profile-container">
        <h2>My Profile</h2>
        <p>Manage and protect your account</p>

       <!-- Display Profile Image -->
        <div class="profile-header">
            <div class="profile-image">
                @if(Auth::guard('admin')->user()->profile_image)
                    <img src="{{ asset('storage/' . Auth::guard('admin')->user()->profile_image) }}" alt="Profile Image" class="profile-img">
                @else
                    <img src="{{ asset('images/default-avatar.png') }}" alt="Default Profile Image" class="profile-img">
                @endif
            </div>
            <h3 class="username">{{ Auth::guard('admin')->user()->name }}</h3>
        </div>

        <div class="user-info">
            <p>
                <span class="label">Name:</span>
                <span class="user-data">{{ Auth::guard('admin')->user()->name }}</span>
            </p>
            <p>  
                <span class="label">Username:</span>
                <span class="user-data">
                    <span id="username-display">{{ Auth::guard('admin')->user()->username }}</span>
                    <input type="text" id="username-input" value="{{ Auth::guard('admin')->user()->username }}" style="display:none;">
                    <span id="edit-username" style="color: blue; cursor: pointer; text-decoration: underline; margin-left: 10px;">Edit</span>
                    <span class="save-username-btn" id="save-username-btn" style="display:none; cursor: pointer; color: green; text-decoration: underline; margin-left: 10px;">Save</span>
                </span>
            </p>

            <p>
                <span class="label">Email:</span>
                <span class="user-data">{{ Auth::guard('admin')->user()->email }}</span>
            </p>

            <p>
                <span class="label">Role:</span>
                <span class="user-data">{{ Auth::guard('admin')->user()->role }}</span>
            </p>
        </div>

                    
        <!-- Form for uploading profile image -->
        <form action="{{ route('profile.upload') }}" method="POST" enctype="multipart/form-data" class="profile-upload-form">
            @csrf
            <label for="profile_image">Upload Profile</label>
            <input type="file" name="profile_image" id="profile_image">
           
            <div class="button-container">
                <button type="submit" class="upload-btn">Upload</button>
            </div>       
        </form>

        <!-- Form for removing profile image -->
        <form action="{{ route('profile.remove') }}" method="POST" class="remove-image-form">
            @csrf
            <div class="button-container">
                <button type="submit" class="remove-image-btn">Remove Image</button>
            </div>
        </form>

        <h3>Set or Change Password</h3>

        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
            <form action="{{ route('myprofile.setPassword') }}" method="POST" class="password-form">
                @csrf
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required>
                </div>
                <button type="submit" class="btn">Set Password</button>
            </form>
    </div>

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

                    // Show success message
                    const successMessage = document.getElementById('success-message');
                    successMessage.style.display = 'block';

                    // Hide the success message after 3 seconds
                    setTimeout(() => {
                        successMessage.style.display = 'none';
                    }, 3000);
                } else {
                    alert('Error updating username.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

    </script>
</body>
</html>
