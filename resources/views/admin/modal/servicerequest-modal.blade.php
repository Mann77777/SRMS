
    <!-- Assign UITC Staff Modal -->
    <div class="modal fade" id="assignStaffModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign UITC Staff</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="assignStaffForm">
                        @csrf
                        <input type="hidden" id="requestIdInput" name="request_id">
                        <input type="hidden" id="requestTypeInput" name="request_type">
                        
                        <div class="form-group">
                            <label>Request Summary</label>
                            <div class="request-summary">
                                <p><strong>Request ID:</strong> <span id="modalRequestId"></span></p>
                                <div id="modalRequestServices"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Select UITC Staff</label>
                            <select class="form-control" name="uitcstaff_id" required>
                                <option value="">Choose UITC Staff</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Transaction Type</label>
                            <select class="form-control" name="transaction_type" required>
                                <option value="">Select Transaction Type</option>
                                <option value="Simple Transaction">Simple Transaction</option>
                                <option value="Complex Transaction">Complex Transaction</option>
                                <option value="Highly Technical Transaction">Highly Technical Transaction</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Notes (Optional)</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes for the UITC Staff"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveAssignStaffBtn">Assign UITC Staff</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Service Request Modal -->
    <div class="modal fade" id="rejectServiceRequestModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Service Request</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="rejectServiceRequestForm">
                        <input type="hidden" name="request_id">
                        <input type="hidden" name="request_type">
                        
                        <div class="form-group">
                            <label>Request Summary</label>
                            <div class="request-summary">
                                <p><strong>Request ID:</strong> <span id="modalRejectRequestId"></span></p>
                                <div id="modalRejectRequestServices"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Reason for Rejection <span class="text-danger">*</span></label>
                            <select class="form-control" id="rejectionReason" name="rejection_reason" required>
                                <option value="">Select Rejection Reason</option>
                                <option value="incomplete_information">Incomplete Information</option>
                                <option value="out_of_scope">Service Out of Scope</option>
                                <option value="resource_unavailable">Resources Unavailable</option>
                                <option value="duplicate_request">Duplicate Request</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Additional Notes</label>
                            <textarea class="form-control" id="rejectionNotes" name="notes" rows="4" placeholder="Provide additional details about the rejection (optional)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRejectBtn">Confirm Rejection</button>
                </div>
            </div>
        </div>
    </div>

        <!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="request-details-container">
                    <div class="request-info">
                        <p><strong>Request ID:</strong> <span id="detailsRequestId"></span></p>
                        <p><strong>Status:</strong> <span id="detailsRequestStatus" class="badge"></span></p>
                        <p><strong>Role:</strong> <span id="detailsRequestRole"></span></p>
                        <p><strong>Submitted:</strong> <span id="detailsRequestDate"></span></p>
                        <p><strong>Completed:</strong> <span id="detailsRequestCompleted"></span></p>
                    </div>
                    <hr>
                    <div class="request-details">
                        <h6>Request Information</h6>
                        <div id="detailsRequestData"></div>
                    </div>
                    
                    <div id="assignmentInfoSection" style="display: none;">
                        <hr>
                        <div class="assignment-info">
                            <h6>Assignment Information</h6>
                            <p><strong>Assigned To:</strong> <span id="detailsAssignedTo">Not assigned yet</span></p>
                            <p><strong>Transaction Type:</strong> <span id="detailsTransactionType">-</span></p>
                            <p><strong>Admin Notes:</strong> <span id="detailsAdminNotes">-</span></p>
                        </div>
                    </div>
                    
                    <div id="rejectionInfoSection" style="display: none;">
                        <hr>
                        <div class="rejection-info">
                            <h6>Rejection Information</h6>
                            <p><strong>Rejection Reason:</strong> <span id="detailsRejectionReason">-</span></p>
                            <p><strong>Rejection Notes:</strong> <span id="detailsRejectionNotes">-</span></p>
                            <p><strong>Rejected On:</strong> <span id="detailsRejectedDate">-</span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                
                <!-- Action buttons based on status -->
                <div id="pendingActionsContainer" style="display: none;">
                    <button type="button" class="btn btn-success modal-approve-btn">Approve</button>
                    <button type="button" class="btn btn-danger modal-reject-btn">Reject</button>
                </div>
            </div>
        </div>
    </div>