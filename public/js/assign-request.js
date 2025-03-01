document.addEventListener('DOMContentLoaded', function() {
    // Function to complete a request
    function completeRequest(requestId) {
        // Disable the complete button to prevent multiple submissions
        const completeButton = document.querySelector(`.btn-complete[data-request-id="${requestId}"]`);
        completeButton.disabled = true;
        completeButton.textContent = 'Completing...';

        // AJAX request to complete the request
        fetch('/uitc-staff/complete-request', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                request_id: requestId,
                actions_taken: 'Request completed by UITC Staff',
                completion_report: 'Standard request completion',
                completion_status: 'fully_completed'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Request completed successfully') {
                // Remove the row or update its status
                const row = completeButton.closest('tr');
                row.classList.add('completed-request');
                
                // Update status badge
                const statusCell = row.querySelector('td:nth-child(5)');
                statusCell.innerHTML = `
                    <span class="badge badge-success">Completed</span>
                `;

                // Hide action buttons
                const actionCell = row.querySelector('td:last-child');
                actionCell.innerHTML = '<span class="text-muted">Completed</span>';

                // Optional: Refresh the table or remove the row
                // row.remove();
            } else {
                // Handle error
                alert('Failed to complete request: ' + data.message);
                completeButton.disabled = false;
                completeButton.textContent = 'Complete';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while completing the request');
            completeButton.disabled = false;
            completeButton.textContent = 'Complete';
        });
    }

    // Attach event listeners to complete buttons
    document.querySelectorAll('.btn-complete').forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-request-id');
            completeRequest(requestId);
        });
    });
});