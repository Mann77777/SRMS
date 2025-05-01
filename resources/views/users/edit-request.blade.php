<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    {{-- Add specific CSS if needed, maybe reuse parts of service request forms --}}
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/student-request.css') }}" rel="stylesheet"> {{-- Example: Reuse student request CSS --}}
    <title>Edit Request - {{ $request->service_category }}</title>
    <style>
        /* Add some spacing */
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .form-section h5 {
            margin-bottom: 1rem;
            color: #555;
        }
        .readonly-field {
            background-color: #e9ecef;
            opacity: 1;
            cursor: not-allowed;
        }
        .current-file-link {
            display: inline-block;
             margin-right: 10px;
         }
         /* Add margin to form groups to prevent overlap - reduce slightly */
         .form-group {
             margin-bottom: 1rem; /* Reduced margin */
         }
         /* Ensure form sections have good separation */
         .form-section {
             margin-bottom: 2.5rem; /* Increased margin */
             padding-bottom: 1.5rem;
              border-bottom: 1px solid #eee;
          }
          /* Style the new form container */
          .edit-form-container {
              background-color: #fff; /* Optional: Add background */
              padding: 30px;
              border-radius: 8px;
              box-shadow: 0 2px 10px rgba(0,0,0,0.1); /* Optional: Add shadow */
              max-width: 800px; /* Limit width */
              margin: 20px auto; /* Center the container */
          }
          /* The .content styles below were conflicting and are now removed */
     </style>
