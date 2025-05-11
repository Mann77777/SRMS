document.addEventListener('DOMContentLoaded', function () {
    // Function to apply filters
    function applyFilters() {
        const status = document.getElementById('status').value;
        const transactionType = document.getElementById('transaction_type').value;
        const searchTerm = document.getElementById('search-input').value; // Corrected ID
        const role = document.getElementById('role').value;

        // Build query string
        let queryParams = [];

        if (status && status !== 'all') {
            queryParams.push(`status=${encodeURIComponent(status)}`);
        }

        if (transactionType && transactionType !== 'all') {
            queryParams.push(`transaction_type=${encodeURIComponent(transactionType)}`);
        }

        if (searchTerm) {
            queryParams.push(`search=${encodeURIComponent(searchTerm)}`);
        }

        if (role && role !== 'all') {
            queryParams.push(`role=${encodeURIComponent(role)}`);
        }

        // Redirect with filters
        let url = window.location.pathname;
        if (queryParams.length > 0) {
            url += '?' + queryParams.join('&');
        }

        window.location.href = url;
    }

    // Add event listeners to filters
    const statusFilter = document.getElementById('status');
    const transactionFilter = document.getElementById('transaction_type');
    const roleFilter = document.getElementById('role');
    const searchInput = document.getElementById('search-input'); // Corrected ID

    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    if (transactionFilter) transactionFilter.addEventListener('change', applyFilters);
    if (roleFilter) roleFilter.addEventListener('change', applyFilters);

    if (searchInput) {
        // Debounce search to avoid too many requests
        let searchTimeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 500);
        });

        // Also handle Enter key
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyFilters();
            }
        });
    }

    // Function to complete a request
    function completeRequest(requestId) {
        // Open the complete request modal instead of directly submitting
        const completeRequestModal = document.getElementById('completeRequestModal');
        const requestIdInput = document.getElementById('completeRequestId');

        if (completeRequestModal && requestIdInput) {
            requestIdInput.value = requestId;
            $(completeRequestModal).modal('show');
        } else {
            console.error('Complete request modal or input not found');
        }
    }

    // Function to show success modal
    function showSuccessModal() {
        Swal.fire({
            title: 'Success!',
            text: 'The request has been successfully completed.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                // Reload the page to refresh the data
                window.location.reload();
            }
        });
    }

    // Attach event listeners to complete buttons
    document.querySelectorAll('.btn-complete').forEach(button => {
        button.addEventListener('click', function () {
            const requestId = this.getAttribute('data-request-id');
            completeRequest(requestId);
        });
    });

    // Form submission handler
    const completeRequestForm = document.getElementById('completeRequestForm');
    if (completeRequestForm) {
        completeRequestForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Validate form
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }

            const formData = new FormData(this);
            const requestId = formData.get('request_id');

            // Disable submit button to prevent multiple submissions
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // AJAX request to complete the request
            fetch('/uitc-staff/complete-request', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.message === 'Request completed successfully') {
                        // Close the modal
                        $('#completeRequestModal').modal('hide');

                        // Show success message
                        showSuccessModal();
                    } else {
                        // Handle error
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to complete request'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while completing the request'
                    });
                })
                .finally(() => {
                    // Re-enable submit button
                    submitButton.disabled = false;
                    submitButton.textContent = 'Submit Completion';
                });
        });
    }
});

