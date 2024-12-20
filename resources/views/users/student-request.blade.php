<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/student-request.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Service Request Form</title>
</head>
<body>
<div class="container">
    <div class="form-section">
        <h4>Select Service Category</h4>
        <select id="serviceCategory" class="form-control" required>
            <option value="">Select a Service Category</option>
            @foreach($studentForms as $form)
                <optgroup label="{{ $form['name'] }}">
                    @foreach($form['options'] as $index => $option)
                        <option value="{{ $form['id'] }}-{{ $index }}">
                            {{ $option['optionName'] }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
            <optgroup label="Other Services">
                <option value="others">Others</option>
            </optgroup>
        </select>
    </div>

    <!-- Personal Information Form Template -->
    <div id="personalInfoForm" style="display:none;">
        <div class="form-section">
            <h4>Personal Information</h4>
            <div class="row">
                <div class="col-md-4">
                    <label>First Name</label>
                    <input type="text" class="form-control" name="first_name" placeholder="First Name" required>
                </div>
                <div class="col-md-4">
                    <label>Last Name</label>
                    <input type="text" class="form-control" name="last_name" placeholder="Last Name" required>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label>Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="Email Address" required>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Form Fields Container -->
    <div id="dynamicFieldsContainer" class="mt-3">
        <!-- Dynamically generated fields will appear here -->
    </div>

    <!-- Others Request Form -->
    <div id="othersRequestForm" style="display:none;">
        <div class="form-section">
            <h4>Other Service Request</h4>
            <div class="form-group">
                <label>Describe Your Request</label>
                <textarea class="form-control" id="othersRequestDescription" name="others_request_description" rows="4" placeholder="Please provide detailed information about your request"></textarea>
                <small class="form-text text-muted">Be as specific as possible to help us understand and process your request.</small>
            </div>
            <div class="form-group">
                <label>Preferred Contact Method</label>
                <div class="row">
                    <div class="col-md-6">
                        <select class="form-control" name="contact_method">
                            <option value="">Select Contact Method</option>
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="contact_detail" placeholder="Enter contact detail">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="form-group mt-3">
        <button type="submit" class="btn btn-primary" id="submitStudentRequest">Submit Request</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceCategory = document.getElementById('serviceCategory');
    const dynamicFieldsContainer = document.getElementById('dynamicFieldsContainer');
    const personalInfoForm = document.getElementById('personalInfoForm');
    const othersRequestForm = document.getElementById('othersRequestForm');
    const submitButton = document.getElementById('submitStudentRequest');

    // Parsed student forms from the backend
    const studentForms = @json($studentForms);

    serviceCategory.addEventListener('change', function() {
        // Clear previous dynamic fields
        dynamicFieldsContainer.innerHTML = '';
        
        // Hide personal info and others form
        personalInfoForm.style.display = 'none';
        othersRequestForm.style.display = 'none';

        const selectedValue = this.value;

        if (selectedValue === 'others') {
            // Show others request form and personal info
            othersRequestForm.style.display = 'block';
            personalInfoForm.style.display = 'block';
            return;
        }

        if (selectedValue) {
            // Parse the selected value (formId-optionIndex)
            const [formId, optionIndex] = selectedValue.split('-');
            
            // Find the corresponding form and option
            const selectedForm = studentForms.find(form => form.id == formId);
            if (selectedForm) {
                const selectedOption = selectedForm.options[optionIndex];

                // Create form for the selected option
                const optionFormDiv = document.createElement('div');
                optionFormDiv.classList.add('form-section');
                
                // Option name as heading
                const headingH4 = document.createElement('h4');
                headingH4.textContent = selectedOption.optionName;
                optionFormDiv.appendChild(headingH4);

                // Create row for fields
                const fieldsRow = document.createElement('div');
                fieldsRow.classList.add('row');

                // Generate fields dynamically
                selectedOption.fields.forEach(field => {
                    const fieldCol = document.createElement('div');
                    fieldCol.classList.add('col-md-6', 'mb-3');

                    const label = document.createElement('label');
                    label.textContent = field.name;

                    const input = document.createElement('input');
                    input.type = field.type === 'text' ? 'text' : field.type;
                    input.classList.add('form-control');
                    input.name = field.name.toLowerCase().replace(/\s+/g, '_');
                    input.required = true;

                    fieldCol.appendChild(label);
                    fieldCol.appendChild(input);
                    fieldsRow.appendChild(fieldCol);
                });

                optionFormDiv.appendChild(fieldsRow);
                dynamicFieldsContainer.appendChild(optionFormDiv);

                // Show personal info form
                personalInfoForm.style.display = 'block';
            }
        }
    });

    // Submit button event listener (placeholder for form submission logic)
    submitButton.addEventListener('click', function() {
        // Collect form data
        const formData = new FormData();
        
        // Add service category
        formData.append('service_category', serviceCategory.value);

        // Add personal info
        const personalInfoInputs = personalInfoForm.querySelectorAll('input');
        personalInfoInputs.forEach(input => {
            formData.append(input.name, input.value);
        });

        // Add dynamic fields
        const dynamicInputs = dynamicFieldsContainer.querySelectorAll('input');
        dynamicInputs.forEach(input => {
            formData.append(input.name, input.value);
        });

        // Add others request data if applicable
        if (othersRequestForm.style.display !== 'none') {
            const othersDescription = document.getElementById('othersRequestDescription');
            const contactMethod = othersRequestForm.querySelector('select[name="contact_method"]');
            const contactDetail = othersRequestForm.querySelector('input[name="contact_detail"]');

            formData.append('others_request_description', othersDescription.value);
            formData.append('contact_method', contactMethod.value);
            formData.append('contact_detail', contactDetail.value);
        }

        // TODO: Send data to server using AJAX
        console.log('Form data collected:', Object.fromEntries(formData));
        alert('Form submission logic to be implemented');
    });
});
</script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</body>
</html>
