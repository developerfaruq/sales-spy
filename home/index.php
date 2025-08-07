<?php 
require '../auth/auth_check.php';

// Initialize data variables
$user = [];
$stats = [
    'total_leads' => 0,
    'active_campaigns' => 0,
    'credits_remaining' => 10000,
    'credits_total' => 10000,
    'leads_balance' => 10000,
    'credits_percentage' => 0,
    'recent_activity' => 0,
    'plan_name' => 'Free',
    'leads_change_percentage' => 0,
    'campaigns_change_count' => 0,
    'activity_change_percentage' => 0
];

$activities = [];
$chartData = [
    'leads' => array_fill(0, 7, 0),
    'filters' => [
        ['value' => 35, 'name' => 'E-commerce', 'color' => 'rgba(87, 181, 231, 1)'],
        ['value' => 25, 'name' => 'Location', 'color' => 'rgba(141, 211, 199, 1)'],
        ['value' => 20, 'name' => 'Industry', 'color' => 'rgba(251, 191, 114, 1)'],
        ['value' => 20, 'name' => 'Technology', 'color' => 'rgba(252, 141, 98, 1)']
    ]
];

try {
    // Get user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        header("Location: signup.html?form=login&status=invalid_user");
        exit;
    }

    // 1. Get user subscription stats (credits, plan, leads_balance)
    $stmt = $pdo->prepare("SELECT plan_name, credits_remaining, credits_total, leads_balance FROM subscriptions WHERE user_id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $subscription = $stmt->fetch();

    $stats['plan_name'] = ucfirst($subscription['plan_name'] ?? 'Free');
    $stats['credits_remaining'] = $subscription['credits_remaining'] ?? 0;
    $stats['credits_total'] = $subscription['credits_total'] ?? 1000;
    $stats['leads_balance'] = $subscription['leads_balance'] ?? 0;
    $stats['credits_percentage'] = ($stats['credits_total'] > 0)
        ? ($stats['credits_remaining'] / $stats['credits_total']) * 100
        : 0;

    // 2. Total leads
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['total_leads'] = $stmt->fetchColumn();

    // 3. Active campaigns
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM campaigns WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['active_campaigns'] = $stmt->fetchColumn();

    // 4. Recent activity (last 24h)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM search_logs WHERE user_id = ? AND search_time > DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['recent_activity'] = $stmt->fetchColumn();

    // 5. Leads change %
    $stmt = $pdo->prepare("
        SELECT 
            (COUNT(CASE WHEN created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 END) - 
             COUNT(CASE WHEN created_at BETWEEN DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01') AND 
                                LAST_DAY(DATE_SUB(NOW(), INTERVAL 1 MONTH)) THEN 1 END)) / 
            NULLIF(COUNT(CASE WHEN created_at BETWEEN DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01') AND 
                                LAST_DAY(DATE_SUB(NOW(), INTERVAL 1 MONTH)) THEN 1 END), 0) * 100 as change_percentage
        FROM leads 
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $leadsChange = $stmt->fetch();
    $stats['leads_change_percentage'] = $leadsChange['change_percentage'] ?? 0;

    // 6. New campaigns this week
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as new_campaigns 
        FROM campaigns 
        WHERE user_id = ? AND status = 'active' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $campaignsChange = $stmt->fetch();
    $stats['campaigns_change_count'] = $campaignsChange['new_campaigns'] ?? 0;

    // 7. Activity change %
    $stmt = $pdo->prepare("
        SELECT 
            (COUNT(CASE WHEN search_time >= CURDATE() - INTERVAL 1 DAY THEN 1 END) - 
             COUNT(CASE WHEN search_time BETWEEN CURDATE() - INTERVAL 2 DAY AND CURDATE() - INTERVAL 1 DAY THEN 1 END)) / 
            NULLIF(COUNT(CASE WHEN search_time BETWEEN CURDATE() - INTERVAL 2 DAY AND CURDATE() - INTERVAL 1 DAY THEN 1 END), 0) * 100 as change_percentage
        FROM search_logs 
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $activityChange = $stmt->fetch();
    $stats['activity_change_percentage'] = $activityChange['change_percentage'] ?? 0;

    // 8. Recent activities (search + leads)
    $stmt = $pdo->prepare("
        (SELECT 
            'search' as activity_type, 
            CONCAT('Search with ', COALESCE(filters_used, 'no filters')) as description, 
            search_time as activity_date
        FROM search_logs 
        WHERE user_id = ? 
        ORDER BY search_time DESC 
        LIMIT 3)
        UNION ALL
        (SELECT 
            'lead' as activity_type, 
            'New lead added' as description, 
            created_at as activity_date
        FROM leads 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 2)
        ORDER BY activity_date DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $activities = $stmt->fetchAll();

    // 9. Weekly lead chart
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as day, 
            COUNT(*) as count 
        FROM leads 
        WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY day ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);

    while ($row = $stmt->fetch()) {
        $dayOfWeek = date('N', strtotime($row['day'])) - 1; // 0=Monday
        if ($dayOfWeek >= 0 && $dayOfWeek < 7) {
            $chartData['leads'][$dayOfWeek] = (int)$row['count'];
        }
    }

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    // Continue with fallback values
}

// Profile picture handling
if (!empty($user['profile_picture'])) {
    $filename = str_replace('uploads/profile_pictures/', '', $user['profile_picture']);
    $avatarUrl = '../uploads/profile_pictures/' . $filename;
} else {
    $avatarUrl = "https://ui-avatars.com/api/?name=" . 
                 urlencode($user['full_name'] ?? 'User') . 
                 "&background=1E3A8A&color=fff&length=1&size=128";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales-Spy Dashboard</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#1E3A8A',secondary:'#5BC0EB'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        .glassmorphism {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        input:focus {
            outline: none;
        }
        .sidebar-expanded {
            width: 240px;
            transition: all 0.6s cubic-bezier(0.4,0,0.2,1);
        }
        .sidebar-collapsed {
            width: 80px;
            transition: all 0.6s cubic-bezier(0.4,0,0.2,1);
        }
        .main-content-expanded {
            margin-left: 240px;
            transition: all 0.6s cubic-bezier(0.4,0,0.2,1);
        }
        .main-content-collapsed {
            margin-left: 80px;
            transition: all 0.6s cubic-bezier(0.4,0,0.2,1);
        }

        .dropdown {
position: relative;
display: inline-block;
}
.dropdown-content {
display: none;
position: absolute;
background-color: white;
min-width: 160px;
box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
z-index: 1;
border-radius: 8px;
right: 0;
}
.dropdown-content a {
color: black;
padding: 12px 16px;
text-decoration: none;
display: block;
}
.dropdown-content a:hover {
background-color: #f8f9fa;
}
.dropdown:hover .dropdown-content {
display: block;
}
        @media (max-width: 768px) {
            .main-content-expanded, .main-content-collapsed {
                margin-left: 0;
            }
        }
        .custom-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }
        .custom-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e2e8f0;
            transition: .4s;
            border-radius: 24px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #3b82f6;
        }
        input:checked + .slider:before {
            transform: translateX(20px);
        }
    </style>
