<!-- Batch Stock Out Modal -->
<div id="batchStockOutModal" class="modal">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <div>
                <h2>Stock Out</h2>
                <p class="modal-subtitle">Select and process multiple items in one transaction</p>
            </div>
            <button class="modal-close" onclick="closeBatchStockOutModal()">&times;</button>
        </div>
        
        <form method="POST" action="<?php echo base_url('stock_transaction_actions.php'); ?>" id="batchStockOutForm">
            <input type="hidden" name="action" value="batch-stock-out">
            
            <div class="modal-body">
                <div class="form-group-full">
                    <label for="batch-transaction-type">Transaction Type *</label>
                    <select name="transaction_type" id="batch-transaction-type" class="form-select" required>
                        <option value="stock-out">Stock Out</option>
                        <option value="damaged">Damaged</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                
                <div class="form-group-full">
                    <label for="batch-note">General Notes</label>
                    <textarea 
                        name="general_note" 
                        id="batch-note" 
                        class="form-input" 
                        rows="2"
                        placeholder="Add general notes for this batch transaction..."
                    ></textarea>
                </div>
                
                <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border);">
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin: 0; font-size: var(--text-lg); font-weight: 600;">Items to Stock Out</h3>
                    <button type="button" class="btn btn-primary" onclick="addStockOutRow()" style="padding: 8px 16px;">
                        <i class="fa-solid fa-plus"></i> Add Item
                    </button>
                </div>
                
                <div id="stock-out-items-container">
                    <!-- stock out items -->
                </div>
                
                <div id="no-items-message" style="text-align: center; padding: 40px; color: #9ca3af;">
                    <i class="fa-solid fa-box-open" style="font-size: 3rem; margin-bottom: 15px; display: block; color: var(--border);"></i>
                    <p style="margin: 0;">Click "Add Item" to start adding items to stock out</p>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeBatchStockOutModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-submit" id="batch-stock-out-submit-btn">
                    <i class="fa-solid fa-check"></i> Process Stock Out
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.stock-out-item-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 12px;
    align-items: start;
    padding: 15px;
    background: var(--background);
    border: 1px solid var(--border);
    border-radius: 8px;
    margin-bottom: 12px;
}

.stock-out-item-row .form-group {
    margin: 0;
}

.stock-out-item-row label {
    display: block;
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--foreground);
    margin-bottom: 6px;
}

.stock-out-item-row .form-select,
.stock-out-item-row .form-input {
    width: 100%;
    padding: 10px 12px;
    font-size: var(--text-base);
    border: 1px solid var(--border);
    border-radius: 6px;
    background: white;
    transition: all 0.2s ease;
}

.stock-out-item-row .form-select:focus,
.stock-out-item-row .form-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(91, 138, 154, 0.1);
}

.stock-out-item-row .form-input:disabled {
    background: var(--background);
    color: #6b7280;
    cursor: not-allowed;
}

.stock-out-item-row small {
    font-size: var(--text-xs);
    color: #6b7280;
    margin-top: 4px;
    display: block;
}

