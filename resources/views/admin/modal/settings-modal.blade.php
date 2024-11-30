
    <!-- Combined Error Modal -->
    <div class="modal" id="combinedErrorModal" tabindex="-1" role="dialog" aria-labelledby="combinedErrorModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="combinedErrorModalLabel">Error</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Check for username and password validation errors -->
                    @if($errors->has('username') && $errors->has('password'))
                        <p>The username has already been taken. Please choose another one.</p>
                        <p>The password must be at least 8 characters long.</p>
                    @elseif($errors->has('username'))
                        <p>The username has already been taken. Please choose another one.</p>
                    @elseif($errors->has('password'))
                        <p>The password must be at least 8 characters long.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Success</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    New administrator has been added successfully!
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
