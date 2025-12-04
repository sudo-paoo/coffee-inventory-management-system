<!-- Mark Received Modal -->
<div id="markReceivedModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <div>
                <h2>Mark Order as Received</h2>
                <p class="modal-subtitle">Confirm that this order has been received</p>
            </div>
            <button class="modal-close" onclick="closeMarkReceivedModal()">&times;</button>
        </div>
        
        <form id="markReceivedForm" method="POST" action="<?php echo base_url('order_actions.php'); ?>">
            <input type="hidden" name="action" value="receive">
            <input type="hidden" name="order_id" id="receive-order-id">
            
            <div class="modal-body">
                <div class="receive-confirmation-message">
                    <div class="text-center" style="margin-bottom: 20px;">
                        <i class="fa-solid fa-circle-check" style="font-size: 3rem; color: var(--primary); margin-bottom: 15px; display: block;"></i>
                        <h3 style="margin: 0 0 5px 0; font-size: var(--text-xl);">Order: <span id="receive-order-number" style="color: var(--primary);"></span></h3>
                        <p id="receive-supplier-name" style="margin: 0; color: #6b7280; font-size: var(--text-sm);"></p>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="order-items-section">
                        <h4 style="margin: 0 0 10px 0; font-size: var(--text-base); color: var(--foreground);">Order Items:</h4>
                        <div class="order-items-list" id="receive-order-items">
                            <!-- Items will be populated by JavaScript -->
                        </div>
                        <div class="order-total" id="receive-order-total" style="text-align: right; margin-top: 10px; padding-top: 10px; border-top: 2px solid var(--border); font-weight: 700; font-size: var(--text-lg);">
                            <!-- Total will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="warning-box">
                        <p style="margin: 0 0 10px 0; color: #92400e; font-weight: 600;">
                            <i class="fa-solid fa-triangle-exclamation"></i> This action will:
                        </p>
                        <ul style="text-align: left; margin: 0 auto; padding-left: 25px; max-width: 450px; color: #78350f;">
                            <li>Update inventory stock quantities for all items</li>
                            <li>Create stock-in transaction records</li>
                            <li>Mark this order as received</li>
                        </ul>
                    </div>
                </div>
                
                <div class="form-group-full" style="margin-top: 20px;">
                    <label for="receive-notes">Additional Notes (Optional)</label>
                    <textarea id="receive-notes" name="notes" class="form-input" rows="3" placeholder="Add any notes about the received order..."></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeMarkReceivedModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="fa-solid fa-check"></i> Confirm Receipt
                </button>
            </div>
        </form>

