document.addEventListener('DOMContentLoaded', function() {
    // Function to apply filters
    function applyFilters() {
        const status = document.getElementById('status').value;
        const transactionType = document.getElementById('transaction_type').value;
        const searchTerm = document.getElementById('user-search').value;
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
    const searchInput = document.getElementById('user-search');
    
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    if (transactionFilter) transactionFilter.addEventListener('change', applyFilters);
    if (roleFilter) roleFilter.addEventListener('change', applyFilters);
    
    if (searchInput) {
        // Debounce search to avoid too many requests
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 500);
        });
        
        // Also handle Enter key
        searchInput.addEventListener('keydown', function(e) {
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
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-request-id');
            completeRequest(requestId);
        });
    });

    // Form submission handler
    const completeRequestForm = document.getElementById('completeRequestForm');
    if (completeRequestForm) {
        completeRequestForm.addEventListener('submit', function(e) {
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
            submitButton.textContent = 'Completing...';

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

$(document).ready(function() {
    // Set CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Click handler for request IDs
    $(document).on('click', '.clickable-request-id', function() {
        console.log("Request ID clicked");
        const row = $(this).closest('tr');
        const requestId = $(this).text().trim();
        
        // Extract data from the current row
        const statusCell = row.find('td:eq(5)');
        const statusText = statusCell.find('.custom-badge').text().trim();
        
        // Get completed date from the completed date cell
        let completedDate = null;
        const completedDateCell = row.find('td:eq(4)');
        
        if (statusText === 'Completed' && !completedDateCell.text().trim().includes('–')) {
            // Try to combine date and time from spans
            const dateSpan = completedDateCell.find('span:first').text().trim();
            const timeSpan = completedDateCell.find('span:last').text().trim();
            
            if (dateSpan && timeSpan) {
                completedDate = dateSpan + ' ' + timeSpan;
            }
        }
        
        // Get request details from the second column
        const requestDetails = row.find('td:eq(1)').html();
        
        // Get request type if available from the complete button
        const completeButton = row.find('.btn-complete');
        const requestType = completeButton.length ? completeButton.data('request-type') : '';
        
        // Build the request data object
        const requestData = {
            id: requestId,
            role: row.find('td:eq(2)').text().trim(),
            request_data: requestDetails,
            date: row.find('td:eq(3) span:first').text().trim() + ' ' + 
                row.find('td:eq(3) span:last').text().trim(),
            status: statusText,
            updated_at: completedDate,
            type: requestType
        };
        
        console.log('Request data for modal:', requestData);
        
        // Update modal with request data
        updateRequestDetailsModal(requestData);
        
        // Show the modal
        $('#requestDetailsModal').modal('show');
    });

    // Function to update the modal with request information
    function updateRequestDetailsModal(requestData) {
        console.log("Updating modal with data:", requestData);
        
        // Set basic request information
        $('#detailsRequestId').text(requestData.id);
        $('#detailsRequestRole').text(requestData.role);
        $('#detailsRequestDate').text(requestData.date);
        
        // Format the data in request_data field
        $('#detailsRequestData').html(requestData.request_data);
        
        // Set status with appropriate color
        const $statusBadge = $('#detailsRequestStatus');
        // Trim and normalize the status text
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
        if (requestData.status === 'Completed' && requestData.updated_at) {
            $('#detailsRequestCompleted').text(requestData.updated_at);
        } else {
            $('#detailsRequestCompleted').text('-');
        }
        
        // Hide sections that we don't need for this view
        // We're in UITC Staff view, so hide admin-specific actions
        if ($('#pendingActionsContainer').length) {
            $('#pendingActionsContainer').hide();
        }
        
        // Hide assignment info section as we don't need it in the UITC staff view
        if ($('#assignmentInfoSection').length) {
            $('#assignmentInfoSection').hide();
        }
        
        // Show rejection info if the request was rejected
        if ($('#rejectionInfoSection').length) {
            if (requestData.status === 'Rejected') {
                $('#rejectionInfoSection').show();
                $('#detailsRejectionReason').text(requestData.rejection_reason || '-');
                $('#detailsRejectionNotes').text(requestData.notes || 'No notes');
                $('#detailsRejectedDate').text(requestData.updated_at || '-');
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
    
    // Handle Complete button click event
    $('.btn-complete').on('click', function() {
        const requestId = $(this).data('request-id');
        const requestType = $(this).data('request-type') || 'student';
        
        $('#completeRequestId').val(requestId);
        $('#completeRequestType').val(requestType);
        $('#completeRequestModal').modal('show');
    });
    
    // Handle form submission for completing requests
    $('#completeRequestForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (this.checkValidity() === false) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        // Get form data
        const formData = $(this).serialize();

        // AJAX call to complete the request
        $.ajax({
            url: '/uitc-staff/complete-request',
            method: 'POST',
            data: formData,
            success: function(response) {
                // Close the complete request modal
                $('#completeRequestModal').modal('hide');
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Request completed successfully',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    // Refresh the page to show updated data
                    window.location.reload();
                });
            },
            error: function(xhr) {
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to complete the request.'
                });
            }
        });
    });
    
    // Function for filters
    function applyFilters() {
        const status = $('#status').val();
        const transactionType = $('#transaction_type').val();
        const searchTerm = $('#user-search').val();
        
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
        
        // Redirect with filters
        let url = window.location.pathname;
        if (queryParams.length > 0) {
            url += '?' + queryParams.join('&');
        }
        
        window.location.href = url;
    }
    
    // Add event listeners to filters
    $('#status').on('change', applyFilters);
    $('#transaction_type').on('change', applyFilters);
    
    // Debounce search to avoid too many requests
    let searchTimeout;
    $('#user-search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });
    
    // Also handle Enter key in search
    $('#user-search').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyFilters();
        }
    });
});