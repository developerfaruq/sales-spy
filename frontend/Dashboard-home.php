



<?php
// Rename this file to Dashboard-home.php and add the following code at the top
require '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html?form=login&status=session_expired");
    exit;
}

// Initialize data variables
$user = [];
$stats = [
    'total_leads' => 0,
    'active_campaigns' => 0,
    'credits_remaining' => 0,
    'credits_total' => 2000,
    'credits_percentage' => 0,
    'recent_activity' => 0,
    'plan_name' => 'Pro'
];

$activities = [];
$chartData = [
    'leads' => [320, 420, 380, 520, 450, 570, 630],
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

    // Get stats from database
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['total_leads'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM campaigns WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['active_campaigns'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT credits FROM user_credits WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $credits = $stmt->fetch();
    $stats['credits_remaining'] = $credits ? $credits['credits'] : 0;
    $stats['credits_percentage'] = ($stats['credits_remaining'] / $stats['credits_total']) * 100;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_activity WHERE user_id = ? AND activity_date > DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['recent_activity'] = $stmt->fetchColumn();

    // Get recent activities
    $stmt = $pdo->prepare("SELECT * FROM user_activity WHERE user_id = ? ORDER BY activity_date DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $activities = $stmt->fetchAll();

    // Get chart data (7 days)
    $stmt = $pdo->prepare("
        SELECT DATE(activity_date) as day, COUNT(*) as count 
        FROM user_activity 
        WHERE user_id = ? AND activity_date > DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(activity_date)
        ORDER BY day ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $chartData['leads'] = array_fill(0, 7, 0); // Initialize with zeros
    
    while ($row = $stmt->fetch()) {
        $dayOfWeek = date('N', strtotime($row['day'])) - 1; // 0-6 for Mon-Sun
        if ($dayOfWeek >= 0 && $dayOfWeek < 7) {
            $chartData['leads'][$dayOfWeek] = (int)$row['count'];
        }
    }

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    // Continue with default values if DB fails
}

// Generate avatar URL
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($user['full_name'] ?? 'User') . 
             "&background=1E3A8A&color=fff&length=1&size=128";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Keep all head content exactly the same as in your original file -->
    <!-- ... -->
</head>
<body>
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar - Update dynamic content -->
        <div id="sidebar" class="sidebar-collapsed fixed h-full bg-white shadow-lg z-20 transition-all duration-700 ease-in-out">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="p-4 border-b flex items-center justify-center">
                    <img src="logo-icon.png" alt="Logo" id="sidebar-logo-img" class="w-8 h-8 mr-0 hidden">
                    <h1 id="sidebar-logo-text" class="font-['Pacifico'] text-2xl text-primary">Sales-Spy</h1>
                </div>
                
                <!-- Navigation (unchanged) -->
                <!-- ... -->
                
                <!-- Upgrade section - Make dynamic -->
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
                    <button id="upgrade-btn-expanded" class="w-full bg-primary text-white py-2 px-4 rounded-button flex items-center justify-center whitespace-nowrap hover:bg-blue-600 transition-colors">
                        <div class="w-5 h-5 flex items-center justify-center mr-2">
                            <i class="ri-arrow-up-line"></i>
                        </div>
                        <span>Upgrade Plan</span>
                    </button>
                    <button id="upgrade-btn-collapsed" class="hidden bg-primary text-white w-10 h-10 rounded-full flex items-center justify-center mx-auto mt-2 hover:bg-blue-600 transition-colors" title="Upgrade">
                        <i class="ri-arrow-up-line"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div id="main-content" class="main-content-expanded flex-1 transition-all duration-700 ease-in-out">
            <!-- Header - Update dynamic content -->
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
                        <button class="bg-primary text-white py-2 px-4 rounded-button whitespace-nowrap hover:bg-blue-600 transition-colors">
                            <span>Upgrade</span>
                        </button>
                        <div class="relative">
                            <button class="flex items-center space-x-2">
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                                    <img src="<?= $avatarUrl ?>" alt="User avatar" class="w-full h-full object-cover">
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content - Update dynamic content -->
            <div class="p-6">
                <!-- Welcome Section -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-800" id="dashboard-greeting">Welcome back, <?= htmlspecialchars($user['full_name'] ?? 'User') ?></h1>
                    <p class="text-gray-600">Here's what's been happening recently.</p>
                </div>
                
                <!-- Stats Grid - Make dynamic -->
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
                                <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['total_leads']) ?></p>
                                <p class="text-sm text-green-500 flex items-center mt-1">
                                    <div class="w-4 h-4 flex items-center justify-center mr-1">
                                        <i class="ri-arrow-up-line"></i>
                                    </div>
                                    <span>12.5% from last month</span>
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
                                    <span>2 new this week</span>
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
                                    <span>18% from yesterday</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity Feed & Analytics -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Activity Feed - Make dynamic -->
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
                                    
                                    if (strpos($activity['activity_type'], 'export') !== false) {
                                        $icon = 'ri-download-line';
                                        $color = 'text-yellow-600';
                                        $bg = 'bg-yellow-100';
                                    } elseif (strpos($activity['activity_type'], 'campaign') !== false) {
                                        $icon = 'ri-rocket-line';
                                        $color = 'text-green-600';
                                        $bg = 'bg-green-100';
                                    } elseif (strpos($activity['activity_type'], 'alert') !== false) {
                                        $icon = 'ri-alert-line';
                                        $color = 'text-red-600';
                                        $bg = 'bg-red-100';
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
                    
                    <!-- Analytics Charts - Make dynamic -->
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

    <!-- JavaScript - Update chart data to use PHP variables -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle (unchanged)
            // ...
            
            // Initialize Leads Chart with dynamic data
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
            
            // Initialize Filter Chart with dynamic data
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
</body>
</html>