$(document).ready(function () {
    // Set CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // DEBUGGING - Log initial dropdown values
    console.log("Initial status value:", $('#status').val());
    console.log("Initial transaction type value:", $('#transaction_type').val());

    // FILTER FUNCTIONALITY

    // Function to apply filters and update the table
    function applyFilters() {
        const status = $('#status').val();
        const transactionType = $('#transaction_type').val();
        const searchTerm = $('#search-input').val(); // Corrected ID

        console.log("Applying filters:", { status, transactionType, searchTerm });

        // Build the request URL with filters
        let url = window.location.pathname;
        let queryParams = [];

        if (status && status !== 'all') {
            queryParams.push(`status=${encodeURIComponent(status)}`);
        }

        if (transactionType && transactionType !== 'all') {
            queryParams.push(`transaction_type=${encodeURIComponent(transactionType)}`);
        }

        if (searchTerm) {
            queryParams.push(`search=${encodeURIComponent(searchTerm)}`);
        }

        // Redirect with filters
        let redirectUrl = url;
        if (queryParams.length > 0) {
            redirectUrl += '?' + queryParams.join('&');
        }

        console.log("Redirecting to:", redirectUrl);
        window.location.href = redirectUrl;
    }

    // Add event listeners to filters
    $('#status').on('change', function () {
        console.log("Status changed to:", $(this).val());
        applyFilters();
    });

    $('#transaction_type').on('change', function () {
        console.log("Transaction type changed to:", $(this).val());
        applyFilters();
    });

    // Handle search button click
    $('.search-btn').on('click', function () { // Use the button class from HTML
        applyFilters();
    });

    // Handle Enter key in search field
    $('#search-input').on('keydown', function (e) { // Corrected ID
        if (e.key === 'Enter') {
            e.preventDefault();
            applyFilters();
        }
    });

    // REQUEST DETAIL VIEW

    // Click handler for request IDs
    $(document).on('click', '.clickable-request-id', function () {
        console.log("Request ID clicked");
        const row = $(this).closest('tr');
        const requestId = $(this).text().trim();
        const requestType = row.find('.btn-complete').data('request-type') || 'student';

        // Fetch request details from server
        $.ajax({
            url: `/uitc-staff/request-details/${requestId}`,
            method: 'GET',
            data: { type: requestType },
            success: function (data) {
                updateRequestDetailsModal(data);
                $('#requestDetailsModal').modal('show');
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.error || 'Failed to fetch request details'
                });
            }
        });
    });

    // Function to update the modal with request information
    function updateRequestDetailsModal(requestData) {
        console.log("Updating modal with data:", requestData);

        // Set basic request information
        $('#detailsRequestId').text(requestData.id);
        $('#detailsRequestRole').text(requestData.role || requestData.request_type || 'N/A');
        $('#detailsRequestDate').text(formatDateTime(requestData.created_at));

        // Format the data in request_data field
        $('#detailsRequestData').html(requestData.request_data || formatRequestData(requestData));

        // Set status with appropriate color
        const $statusBadge = $('#detailsRequestStatus');
        const statusText = requestData.status.trim();
        $statusBadge.text(statusText);
        $statusBadge.removeClass().addClass('custom-badge');

        // Match the status badge styling with the table
        if (statusText === 'Pending') {
            $statusBadge.addClass('custom-badge-warning');
        } else if (statusText === 'In Progress') {
            $statusBadge.addClass('custom-badge-info');
        } else if (statusText === 'Completed') {
            $statusBadge.addClass('custom-badge-success');
        } else if (statusText === 'Rejected' || statusText === 'Cancelled') {
            $statusBadge.addClass('custom-badge-danger');
        } else {
            $statusBadge.addClass('custom-badge-secondary');
        }

        // Handle completed date
        if (requestData.status === 'Completed') {
            if (requestData.completed_at) {
                $('#detailsRequestCompleted').text(formatDateTime(requestData.completed_at));
            } else if (requestData.updated_at) {
                $('#detailsRequestCompleted').text(formatDateTime(requestData.updated_at));
            } else {
                $('#detailsRequestCompleted').text('-');
            }
        } else {
            $('#detailsRequestCompleted').text('-');
        }

        // Hide sections that we don't need for UITC Staff view
        if ($('#pendingActionsContainer').length) {
            $('#pendingActionsContainer').hide();
        }

        // Show assignment info if present
        if ($('#assignmentInfoSection').length) {
            if (requestData.assigned_uitc_staff_id) {
                $('#assignmentInfoSection').show();
                $('#detailsAssignedTo').text(requestData.assignedUITCStaff?.name || 'You');
                $('#detailsTransactionType').text(formatTransactionType(requestData.transaction_type));
                $('#detailsAdminNotes').text(requestData.admin_notes || 'No notes');
            } else {
                $('#assignmentInfoSection').hide();
            }
        }

        // Show rejection info if the request was rejected
        if ($('#rejectionInfoSection').length) {
            if (requestData.status === 'Rejected') {
                $('#rejectionInfoSection').show();
                $('#detailsRejectionReason').text(requestData.rejection_reason || '-');
                $('#detailsRejectionNotes').text(requestData.rejection_notes || 'No notes');
                $('#detailsRejectedDate').text(formatDateTime(requestData.updated_at));
            } else {
                $('#rejectionInfoSection').hide();
            }
        }

        // Show completion info if the request was completed
        if ($('#completionInfoSection').length) {
            if (requestData.status === 'Completed') {
                $('#completionInfoSection').show();
                $('#detailsCompletionReport').text(requestData.completion_report || '-');
                $('#detailsActionsTaken').text(requestData.actions_taken || '-');
            } else {
                $('#completionInfoSection').hide();
            }
        }
    }

    // Format date and time from ISO format
    function formatDateTime(dateTimeString) {
        if (!dateTimeString) return '-';

        const date = new Date(dateTimeString);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        const month = months[date.getMonth()];
        const day = date.getDate();
        const year = date.getFullYear();

        let hours = date.getHours();
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12;
        hours = hours ? hours : 12; // Convert 0 to 12

        return `${month} ${day}, ${year} ${hours}:${minutes} ${ampm}`;
    }

    // Format transaction type
    function formatTransactionType(type) {
        if (!type) return 'N/A';

        switch (type.toLowerCase()) {
            case 'simple': return 'Simple Transaction';
            case 'complex': return 'Complex Transaction';
            case 'highly technical': return 'Highly Technical Transaction';
            default: return type;
        }
    }

    // Helper to create HTML for a field
    function createFieldHtml(label, value) {
        if (value === null || typeof value === 'undefined' || value === '') {
            return ''; // Don't display empty fields
        }
        // Sanitize value to prevent XSS if it's not already handled (basic example)
        const sanitizedValue = String(value).replace(/</g, "<").replace(/>/g, ">");
        return `<strong>${label}:</strong> ${sanitizedValue}<br>`;
    }

    // Format request data for display
    function formatRequestData(request) {
        let html = '';

        // Basic Info (already handled by other parts of updateRequestDetailsModal, but good for completeness if used standalone)
        let name = request.requester_first_name && request.requester_last_name
            ? `${request.requester_first_name} ${request.requester_last_name}`
            : (request.first_name && request.last_name ? `${request.first_name} ${request.last_name}` : (request.user?.name || 'N/A'));
        html += createFieldHtml('Requester Name', name);
        if (request.user?.email) {
            html += createFieldHtml('Requester Email', request.user.email);
        } else if (request.email) { // Fallback if email is directly on request object
            html += createFieldHtml('Requester Email', request.email);
        }


        if (request.request_type === 'student' && request.student_id) {
            html += createFieldHtml('Student ID', request.student_id);
        } else if (request.request_type === 'faculty' && (request.faculty_id || request.user?.faculty_id)) {
            html += createFieldHtml('Faculty ID', request.faculty_id || request.user?.faculty_id);
        }

        html += createFieldHtml('Service Category', formatServiceCategory(request.service_category));

        // Common fields
        html += createFieldHtml('Description', request.description);
        html += createFieldHtml('Additional Notes', request.additional_notes);
        html += createFieldHtml('Preferred Date', request.preferred_date ? formatDateTime(request.preferred_date, false) : 'N/A');
        html += createFieldHtml('Preferred Time', request.preferred_time ? formatDateTime('1970-01-01T' + request.preferred_time, true, true) : 'N/A'); // Assuming time is just HH:MM:SS

        if (request.supporting_document) {
            // Assuming supporting_document is a URL or path that can be linked
            // For security, ensure this URL is safe or handle downloads appropriately server-side
            const docName = request.supporting_document.split('/').pop();
            html += `<strong>Supporting Document:</strong> <a href="/admin/view-supporting-document/${request.id}?type=${request.request_type}" target="_blank">${docName || 'View Document'}</a><br>`;
        }

        // Service-specific fields based on service_category
        switch (request.service_category) {
            case 'create':
            case 'reset_email_password':
            case 'change_of_data_ms':
            case 'reset_tup_web_password':
            case 'reset_ers_password':
            case 'change_of_data_portal':
                html += createFieldHtml('Account Email', request.account_email);
                if (request.service_category === 'change_of_data_ms' || request.service_category === 'change_of_data_portal') {
                    html += createFieldHtml('Data Type to Change', request.data_type);
                    html += createFieldHtml('New Data', request.new_data);
                }
                break;
            case 'dtr':
            case 'biometric_record':
                html += createFieldHtml('DTR/Biometric Months', Array.isArray(request.dtr_months) ? request.dtr_months.join(', ') : request.dtr_months);
                html += createFieldHtml('DTR with Details', request.dtr_with_details);
                html += createFieldHtml('Specific Months', Array.isArray(request.months) ? request.months.join(', ') : request.months);
                html += createFieldHtml('Year', request.year);
                break;
            case 'biometrics_enrollement': // Corrected spelling if it's 'enrollment' in DB
                html += createFieldHtml('Middle Name', request.middle_name);
                html += createFieldHtml('College', request.college);
                html += createFieldHtml('Department', request.department);
                html += createFieldHtml('Plantilla Position', request.plantilla_position);
                html += createFieldHtml('Date of Birth', request.date_of_birth ? formatDateTime(request.date_of_birth, false) : 'N/A');
                html += createFieldHtml('Phone Number', request.phone_number);
                html += createFieldHtml('Address', request.address);
                html += createFieldHtml('Blood Type', request.blood_type);
                html += createFieldHtml('Emergency Contact Person', request.emergency_contact_person);
                html += createFieldHtml('Emergency Contact Number', request.emergency_contact_number);
                break;
            case 'new_internet':
            case 'new_telephone':
            case 'repair_and_maintenance': // Internet/Telephone Repair
            case 'computer_repair_maintenance':
            case 'printer_repair_maintenance':
                html += createFieldHtml('Location/Office', request.location);
                if (request.service_category === 'repair_and_maintenance' ||
                    request.service_category === 'computer_repair_maintenance' ||
                    request.service_category === 'printer_repair_maintenance') {
                    html += createFieldHtml('Problem Encountered', request.problem_encountered);
                    // repair_maintenance might be a sub-category or type of repair
                    html += createFieldHtml('Repair/Maintenance Details', request.repair_maintenance);
                }
                break;
            case 'request_led_screen':
                html += createFieldHtml('LED Screen Details/Purpose', request.led_screen_details);
                break;
            case 'install_application':
                html += createFieldHtml('Application Name', request.application_name);
                html += createFieldHtml('Purpose of Installation', request.installation_purpose);
                html += createFieldHtml('Installation Notes', request.installation_notes);
                break;
            case 'post_publication':
                html += createFieldHtml('Author/Department', request.publication_author);
                html += createFieldHtml('Editor/Contact Person', request.publication_editor);
                html += createFieldHtml('Publication Start Date', request.publication_start_date ? formatDateTime(request.publication_start_date, false) : 'N/A');
                html += createFieldHtml('Publication End Date', request.publication_end_date ? formatDateTime(request.publication_end_date, false) : 'N/A');
                html += createFieldHtml('Publication Details', request.publication_details);
                if (request.publication_image_path) {
                    html += `<strong>Publication Image:</strong> <a href="/storage/${request.publication_image_path}" target="_blank">View Image</a><br>`;
                }
                if (request.publication_file_path) {
                    html += `<strong>Publication File:</strong> <a href="/storage/${request.publication_file_path}" target="_blank">View File</a><br>`;
                }
                break;
            case 'data_docs_reports':
                html += createFieldHtml('Details of Data/Documents/Reports', request.data_documents_details);
                break;
            case 'others':
                // For 'others', the main description is the service detail.
                // html += createFieldHtml('Service Details', request.description); // Already covered by common 'Description'
                break;
        }

        // Fallback for any other fields not explicitly handled (optional)
        // You might want to list other non-empty fields that are not part of a standard exclusion list.
        // const excludedFields = ['id', 'user_id', 'created_at', 'updated_at', 'deleted_at', 'status', 
        //                         'assigned_uitc_staff_id', 'admin_notes', 'transaction_type', 
        //                         'actions_taken', 'completion_report', 'user', 'assignedUITCStaff', 
        //                         'requester_first_name', 'requester_last_name', 'user_role', 'requester_email',
        //                         'request_type', 'service_category', 'first_name', 'last_name', 'student_id', 'faculty_id',
        //                         'description', 'additional_notes', 'preferred_date', 'preferred_time', 'supporting_document',
        //                         // Add all handled fields from the switch case above
        //                         'account_email', 'data_type', 'new_data', 'dtr_months', 'dtr_with_details', 'months', 'year',
        //                         'middle_name', 'college', 'department', 'plantilla_position', 'date_of_birth', 'phone_number', 'address', 'blood_type', 'emergency_contact_person', 'emergency_contact_number',
        //                         'location', 'problem_encountered', 'repair_maintenance', 'led_screen_details', 'application_name', 'installation_purpose', 'installation_notes',
        //                         'publication_author', 'publication_editor', 'publication_start_date', 'publication_end_date', 'publication_details', 'publication_image_path', 'publication_file_path',
        //                         'data_documents_details'
        //                         ];
        // for (const key in request) {
        //     if (request.hasOwnProperty(key) && !excludedFields.includes(key) && request[key] !== null && request[key] !== '') {
        //         html += createFieldHtml(key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()), request[key]);
        //     }
        // }

        return html || 'No specific details provided for this service category.';
    }

    // Format service category
    function formatServiceCategory(category) {
        if (!category) return 'N/A';

        const services = {
            'create': 'Create MS Office/TUP Email Account',
            'reset_email_password': 'Reset MS Office/TUP Email Password',
            'change_of_data_ms': 'Change of Data (MS Office)',
            'reset_tup_web_password': 'Reset TUP Web Password',
            'reset_ers_password': 'Reset ERS Password',
            'change_of_data_portal': 'Change of Data (Portal)',
            'dtr': 'Daily Time Record',
            'biometric_record': 'Biometric Record',
            'biometrics_enrollement': 'Biometrics Enrollment',
            'new_internet': 'New Internet Connection',
            'new_telephone': 'New Telephone Connection',
            'repair_and_maintenance': 'Internet/Telephone Repair and Maintenance',
            'computer_repair_maintenance': 'Computer Repair and Maintenance',
            'printer_repair_maintenance': 'Printer Repair and Maintenance',
            'request_led_screen': 'LED Screen Request',
            'install_application': 'Install Application/Information System/Software',
            'post_publication': 'Post Publication/Update of Information Website',
            'data_docs_reports': 'Data, Documents and Reports',
            'others': 'Other Service'
        };

        return services[category] || category;
    }

    // COMPLETE REQUEST FUNCTIONALITY

    // Handle Complete button click
    $('.btn-complete').on('click', function () {
        const requestId = $(this).data('request-id');
        const requestType = $(this).data('request-type') || 'student';

        $('#completeRequestId').val(requestId);
        $('#completeRequestType').val(requestType);

        // Reset form validation
        $('#completeRequestForm').removeClass('was-validated');
        $('#completionReport').val('');
        $('#actionsTaken').val('');

        // Show modal
        $('#completeRequestModal').modal('show');
    });

    // Set pre-selected values for filters based on URL parameters
    function setFiltersFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);

        console.log("URL Parameters:", Object.fromEntries(urlParams.entries()));

        // Set status dropdown
        if (urlParams.has('status')) {
            $('#status').val(urlParams.get('status'));
            console.log("Set status to:", urlParams.get('status'));
        }

        // Set transaction type dropdown
        if (urlParams.has('transaction_type')) {
            $('#transaction_type').val(urlParams.get('transaction_type'));
            console.log("Set transaction_type to:", urlParams.get('transaction_type'));
        }

        // Set search input
        if (urlParams.has('search')) {
            $('#search-input').val(urlParams.get('search')); // Corrected ID
            console.log("Set search to:", urlParams.get('search'));
        }
    }

    // Initialize filters from URL parameters
    setFiltersFromUrl();

    // UNRESOLVABLE REQUEST FUNCTIONALITY

    // Handle Unresolvable button click
    $(document).on('click', '.btn-unresolvable', function () {
        const requestId = $(this).data('request-id');
        const requestType = $(this).data('request-type') || 'student'; // Default to student if not specified

        $('#unresolvableRequestId').val(requestId);
        $('#unresolvableRequestType').val(requestType);

        // Reset form validation and clear fields
        $('#unresolvableRequestForm').removeClass('was-validated');
        $('#unresolvableReason').val('');
        $('#unresolvableActionsTaken').val('');

        // Show modal
        $('#unresolvableRequestModal').modal('show');
    });

    // Handle form submission for marking requests as unresolvable
    $('#unresolvableRequestForm').on('submit', function (e) {
        e.preventDefault();

        // Validate form
        if (this.checkValidity() === false) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        // Disable submit button
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');

        // Get form data
        const formData = $(this).serialize();

        // AJAX call to mark the request as unresolvable
        $.ajax({
            url: '/uitc-staff/requests/mark-unresolvable',
            method: 'POST',
            data: formData,
            success: function (response) {
                // Close the modal
                $('#unresolvableRequestModal').modal('hide');

                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message || 'Request marked as unresolvable successfully.',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    // Refresh the page to show updated data
                    window.location.reload();
                });
            },
            error: function (xhr) {
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to mark the request as unresolvable.'
                });

                // Re-enable submit button
                submitBtn.prop('disabled', false).text('Mark as Unresolvable');
            }
        });
    });
});
