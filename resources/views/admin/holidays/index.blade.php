<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin_servicerequest.css') }}" rel="stylesheet">

    <title>Admin - Schedule Management</title>
</head>
<body>
    @include('layouts.admin-navbar')
    @include('layouts.admin-sidebar')

    <div class="content">
        <h1>Holiday Management</h1>
        
        <div class="filter-container">
            <form method="GET" action="{{ route('admin.holidays.index') }}" class="form-inline">
                <div class="dropdown-container">
                    <label for="year">Year: </label>
                    <select name="year" id="year" class="form-control ml-2" onchange="this.form.submit()">
                        @for ($i = now()->year - 2; $i <= now()->year + 2; $i++)
                            <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </form>
            
            <div class="actions-container">
                <a href="{{ route('admin.holidays.create') }}" class="action-btn btn-primary">
                    <i class="fas fa-plus"></i> Add Holiday
                </a>
                <a href="{{ route('admin.holidays.import-common') }}" class="action-btn btn-secondary" 
                onclick="return confirm('Import common Philippine holidays? This will only add holidays that don\'t already exist.')">
                    <i class="fas fa-download"></i> Import Common Holidays
                </a>
            </div>
        </div>
        
        <div class="request-table-container">
            <table class="request-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Date/Period</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holidays as $holiday)
                        <tr>
                            <td>{{ $holiday['name'] }}</td>
                            <td>
                                @php
                                    $type = $holiday['type'] ?? 'holiday';
                                    $badgeClass = 'custom-badge-secondary';
                                    $displayText = ucfirst(str_replace('_', ' ', $type));
                                    
                                    if ($type == 'holiday') {
                                        $badgeClass = 'custom-badge-danger';
                                        $displayText = 'Holiday';
                                    } elseif ($type == 'semestral_break') {
                                        $badgeClass = 'custom-badge-warning';
                                        $displayText = 'Semestral Break';
                                    } elseif ($type == 'exam_week') {
                                        $badgeClass = 'custom-badge-info';
                                        $displayText = 'Exam Week';
                                    } elseif ($type == 'enrollment_period') {
                                        $badgeClass = 'custom-badge-primary';
                                        $displayText = 'Enrollment Period';
                                    } elseif ($type == 'special_event') {
                                        $badgeClass = 'custom-badge-success';
                                        $displayText = 'Special Event';
                                    }
                                @endphp
                                
                                <span class="custom-badge {{ $badgeClass }}">{{ $displayText }}</span>
                            </td>
                            <td>
                                @if($holiday['start_date'] && $holiday['end_date'])
                                    {{ $holiday['start_date'] }} to {{ $holiday['end_date'] }}
                                @else
                                    {{ $holiday['formatted_date'] }}
                                @endif
                            </td>
                            <td>{{ $holiday['description'] ?? 'N/A' }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.holidays.edit', $holiday['id']) }}" class="btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('admin.holidays.destroy', $holiday['id']) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-cancel" onclick="return confirm('Are you sure you want to delete this holiday?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No periods found for {{ $year }}.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');

/* Base styling */
.filter-container {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    align-items: center;
}

.actions-container {
    display: flex;
    gap: 15px;
}

.action-btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    font-size: 14px;
    min-width: 180px;
}

.action-btn i {
    margin-right: 8px;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-primary:hover {
    background-color: #0069d9;
    color: white;
    text-decoration: none;
}

.btn-secondary:hover {
    background-color: #5a6268;
    color: white;
    text-decoration: none;
}

/* Dropdown styling */
.dropdown-container {
    display: flex;
    align-items: center;
}

select {
    width: 180px;
    padding: 10px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 15px;
    background-color: #fff;
    margin-left: 10px;
    cursor: pointer;
}

/* Table styling */
.request-table-container {
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
    overflow-x: auto;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.request-table {
    width: 100%;
    border-collapse: collapse;
}

.request-table th, 
.request-table td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
}

.request-table tr:hover {
    background-color: #f1f1f1;
}

.request-table th {
    background-color: #C4203C;
    color: white;
    padding: 10px;
}

/* Button styling */
.btn-edit, .btn-cancel {
    display: block;
    width: 100%;
    margin: 5px 0;
    padding: 6px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    color: #ffffff;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
}

.btn-edit {
    background-color: #04AA6D;
}

.btn-cancel {
    background-color: #F44336;
}

.btn-edit:hover, .btn-cancel:hover {
    opacity: 0.9;
    color: white;
    text-decoration: none;
}

/* Badge styling */
.custom-badge {
    display: inline-block;
    padding: 0.25em 0.6em;
    font-size: 75%;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25rem;
    cursor: default;
}

.custom-badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.custom-badge-info {
    background-color: #17a2b8;
    color: white;
}

.custom-badge-success {
    background-color: #28a745;
    color: white;
}

.custom-badge-danger {
    background-color: #dc3545;
    color: white;
}

.custom-badge-secondary {
    background-color: #6c757d;
    color: white;
}

.custom-badge-primary {
    background-color: #007bff;
    color: white;
}
</style>
</body>
</html>