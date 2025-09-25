<?php
// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../../config/db.php';
require 'auth_check.php';

header('Content-Type: application/json');
if (!isset($_SESSION['admin_id'])) {
	http_response_code(401);
	echo json_encode(['success' => false, 'error' => 'Unauthorized']);
	exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$period = $_GET['period'] ?? 'month';

try {
    // Test database connection first
    if (!isset($pdo)) {
        throw new Exception('Database connection not established');
    }
    
    // Test a simple query first
    $testQuery = $pdo->query("SELECT 1");
    if (!$testQuery) {
        throw new Exception('Database query test failed');
    }
    
    // Validate period
    $validPeriods = ['week', 'month', 'quarter', 'year'];
    if (!in_array($period, $validPeriods)) {
        $period = 'month';
    }
    
    // Get subscription distribution over time
    $distributionData = getSubscriptionDistribution($pdo, $period);
    
    // Get plan distribution
    $planDistribution = getPlanDistribution($pdo);
    
    // Get basic stats
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $activeSubscriptions = $pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status = 'active'")->fetchColumn();
    $monthlyRevenue = getMonthlyRevenue($pdo);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'distribution' => $distributionData,
            'plan_distribution' => $planDistribution,
            'stats' => [
                'total_users' => (int)$totalUsers,
                'active_subscriptions' => (int)$activeSubscriptions,
                'monthly_revenue' => (float)$monthlyRevenue
            ]
        ]
    ]);
    
} catch (Exception $e) {
    // More detailed error logging
    error_log("Subscription stats error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Error fetching subscription statistics',
        'debug_error' => $e->getMessage(), // Remove this in production
        'debug_line' => $e->getLine(),     // Remove this in production
        'debug_file' => $e->getFile()      // Remove this in production
    ]);
}

function getSubscriptionDistribution($pdo, $period) {
    // Generate labels and get the appropriate date format
    switch ($period) {
        case 'week':
            $dateFormat = '%Y-%u'; // Year-Week
            $labels = [];
            for ($i = 6; $i >= 0; $i--) {
                $labels[] = date('M d', strtotime("-{$i} weeks"));
            }
            $intervalCondition = "s.start_date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 WEEK)";
            break;
        case 'quarter':
            $dateFormat = '%Y-Q%q'; // Year-Quarter
            $labels = [];
            $currentQuarter = ceil(date('n') / 3);
            $currentYear = date('Y');
            for ($i = 3; $i >= 0; $i--) {
                $q = $currentQuarter - $i;
                $y = $currentYear;
                if ($q <= 0) {
                    $q += 4;
                    $y--;
                }
                $labels[] = "Q{$q} {$y}";
            }
            $intervalCondition = "s.start_date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)";
            break;
        case 'year':
            $dateFormat = '%Y'; // Year only
            $labels = [];
            for ($i = 4; $i >= 0; $i--) {
                $labels[] = date('Y', strtotime("-{$i} years"));
            }
            $intervalCondition = "s.start_date >= DATE_SUB(CURRENT_DATE, INTERVAL 5 YEAR)";
            break;
        default: // month
            $dateFormat = '%Y-%m'; // Year-Month
            $labels = [];
            for ($i = 5; $i >= 0; $i--) {
                $labels[] = date('M Y', strtotime("-{$i} months"));
            }
            $intervalCondition = "s.start_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)";
    }
    
    // Build the SQL query
    $sql = "
        SELECT 
            DATE_FORMAT(s.start_date, '{$dateFormat}') as period_label,
            s.plan_name,
            COUNT(*) as count
        FROM subscriptions s 
        WHERE {$intervalCondition}
        GROUP BY DATE_FORMAT(s.start_date, '{$dateFormat}'), s.plan_name
        ORDER BY period_label
    ";
    
    $stmt = $pdo->query($sql);
    if (!$stmt) {
        throw new Exception('Failed to execute subscription distribution query');
    }
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Initialize chart data structure
    $chartData = [
        'labels' => $labels,
        'series' => [
            'free' => array_fill(0, count($labels), 0),
            'pro' => array_fill(0, count($labels), 0),
            'enterprise' => array_fill(0, count($labels), 0)
        ]
    ];
    
    // Process the results and map to correct time periods
    $periodMap = [];
    foreach ($labels as $index => $label) {
        switch ($period) {
            case 'week':
                $periodMap[date('Y-W', strtotime("-" . (count($labels) - 1 - $index) . " weeks"))] = $index;
                break;
            case 'quarter':
                // Map quarter labels to database format
                if (preg_match('/Q(\d) (\d{4})/', $label, $matches)) {
                    $q = $matches[1];
                    $y = $matches[2];
                    $periodMap["{$y}-Q{$q}"] = $index;
                }
                break;
            case 'year':
                $periodMap[$label] = $index;
                break;
            default: // month
                $periodMap[date('Y-m', strtotime("-" . (count($labels) - 1 - $index) . " months"))] = $index;
        }
    }
    
    // Map database results to chart data
    foreach ($results as $row) {
        $periodLabel = $row['period_label'];
        $plan = strtolower($row['plan_name']);
        $count = (int)$row['count'];
        
        // Find the corresponding index in our labels array
        if (isset($periodMap[$periodLabel]) && isset($chartData['series'][$plan])) {
            $index = $periodMap[$periodLabel];
            $chartData['series'][$plan][$index] += $count;
        }
    }
    
    return $chartData;
}

function getPlanDistribution($pdo) {
    $sql = "
        SELECT 
            plan_name,
            COUNT(*) as count
        FROM subscriptions 
        WHERE status IN ('active', 'trial')
        GROUP BY plan_name
    ";
    
    $stmt = $pdo->query($sql);
    if (!$stmt) {
        throw new Exception('Failed to execute plan distribution query');
    }
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $distribution = [];
    foreach ($results as $row) {
        $distribution[] = [
            'name' => ucfirst($row['plan_name']),
            'value' => (int)$row['count']
        ];
    }
    
    // If no data, return sample data to prevent empty charts
    if (empty($distribution)) {
        $distribution = [
            ['name' => 'Free', 'value' => 0],
            ['name' => 'Pro', 'value' => 0],
            ['name' => 'Enterprise', 'value' => 0]
        ];
    }
    
    return $distribution;
}

function getMonthlyRevenue($pdo) {
    // Simplified calculation
    $planPrices = [
        'free' => 0,
        'pro' => 49.99,
        'enterprise' => 199.99
    ];
    
    $sql = "
        SELECT 
            plan_name,
            COUNT(*) as count
        FROM subscriptions 
        WHERE status = 'active' 
        AND plan_name != 'free'
        GROUP BY plan_name
    ";
    
    $stmt = $pdo->query($sql);
    if (!$stmt) {
        throw new Exception('Failed to execute revenue query');
    }
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $revenue = 0;
    foreach ($results as $row) {
        $plan = strtolower($row['plan_name']);
        if (isset($planPrices[$plan])) {
            $revenue += $planPrices[$plan] * $row['count'];
        }
    }
    
    return $revenue;
}
?>