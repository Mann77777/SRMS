<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Customer Satisfaction & Feedback Details</title>
    <style>
        .survey-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .form-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .rating-item {
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .rating-label {
            font-weight: bold;
        }
        .rating-value {
            margin-left: 10px;
            font-size: 1.1em;
        }
        .star-rating {
            color: #ffc107;
        }
        .rating-description {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
        .average-rating {
            margin-top: 20px;
            font-size: 1.2em;
            font-weight: bold;
            padding: 15px;
            background-color: #e9f7ef;
            border-radius: 5px;
        }
        .comments-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f1f8ff;
            border-radius: 5px;
        }
        .request-details {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .request-details h5 {
            margin-bottom: 15px;
            font-weight: bold;
        }
        .request-details p {
            margin-bottom: 8px;
        }
        .request-details strong {
            color: #333;
        }
        .back-btn {
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .content {
            padding: 20px;
            margin-left: 250px;
            margin-top: 70px; /* Added top margin to avoid navbar overlap */
            transition: margin-left 0.5s;
        }
        
        .tagalog-text {
            font-size: 0.85em;
            color: #666;
            font-style: italic;
        }
        
        table.table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                margin-top: 70px; /* Maintain top margin on mobile */
            }
        }
    </style>
</head>
<body>
    
    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')
    
    <div class="content">
        <div class="survey-container">
            <div class="form-header">
                <h3 class="text-center mb-3">CUSTOMER SATISFACTION & FEEDBACK DETAILS</h3>
            </div>
            
            <div class="request-details">
                <h5>Request Information</h5>
                <p><strong>Request ID:</strong> 
                    @if(Auth::user()->role == "Student")
                        {{ 'SSR-' . date('Ymd', strtotime($serviceRequest->created_at)) . '-' . str_pad($serviceRequest->id, 4, '0', STR_PAD_LEFT) }}
                    @else
                        {{ 'FSR-' . date('Ymd', strtotime($serviceRequest->created_at)) . '-' . str_pad($serviceRequest->id, 4, '0', STR_PAD_LEFT) }}
                    @endif
                </p>
                <p><strong>Service Type:</strong> 
                    @switch($serviceRequest->service_category)
                        @case('create')
                            Create MS Office/TUP Email Account
                            @break
                        @case('reset_email_password')
                            Reset MS Office/TUP Email Password
                            @break
                        @case('change_of_data_ms')
                            Change of Data (MS Office)
                            @break
                        @case('reset_tup_web_password')
                            Reset TUP Web Password
                            @break
                        @case('reset_ers_password')
                            Reset ERS Password
                            @break
                        @case('change_of_data_portal')
                            Change of Data (Portal)
                            @break
                        @case('dtr')
                            Daily Time Record
                            @break
                        @case('biometric_record')
                            Biometric Record
                            @break
                        @case('biometrics_enrollement')
                            Biometrics Enrollment
                            @break
                        @case('new_internet')
                            New Internet Connection
                            @break
                        @case('new_telephone')
                            New Telephone Connection
                            @break
                        @case('repair_and_maintenance')
                            Internet/Telephone Repair and Maintenance
                            @break
                        @case('computer_repair_maintenance')
                            Computer Repair and Maintenance
                            @break
                        @case('printer_repair_maintenance')
                            Printer Repair and Maintenance
                            @break
                        @case('request_led_screen')
                            LED Screen Request
                            @break
                        @case('install_application')
                            Install Application/Information System/Software
                            @break
                        @case('post_publication')
                            Post Publication/Update of Information Website
                            @break
                        @case('data_docs_reports')
                            Data, Documents and Reports
                            @break
                        @case('others')
                            {{ $serviceRequest->description ?? 'Other Service' }}
                            @break
                        @default
                            {{ $serviceRequest->service_category }}
                    @endswitch
                </p>
                <p><strong>Submitted On:</strong> {{ \Carbon\Carbon::parse($serviceRequest->created_at)->format('M d, Y h:i A') }}</p>
                <p><strong>Completed On:</strong> {{ \Carbon\Carbon::parse($serviceRequest->updated_at)->format('M d, Y h:i A') }}</p>
                <p><strong>Assigned Staff:</strong> {{ $serviceRequest->assignedUITCStaff ? $serviceRequest->assignedUITCStaff->name : 'N/A' }}</p>
            </div>
            
            <div class="average-rating">
                <h5 class="mb-0">Average Rating: {{ number_format($survey->average_rating, 1) }} / 5.0</h5>
            </div>
            
            <h5 class="mt-4">Detailed Ratings</h5>
            
            <table class="table mt-3" style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="width: 60%; border: 1px solid #dee2e6; padding: 10px; text-align: center;">Criteria</th>
                        <th style="width: 8%; border: 1px solid #dee2e6; padding: 10px; text-align: center;">5</th>
                        <th style="width: 8%; border: 1px solid #dee2e6; padding: 10px; text-align: center;">4</th>
                        <th style="width: 8%; border: 1px solid #dee2e6; padding: 10px; text-align: center;">3</th>
                        <th style="width: 8%; border: 1px solid #dee2e6; padding: 10px; text-align: center;">2</th>
                        <th style="width: 8%; border: 1px solid #dee2e6; padding: 10px; text-align: center;">1</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: left; background-color: #f9f9f9;">
                            <div class="rating-label">Responsiveness <span class="tagalog-text">(Pagtutugon)</span></div>
                            <div class="rating-description">(How does the employee/office respond to your concerns?)</div>
                            <div class="rating-description tagalog-text">(Paano tumutugon ang empleyadong/opisina sa iyong mga alalahanin?)</div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->responsiveness == 5 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->responsiveness == 4 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->responsiveness == 3 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->responsiveness == 2 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->responsiveness == 1 ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: left; background-color: #f9f9f9;">
                            <div class="rating-label">Reliability/Quality <span class="tagalog-text">(Maasahan/Kalidad)</span></div>
                            <div class="rating-description">(How was the quality of service that you have experienced?)</div>
                            <div class="rating-description tagalog-text">(Paano ang kalidad ng serbisyo na iyong naranasan?)</div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->reliability == 5 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->reliability == 4 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->reliability == 3 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->reliability == 2 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->reliability == 1 ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: left; background-color: #f9f9f9;">
                            <div class="rating-label">Access & Facilities <span class="tagalog-text">(Daanan at Pasilidad)</span></div>
                            <div class="rating-description">(How comfortable and accessible was the office to you?)</div>
                            <div class="rating-description tagalog-text">(Gaano ka komportable at accessible ang opisina para sa iyo?)</div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->access_facilities == 5 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->access_facilities == 4 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->access_facilities == 3 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->access_facilities == 2 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->access_facilities == 1 ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: left; background-color: #f9f9f9;">
                            <div class="rating-label">Communication <span class="tagalog-text">(Komunikasyon)</span></div>
                            <div class="rating-description">(How does the employee communicate with you?)</div>
                            <div class="rating-description tagalog-text">(Paano nakikipag-usap sa iyo ang empleyado?)</div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->communication == 5 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->communication == 4 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->communication == 3 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->communication == 2 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->communication == 1 ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: left; background-color: #f9f9f9;">
                            <div class="rating-label">Costs <span class="tagalog-text">(Gastos)</span></div>
                            <div class="rating-description">(How costly was the service you have received?)</div>
                            <div class="rating-description tagalog-text">(Magkano ang halaga ng ibinigay na serbisyo sa iyo?)</div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->costs == 5 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->costs == 4 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->costs == 3 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->costs == 2 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->costs == 1 ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: left; background-color: #f9f9f9;">
                            <div class="rating-label">Integrity <span class="tagalog-text">(Integridad)</span></div>
                            <div class="rating-description">(How fair and honestly does the employee treated you?)</div>
                            <div class="rating-description tagalog-text">(Gaano ka patas at tapat ang pagtratro sa iyo ng empleyado?)</div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->integrity == 5 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->integrity == 4 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->integrity == 3 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->integrity == 2 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->integrity == 1 ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: left; background-color: #f9f9f9;">
                            <div class="rating-label">Assurance <span class="tagalog-text">(Kasiguraduhan)</span></div>
                            <div class="rating-description">How capable was the employee that attended to your concern?</div>
                            <div class="rating-description tagalog-text">(Gaano kahusay ang empleyadong na tumulong sa iyong alalahanin?)</div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check">
                                <input type="radio" disabled {{ $survey->assurance == 5 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check">
                                <input type="radio" disabled {{ $survey->assurance == 4 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check">
                                <input type="radio" disabled {{ $survey->assurance == 3 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check">
                                <input type="radio" disabled {{ $survey->assurance == 2 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check">
                                <input type="radio" disabled {{ $survey->assurance == 1 ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: left; background-color: #f9f9f9;">
                            <div class="rating-label">Outcome <span class="tagalog-text">(Kinalabasan)</span></div>
                            <div class="rating-description">(How was your overall experience?)</div>
                            <div class="rating-description tagalog-text">(Kumusta ang iyong pangkalahatang karanasan?)</div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->outcome == 5 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->outcome == 4 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->outcome == 3 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->outcome == 2 ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td style="border: 1px solid #dee2e6; padding: 10px; text-align: center; vertical-align: middle;">
                            <div class="form-check d-flex justify-content-center">
                                <input type="radio" disabled {{ $survey->outcome == 1 ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="rating-legend mt-2 mb-4" style="font-size: 0.85em; margin-top: 10px; margin-bottom: 15px; color: #555;">
                <strong>Rating Legend:</strong> 5 – Outstanding; 4 – Very Satisfactory; 3 – Satisfactory; 2 – Fair; 1 – Poor or Needs Improvement
                <br><span class="tagalog-text">(Pamantayan: 5 - Natatangi; 4 - Lubhang Kasiya-siya; 3 - Kasiya-siya; 2 - Katamtaman; 1 - Mahina o Nangangailangan ng Pagpapabuti)</span>
            </div>
            
            @if($survey->additional_comments)
            <div class="comments-section mt-4" style="margin-top: 20px;">
                <p><strong>Additional Comments/Feedback:</strong></p>
                <div class="mt-3 p-3 bg-white rounded" style="border: 1px solid #dee2e6;">
                    <p>{{ $survey->additional_comments }}</p>
                </div>
            </div>
            @endif
            
            <div class="back-btn text-center">
                <a href="{{ route('request.history') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Request History
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
</body>
</html>