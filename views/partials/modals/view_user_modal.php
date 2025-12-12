<!-- View User Modal -->
<div id="viewUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2>User Details</h2>
                <p class="modal-subtitle">View user information</p>
            </div>
            <button class="modal-close" onclick="closeViewModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="view-details">
                <div class="detail-group">
                    <label>Full Name</label>
                    <p id="view-user-name"></p>
                </div>
                
                <div class="detail-group">
                    <label>Email Address</label>
                    <p id="view-user-email"></p>
                </div>
                
                <div class="detail-group">
                    <label>Contact Number</label>
                    <p id="view-user-contact"></p>
                </div>
                
                <div class="detail-row">
                    <div class="detail-group">
                        <label>Role</label>
                        <p><span id="view-user-role" class="status-badge"></span></p>
                    </div>
                    
                    <div class="detail-group">
                        <label>Account Status</label>
                        <p><span id="view-user-status" class="status-badge"></span></p>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-group">
                        <label>Created At</label>
                        <p id="view-user-created"></p>
                    </div>
                    
                    <div class="detail-group">
                        <label>Last Updated</label>
                        <p id="view-user-updated"></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>
