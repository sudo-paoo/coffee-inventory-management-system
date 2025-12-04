<!-- Toggle User Status Modal -->
<div id="toggleStatusModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <div>
                <h2>Confirm Action</h2>
            </div>
            <button class="modal-close" onclick="closeToggleStatusModal()">&times;</button>
        </div>
        
        <form method="POST" action="<?php echo base_url('user_actions.php'); ?>">
            <input type="hidden" name="action" value="toggle-status">
            <input type="hidden" name="id" id="toggle-user-id">
            <input type="hidden" name="is_active" id="toggle-new-status">
            
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <i class="fa-solid fa-circle-exclamation" style="font-size: 3rem; color: #f59e0b; margin-bottom: 15px;"></i>
                    <p style="font-size: var(--text-lg); margin: 0;">
                        Are you sure you want to <strong id="toggle-action"></strong> the account for:
                    </p>
                    <p style="font-size: var(--text-xl); font-weight: 600; margin: 10px 0 0 0; color: var(--primary);" id="toggle-user-name"></p>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeToggleStatusModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i> Confirm
                </button>
            </div>
        </form>
    </div>
</div>
