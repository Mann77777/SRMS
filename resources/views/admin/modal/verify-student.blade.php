<div class="modal" id="verifyStudentModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Verify Student Account</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="student-details">
                    <p><strong>Name:</strong> <span id="student-name"></span></p>
                    <p><strong>Email:</strong> <span id="student-email"></span></p>
                    <p><strong>Student ID:</strong> <span id="student-id"></span></p>
                    <p><strong>College:</strong> <span id="student-college"></span></p>
                    <p><strong>Course:</strong> <span id="student-course"></span></p>
                    <p><strong>Year Level:</strong> <span id="student-year"></span></p>
                    <p><strong>Verification Status:</strong> <span id="student-verification-status"></span></p>
                </div>
                <div class="verification-action">
                    <label>Verification Decision:</label>
                    <select id="verification-decision">
                        <option value="approve">Approve</option>
                        <option value="reject">Reject</option>
                    </select>
                    <div id="rejection-notes" style="display: none;">
                        <label>Rejection Notes:</label>
                        <textarea id="admin-notes" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submit-verification">Submit</button>
            </div>
        </div>
    </div>
</div>