</head>
<body class="{{ Auth::check() ? 'user-authenticated' : '' }}" data-user-role="{{ Auth::user()->role }}">

    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')

    <div class="content">
        <h1>Edit Service Request</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Determine user role to adjust form action or logic if needed --}}
        @php
            $userRole = Auth::user()->role;
            $isStudent = ($userRole === 'Student');
            $isFaculty = ($userRole === 'Faculty & Staff');
             $formAction = route('requests.update', $request->id); // Use the generic update route
         @endphp

         <div class="edit-form-container"> {{-- Added container --}}
         <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT') {{-- Use PUT or PATCH for updates --}}

            @php use App\Helpers\ServiceHelper; @endphp {{-- Import the helper --}}

            {{-- Display Request ID and Service Category (Readonly) --}}
            <div class="form-section">
                <h5>Request Information</h5>
                <div class="form-group">
                    <label>Request ID</label>
                    <input type="text" class="form-control readonly-field" value="{{ ($isStudent ? 'SSR-' : 'FSR-') . date('Ymd', strtotime($request->created_at)) . '-' . str_pad($request->id, 4, '0', STR_PAD_LEFT) }}" readonly>
                </div>
                 <div class="form-group">
                     <label>Service Category</label>
                     {{-- Call the static helper directly (imported at top of form) --}}
                     <input type="text" class="form-control readonly-field" value="{{ ServiceHelper::formatServiceCategory($request->service_category, $request->description) }}" readonly>
                     {{-- Hidden input to ensure service_category isn't lost if needed, though it shouldn't be editable --}}
                     {{-- <input type="hidden" name="service_category" value="{{ $request->service_category }}"> --}}
                </div>
            </div>

             {{-- Service Specific Fields --}}
            <div class="form-section">
                <h5>Service Specific Details</h5>
                @switch($request->service_category)
                    @case('reset_email_password')
                    @case('reset_tup_web_password')
                    @case('reset_ers_password')
                        <div class="form-group">
                            <label for="account_email">Account Email</label>
                            <input type="email" class="form-control" id="account_email" name="account_email" value="{{ old('account_email', $request->account_email) }}" required>
                        </div>
                        @break

                    @case('change_of_data_ms')
                    @case('change_of_data_portal')
                        <div class="form-group">
                            <label for="data_type">Data to be Updated</label>
                            <input type="text" class="form-control" id="data_type" name="data_type" value="{{ old('data_type', $request->data_type) }}" required>
                        </div>
                         <div class="form-group">
                             <label for="new_data">New Data</label>
                             <textarea class="form-control" id="new_data" name="new_data" rows="3" required>{{ old('new_data', $request->new_data) }}</textarea>
                         </div>
                         {{-- Cleaned up misplaced closing div and empty if block --}}
                         <div class="form-group">
                             <label for="supporting_document">Supporting Document (Optional: Replace existing)</label>
                             @if($request->supporting_document)
                             <div class="mb-2">
                                 Current: <a href="{{ Storage::url($request->supporting_document) }}" target="_blank" class="current-file-link">{{ basename($request->supporting_document) }}</a>
                                 <div class="form-check form-check-inline">
                                      <input class="form-check-input" type="checkbox" id="remove_supporting_document" name="remove_supporting_document" value="1">
                                      <label class="form-check-label" for="remove_supporting_document">Remove current document</label>
                                 </div>
                             </div>
                             @endif
                             <input type="file" class="form-control-file" id="supporting_document" name="supporting_document">
                             <small class="form-text text-muted">Max file size: 2MB. Allowed types: jpg, png, pdf, doc, docx.</small>
                        </div>
                        @break

                    @case('biometric_record') // Added this case
                    @case('dtr') {{-- Faculty Only --}}
                        <div class="form-group">
                            <label for="dtr_months">Month(s) of Record</label> {{-- Made label more generic --}}
                            <input type="text" class="form-control" id="dtr_months" name="dtr_months" value="{{ old('dtr_months', $request->dtr_months) }}" required>
                        </div>
                         <div class="form-check">
                             <input class="form-check-input" type="checkbox" id="dtr_with_details" name="dtr_with_details" value="1" {{ old('dtr_with_details', $request->dtr_with_details) ? 'checked' : '' }}>
                             <label class="form-check-label" for="dtr_with_details">
                                 Include In/Out Details (if applicable) {{-- Clarified label --}}
                             </label>
                         </div>
                        @break

                    @case('biometrics_enrollement') {{-- Faculty Only --}}
                         <div class="form-group">
                             <label for="middle_name">Middle Name</label>
                             <input type="text" class="form-control" id="middle_name" name="middle_name" value="{{ old('middle_name', $request->middle_name) }}">
                         </div>
                         <div class="form-group">
                             <label for="college">College</label>
                             <input type="text" class="form-control" id="college" name="college" value="{{ old('college', $request->college) }}" required>
                         </div>
                         <div class="form-group">
                             <label for="department">Department</label>
                             <input type="text" class="form-control" id="department" name="department" value="{{ old('department', $request->department) }}" required>
                         </div>
                         <div class="form-group">
                             <label for="plantilla_position">Plantilla Position</label>
                             <input type="text" class="form-control" id="plantilla_position" name="plantilla_position" value="{{ old('plantilla_position', $request->plantilla_position) }}" required>
                         </div>
                         <div class="form-group">
                             <label for="date_of_birth">Date of Birth</label>
                             <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $request->date_of_birth) }}" required>
                         </div>
                         <div class="form-group">
                             <label for="phone_number">Phone Number</label>
                             <input type="tel" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $request->phone_number) }}" required>
                         </div>
                         <div class="form-group">
                             <label for="address">Address</label>
                             <textarea class="form-control" id="address" name="address" rows="3" required>{{ old('address', $request->address) }}</textarea>
                         </div>
                         <div class="form-group">
                             <label for="blood_type">Blood Type</label>
                             <input type="text" class="form-control" id="blood_type" name="blood_type" value="{{ old('blood_type', $request->blood_type) }}">
                         </div>
                         <div class="form-group">
                             <label for="emergency_contact_person">Emergency Contact Person</label>
                             <input type="text" class="form-control" id="emergency_contact_person" name="emergency_contact_person" value="{{ old('emergency_contact_person', $request->emergency_contact_person) }}" required>
                         </div>
                         <div class="form-group">
                             <label for="emergency_contact_number">Emergency Contact Number</label>
                             <input type="tel" class="form-control" id="emergency_contact_number" name="emergency_contact_number" value="{{ old('emergency_contact_number', $request->emergency_contact_number) }}" required>
                         </div>
                        @break

                    @case('new_internet')
                    @case('new_telephone')
                    @case('repair_and_maintenance')
                    @case('computer_repair_maintenance')
                    @case('printer_repair_maintenance') {{-- Faculty Only --}}
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="{{ old('location', $request->location) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="problem_encountered">Problem(s) Encountered</label>
                            <textarea class="form-control" id="problem_encountered" name="problem_encountered" rows="3" required>{{ old('problem_encountered', $request->problem_encountered) }}</textarea>
                        </div>
                        @break

                    @case('request_led_screen')
                        <div class="form-group">
                            <label for="preferred_date">Preferred Date</label>
                            <input type="date" class="form-control" id="preferred_date" name="preferred_date" value="{{ old('preferred_date', $request->preferred_date) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="preferred_time">Preferred Time</label>
                            <input type="time" class="form-control" id="preferred_time" name="preferred_time" value="{{ old('preferred_time', $request->preferred_time) }}" required>
                        </div>
                         @if($isFaculty) {{-- Faculty Only Field --}}
                         <div class="form-group">
                             <label for="led_screen_details">Additional Details/Instructions</label>
                             <textarea class="form-control" id="led_screen_details" name="led_screen_details" rows="3">{{ old('led_screen_details', $request->led_screen_details) }}</textarea>
                         </div>
                         @endif
                        @break

                    @case('install_application') {{-- Faculty Only --}}
                         <div class="form-group">
                             <label for="application_name">Application/System/Software Name</label>
                             <input type="text" class="form-control" id="application_name" name="application_name" value="{{ old('application_name', $request->application_name) }}" required>
                         </div>
                         <div class="form-group">
                             <label for="installation_purpose">Purpose of Installation</label>
                             <textarea class="form-control" id="installation_purpose" name="installation_purpose" rows="3" required>{{ old('installation_purpose', $request->installation_purpose) }}</textarea>
                         </div>
                         <div class="form-group">
                             <label for="installation_notes">Additional Requirements/Notes</label>
                             <textarea class="form-control" id="installation_notes" name="installation_notes" rows="3">{{ old('installation_notes', $request->installation_notes) }}</textarea>
                         </div>
                        @break

                    @case('post_publication') {{-- Faculty Only --}}
                         <div class="form-group">
                             <label for="publication_author">Author</label>
                             <input type="text" class="form-control" id="publication_author" name="publication_author" value="{{ old('publication_author', $request->publication_author) }}" required>
                         </div>
                         <div class="form-group">
                             <label for="publication_editor">Editor</label>
                             <input type="text" class="form-control" id="publication_editor" name="publication_editor" value="{{ old('publication_editor', $request->publication_editor) }}" required>
                         </div>
                         <div class="form-group">
                             <label for="publication_start_date">Date of Publication</label>
                             <input type="date" class="form-control" id="publication_start_date" name="publication_start_date" value="{{ old('publication_start_date', $request->publication_start_date) }}" required>
                         </div>
                         <div class="form-group">
                             <label for="publication_end_date">End of Publication</label>
                             <input type="date" class="form-control" id="publication_end_date" name="publication_end_date" value="{{ old('publication_end_date', $request->publication_end_date) }}" required>
                         </div>
                        @break

                    @case('data_docs_reports') {{-- Faculty Only --}}
                         <div class="form-group">
                             <label for="data_documents_details">Details of Data/Documents/Reports Needed</label>
                             <textarea class="form-control" id="data_documents_details" name="data_documents_details" rows="3" required>{{ old('data_documents_details', $request->data_documents_details) }}</textarea>
                         </div>
                        @break

                    @case('others')
                        <div class="form-group">
                            <label for="description">Description of Service Needed</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required>{{ old('description', $request->description) }}</textarea>
                        </div>
                        @break

                    @default
                        <p>No specific fields to edit for this service category.</p>
                 @endswitch
             </div>

             {{-- Removed Additional Notes Section --}}

             <div class="form-group text-right">
                <a href="{{ route('myrequests') }}" class="btn btn-secondary">Cancel</a>
                 <button type="submit" class="btn btn-primary">Update Request</button>
             </div>
         </form>
         </div> {{-- Close container --}}
    </div>

    <!-- Import JS files -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}" defer></script>
    {{-- Add any specific JS needed for this form --}}

</body>
</html>
