<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin_servicerequest.css') }}" rel="stylesheet">
    <title>Create Holiday</title>
</head>
<body>
    @include('layouts.admin-navbar')
    @include('layouts.admin-sidebar')

    <div class="content">
        <h1>Add Holiday</h1>
            
            <div class="card">
                <div class="card-body">
                <form action="{{ route('admin.holidays.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                id="name" name="name" value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="type">Period Type</label>
            <select name="type" id="type" class="form-control" required onchange="togglePeriodFields()">
                @foreach($periodTypes as $value => $label)
                    <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_recurring" id="is_recurring" 
                    value="1" {{ old('is_recurring') ? 'checked' : '' }}
                    onchange="toggleDateFields()">
                <label class="form-check-label" for="is_recurring">
                    Recurring Event
                </label>
            </div>
        </div>

        <div id="date-fields-container">
            <!-- Single day (for holidays and events) -->
            <div id="single-day-fields" class="form-group" style="{{ (old('type') == 'semestral_break' || old('type') == 'exam_week') ? 'display:none' : '' }}">
                <label for="date">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" 
                    value="{{ old('date') }}">
                @error('date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Multi-day periods (for breaks and longer events) -->
            <div id="multi-day-fields" class="form-group" style="{{ (old('type') == 'semestral_break' || old('type') == 'exam_week') ? '' : 'display:none' }}">
                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                    value="{{ old('start_date') }}">
                @error('start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                    
                <label for="end_date" class="mt-2">End Date <span class="text-danger">*</span></label>
                <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                    value="{{ old('end_date') }}">
                @error('end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Recurring fields -->
        <div id="recurring-fields" class="form-group" style="{{ old('is_recurring') ? '' : 'display:none' }}">
            <div class="row">
                <div class="col-md-6">
                    <label for="recurring_month">Month <span class="text-danger">*</span></label>
                    <select class="form-control @error('recurring_month') is-invalid @enderror" 
                        id="recurring_month" name="recurring_month">
                        <option value="">Select Month</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ old('recurring_month') == $i ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                            </option>
                        @endfor
                    </select>
                    @error('recurring_month')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="recurring_day">Day <span class="text-danger">*</span></label>
                    <select class="form-control @error('recurring_day') is-invalid @enderror" 
                        id="recurring_day" name="recurring_day">
                        <option value="">Select Day</option>
                        @for ($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}" {{ old('recurring_day') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                    @error('recurring_day')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control @error('description') is-invalid @enderror" 
                    id="description" name="description" rows="3">{{ old('description') }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group gap-2">
            <a href="{{ route('admin.holidays.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary w-auto">Save</button>
        </div>
    </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script>
        function toggleDateFields() {
            const isRecurring = document.getElementById('is_recurring').checked;
            document.getElementById('date-fields-container').style.display = isRecurring ? 'none' : 'block';
            document.getElementById('recurring-fields').style.display = isRecurring ? 'block' : 'none';
        }
        
        function togglePeriodFields() {
            const type = document.getElementById('type').value;
            const isSingleDay = (type == 'holiday' || type == 'special_event');
            
            document.getElementById('single-day-fields').style.display = isSingleDay ? 'block' : 'none';
            document.getElementById('multi-day-fields').style.display = isSingleDay ? 'none' : 'block';
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('success'))

        <script>

            Swal.fire({

                title: 'Success!',

                text: '{{ session('success') }}',

                icon: 'success',

                confirmButtonText: 'OK'

            });

        </script>

    @endif

</body>
</html>