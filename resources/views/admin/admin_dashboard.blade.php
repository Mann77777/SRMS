<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    <title>Dashboard</title>
</head>
<body>
    
    <section class="hero">
        <h1>HELLO ADMIN, {{ Auth::guard('admin')->user()->name }}!</h1>
        
        <div class="button-container">
            @if(Auth::guard('admin')->user()->role === 'Admin')
                <button onclick="window.location.href='/admin-panel'" class="btn-primary">Admin Panel</button>
                <button onclick="window.location.href='/manage-requests'" class="btn-secondary">Manage Requests</button>
            @elseif(Auth::guard('admin')->user()->role === 'Technician')
                <button onclick="window.location.href='/technician-tasks'" class="btn-primary">Assigned Tasks</button>
                <button onclick="window.location.href='/task-status'" class="btn-secondary">Update Task Status</button>
            @endif
        </div>
    </section>

</body>
</html>
