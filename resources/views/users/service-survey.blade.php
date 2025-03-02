<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Service Satisfaction Survey</title>
    <style>
        .survey-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .likert-question {
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 15px;
        }
        .likert-scale {
            display: flex;
            flex-direction: column;
        }
        .likert-option {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .likert-option input[type="radio"] {
            margin-right: 10px;
        }
        .likert-option label {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="survey-container">
            <h2 class="text-center mb-4">Service Request System Satisfaction Survey</h2>
            <form action="{{ route('submit.survey') }}" method="POST">
                @csrf
                <input type="hidden" name="request_id" value="{{ $request->id }}">
                
                <div class="likert-question">
                    <h5>1. Ease of Use</h5>
                    <p class="text-muted">The service request system is easy to manage.</p>
                    <div class="likert-scale">
                        <div class="likert-option">
                            <input type="radio" id="ease1" name="ease_of_use" value="1" required>
                            <label for="ease1">1 - Strongly Disagree</label>
                        </div>
                        <div class="likert-option">
                            <input type="radio" id="ease2" name="ease_of_use" value="2">
                            <label for="ease2">2 - Disagree</label>
                        </div>
                        <div class="likert-option">
                            <input type="radio" id="ease3" name="ease_of_use" value="3">
                            <label for="ease3">3 - Neutral</label>
                        </div>
                        <div class="likert-option">
                            <input type="radio" id="ease4" name="ease_of_use" value="4">
                            <label for="ease4">4 - Agree</label>
                        </div>
                        <div class="likert-option">
                            <input type="radio" id="ease5" name="ease_of_use" value="5">
                            <label for="ease5">5 - Strongly Agree</label>
                        </div>
                    </div>
                </div>

                <div class="likert-question">
                    <h5>2. Request Processing</h5>
                    <p class="text-muted">My service request was processed efficiently.</p>
                    <div class="likert-scale">
                        <div class="likert-option">
                            <input type="radio" id="process1" name="request_processing" value="1" required>
                            <label for="process1">1 - Strongly Disagree</label>
                        </div>
                        <div class="likert-option">
                            <input type="radio" id="process2" name="request_processing" value="2">
                            <label for="process2">2 - Disagree</label>
                        </div>
                        <div class="likert-option">
                            <input type="radio" id="process3" name="request_processing" value="3">
                            <label for="process3">3 - Neutral</label>
                        </div>
                        <div class="likert-option">
                            <input type="radio" id="process4" name="request_processing" value="4">
                            <label for="process4">4 - Agree</label>
                        </div>
                        <div class="likert-option">
                            <input type="radio" id="process5" name="request_processing" value="5">
                            <label for="process5">5 - Strongly Agree</label>
                        </div>
                    </div>
                </div>

                <div class="likert-question">
                    <h5>3. Staff Interaction</h5>
                    <p class="text-muted">The UITC staff was helpful and professional.</p>
                    <div class="likert-scale">
                        <div class="likert-option">
                            <input type="radio" id="staff1" name="staff_interaction" value="1" required>
                            <label for="staff1">1 - Strongly Disagree</label>
                        </div>
                        <div class="likert-option">
                            <input type="radio" id="staff2" name="staff_interaction" value="2">
                            <label for="staff2">2 - Disagree</label>
                        </div>
                        <div class="likert-option">
                            <input type="radio" id="staff3" name="staff_interaction" value="3">
                            <label for="staff3">3 - Neutral</label>
                        </div>
                        <div class="likert-option">
                            <input type="radio" id="staff4" name="staff_interaction" value="4">
                            <label for="staff4">4 - Agree</label>
                        </div>
                        <div class="likert-option">
                            <input type="radio" id="staff5" name="staff_interaction" value="5">
                            <label for="staff5">5 - Strongly Agree</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="additional_comments">Additional Comments</label>
                    <textarea class="form-control" id="additional_comments" name="additional_comments" rows="4" placeholder="Share your feedback (optional)"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Submit Survey</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>