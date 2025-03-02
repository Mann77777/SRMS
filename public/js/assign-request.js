document.addEventListener('DOMContentLoaded', function() {
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

    // Attach event listeners to complete buttons
    document.querySelectorAll('.btn-complete').forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-request-id');
            completeRequest(requestId);
        });
    });

    // Form submission handler
    document.getElementById('completeRequestForm')?.addEventListener('submit', function(e) {
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

        // AJAX request to complete the request
        fetch('/uitc-staff/complete-request', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Request completed successfully') {
                // Remove the row or update its status
                const row = document.querySelector(`.btn-complete[data-request-id="${requestId}"]`).closest('tr');
                row.classList.add('completed-request');
                
                // Update status badge
                const statusCell = row.querySelector('td:nth-child(5)');
                statusCell.innerHTML = `
                    <span class="badge badge-success">Completed</span>
                `;

                // Hide action buttons
                const actionCell = row.querySelector('td:last-child');
                actionCell.innerHTML = '<span class="text-muted">Completed</span>';

                // Close the modal
                $('#completeRequestModal').modal('hide');
                $('#requestCompletedSuccessModal').modal('show');
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
            submitButton.textContent = 'Complete Request';
        });
    });
});