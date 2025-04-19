<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Customer Satisfaction</title>
    <style>
        .survey-container {
            max-width: 900px;
            margin: 50px auto;
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
        .user-type {
            margin-bottom: 15px;
        }
        .likert-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .likert-table th, .likert-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .likert-table th {
            background-color: #f8f9fa;
        }
        .likert-table td:first-child {
            text-align: left;
            width: 50%;
        }
        .likert-criteria {
            font-weight: bold;
        }
        .likert-subcriteria {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
        .rating-legend {
            font-size: 0.85em;
            margin-top: 10px;
            margin-bottom: 15px;
            color: #555;
        }
        .comments-section {
            margin-top: 20px;
        }
        .tagalog-text {
            font-size: 0.85em;
            color: #666;
            font-style: italic;
        }
        .direction-text {
            margin-bottom: 15px;
        }
        /* Modal header styling */
        .modal-header {
            background-color: #28a745;
            color: white;
            border-radius: 5px 5px 0 0;
        }
        /* Success icon styling */
        .success-icon {
            font-size: 60px;
            color: #28a745;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="survey-container">
            <div class="form-header">
                <h3 class="text-center mb-3">CUSTOMER SATISFACTION & FEEDBACK FORM</h3>
                
                <div class="direction-text">
                    <p><strong>DIRECTIONS:</strong> Kindly rate how the employee or office has satisfied your needs, request or concern based on the following criteria:
                    <br><span class="tagalog-text">(Panuto: Paki-rate kung gaano natugunan ng empleyadong o opisina ang iyong mga pangangailangan, kahilingan o alalahanin batay sa sumusunod na pamantayan:)</span></p>
                </div>
            </div>

            <form id="satisfactionForm" action="{{ url('/submit-survey') }}" method="POST" 
                  @if($request->is_surveyed) class="was-validated" disabled @endif>
                @csrf
                <input type="hidden" name="request_id" value="{{ $request->id }}">
                
                <table class="likert-table">
                    <thead>
                        <tr>
                            <th>Criteria</th>
                            <th>5</th>
                            <th>4</th>
                            <th>3</th>
                            <th>2</th>
                            <th>1</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="likert-criteria">Responsiveness <span class="tagalog-text">(Pagtutugon)</span></div>
                                <div class="likert-subcriteria">(How does the employee/office respond to your concerns?)
                                <br><span class="tagalog-text">(Paano tumutugon ang empleyadong/opisina sa iyong mga alalahanin?)</span></div>
                            </td>
                            <td><input type="radio" name="responsiveness" value="5" required></td>
                            <td><input type="radio" name="responsiveness" value="4"></td>
                            <td><input type="radio" name="responsiveness" value="3"></td>
                            <td><input type="radio" name="responsiveness" value="2"></td>
                            <td><input type="radio" name="responsiveness" value="1"></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="likert-criteria">Reliability/Quality <span class="tagalog-text">(Maasahan/Kalidad)</span></div>
                                <div class="likert-subcriteria">(How was the quality of service that you have experienced?)
                                <br><span class="tagalog-text">(Paano ang kalidad ng serbisyo na iyong naranasan?)</span></div>
                            </td>
                            <td><input type="radio" name="reliability" value="5" required></td>
                            <td><input type="radio" name="reliability" value="4"></td>
                            <td><input type="radio" name="reliability" value="3"></td>
                            <td><input type="radio" name="reliability" value="2"></td>
                            <td><input type="radio" name="reliability" value="1"></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="likert-criteria">Access & Facilities <span class="tagalog-text">(Daanan at Pasilidad)</span></div>
                                <div class="likert-subcriteria">(How comfortable and accessible was the office to you?)
                                <br><span class="tagalog-text">(Gaano ka komportable at accessible ang opisina para sa iyo?)</span></div>
                            </td>
                            <td><input type="radio" name="access_facilities" value="5" required></td>
                            <td><input type="radio" name="access_facilities" value="4"></td>
                            <td><input type="radio" name="access_facilities" value="3"></td>
                            <td><input type="radio" name="access_facilities" value="2"></td>
                            <td><input type="radio" name="access_facilities" value="1"></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="likert-criteria">Communication <span class="tagalog-text">(Komunikasyon)</span></div>
                                <div class="likert-subcriteria">(How does the employee communicate with you?)
                                <br><span class="tagalog-text">(Paano nakikipag-usap sa iyo ang empleyado?)</span></div>
                            </td>
                            <td><input type="radio" name="communication" value="5" required></td>
                            <td><input type="radio" name="communication" value="4"></td>
                            <td><input type="radio" name="communication" value="3"></td>
                            <td><input type="radio" name="communication" value="2"></td>
                            <td><input type="radio" name="communication" value="1"></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="likert-criteria">Costs <span class="tagalog-text">(Gastos)</span></div>
                                <div class="likert-subcriteria">(How costly was the service you have received?)
                                <br><span class="tagalog-text">(Magkano ang halaga ng ibinigay na serbisyo sa iyo?)</span></div>
                            </td>
                            <td><input type="radio" name="costs" value="5" required></td>
                            <td><input type="radio" name="costs" value="4"></td>
                            <td><input type="radio" name="costs" value="3"></td>
                            <td><input type="radio" name="costs" value="2"></td>
                            <td><input type="radio" name="costs" value="1"></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="likert-criteria">Integrity <span class="tagalog-text">(Integridad)</span></div>
                                <div class="likert-subcriteria">(How fair and honestly does the employee treated you?)
                                <br><span class="tagalog-text">(Gaano ka patas at tapat ang pagtratro sa iyo ng empleyado?)</span></div>
                            </td>
                            <td><input type="radio" name="integrity" value="5" required></td>
                            <td><input type="radio" name="integrity" value="4"></td>
                            <td><input type="radio" name="integrity" value="3"></td>
                            <td><input type="radio" name="integrity" value="2"></td>
                            <td><input type="radio" name="integrity" value="1"></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="likert-criteria">Assurance <span class="tagalog-text">(Kasiguraduhan)</span></div>
                                <div class="likert-subcriteria">(How capable was the employee that attended to your concern?)
                                <br><span class="tagalog-text">(Gaano kahusay ang empleyadong na tumulong sa iyong alalahanin?)</span></div>
                            </td>
                            <td><input type="radio" name="assurance" value="5" required></td>
                            <td><input type="radio" name="assurance" value="4"></td>
                            <td><input type="radio" name="assurance" value="3"></td>
                            <td><input type="radio" name="assurance" value="2"></td>
                            <td><input type="radio" name="assurance" value="1"></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="likert-criteria">Outcome <span class="tagalog-text">(Kinalabasan)</span></div>
                                <div class="likert-subcriteria">(How was your overall experience?)
                                <br><span class="tagalog-text">(Kumusta ang iyong pangkalahatang karanasan?)</span></div>
                            </td>
                            <td><input type="radio" name="outcome" value="5" required></td>
                            <td><input type="radio" name="outcome" value="4"></td>
                            <td><input type="radio" name="outcome" value="3"></td>
                            <td><input type="radio" name="outcome" value="2"></td>
                            <td><input type="radio" name="outcome" value="1"></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="rating-legend">
                    <strong>Rating Legend:</strong> 5 – Outstanding; 4 – Very Satisfactory; 3 – Satisfactory; 2 – Fair; 1 – Poor or Needs Improvement
                    <br><span class="tagalog-text">(Pamantayan: 5 - Natatangi; 4 - Lubhang Kasiya-siya; 3 - Kasiya-siya; 2 - Katamtaman; 1 - Mahina o Nangangailangan ng Pagpapabuti)</span>
                </div>
                
                <div class="comments-section">
                    <p><strong>We are interested to know why you give that rating; you may add comments/feedback below.</strong>
                    <br><span class="tagalog-text">(Interesado kaming malaman kung bakit mo ibinigay ang rating na iyon; maaari kang magdagdag ng mga komento/feedback sa .)</span></p>
                    
                    <div class="form-group">
                        <strong>Explanation of the commendation/complaint/request for assistance/feedback:</strong>
                        <br><span class="tagalog-text">(Paliwag ng papuri/reklamo/kahilingan para sa tulong/puna:)</span>
                        <textarea class="form-control" id="additional_comments" name="additional_comments" rows="4"></textarea>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Submit Survey</button>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Survey Submitted Successfully</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4>Thank You!</h4>
                    <p>Your feedback has been submitted successfully.</p>
                    <p>We appreciate your time in providing valuable feedback that helps us improve our services.</p>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('request.history') }}" class="btn btn-success btn-block">Return to Request History</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Show the success modal if indicated by the controller
            @if(isset($showSuccessModal) && $showSuccessModal)
                $('#successModal').modal('show');
            @endif
            
            // Disable form elements if the survey has already been submitted
            @if($request->is_surveyed)
                $('#satisfactionForm input, #satisfactionForm textarea, #satisfactionForm button').prop('disabled', true);
                $('#satisfactionForm').prepend('<div class="alert alert-info mb-3">This survey has already been submitted. Thank you for your feedback!</div>');
            @endif
        });
        
        // When the modal is hidden, redirect to the request history page
        $('#successModal').on('hidden.bs.modal', function () {
            window.location.href = "{{ route('request.history') }}";
        });
    </script>
</body>
</html>