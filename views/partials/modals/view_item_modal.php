<!-- View Item Modal -->
<div id="viewItemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2>Item Details</h2>
                <p class="modal-subtitle">View complete item information</p>
            </div>
            <button class="modal-close" onclick="closeViewModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="view-image-container" id="view-image-container" style="display: none;">
                <img id="view-item-image" src="" alt="Item Image" class="view-item-image" />
            </div>
            
            <div class="view-details-grid">
                <div class="view-detail-item">
                    <label>Item Name</label>
                    <p id="view-item-name"></p>
                </div>
                
                <div class="view-detail-item">
                    <label>Category</label>
                    <p id="view-category"></p>
                </div>
                
                <div class="view-detail-item">
                    <label>Supplier</label>
                    <p id="view-supplier"></p>
                </div>
                
                <div class="view-detail-item">
                    <label>Stock Quantity</label>
                    <p id="view-stock"></p>
                </div>
                
                <div class="view-detail-item">
                    <label>Reorder Level</label>
                    <p id="view-reorder"></p>
                </div>
                
                <div class="view-detail-item">
                    <label>Cost</label>
                    <p id="view-cost"></p>
                </div>
                
                <div class="view-detail-item">
                    <label>Selling Price</label>
                    <p id="view-price"></p>
                </div>
                
                <div class="view-detail-item">
                    <label>Status</label>
                    <p><span id="view-status" class="status-badge"></span></p>
                </div>
                
                <div class="view-detail-item">
                    <label>Expiration Date</label>
                    <p id="view-expiration"></p>
                </div>
                
                <div class="view-detail-item view-detail-full">
                    <label>Description</label>
                    <p id="view-description"></p>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>
