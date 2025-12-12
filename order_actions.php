<?php
// Set timezone
date_default_timezone_set('Asia/Manila');

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Unauthorized access';
    header('Location: ' . base_url('?page=login'));
    exit;
}

// Get database connection
$pdo = Database::connect();

$current_user = get_session_user();
$action = $_POST['action'] ?? '';

try {
    if ($action === 'create') {
        // CREATE NEW ORDER
        
        // Validate inputs
        $supplier_id = $_POST['supplier_id'] ?? null;
        $delivery_date = $_POST['delivery_date'] ?? null;
        $items = $_POST['items'] ?? [];
        $total_cost = $_POST['total_cost'] ?? 0;
        $notes = $_POST['notes'] ?? '';
        
        if (!$supplier_id || empty($items)) {
            $_SESSION['error_message'] = 'Supplier and at least one item are required';
            header('Location: ' . base_url('?page=transactions&tab=pending-orders'));
            exit;
        }
        
        // Generate order number with current Manila time
        $current_datetime = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $order_number = 'ORD-' . $current_datetime->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        // Check if order number exists, regenerate if needed
        $check_stmt = $pdo->prepare("SELECT id FROM orders WHERE order_number = ?");
        $check_stmt->execute([$order_number]);
        while ($check_stmt->fetch()) {
            $order_number = 'ORD-' . $current_datetime->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            $check_stmt->execute([$order_number]);
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Insert order with explicit timestamp
        $ordered_at = $current_datetime->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare("
            INSERT INTO orders (order_number, supplier_id, status, total_cost, ordered_by, ordered_at, notes)
            VALUES (?, ?, 'pending', ?, ?, ?, ?)
        ");
        $stmt->execute([
            $order_number,
            $supplier_id,
            $total_cost,
            $current_user['id'],
            $ordered_at,
            $notes
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Insert order items
        $item_stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, item_id, item_name, quantity, unit, unit_cost, total_cost)
            VALUES (?, ?, (SELECT name FROM items WHERE id = ?), ?, ?, ?, ?)
        ");
        
        foreach ($items as $item) {
            if (!empty($item['inventory_id']) && !empty($item['quantity']) && !empty($item['unit_cost'])) {
                $item_total = $item['quantity'] * $item['unit_cost'];
                $item_stmt->execute([
                    $order_id,
                    $item['inventory_id'],
                    $item['inventory_id'],
                    $item['quantity'],
                    $item['unit'],
                    $item['unit_cost'],
                    $item_total
                ]);
            }
        }
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Order {$order_number} created successfully";
        header('Location: ' . base_url('?page=transactions&tab=pending-orders'));
        exit;
        
    } elseif ($action === 'receive') {
        // MARK ORDER AS RECEIVED
        
        // Both admins and staff
        if (!in_array($current_user['role'], ['admin', 'staff'])) {
            $_SESSION['error_message'] = 'Unauthorized action';
            header('Location: ' . base_url('?page=transactions&tab=pending-orders'));
            exit;
        }
        
        $order_id = $_POST['order_id'] ?? null;
        $notes = $_POST['notes'] ?? '';
        
        if (!$order_id) {
            $_SESSION['error_message'] = 'Order ID is required';
            header('Location: ' . base_url('?page=transactions&tab=pending-orders'));
            exit;
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Get order details
        $order_stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND status = 'pending'");
        $order_stmt->execute([$order_id]);
        $order = $order_stmt->fetch();
        
        if (!$order) {
            $pdo->rollBack();
            $_SESSION['error_message'] = 'Order not found or already received';
            header('Location: ' . base_url('?page=transactions&tab=pending-orders'));
            exit;
        }
        
        // Update order status with explicit timestamp
        $received_at = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s');
        $update_stmt = $pdo->prepare("
            UPDATE orders 
            SET status = 'received', 
                received_by = ?, 
                received_at = ?,
                notes = CONCAT(COALESCE(notes, ''), IF(notes IS NOT NULL AND notes != '', '\n', ''), ?)
            WHERE id = ?
        ");
        $update_stmt->execute([
            $current_user['id'],
            $received_at,
            $notes ?: 'Order received',
            $order_id
        ]);
        
        // Get order items
        $items_stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $items_stmt->execute([$order_id]);
        $order_items = $items_stmt->fetchAll();
        
        // Update inventory and create transactions
        $current_timestamp = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s');
        
        $update_inventory_stmt = $pdo->prepare("
            UPDATE items 
            SET stock_quantity = stock_quantity + ?,
                updated_at = ?,
                updated_by = ?
            WHERE id = ?
        ");
        
        $create_transaction_stmt = $pdo->prepare("
            INSERT INTO transactions (item_id, user_id, transaction_type, quantity, unit, value_before, value_after, note, transaction_date)
            VALUES (?, ?, 'stock-in', ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($order_items as $item) {
            // Get current stock
            $stock_stmt = $pdo->prepare("SELECT stock_quantity FROM items WHERE id = ?");
            $stock_stmt->execute([$item['item_id']]);
            $current_stock = $stock_stmt->fetchColumn();
            
            if ($current_stock !== false) {
                $new_stock = $current_stock + $item['quantity'];
                
                // Update inventory
                $update_inventory_stmt->execute([
                    $item['quantity'],
                    $current_timestamp,
                    $current_user['id'],
                    $item['item_id']
                ]);
                
                // Create transaction record
                $create_transaction_stmt->execute([
                    $item['item_id'],
                    $current_user['id'],
                    $item['quantity'],
                    $item['unit'],
                    $current_stock,
                    $new_stock,
                    "Received from order: {$order['order_number']}",
                    $current_timestamp
                ]);
            }
        }
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Order {$order['order_number']} marked as received and inventory updated";
        header('Location: ' . base_url('?page=transactions&tab=transaction-history'));
        exit;
        
    } else {
        $_SESSION['error_message'] = 'Invalid action';
        header('Location: ' . base_url('?page=transactions'));
        exit;
    }
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    header('Location: ' . base_url('?page=transactions'));
    exit;
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
    header('Location: ' . base_url('?page=transactions'));
    exit;
}