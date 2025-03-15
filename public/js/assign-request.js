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