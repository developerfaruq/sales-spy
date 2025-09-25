<?php
require_once '../../../config/db.php';
require '../../subscription/api/auth_check.php';
if (!isset($_SESSION['admin_id'])) {
	http_response_code(401);
	echo json_encode(['success' => false, 'error' => 'Unauthorized']);
	exit;
}


$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            handleGetPlans($pdo);
            break;
        case 'POST':
            handleCreatePlan($pdo);
            break;
        case 'PUT':
            handleUpdatePlan($pdo);
            break;
        case 'DELETE':
            handleDeletePlan($pdo);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Plans API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

function handleGetPlans($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM plans ORDER BY monthly_price ASC");
        $plans = $stmt->fetchAll();
        
        // Decode features JSON for each plan
        foreach ($plans as &$plan) {
            $plan['features'] = json_decode($plan['features'], true) ?? [];
        }
        
        echo json_encode([
            'success' => true, 
            'data' => $plans
        ]);
    } catch (Exception $e) {
        throw $e;
    }
}

function handleCreatePlan($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['plan_name', 'monthly_price', 'credits_per_month', 'features'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || (is_string($input[$field]) && trim($input[$field]) === '')) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            return;
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO plans (plan_name, description, monthly_price, yearly_price, leads_per_month, features, credits_per_month, is_active, is_popular) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $yearlyPrice = isset($input['yearly_price']) ? $input['yearly_price'] : ($input['monthly_price'] * 12 * 0.8); // 20% discount
        $description = isset($input['description']) ? $input['description'] : '';
        $leadsPerMonth = isset($input['leads_per_month']) ? $input['leads_per_month'] : 0;
        $isActive = isset($input['is_active']) ? (bool)$input['is_active'] : true;
        $isPopular = isset($input['is_popular']) ? (bool)$input['is_popular'] : false;
        
        $stmt->execute([
            $input['plan_name'],
            $description,
            $input['monthly_price'],
            $yearlyPrice,
            $leadsPerMonth,
            json_encode($input['features']),
            $input['credits_per_month'],
            $isActive,
            $isPopular
        ]);
        
        $planId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Plan created successfully',
            'plan_id' => $planId
        ]);
    } catch (Exception $e) {
        throw $e;
    }
}

function handleUpdatePlan($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Plan ID is required']);
        return;
    }
    
    try {
        // Check if plan exists
        $stmt = $pdo->prepare("SELECT id FROM plans WHERE id = ?");
        $stmt->execute([$input['id']]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Plan not found']);
            return;
        }
        
        // Build dynamic update query
        $updateFields = [];
        $values = [];
        
        if (isset($input['plan_name'])) {
            $updateFields[] = "plan_name = ?";
            $values[] = $input['plan_name'];
        }
        if (isset($input['description'])) {
            $updateFields[] = "description = ?";
            $values[] = $input['description'];
        }
        if (isset($input['monthly_price'])) {
            $updateFields[] = "monthly_price = ?";
            $values[] = $input['monthly_price'];
        }
        if (isset($input['yearly_price'])) {
            $updateFields[] = "yearly_price = ?";
            $values[] = $input['yearly_price'];
        }
        if (isset($input['leads_per_month'])) {
            $updateFields[] = "leads_per_month = ?";
            $values[] = $input['leads_per_month'];
        }
        if (isset($input['credits_per_month'])) {
            $updateFields[] = "credits_per_month = ?";
            $values[] = $input['credits_per_month'];
        }
        if (isset($input['features'])) {
            $updateFields[] = "features = ?";
            $values[] = json_encode($input['features']);
        }
        if (isset($input['is_active'])) {
            $updateFields[] = "is_active = ?";
            $values[] = (bool)$input['is_active'];
        }
        if (isset($input['is_popular'])) {
            $updateFields[] = "is_popular = ?";
            $values[] = (bool)$input['is_popular'];
        }
        
        if (empty($updateFields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            return;
        }
        
        // Add updated_at field
        $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
        $values[] = $input['id'];
        
        $sql = "UPDATE plans SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Plan updated successfully'
        ]);
    } catch (Exception $e) {
        throw $e;
    }
}

function handleDeletePlan($pdo) {
    $planId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$planId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Plan ID is required']);
        return;
    }
    
    try {
        // Check if plan exists
        $stmt = $pdo->prepare("SELECT id FROM plans WHERE id = ?");
        $stmt->execute([$planId]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Plan not found']);
            return;
        }
        
        // Delete the plan
        $stmt = $pdo->prepare("DELETE FROM plans WHERE id = ?");
        $stmt->execute([$planId]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Plan deleted successfully'
        ]);
    } catch (Exception $e) {
        throw $e;
    }
}
?>