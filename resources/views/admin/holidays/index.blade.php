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
    <link href="{{ asset('css/index.css') }}" rel="stylesheet">
    <title>Admin - Schedule Management</title>
</head>
<body>
    @include('layouts.admin-navbar')
    @include('layouts.admin-sidebar')

    <div class="content">
        <h1>Schedule Management</h1>
        
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
    
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>

</body>
</html>