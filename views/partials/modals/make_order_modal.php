<!-- Make Order Modal -->
<div id="makeOrderModal" class="modal">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <div>
                <h2>Create New Order</h2>
                <p class="modal-subtitle">Add items to create a purchase order</p>
            </div>
            <button class="modal-close" onclick="closeMakeOrderModal()">&times;</button>
        </div>
        
        <form id="makeOrderForm" method="POST" action="<?php echo base_url('order_actions.php'); ?>">
            <input type="hidden" name="action" value="create">
            
            <div class="modal-body">
                <!-- Supplier and Delivery Date -->
                <div class="form-group">
                    <label for="order-supplier">Supplier *</label>
                    <select id="order-supplier" name="supplier_id" class="form-select" required>
                        <option value="">Select Supplier</option>
                        <?php foreach ($all_suppliers as $sup): ?>
                        <option value="<?php echo $sup['id']; ?>"><?php echo escape($sup['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Order Items -->
                <div class="form-group-full">
                    <label>Order Items *</label>
                    <div class="order-items-container">
                        <table class="order-items-table">
                            <thead>
                                <tr>
                                    <th style="width: 38%;">Item</th>
                                    <th style="width: 18%;">Quantity</th>
                                    <th style="width: 16%;">Unit</th>
                                    <th style="width: 20%;">Unit Cost (₱)</th>
                                    <th style="width: 8%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="orderItemsBody">
                                <tr class="order-item-row">
                                    <td>
                                        <select name="items[0][inventory_id]" class="form-select item-select" required onchange="updateItemUnit(this, 0)">
                                            <option value="">Select Item</option>
                                            <?php foreach ($items as $item): ?>
                                            <option value="<?php echo $item['id']; ?>" data-unit="<?php echo escape($item['unit']); ?>" data-cost="<?php echo $item['cost']; ?>">
                                                <?php echo escape($item['name']); ?> (<?php echo escape($item['category_name']); ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-input item-quantity" min="0.01" step="0.01" required onchange="calculateTotal()">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][unit]" class="form-input item-unit" readonly>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][unit_cost]" class="form-input item-cost" min="0.01" step="0.01" required onchange="calculateTotal()">
                                    </td>
                                    <td>
                                        <button type="button" class="btn-remove-item" onclick="removeOrderItem(this)" disabled>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <button type="button" class="btn btn-primary" onclick="addOrderItem()" style="margin-top: 10px; padding: 10px 20px;">
                            <i class="fa-solid fa-plus"></i> Add Item
                        </button>
                    </div>
                </div>

                <!-- Total Cost Display -->
                <div class="form-group-full" style="text-align: right; margin-top: 20px;">
                    <h3 style="margin: 0;">Total Cost: <span id="totalCostDisplay">₱0.00</span></h3>
                    <input type="hidden" name="total_cost" id="totalCostInput" value="0">
                </div>

                <!-- Notes -->
                <div class="form-group-full">
                    <label for="order-notes">Notes</label>
                    <textarea id="order-notes" name="notes" class="form-input" rows="3" placeholder="Optional order notes..."></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeMakeOrderModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="fa-solid fa-cart-shopping"></i> Create Order
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let orderItemIndex = 1;

function addOrderItem() {
    const tbody = document.getElementById('orderItemsBody');
    const newRow = document.createElement('tr');
    newRow.className = 'order-item-row';
    newRow.innerHTML = `
        <td>
            <select name="items[${orderItemIndex}][inventory_id]" class="form-select item-select" required onchange="updateItemUnit(this, ${orderItemIndex})">
                <option value="">Select Item</option>
                <?php foreach ($items as $item): ?>
                <option value="<?php echo $item['id']; ?>" data-unit="<?php echo escape($item['unit']); ?>" data-cost="<?php echo $item['cost']; ?>">
                    <?php echo escape($item['name']); ?> (<?php echo escape($item['category_name']); ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="number" name="items[${orderItemIndex}][quantity]" class="form-input item-quantity" min="0.01" step="0.01" required onchange="calculateTotal()">
        </td>
        <td>
            <input type="text" name="items[${orderItemIndex}][unit]" class="form-input item-unit" readonly>
        </td>
        <td>
            <input type="number" name="items[${orderItemIndex}][unit_cost]" class="form-input item-cost" min="0.01" step="0.01" required onchange="calculateTotal()">
        </td>
        <td>
            <button type="button" class="btn-remove-item" onclick="removeOrderItem(this)">
                <i class="fa-solid fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(newRow);
    orderItemIndex++;
    updateRemoveButtons();
}

function removeOrderItem(button) {
    const row = button.closest('tr');
    row.remove();
    updateRemoveButtons();
    calculateTotal();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.order-item-row');
    const removeButtons = document.querySelectorAll('.btn-remove-item');
    
    if (rows.length === 1) {
        removeButtons[0].disabled = true;
    } else {
        removeButtons.forEach(btn => btn.disabled = false);
    }
}

function updateItemUnit(selectElement, index) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const unit = selectedOption.getAttribute('data-unit');
    const cost = selectedOption.getAttribute('data-cost');
    
    const row = selectElement.closest('tr');
    const unitInput = row.querySelector('.item-unit');
    const costInput = row.querySelector('.item-cost');
    
    if (unit) {
        unitInput.value = unit;
    }
    
    if (cost) {
        costInput.value = parseFloat(cost).toFixed(2);
    }
    
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    const rows = document.querySelectorAll('.order-item-row');
    
    rows.forEach(row => {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const cost = parseFloat(row.querySelector('.item-cost').value) || 0;
        total += quantity * cost;
    });
    
    document.getElementById('totalCostDisplay').textContent = '₱' + total.toFixed(2);
    document.getElementById('totalCostInput').value = total.toFixed(2);
}

// Reset form when modal is closed
function closeMakeOrderModal() {
    document.getElementById('makeOrderModal').style.display = 'none';
    document.getElementById('makeOrderForm').reset();
    
    // Reset to one item row
    const tbody = document.getElementById('orderItemsBody');
    const rows = tbody.querySelectorAll('.order-item-row');
    for (let i = 1; i < rows.length; i++) {
        rows[i].remove();
    }
    
    orderItemIndex = 1;
    updateRemoveButtons();
    calculateTotal();
}
</script>