</head>
<body>
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar-expanded fixed h-full bg-white shadow-lg z-20 transition-all duration-700 ease-in-out">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="p-4 border-b flex items-center justify-center relative">
                    <img src="https://res.cloudinary.com/dtrn8j0sz/image/upload/v1749075914/SS_s4jkfw.jpg" alt="Logo" id="sidebar-logo-img" class="w-8 h-8 mr-0 hidden">
                    <h1 id="sidebar-logo-text" class="font-['Pacifico'] text-2xl text-primary">Sales-Spy</h1>
                    <!-- Mobile-only collapse button -->
                    <button id="sidebar-mobile-close" class="absolute right-2 top-2 p-2 rounded-full hover:bg-gray-100 md:hidden" aria-label="Close sidebar">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-4">
                    <ul>
                        <li class="mb-2">
                            <a href="index.php" class="flex items-center px-4 py-3 text-primary bg-blue-50 rounded-r-lg border-l-4 border-primary">
                                <div class="w-6 h-6 flex items-center justify-center mr-3">
                                    <i class="ri-dashboard-line"></i>
                                </div>
                                <span class="sidebar-text">Dashboard</span>
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                                <div class="w-6 h-6 flex items-center justify-center mr-3">
                                    <i class="ri-global-line"></i>
                                </div>
                                <span class="sidebar-text">Websites</span>
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                                <div class="w-6 h-6 flex items-center justify-center mr-3">
                                    <i class="ri-shopping-cart-line"></i>
                                </div>
                                <span class="sidebar-text">E-commerce</span>
                            </a>
                        </li>
                        <li class="mb-2">
              <a href="transaction_his/"
                class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                <div class="w-6 h-6 flex items-center justify-center mr-3">
                <i class="ri-file-list-3-line"></i>
                </div>
                <span class="sidebar-text">Transaction History</span>
              </a>
              </li>

                         <li class="mb-2">
                <a href="payment/" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                  <div class="w-6 h-6 flex items-center justify-center mr-3"> <i class="ri-bank-card-line"></i></div>
                  <span class="sidebar-text">Payment</span>
                </a>
              </li>
                                      <li class="mb-2">
              <a href="transaction_his/"
                class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                <div class="w-6 h-6 flex items-center justify-center mr-3">
                <i class="ri-file-list-3-line"></i>
                </div>
                <span class="sidebar-text">Transaction History</span>
              </a>
              </li>
                        <li class="mb-2">
                            <a href="settings/" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                                <div class="w-6 h-6 flex items-center justify-center mr-3">
                                    <i class="ri-settings-line"></i>
                                </div>
                                <span class="sidebar-text">Settings</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <!-- Upgrade section -->
                <div id="upgrade-section" class="p-4 border-t">
                    <div id="upgrade-expanded" class="bg-gray-50 rounded-lg p-4 mb-3">
                        <p class="text-sm text-gray-600 mb-2">Credits remaining</p>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-lg"><?= number_format($stats['credits_remaining']) ?></span>
                            <span class="text-xs text-gray-500">of <?= number_format($stats['credits_total']) ?></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-primary rounded-full h-2" style="width: <?= $stats['credits_percentage'] ?>%"></div>
                        </div>
                    </div>
                    <a href="Dashboard-pay.html">
                    <button id="upgrade-btn-expanded" class="w-full bg-primary text-white py-2 px-4 rounded-button flex items-center justify-center whitespace-nowrap hover:bg-blue-600 transition-colors">
                        <div class="w-5 h-5 flex items-center justify-center mr-2">
                            <i class="ri-arrow-up-line"></i>
                        </div>
                        <span>Upgrade Plan</span>
                    </button>
                    </a>
                    <a href="Dashboard-pay.html">
                    <button id="upgrade-btn-collapsed" class="hidden bg-primary text-white w-10 h-10 rounded-full flex items-center justify-center mx-auto mt-2 hover:bg-blue-600 transition-colors" title="Upgrade">
                        <i class="ri-arrow-up-line"></i>
                    </button>
                    </a>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const sidebar = document.getElementById('sidebar');
                        const upgradeExpanded = document.getElementById('upgrade-expanded');
                        const upgradeBtnExpanded = document.getElementById('upgrade-btn-expanded');
                        const upgradeBtnCollapsed = document.getElementById('upgrade-btn-collapsed');
                        const logoImg = document.getElementById('sidebar-logo-img');
                        const logoText = document.getElementById('sidebar-logo-text');

                        function updateUpgradeSection() {
                            if (sidebar.classList.contains('sidebar-collapsed')) {
                                upgradeExpanded.style.display = 'none';
                                upgradeBtnExpanded.style.display = 'none';
                                upgradeBtnCollapsed.classList.remove('hidden');
                            } else {
                                upgradeExpanded.style.display = '';
                                upgradeBtnExpanded.style.display = '';
                                upgradeBtnCollapsed.classList.add('hidden');
                            }
                        }

                        function updateLogo() {
                            if (sidebar.classList.contains('sidebar-collapsed')) {
                                logoImg.classList.remove('hidden');
                                logoText.classList.add('hidden');
                            } else {
                                logoImg.classList.add('hidden');
                                logoText.classList.remove('hidden');
                            }
                        }

                        // Initial state
                        updateUpgradeSection();
                        updateLogo();

                        // Listen for sidebar toggle
                        const sidebarToggle = document.getElementById('sidebar-toggle');
                        sidebarToggle.addEventListener('click', function() {
                            setTimeout(() => {
                                updateUpgradeSection();
                                updateLogo();
                            }, 310); // Wait for transition
                        });

                        // Also update on resize (for responsive)
                        window.addEventListener('resize', function() {
                            setTimeout(() => {
                                updateUpgradeSection();
                                updateLogo();
                            }, 310);
                        });
                    });
                </script>
            </div>
        </div>
        
        <!-- Main Content -->
        <div id="main-content" class="main-content-expanded flex-1 transition-all duration-700 ease-in-out">
            <!-- Header -->
            <header class="bg-white shadow-sm sticky top-0 z-10">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center">
                        <button id="sidebar-toggle" class="p-2 rounded-full hover:bg-gray-100 mr-4">
                            <div class="w-5 h-5 flex items-center justify-center">
                                <i class="ri-menu-line"></i>
                            </div>
                        </button>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center bg-gray-100 rounded-full px-3 py-1">
                            <div class="w-5 h-5 flex items-center justify-center mr-2 text-primary">
                                <i class="ri-coin-line"></i>
                            </div>
                            <span class="text-sm font-medium"><?= number_format($stats['credits_remaining']) ?> credits</span>
                        </div>
                        <a href="Dashboard-pay.html">
                        <button class="bg-primary text-white py-2 px-4 rounded-button whitespace-nowrap hover:bg-blue-600 transition-colors">
                            <span>Upgrade</span>
                        </button>
                        </a>


