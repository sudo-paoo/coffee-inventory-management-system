<!-- Delete Confirmation Modal -->
<div id="deleteItemModal" class="modal">
    <div class="modal-content modal-content-small">
        <div class="modal-header">
            <div>
                <h2>Delete Item</h2>
                <p class="modal-subtitle">This action cannot be undone</p>
            </div>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        
        <form method="POST" action="<?php echo base_url('inventory_actions.php'); ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="delete-item-id">
            
            <div class="modal-body">
                <div class="delete-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <p>Are you sure you want to delete <strong id="delete-item-name"></strong>?</p>
                        <p style="margin-top: 8px; font-size: 0.875rem;">This will permanently remove the item from your inventory.</p>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-delete btn-confirm-delete">
                    <i class="fa-solid fa-trash"></i> Delete Item
                </button>
            </div>
        </form>
    </div>
</div>
