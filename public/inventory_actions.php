<?php
// Inventory CRUD operations (Create, Update, Delete)

// Set timezone
date_default_timezone_set('Asia/Manila');

session_start();

// Require authentication
require_once __DIR__ . '/../includes/auth.php';
require_auth();

// Load database and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get database connection
$pdo = Database::connect();

// Get current user
$current_user = get_session_user();

// Response helper function
function json_response($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php?page=inventory');
}

// Get action from POST data
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'save':
            // Check if adding new or editing existing
            $item_id = $_POST['id'] ?? null;
            
            // Validate required fields
            $name = trim($_POST['name'] ?? '');
            $category_id = $_POST['category_id'] ?? null;
            $supplier_id = $_POST['supplier_id'] ?? null;
            $unit = trim($_POST['unit'] ?? '');
            $stock_quantity = $_POST['stock_quantity'] ?? 0;
            $reorder_level = $_POST['reorder_level'] ?? 0;
            $cost = $_POST['cost'] ?? 0;
            $price = $_POST['price'] ?? 0;
            $expiration_date = $_POST['expiration_date'] ?? null;
            $notes = trim($_POST['notes'] ?? '');
            
            // Validation
            if (empty($name)) {
                $_SESSION['error_message'] = 'Item name is required.';
                redirect('index.php?page=inventory');
            }
            
            if (empty($category_id)) {
                $_SESSION['error_message'] = 'Category is required.';
                redirect('index.php?page=inventory');
            }
            
            if ($item_id) {
                // Update existing item (admin only)
                if ($current_user['role'] !== 'admin') {
                    $_SESSION['error_message'] = 'You do not have permission to edit items.';
                    redirect('index.php?page=inventory');
                }
                
                $sql = "UPDATE items SET 
                        name = :name,
                        category_id = :category_id,
                        supplier_id = :supplier_id,
                        unit = :unit,
                        stock_quantity = :stock_quantity,
                        reorder_level = :reorder_level,
                        cost = :cost,
                        price = :price,
                        expiration_date = :expiration_date,
                        notes = :notes,
                        updated_at = NOW(),
                        updated_by = :updated_by
                        WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name' => $name,
                    ':category_id' => $category_id,
                    ':supplier_id' => $supplier_id ?: null,
                    ':unit' => $unit,
                    ':stock_quantity' => $stock_quantity,
                    ':reorder_level' => $reorder_level,
                    ':cost' => $cost,
                    ':price' => $price,
                    ':expiration_date' => $expiration_date ?: null,
                    ':notes' => $notes ?: null,
                    ':updated_by' => $current_user['id'],
                    ':id' => $item_id
                ]);
                
                $_SESSION['success_message'] = 'Item updated successfully!';
            } else {
                // Add new item (admin only)
                if ($current_user['role'] !== 'admin') {
                    $_SESSION['error_message'] = 'You do not have permission to add items.';
                    redirect('index.php?page=inventory');
                }
                
                $sql = "INSERT INTO items (
                        name, category_id, supplier_id, unit, stock_quantity, 
                        reorder_level, cost, price, expiration_date, notes,
                        created_at, updated_at
                    ) VALUES (
                        :name, :category_id, :supplier_id, :unit, :stock_quantity,
                        :reorder_level, :cost, :price, :expiration_date, :notes,
                        NOW(), NOW()
                    )";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name' => $name,
                    ':category_id' => $category_id,
                    ':supplier_id' => $supplier_id ?: null,
                    ':unit' => $unit,
                    ':stock_quantity' => $stock_quantity,
                    ':reorder_level' => $reorder_level,
                    ':cost' => $cost,
                    ':price' => $price,
                    ':expiration_date' => $expiration_date ?: null,
                    ':notes' => $notes ?: null
                ]);
                
                $_SESSION['success_message'] = 'Item added successfully!';
            }
            
            redirect('index.php?page=inventory');
            break;
            
        case 'delete':
            // Delete item (adminonly)
            if ($current_user['role'] !== 'admin') {
                $_SESSION['error_message'] = 'You do not have permission to delete items.';
                redirect('index.php?page=inventory');
            }
            
            $item_id = $_POST['id'] ?? null;
            
            if (empty($item_id)) {
                $_SESSION['error_message'] = 'Item ID is required.';
                redirect('index.php?page=inventory');
            }
            
            // Check if item has related transactions
            $check_sql = "SELECT COUNT(*) as count FROM transactions WHERE item_id = :id";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([':id' => $item_id]);
            $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $_SESSION['error_message'] = 'Cannot delete item with existing transactions.';
                redirect('index.php?page=inventory');
            }
            
            // Check if item has related order items
            $check_orders_sql = "SELECT COUNT(*) as count FROM order_items WHERE item_id = :id";
            $check_orders_stmt = $pdo->prepare($check_orders_sql);
            $check_orders_stmt->execute([':id' => $item_id]);
            $orders_result = $check_orders_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($orders_result['count'] > 0) {
                $_SESSION['error_message'] = 'Cannot delete item with existing orders. Please delete the related orders first.';
                redirect('index.php?page=inventory');
            }
            
            // Delete the item
            $sql = "DELETE FROM items WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $item_id]);
            
            $_SESSION['success_message'] = 'Item deleted successfully!';
            redirect('index.php?page=inventory');
            break;
            
        default:
            $_SESSION['error_message'] = 'Invalid action.';
            redirect('index.php?page=inventory');
            break;
    }
} catch (PDOException $e) {
    error_log('Database error in inventory_actions.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    redirect('index.php?page=inventory');
}