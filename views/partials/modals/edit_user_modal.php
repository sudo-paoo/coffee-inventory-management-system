<!-- Add/Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2 id="modalTitle">Add New User</h2>
                <p class="modal-subtitle">Fill in the user information</p>
            </div>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        
        <form id="userForm" method="POST" action="<?php echo base_url('user_actions.php'); ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="user-id">
            
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="user-first-name">First Name *</label>
                        <input type="text" id="user-first-name" name="first_name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="user-last-name">Last Name *</label>
                        <input type="text" id="user-last-name" name="last_name" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-group-full">
                    <label for="user-email">Email Address *</label>
                    <input type="email" id="user-email" name="email" class="form-input" required>
                </div>
                
                <div class="form-group-full">
                    <label for="user-contact">Contact Number</label>
                    <input type="tel" id="user-contact" name="contact_number" class="form-input" placeholder="e.g., 09123456789">
                </div>
                
                <div class="form-group-full">
                    <label for="user-role">Role *</label>
                    <select id="user-role" name="role" class="form-select" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                
                <div class="form-group-full" id="password-group">
                    <label for="user-password"><span id="password-label">Password *</span></label>
                    <input type="password" id="user-password" name="password" class="form-input" minlength="6">
                    <small id="password-hint" style="color: #6b7280; margin-top: 4px; display: block;">Minimum 6 characters</small>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="fa-solid fa-save"></i> Save User
                </button>
            </div>
        </form>
    </div>
</div>
