<div class="modal" id="verifyFacultyStaffModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Verify Faculty & Staff Account</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="facultystaff-details">
                    <p><strong>Name:</strong> <span id="facultystaff-name"></span></p>
                    <p><strong>Email:</strong> <span id="facultystaff-email"></span></p>
                    <p><strong>Username:</strong> <span id="facultystaff-username"></span></p>
                    <p><strong>Verification Status:</strong> <span id="facultystaff-verification-status"></span></p>
                </div>
                <div class="verification-action">
                    <label>Verification Decision:</label>
                    <select id="verification-decision-faculty">
                        <option value="approve">Approve</option>
                        <option value="reject">Reject</option>
                    </select>
                    <div id="rejection-notes-faculty" style="display: none;">
                        <label>Rejection Notes:</label>
                        <textarea id="admin-notes-faculty" rows="3"></textarea>
                    </div>
            </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submit-facultystaff-verification">Submit</button>
            </div>
        </div>
    </div>
</div>