<div class="dropdown">
          <button class="flex items-center space-x-2 focus:outline-none">
            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
              <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="User avatar" class="w-full h-full object-cover">
            </div>
            <i class="ri-arrow-down-s-line text-gray-500"></i>
          </button>
          <div class="dropdown-content right-0 mt-2">
            <a href="#">Profile</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php">Logout</a>
          </div>
        </div>


                        
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <div class="p-6">
                <!-- Welcome Section -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-800" id="dashboard-greeting">Welcome back, <?= htmlspecialchars($user['full_name'] ?? 'User') ?></h1>
                    <p class="text-gray-600">Here's what's been happening recently.</p>
                </div>
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Leads -->
                    <div class="glassmorphism rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-gray-600 font-medium">Total Leads</h3>
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-primary">
                                <div class="w-5 h-5 flex items-center justify-center">
                                    <i class="ri-user-search-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['leads_balance']) ?></p>
                                <p class="text-sm text-green-500 flex items-center mt-1">
                                    <div class="w-4 h-4 flex items-center justify-center mr-1">
                                        <i class="ri-arrow-up-line"></i>
                                    </div>
                                    <span><?= abs(round($stats['leads_change_percentage'], 1)) ?>% from last month</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Active Campaigns -->
                    <div class="glassmorphism rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-gray-600 font-medium">Active Campaigns</h3>
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                                <div class="w-5 h-5 flex items-center justify-center">
                                    <i class="ri-rocket-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['active_campaigns']) ?></p>
                                <p class="text-sm text-green-500 flex items-center mt-1">
                                    <div class="w-4 h-4 flex items-center justify-center mr-1">
                                        <i class="ri-arrow-up-line"></i>
                                    </div>
                                    <span><?= $stats['campaigns_change_count'] ?> new this week</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Credits Remaining -->
                    <div class="glassmorphism rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-gray-600 font-medium">Credits Remaining</h3>
                            <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                                <div class="w-5 h-5 flex items-center justify-center">
                                    <i class="ri-coin-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['credits_remaining']) ?></p>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                                    <div class="bg-primary rounded-full h-2" style="width: <?= $stats['credits_percentage'] ?>%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1"><?= round($stats['credits_percentage'], 1) ?>% remaining</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="glassmorphism rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-gray-600 font-medium">Recent Activity</h3>
                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                                <div class="w-5 h-5 flex items-center justify-center">
                                    <i class="ri-pulse-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['recent_activity']) ?></p>
                                <p class="text-sm text-green-500 flex items-center mt-1">
                                    <div class="w-4 h-4 flex items-center justify-center mr-1">
                                        <i class="ri-arrow-up-line"></i>
                                    </div>
                                    <span><?= abs(round($stats['activity_change_percentage'], 1)) ?>% from yesterday</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity Feed & Analytics -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Activity Feed -->
                    <div class="lg:col-span-1 glassmorphism rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-800">Recent Activity</h2>
                            <button class="text-primary hover:text-blue-700 text-sm">View all</button>
                        </div>
                        
                        <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2">
                            <?php if (!empty($activities)): ?>
                                <?php foreach ($activities as $activity): ?>
                                    <?php
                                    // Determine icon and color based on activity type
                                    $icon = 'ri-search-line';
                                    $color = 'text-primary';
                                    $bg = 'bg-blue-100';
                                    
                                    if (strpos($activity['activity_type'], 'lead') !== false) {
                                        $icon = 'ri-user-add-line';
                                        $color = 'text-green-600';
                                        $bg = 'bg-green-100';
                                    } elseif (strpos($activity['activity_type'], 'export') !== false) {
                                        $icon = 'ri-download-line';
                                        $color = 'text-yellow-600';
                                        $bg = 'bg-yellow-100';
                                    } elseif (strpos($activity['activity_type'], 'campaign') !== false) {
                                        $icon = 'ri-rocket-line';
                                        $color = 'text-purple-600';
                                        $bg = 'bg-purple-100';
                                    }
                                    ?>
                                    <div class="flex items-start space-x-3 pb-4 border-b border-gray-100">
                                        <div class="w-8 h-8 rounded-full <?= $bg ?> flex items-center justify-center <?= $color ?> flex-shrink-0 mt-1">
                                            <div class="w-4 h-4 flex items-center justify-center">
                                                <i class="<?= $icon ?>"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-gray-800 font-medium"><?= htmlspecialchars(ucfirst($activity['activity_type'])) ?></p>
                                            <p class="text-sm text-gray-500"><?= htmlspecialchars($activity['description']) ?></p>
                                            <p class="text-xs text-gray-400 mt-1">
                                                <?= date('M j, Y \a\t g:i A', strtotime($activity['activity_date'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-8 text-gray-500">
                                    <p>No recent activity found</p>
                                    <a href="#" class="text-primary text-sm hover:underline mt-2 inline-block">Start a new search</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    
                    <!-- Analytics Charts -->
                    <div class="lg:col-span-2 glassmorphism rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-800">Analytics Overview</h2>
                            <div class="flex items-center space-x-2">
                                <button class="px-3 py-1 text-sm bg-primary text-white rounded-full !rounded-button whitespace-nowrap">Last 7 days</button>
                                <button class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-100 rounded-full !rounded-button whitespace-nowrap">Last 30 days</button>
                                <button class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-100 rounded-full !rounded-button whitespace-nowrap">All time</button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Line Chart -->
                            <div>
                                <h3 class="text-gray-600 font-medium mb-4">Leads Generated</h3>
                                <div id="leads-chart" style="width: 100%; height: 250px;"></div>
                            </div>
                            
                            <!-- Pie Chart -->
                            <div>
                                <h3 class="text-gray-600 font-medium mb-4">Filter Usage</h3>
                                <div id="filter-chart" style="width: 100%; height: 250px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const sidebarMobileClose = document.getElementById('sidebar-mobile-close');

            // Helper: is mobile viewport
            function isMobile() {
                return window.innerWidth < 768;
            }

            // Sidebar open/close for mobile and desktop
            function openSidebar() {
                sidebar.classList.add('sidebar-expanded');
                sidebar.classList.remove('sidebar-collapsed');
                mainContent.classList.add('main-content-expanded');
                mainContent.classList.remove('main-content-collapsed');
                sidebarTexts.forEach(text => text.style.display = '');
                if (isMobile()) {
                    sidebar.style.transform = 'translateX(0)';
                    if (sidebarMobileClose) sidebarMobileClose.style.display = '';
                }
            }

            function closeSidebar() {
                sidebar.classList.remove('sidebar-expanded');
                sidebar.classList.add('sidebar-collapsed');
                mainContent.classList.remove('main-content-expanded');
                mainContent.classList.add('main-content-collapsed');
                sidebarTexts.forEach(text => text.style.display = 'none');
                if (isMobile()) {
                    sidebar.style.transform = 'translateX(-100%)';
                    if (sidebarMobileClose) sidebarMobileClose.style.display = 'none';
                }
            }

            function handleResize() {
                if (isMobile()) {
                    closeSidebar();
                    if (sidebarMobileClose) sidebarMobileClose.style.display = '';
                } else {
                    openSidebar();
                    if (sidebarMobileClose) sidebarMobileClose.style.display = 'none';
                }
            }

            window.addEventListener('DOMContentLoaded', handleResize);
            window.addEventListener('resize', handleResize);

            sidebarToggle.addEventListener('click', function() {
                if (sidebar.classList.contains('sidebar-expanded')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            if (sidebarMobileClose) {
                sidebarMobileClose.addEventListener('click', closeSidebar);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Leads Chart
            const leadsChart = echarts.init(document.getElementById('leads-chart'));
            
            const leadsOption = {
                animation: false,
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(255, 255, 255, 0.8)',
                    textStyle: {
                        color: '#1f2937'
                    }
                },
                grid: {
                    top: 10,
                    right: 10,
                    bottom: 20,
                    left: 40
                },
                xAxis: {
                    type: 'category',
                    data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    axisLine: {
                        lineStyle: {
                            color: '#e2e8f0'
                        }
                    },
                    axisLabel: {
                        color: '#64748b'
                    }
                },
                yAxis: {
                    type: 'value',
                    axisLine: {
                        show: false
                    },
                    axisLabel: {
                        color: '#64748b'
                    },
                    splitLine: {
                        lineStyle: {
                            color: '#e2e8f0'
                        }
                    }
                },
                series: [
                    {
                        name: 'Leads',
                        type: 'line',
                        smooth: true,
                        symbol: 'none',
                        lineStyle: {
                            width: 3,
                            color: 'rgba(87, 181, 231, 1)'
                        },
                        areaStyle: {
                            color: {
                                type: 'linear',
                                x: 0,
                                y: 0,
                                x2: 0,
                                y2: 1,
                                colorStops: [
                                    {
                                        offset: 0,
                                        color: 'rgba(87, 181, 231, 0.2)'
                                    },
                                    {
                                        offset: 1,
                                        color: 'rgba(87, 181, 231, 0.01)'
                                    }
                                ]
                            }
                        },
                        data: <?= json_encode($chartData['leads']) ?>
                    }
                ]
            };
            
            leadsChart.setOption(leadsOption);
            
            // Initialize Filter Chart
            const filterChart = echarts.init(document.getElementById('filter-chart'));
            
            const filterOption = {
                animation: false,
                tooltip: {
                    trigger: 'item',
                    backgroundColor: 'rgba(255, 255, 255, 0.8)',
                    textStyle: {
                        color: '#1f2937'
                    }
                },
                legend: {
                    orient: 'vertical',
                    right: 10,
                    top: 'center',
                    textStyle: {
                        color: '#64748b'
                    }
                },
                series: [
                    {
                        name: 'Filter Usage',
                        type: 'pie',
                        radius: ['50%', '70%'],
                        avoidLabelOverlap: false,
                        itemStyle: {
                            borderRadius: 8,
                            borderColor: '#fff',
                            borderWidth: 2
                        },
                        label: {
                            show: false
                        },
                        emphasis: {
                            label: {
                                show: false
                            }
                        },
                        labelLine: {
                            show: false
                        },
                        data: <?= json_encode($chartData['filters']) ?>
                    }
                ]
            };
            
            filterChart.setOption(filterOption);
            
            // Handle window resize for charts
            window.addEventListener('resize', function() {
                leadsChart.resize();
                filterChart.resize();
            });
        });
    </script>
    <script>
  // Personalize dashboard greeting with name from localStorage
  document.addEventListener('DOMContentLoaded', function() {
    const greeting = document.getElementById('dashboard-greeting');
    const name = localStorage.getItem('dashboardName');
    if (name && greeting) {
      greeting.textContent = `Welcome back, ${name}`;
      // Optionally clear the name after use:
      // localStorage.removeItem('dashboardName');
    }
  });
</script>
<script>
// Profile image sync logic for all dashboards (localStorage-based)
// This will be replaced by backend API calls in the future.
document.addEventListener('DOMContentLoaded', function() {
  const headerImg = document.getElementById('header-profile-img');
  const localProfileImg = localStorage.getItem('profileImageBase64');
  if (headerImg && localProfileImg) {
    headerImg.src = localProfileImg;
  }
  // --- BACKEND INTEGRATION POINT ---
  // On page load, replace localStorage fetch with API call to get user profile image.
});
</script>
</body>
</html>