.stock-out-item-row .remove-btn {
    background: var(--destructive);
    color: white;
    border: none;
    padding: 10px 12px;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 26px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stock-out-item-row .remove-btn:hover {
    background: #b91c1c;
    transform: translateY(-1px);
}

.stock-out-error {
    color: var(--destructive);
    font-size: var(--text-sm);
    margin-top: 5px;
    display: none;
}
</style>

<script>
let stockOutRowCounter = 0;
const allItems = <?php echo json_encode($items); ?>;

function openBatchStockOutModal() {
    document.getElementById('batchStockOutModal').style.display = 'flex';
    document.getElementById('stock-out-items-container').innerHTML = '';
    document.getElementById('no-items-message').style.display = 'block';
    document.getElementById('batch-transaction-type').value = 'stock-out';
    document.getElementById('batch-note').value = '';
    stockOutRowCounter = 0;
    updateSubmitButton();
}

function closeBatchStockOutModal() {
    document.getElementById('batchStockOutModal').style.display = 'none';
}

function addStockOutRow() {
    const container = document.getElementById('stock-out-items-container');
    const noItemsMsg = document.getElementById('no-items-message');
    
    noItemsMsg.style.display = 'none';
    
    const rowId = stockOutRowCounter++;
    const rowDiv = document.createElement('div');
    rowDiv.className = 'stock-out-item-row';
    rowDiv.id = `stock-out-row-${rowId}`;
    
    let optionsHTML = '<option value="">Select an item...</option>';
    allItems.forEach(item => {
        optionsHTML += `<option value="${item.id}" data-stock="${item.stock_quantity}" data-unit="${item.unit}">${item.name} (${item.category_name})</option>`;
    });
    
    rowDiv.innerHTML = `
        <div class="form-group">
            <label>Item *</label>
            <select name="items[${rowId}][item_id]" class="form-select item-select" required onchange="updateItemInfo(${rowId})">
                ${optionsHTML}
            </select>
            <small id="stock-info-${rowId}"></small>
        </div>
        
        <div class="form-group">
            <label>Quantity *</label>
            <input 
                type="number" 
                name="items[${rowId}][quantity]" 
                class="form-input" 
                step="0.01" 
                min="0.01"
                required
                oninput="validateRowQuantity(${rowId})"
                placeholder="Enter quantity"
            >
            <small class="stock-out-error" id="error-${rowId}" style="display: none;">Exceeds available stock!</small>
        </div>
        
        <div class="form-group">
            <label>Unit</label>
            <input 
                type="text" 
                id="unit-${rowId}"
                class="form-input" 
                disabled
            >
        </div>
        
        <button type="button" class="remove-btn" onclick="removeStockOutRow(${rowId})" title="Remove Item">
            <i class="fa-solid fa-trash"></i>
        </button>
    `;
    
    container.appendChild(rowDiv);
    updateSubmitButton();
}

function removeStockOutRow(rowId) {
    const row = document.getElementById(`stock-out-row-${rowId}`);
    row.remove();
    
    const container = document.getElementById('stock-out-items-container');
    const noItemsMsg = document.getElementById('no-items-message');
    
    if (container.children.length === 0) {
        noItemsMsg.style.display = 'block';
    }
    
    updateSubmitButton();
}

function updateItemInfo(rowId) {
    const select = document.querySelector(`#stock-out-row-${rowId} .item-select`);
    const selectedOption = select.options[select.selectedIndex];
    const stockInfo = document.getElementById(`stock-info-${rowId}`);
    const unitField = document.getElementById(`unit-${rowId}`);
    
    if (selectedOption.value) {
        const stock = selectedOption.getAttribute('data-stock');
        const unit = selectedOption.getAttribute('data-unit');
        stockInfo.textContent = `Available: ${stock} ${unit}`;
        unitField.value = unit;
    } else {
        stockInfo.textContent = '';
        unitField.value = '';
    }
    
    validateRowQuantity(rowId);
}

function validateRowQuantity(rowId) {
    const row = document.getElementById(`stock-out-row-${rowId}`);
    if (!row) return;
    
    const select = row.querySelector('.item-select');
    const selectedOption = select.options[select.selectedIndex];
    const quantityInput = row.querySelector('input[type="number"]');
    const errorMsg = document.getElementById(`error-${rowId}`);
    
    if (selectedOption && selectedOption.value && quantityInput.value) {
        const availableStock = parseFloat(selectedOption.getAttribute('data-stock'));
        const quantity = parseFloat(quantityInput.value);
        
        if (quantity > availableStock) {
            errorMsg.style.display = 'block';
            quantityInput.style.borderColor = 'var(--destructive)';
        } else {
            errorMsg.style.display = 'none';
            quantityInput.style.borderColor = 'var(--border)';
        }
    } else {
        errorMsg.style.display = 'none';
        quantityInput.style.borderColor = 'var(--border)';
    }
    
    updateSubmitButton();
}

function updateSubmitButton() {
    const submitBtn = document.getElementById('batch-stock-out-submit-btn');
    const container = document.getElementById('stock-out-items-container');
    const hasErrors = document.querySelectorAll('.stock-out-error[style*="display: block"]').length > 0;
    
    submitBtn.disabled = container.children.length === 0 || hasErrors;
}

// Form validation
document.getElementById('batchStockOutForm').addEventListener('submit', function(e) {
    const container = document.getElementById('stock-out-items-container');
    
    if (container.children.length === 0) {
        e.preventDefault();
        alert('Please add at least one item to stock out.');
        return false;
    }
    
    // Check for validation errors
    const hasErrors = document.querySelectorAll('.stock-out-error[style*="display: block"]').length > 0;
    if (hasErrors) {
        e.preventDefault();
        alert('Please fix the validation errors before submitting.');
        return false;
    }
    
    return true;
});
</script>
