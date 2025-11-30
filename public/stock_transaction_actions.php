<?php
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php?page=inventory');
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'batch-stock-out':
            $items_data = $_POST['items'] ?? [];
            $transaction_type = $_POST['transaction_type'] ?? 'stock-out';
            $general_note = trim($_POST['general_note'] ?? '');
            
            // Validation
            if (empty($items_data)) {
                $_SESSION['error_message'] = 'No items provided for stock out.';
                redirect('index.php?page=inventory');
            }
            
            // Begin transaction
            $pdo->beginTransaction();
            
            try {
                $processed_count = 0;
                $total_items = count($items_data);
                
                foreach ($items_data as $item_data) {
                    $item_id = $item_data['item_id'] ?? null;
                    $quantity = $item_data['quantity'] ?? 0;
                    
                    if (empty($item_id) || $quantity <= 0) {
                        continue;
                    }
                    
                    // Get item details
                    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
                    $stmt->execute([$item_id]);
                    $item = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$item) {
                        continue;
                    }
                    
                    // Check if quantity is available
                    if ($quantity > $item['stock_quantity']) {
                        throw new Exception("Insufficient stock for {$item['name']}. Available: {$item['stock_quantity']} {$item['unit']}");
                    }
                    
                    // Update item stock
                    $new_quantity = $item['stock_quantity'] - $quantity;
                    $update_stmt = $pdo->prepare("
                        UPDATE items 
                        SET stock_quantity = ?,
                            updated_at = NOW(),
                            updated_by = ?
                        WHERE id = ?
                    ");
                    $update_stmt->execute([$new_quantity, $current_user['id'], $item_id]);
                    
                    // Record transaction
                    $trans_stmt = $pdo->prepare("
                        INSERT INTO transactions (
                            item_id,
                            user_id,
                            transaction_type,
                            quantity,
                            unit,
                            value_before,
                            value_after,
                            note,
                            transaction_date,
                            created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    
                    $trans_stmt->execute([
                        $item_id,
                        $current_user['id'],
                        $transaction_type,
                        $quantity,
                        $item['unit'],
                        $item['stock_quantity'],
                        $new_quantity,
                        $general_note
                    ]);
                    
                    $processed_count++;
                }
                
                $pdo->commit();
                
                if ($processed_count > 0) {
                    $type_label = ucfirst(str_replace('-', ' ', $transaction_type));
                    $_SESSION['success_message'] = "{$type_label} transaction completed! {$processed_count} item(s) processed successfully.";
                } else {
                    $_SESSION['error_message'] = 'No valid items were processed.';
                }
                
                redirect('index.php?page=inventory');
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'stock-out':
        case 'damaged':
        case 'expired':
            // Validate required fields
            $item_id = $_POST['item_id'] ?? null;
            $quantity = $_POST['quantity'] ?? 0;
            $transaction_type = $_POST['transaction_type'] ?? $action;
            $note = trim($_POST['note'] ?? '');
            
            // Validation
            if (empty($item_id)) {
                $_SESSION['error_message'] = 'Item ID is required.';
                redirect('index.php?page=inventory');
            }
            
            if ($quantity <= 0) {
                $_SESSION['error_message'] = 'Quantity must be greater than 0.';
                redirect('index.php?page=inventory');
            }
            
            // Get item details
            $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
            $stmt->execute([$item_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$item) {
                $_SESSION['error_message'] = 'Item not found.';
                redirect('index.php?page=inventory');
            }
            
            // Check if quantity is available
            if ($quantity > $item['stock_quantity']) {
                $_SESSION['error_message'] = 'Insufficient stock. Available: ' . $item['stock_quantity'] . ' ' . $item['unit'];
                redirect('index.php?page=inventory');
            }
            
            // Begin transaction
            $pdo->beginTransaction();
            
            try {
                // Update item stock
                $new_quantity = $item['stock_quantity'] - $quantity;
                $update_stmt = $pdo->prepare("
                    UPDATE items 
                    SET stock_quantity = ?,
                        updated_at = NOW(),
                        updated_by = ?
                    WHERE id = ?
                ");
                $update_stmt->execute([$new_quantity, $current_user['id'], $item_id]);
                
                // Record transaction
                $trans_stmt = $pdo->prepare("
                    INSERT INTO transactions (
                        item_id,
                        user_id,
                        transaction_type,
                        quantity,
                        unit,
                        value_before,
                        value_after,
                        note,
                        transaction_date,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                
                $trans_stmt->execute([
                    $item_id,
                    $current_user['id'],
                    $transaction_type,
                    $quantity,
                    $item['unit'],
                    $item['stock_quantity'],
                    $new_quantity,
                    $note
                ]);
                
                $pdo->commit();
                
                // Set success message based on transaction type
                $type_label = ucfirst(str_replace('-', ' ', $transaction_type));
                $_SESSION['success_message'] = $type_label . ' transaction recorded successfully! Stock updated.';
                redirect('index.php?page=inventory');
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        default:
            $_SESSION['error_message'] = 'Invalid action.';
            redirect('index.php?page=inventory');
            break;
    }
} catch (PDOException $e) {
    error_log('Database error in stock_transaction_actions.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    redirect('index.php?page=inventory');
}
