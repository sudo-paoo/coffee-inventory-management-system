<!-- Add/Edit Item Modal -->
<div id="editItemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2 id="modalTitle">Add New Item</h2>
                <p class="modal-subtitle">Fill in the item information</p>
            </div>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        
        <form id="itemForm" method="POST" action="<?php echo base_url('inventory_actions.php'); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save" id="form-action">
            <input type="hidden" name="id" id="item-id">
            <input type="hidden" name="existing_image" id="existing-image">
            
            <div class="modal-body">
                <div class="form-group-full">
                    <label for="item-image">Item Image</label>
                    <div class="image-upload-container">
                        <div class="image-preview" id="image-preview">
                            <img id="preview-img" src="" alt="Preview" style="display: none;">
                            <div id="preview-placeholder" class="preview-placeholder">
                                <i class="fa-solid fa-image"></i>
                                <p>No image selected</p>
                            </div>
                        </div>
                        <div class="image-upload-controls">
                            <input type="file" id="item-image" name="image" class="form-input-file" accept="image/jpeg,image/jpg,image/png,image/gif">
                            <button type="button" class="btn btn-secondary btn-choose-file" onclick="document.getElementById('item-image').click()">
                                <i class="fa-solid fa-upload"></i> Choose Image
                            </button>
                            <button type="button" class="btn btn-secondary btn-remove-image" onclick="removeImage()" style="display: none;">
                                <i class="fa-solid fa-times"></i> Remove
                            </button>
                            <small class="form-help">Supported: JPG, PNG, GIF (Max 2MB)</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="item-name">Item Name *</label>
                        <input type="text" id="item-name" name="name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="item-category">Category *</label>
                        <select id="item-category" name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($all_categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo escape($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="item-supplier">Supplier *</label>
                        <select id="item-supplier" name="supplier_id" class="form-select" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($all_suppliers as $sup): ?>
                            <option value="<?php echo $sup['id']; ?>"><?php echo escape($sup['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="item-unit">Unit *</label>
                        <select id="item-unit" name="unit" class="form-select" required>
                            <option value="">Select Unit</option>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="g">Gram (g)</option>
                            <option value="L">Liter (L)</option>
                            <option value="mL">Milliliter (mL)</option>
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="pack">Pack</option>
                            <option value="box">Box</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="item-stock">Stock Quantity *</label>
                        <input type="number" id="item-stock" name="stock_quantity" class="form-input" min="0" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="item-reorder">Reorder Level *</label>
                        <input type="number" id="item-reorder" name="reorder_level" class="form-input" min="0" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="item-cost">Cost (₱) *</label>
                        <input type="number" id="item-cost" name="cost" class="form-input" min="0" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="item-price">Selling Price (₱) *</label>
                        <input type="number" id="item-price" name="price" class="form-input" min="0" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-group-full">
                    <label for="item-expiration">Expiration Date</label>
                    <input type="date" id="item-expiration" name="expiration_date" class="form-input">
                </div>
                
                <div class="form-group-full">
                    <label for="item-description">Notes</label>
                    <textarea id="item-description" name="notes" class="form-input" rows="3" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="fa-solid fa-save"></i> Save Item
                </button>
            </div>
        </form>
    </div>
</div>
