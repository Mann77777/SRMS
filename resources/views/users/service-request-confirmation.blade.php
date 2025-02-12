<div class="modal fade show" id="serviceRequestSuccessModal" tabindex="-1" role="dialog" style="display: block; background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Request Submitted Successfully</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="redirectToMyRequests()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Your service request for <strong>{{ $serviceCategory }}</strong> has been submitted successfully.</p>
                <p>Request ID: <strong>{{ $requestId }}</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="redirectToMyRequests()">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function redirectToMyRequests() {
    window.location.href = "{{ route('myrequests') }}";
}

// Ensure modal is shown
$(document).ready(function() {
    $('#serviceRequestSuccessModal').modal('show');
});
</script>