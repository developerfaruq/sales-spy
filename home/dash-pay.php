<?php
 require '../auth/auth_check.php';
$user_id = $_SESSION['user_id'];
/*
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_plan'])) {
    $selected = $_POST['selected_plan'];

    // Plan mapping
    $plans = [
        'free' => ['plan_name' => 'free', 'credits_total' => 1000, 'leads_balance' => 1000, 'price' => 0],
        'basic' => ['plan_name' => 'basic', 'credits_total' => 5000, 'leads_balance' => 5000, 'price' => 20],
        'pro' => ['plan_name' => 'pro', 'credits_total' => 10000, 'leads_balance' => 10000, 'price' => 50],
        'expertise' => ['plan_name' => 'enterprise', 'credits_total' => 10000000, 'leads_balance' => 10000000, 'price' => 150]
    ];

    if (!array_key_exists($selected, $plans)) {
        die("Invalid plan selected.");
    }

    $plan = $plans[$selected];

    try {
        // Check if subscription already exists
        $stmt = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $subscription = $stmt->fetch();

        if ($subscription) {
            // Update subscription
            $update = $pdo->prepare("UPDATE subscriptions SET plan_name = ?, credits_remaining = ?, credits_total = ?, leads_balance = ?, start_date = NOW(), is_active = 1 WHERE user_id = ?");
            $update->execute([
                $plan['plan_name'],
                $plan['credits_total'],
                $plan['credits_total'],
                $plan['leads_balance'],
                $user_id
            ]);
        } else {
            // Create new subscription
            $insert = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_name, credits_remaining, credits_total, leads_balance, start_date, is_active) VALUES (?, ?, ?, ?, ?, NOW(), 1)");
            $insert->execute([
                $user_id,
                $plan['plan_name'],
                $plan['credits_total'],
                $plan['credits_total'],
                $plan['leads_balance']
            ]);
        }

        // Optional: redirect or show success message
        header("Location: dashboard.php?payment=success&plan={$plan['plan_name']}");
        exit;

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}*/
// Profile picture handling
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
//credit balance for side bar
$user = [];
$stats = [
    'credits_remaining' => 1250,
    'credits_total' => 2000,
    'credits_percentage' => 0,
];

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

// Fetch active sessions
$stmt = $pdo->prepare("SELECT * FROM user_sessions WHERE user_id = ? ORDER BY last_active DESC");
$stmt->execute([$_SESSION['user_id']]);
$sessions = $stmt->fetchAll();
// Fetch the active TRC-20 USDT wallet
$stmt = $pdo->prepare("SELECT * FROM payment_wallets WHERE network = 'TRC-20' AND currency = 'USDT' AND is_active = 1 LIMIT 1");
$stmt->execute();
$wallet = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch transactions
/*$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);*/
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sales-Spy - Crypto Payment Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
      rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: { primary: "#1E3A8A", secondary: "#5BC0EB" },
            borderRadius: {
              none: "0px",
              sm: "4px",
              DEFAULT: "8px",
              md: "12px",
              lg: "16px",
              xl: "20px",
              "2xl": "24px",
              "3xl": "32px",
              full: "9999px",
              button: "8px",
            },
          },
        },
      };
    </script>
    <style>
      :where([class^="ri-"])::before {
        content: "\f3c2";
      }

      body {
        font-family: "Inter", sans-serif;
        background-color: #f9fafb;
        /* match home */
      }

      .glassmorphism {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.3);
      }

      .sidebar-expanded {
        width: 240px;
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
      }

      .sidebar-collapsed {
        width: 80px;
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
      }

      .main-content-expanded {
        margin-left: 240px;
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
      }

      .main-content-collapsed {
        margin-left: 80px;
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
      }

      @media (max-width: 768px) {

        .main-content-expanded,
        .main-content-collapsed {
          margin-left: 0;
        }
      }

      .sidebar-item:hover {
        background-color: rgba(45, 127, 249, 0.1);
      }

      .plan-card {
        transition: all 0.3s ease;
      }

      .plan-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      }

      .plan-card.active {
        border-color: #2d7ff9;
        background-color: rgba(45, 127, 249, 0.05);
      }

      .toggle {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 24px;
      }

      .toggle input {
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
        background-color: #ccc;
        transition: 0.4s;
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
        transition: 0.4s;
        border-radius: 50%;
      }

      input:checked+.slider {
        background-color: #2d7ff9;
      }

      input:checked+.slider:before {
        transform: translateX(24px);
      }

      input[type="range"] {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 6px;
        background: #e5e7eb;
        border-radius: 5px;
        outline: none;
      }

      input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        background: #2d7ff9;
        cursor: pointer;
        border-radius: 50%;
      }

      input[type="range"]::-moz-range-thumb {
        width: 18px;
        height: 18px;
        background: #2d7ff9;
        cursor: pointer;
        border-radius: 50%;
      }

      input[type="number"]::-webkit-inner-spin-button,
      input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
      }

      .custom-radio {
        display: flex;
        align-items: center;
      }

      .custom-radio input[type="radio"] {
        display: none;
      }

      .custom-radio label {
        position: relative;
        padding-left: 28px;
        cursor: pointer;
        display: inline-block;
      }

      .custom-radio label:before {
        content: "";
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        background-color: #fff;
      }

      .custom-radio input[type="radio"]:checked+label:before {
        border-color: #2d7ff9;
      }

      .custom-radio input[type="radio"]:checked+label:after {
        content: "";
        position: absolute;
        left: 5px;
        top: 50%;
        transform: translateY(-50%);
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #2d7ff9;
      }

      .custom-checkbox {
        display: flex;
        align-items: center;
      }

      .custom-checkbox input[type="checkbox"] {
        display: none;
      }

      .custom-checkbox label {
        position: relative;
        padding-left: 28px;
        cursor: pointer;
        display: inline-block;
      }

      .custom-checkbox label:before {
        content: "";
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        background-color: #fff;
      }

      .custom-checkbox input[type="checkbox"]:checked+label:before {
        background-color: #2d7ff9;
        border-color: #2d7ff9;
      }

      .custom-checkbox input[type="checkbox"]:checked+label:after {
        content: "";
        position: absolute;
        left: 6px;
        top: 50%;
        transform: translateY(-65%) rotate(45deg);
        width: 6px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
      }

      .tooltip {
        position: relative;
        display: inline-block;
      }

      .tooltip .tooltip-text {
        visibility: hidden;
        width: 200px;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 8px;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.3s;
      }

      .tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
      }

      /* Fintech modal tweaks for mobile and clarity */
      @media (max-width: 480px) {
        .fintech-modal {
          padding: 1.25rem !important;
          border-radius: 0.75rem !important;
        }

        .fintech-modal .w-40,
        .fintech-modal .h-40 {
          width: 8rem !important;
          height: 8rem !important;
        }

        .fintech-modal .w-36,
        .fintech-modal .h-36 {
          width: 7rem !important;
          height: 7rem !important;
        }
      }

      @media (max-width: 767px) {
        .plan-card {
          min-width: 85vw;
          max-width: 90vw;
          flex: 0 0 auto;
          margin-right: 0.5rem;
        }

        .p-6 {
          padding: 1.25rem !important;
        }

        .max-w-7xl {
          max-width: 100vw !important;
        }

        body,
        html {
          width: 100vw;
          overflow-x: hidden;
        }
      }

      /* --- Card payment modal tweaks for mobile --- */
      @media (max-width: 480px) {

        .fintech-modal input,
        .fintech-modal button {
          font-size: 15px !important;
        }

        .fintech-modal .flex.gap-4 {
          flex-direction: column;
          gap: 0.5rem;
        }
      }

      .qr-disabled {
        pointer-events: none;
        opacity: 0.5;
        filter: grayscale(0.7);
      }

      .qr-enabled {
        pointer-events: auto;
        opacity: 1;
        filter: none;
      }

      .plan-card.selected {
        border-width: 2px;
        border-color: #1E3A8A;
        box-shadow: 0 0 0 2px #1E3A8A33;
        background-color: #f0f6ff;
      }
    </style>
  </head>

  <body>
    <div class="flex h-screen bg-gray-50">
      <!-- Sidebar -->
      <div id="sidebar"
        class="sidebar-expanded fixed h-full bg-white shadow-lg z-20 transition-all duration-700 ease-in-out">
        <div class="flex flex-col h-full">
          <!-- Logo -->
          <div class="p-4 border-b flex items-center justify-center relative">
            <img src="https://res.cloudinary.com/dtrn8j0sz/image/upload/v1749075914/SS_s4jkfw.jpg" alt="Logo"
              id="sidebar-logo-img" class="w-8 h-8 mr-0 hidden" />
            <h1 id="sidebar-logo-text" class="font-['Pacifico'] text-2xl text-primary">
              Sales-Spy
            </h1>
            <!-- Mobile-only collapse button -->
            <button id="sidebar-mobile-close"
              class="absolute right-2 top-2 p-2 rounded-full hover:bg-gray-100 md:hidden" aria-label="Close sidebar">
              <i class="ri-close-line text-xl"></i>
            </button>
          </div>
          <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
            <ul>
              <li class="mb-2">
              <a href="index.php"
                class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                <div class="w-6 h-6 flex items-center justify-center mr-3">
                <i class="ri-dashboard-line"></i>
                </div>
                <span class="sidebar-text">Dashboard</span>
              </a>
              </li>
              <li class="mb-2">
              <a href="Dashboard-com.html"
                class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                <div class="w-6 h-6 flex items-center justify-center mr-3">
                <i class="ri-global-line"></i>
                </div>
                <span class="sidebar-text">Websites</span>
              </a>
              </li>
              <li class="mb-2">
              <a href="Dashboard-ecc.html"
                class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                <div class="w-6 h-6 flex items-center justify-center mr-3">
                <i class="ri-shopping-cart-line"></i>
                </div>
                <span class="sidebar-text">E-commerce</span>
              </a>
              </li>

              <li class="mb-2">
              <a href="Dashboard-pay.html"
                class="flex items-center px-4 py-3 text-primary bg-blue-50 rounded-r-lg border-l-4 border-primary">
                <div class="w-6 h-6 flex items-center justify-center mr-3">
                <i class="ri-bank-card-line"></i>
                </div>
                <span class="sidebar-text">Payment</span>
              </a>
              </li>

              <li class="mb-2">
              <a href="Dashboard-his.html"
                class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                <div class="w-6 h-6 flex items-center justify-center mr-3">
                <i class="ri-file-list-3-line"></i>
                </div>
                <span class="sidebar-text">Transaction History</span>
              </a>
              </li>

              <li class="mb-2">
              <a href="settings.php"
                class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
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
                <div
                  class="bg-primary rounded-full h-2"
                  style="width: <?= $stats['credits_percentage'] ?>%"
                ></div>
              </div>
            </div>
            <a href="dash-pay.php">
              <button
                id="upgrade-btn-expanded"
                class="w-full bg-primary text-white py-2 px-4 rounded-button flex items-center justify-center whitespace-nowrap hover:bg-blue-600 transition-colors"
              >
                <div class="w-5 h-5 flex items-center justify-center mr-2">
                  <i class="ri-arrow-up-line"></i>
                </div>
                <span>Upgrade Plan</span>
              </button>
            </a>
            <a href="Dashboard-pay.html">
              <button
                id="upgrade-btn-collapsed"
                class="hidden bg-primary text-white w-10 h-10 rounded-full flex items-center justify-center mx-auto mt-2 hover:bg-blue-600 transition-colors"
                title="Upgrade"
              >
                <i class="ri-arrow-up-line"></i>
              </button>
            </a>
          </div>
          <style>
            @media (max-width: 768px) {
              .sidebar-collapsed .sidebar-text-mobile {
                display: none !important;
              }
            }
          </style>
          <script>
            document.addEventListener("DOMContentLoaded", function () {
              const sidebar = document.getElementById("sidebar");
              const upgradeExpanded =
                document.getElementById("upgrade-expanded");
              const upgradeBtnExpanded = document.getElementById(
                "upgrade-btn-expanded"
              );
              const upgradeBtnCollapsed = document.getElementById(
                "upgrade-btn-collapsed"
              );
              const logoImg = document.getElementById("sidebar-logo-img");
              const logoText = document.getElementById("sidebar-logo-text");
              function updateUpgradeSection() {
                if (sidebar.classList.contains("sidebar-collapsed")) {
                  upgradeExpanded.style.display = "none";
                  upgradeBtnExpanded.style.display = "none";
                  upgradeBtnCollapsed.classList.remove("hidden");
                } else {
                  upgradeExpanded.style.display = "";
                  upgradeBtnExpanded.style.display = "";
                  upgradeBtnCollapsed.classList.add("hidden");
                }
              }
              function updateLogo() {
                if (sidebar.classList.contains("sidebar-collapsed")) {
                  logoImg.classList.remove("hidden");
                  logoText.classList.add("hidden");
                } else {
                  logoImg.classList.add("hidden");
                  logoText.classList.remove("hidden");
                }
              }
              updateUpgradeSection();
              updateLogo();
              const sidebarToggle = document.getElementById("sidebar-toggle");
              sidebarToggle.addEventListener("click", function () {
                setTimeout(() => {
                  updateUpgradeSection();
                  updateLogo();
                }, 310);
              });
              window.addEventListener("resize", function () {
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
        <!-- Header (copied from home) -->
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
                <button
                  class="bg-primary text-white py-2 px-4 rounded-button whitespace-nowrap hover:bg-blue-600 transition-colors">
                  <span>Upgrade</span>
                </button>
              </a>
              <div class="relative">
                <button class="flex items-center space-x-2">
                  <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                    <img
                      src="<?= htmlspecialchars($avatarUrl) ?>"
                      alt="User avatar" class="w-full h-full object-cover" />
                  </div>
                </button>
              </div>
            </div>
          </div>
        </header>
        <!-- Main Dashboard Content -->
        <div class="p-6 max-w-7xl mx-auto">
          
<div class="mb-6">
    <h1 class="text-2xl font-bold text-primary mb-2" style="color: #1E3A8A;">Payments</h1>
    <p class="text-gray-600">
        Manage your <span class="font-bold" style="color: #1E3A8A;">subscription</span> plans and <span class="font-bold" style="color: #1E3A8A;">payment</span> methods.
    </p>
</div>
<!-- Plan Selection Section -->
<section class="mb-8">
    <div class="flex items-center justify-between mb-6 px-2 sm:px-0">
        <h2 class="text-xl font-semibold text-gray-900">Select a Plan</h2>
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">Monthly</span>
            <label class="toggle">
                <input type="checkbox" id="billing-toggle" />
                <span class="slider"></span>
            </label>
            <span class="text-sm text-gray-600">Yearly <span class="text-xs text-primary">(Save 20%)</span></span>
        </div>
    </div>
    <div id="plan-container" class="flex gap-4 overflow-x-auto pb-2 -mx-2 px-2 md:grid md:grid-cols-3 md:gap-6 md:overflow-x-visible md:mx-0 md:px-0" style="scrollbar-width: thin; -webkit-overflow-scrolling: touch;">
        <!-- Plan cards will be dynamically inserted here -->
    </div>
</section>
<!-- Billing Summary Panel -->
<section class="mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Billing Summary</h2>
    <div class="glassmorphism bg-white rounded-lg border border-gray-200 p-6 relative">
        <button id="edit-plan-btn" class="absolute top-6 right-6 text-sm font-medium text-primary hover:text-primary/80 flex items-center whitespace-nowrap" type="button">
            <div class="w-4 h-4 flex items-center justify-center mr-1">
                <i class="ri-edit-line"></i>
            </div>
            Edit Plan
        </button>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Selected Plan</h3>
                    <div class="flex items-center">
                        <div class="w-5 h-5 flex items-center justify-center text-primary mr-2">
                            <i class="ri-rocket-line"></i>
                        </div>
                        <span id="billing-plan-name" class="text-base font-medium text-gray-900">Pro Plan ($50/mo)</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Payment Method</h3>
                    <div class="flex items-center">
                        <div class="w-5 h-5 flex items-center justify-center text-primary mr-2">
                            <i class="ri-bank-card-line"></i>
                        </div>
                        <span class="text-base font-medium text-gray-900">TRC-20 USDT</span>
                    </div>
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Billing Cycle</h3>
                    <div class="flex items-center">
                        <div class="w-5 h-5 flex items-center justify-center text-primary mr-2">
                            <i class="ri-calendar-line"></i>
                        </div>
                        <span id="billing-cycle" class="text-base font-medium text-gray-900">Monthly (Renews every 30 days)</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Next Payment Date</h3>
                    <div class="flex items-center">
                        <div class="w-5 h-5 flex items-center justify-center text-primary mr-2">
                            <i class="ri-calendar-line"></i>
                        </div>
                        <span id="billing-next-date" class="text-base font-medium text-gray-900"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <!-- Save Wallet Form -->
              <section>
              <h2 class="text-lg font-semibold text-gray-800 mb-4">
    TRC-20 USDT Payment Wallet
  </h2>

  <div class="glassmorphism bg-white rounded-lg border border-gray-200 p-6">
    <?php if ($wallet): ?>
      <form id="wallet-form">
        <div class="space-y-5">
          <div>
            <label for="wallet-name" class="block text-sm font-medium text-gray-700">
              Network
            </label>
            <input type="text" id="wallet-name" value="<?= htmlspecialchars($wallet['network']) ?>" disabled class="w-full px-4 py-2 border rounded-md bg-gray-100 cursor-not-allowed" />
          </div>

          <div>
            <label for="wallet-currency" class="block text-sm font-medium text-gray-700">
              Currency
            </label>
            <input type="text" id="wallet-currency" value="<?= htmlspecialchars($wallet['currency']) ?>" disabled class="w-full px-4 py-2 border rounded-md bg-gray-100 cursor-not-allowed" />
          </div>

          <div>
            <label for="wallet-address" class="block text-sm font-medium text-gray-700">
              Wallet Address
            </label>
            <div class="flex items-center space-x-2">
              <input type="text" id="wallet-address" value="<?= htmlspecialchars($wallet['wallet_address']) ?>" readonly class="w-full px-4 py-2 border rounded-md bg-gray-50" />
              <button type="button" onclick="copyWalletAddress()" class="px-3 py-2 text-sm font-medium bg-blue-500 text-white rounded hover:bg-blue-600">
                Copy
              </button>
            </div>
          </div>

          <?php if (!empty($wallet['instructions'])): ?>
          <div class="text-sm text-gray-700 mt-4">
            <?= nl2br(htmlspecialchars($wallet['instructions'])) ?>
          </div>
          <?php endif; ?>
        </div>
      </form>
    <?php else: ?>
      <p class="text-red-600 text-center">No active TRC-20 USDT wallet found.</p>
    <?php endif; ?>
  </div>
  <script>
function copyWalletAddress() {
  const walletInput = document.getElementById("wallet-address");
  walletInput.select();
  walletInput.setSelectionRange(0, 99999);
  document.execCommand("copy");

  alert("Wallet address copied to clipboard!");
}
</script>
              
            <!-- Transaction History Section: Improved Layout & "View Full Transaction History" Button -->
            <div class="mt-8">
              <h2 class="text-lg font-semibold text-gray-800 mb-4">
                Transaction History
              </h2>
              <div class="glassmorphism bg-white rounded-xl border border-gray-200 p-0 shadow-lg overflow-hidden">
                <!-- Desktop Table Header -->
                <div class="hidden sm:block bg-white border-b border-gray-200">
                  <div class="grid grid-cols-6 gap-4 p-6 text-sm font-semibold text-gray-900">
                    <div>Date & Time</div>
                    <div>TXID</div>
                    <div>Payment Type</div>
                    <div>Amount</div>
                    <div>Status</div>
                    <div>Actions</div>
                  </div>
                </div>
                <!-- Transaction Items (up to 3, always visible) -->
                <div id="pay-tx-list" class="divide-y divide-gray-200"></div>
                <!-- Mobile Card List -->
                <div id="pay-tx-mobile-list" class="sm:hidden"></div>
                <!-- Empty State -->
                <div id="pay-tx-empty" class="flex flex-col items-center justify-center py-12 text-gray-500 hidden">
                  <i class="ri-file-search-line text-5xl mb-4 text-gray-300"></i>
                  <span class="text-lg font-medium">No transactions found</span>
                </div>
                <!-- View Full History Button -->
                <div class="flex justify-end p-4 bg-gray-50 border-t">
                  <a href="Dashboard-his.html">
                    <button class="bg-primary text-white px-6 py-2 rounded-button hover:bg-blue-700 transition-all flex items-center gap-2">
                      <i class="ri-file-list-3-line"></i>
                      View Full Transaction History
                    </button>
                  </a>
                </div>
              </div>
              <!-- Transaction Details Modal (matches Dashboard-his.html) -->
              <div
                id="pay-tx-modal"
                class="hidden fixed inset-0 bg-gray-900/40 backdrop-blur-[3px] z-50 flex items-center justify-center p-4"
              >
                <div class="bg-white rounded-2xl max-w-md w-full p-6 fade-in shadow-2xl border border-gray-200">
                  <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">Transaction Details</h3>
                    <button
                      onclick="document.getElementById('pay-tx-modal').classList.add('hidden')"
                      class="bg-gray-100 hover:bg-gray-200 rounded-button p-2 transition-all !rounded-button"
                    >
                      <div class="w-5 h-5 flex items-center justify-center">
                        <i class="ri-close-line"></i>
                      </div>
                    </button>
                  </div>
                  <div id="pay-tx-modal-content" class="space-y-4"></div>
                  <div class="flex gap-3 mt-6">
                    <button
                    onclick="downloadReceipt()"
                      class="flex-1 bg-primary hover:bg-primary/80 text-white px-4 py-3 rounded-button font-medium transition-all whitespace-nowrap !rounded-button"
                    >
                      Download Receipt
                    </button>

                    <button
                      onclick="document.getElementById('pay-tx-modal').classList.add('hidden')"
                      class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-900 px-4 py-3 rounded-button font-medium transition-all whitespace-nowrap !rounded-button"
                    >
                      Close
                    </button>
                  </div>
                </div>
              </div>
<script>
document.addEventListener("DOMContentLoaded", function () {
  fetch("fetch_transactions.php")
    .then((response) => response.json())
    .then((data) => {
      const desktopList = document.getElementById("pay-tx-list");
      const mobileList = document.getElementById("pay-tx-mobile-list");
      const emptyState = document.getElementById("pay-tx-empty");

      if (data.success && data.data.length > 0) {
        desktopList.innerHTML = "";
        mobileList.innerHTML = "";
        emptyState.classList.add("hidden");

        data.data.forEach((tx) => {
          const statusClasses = {
            success: "text-green-700 bg-green-100",
            failed: "text-red-700 bg-red-100",
            pending: "text-yellow-700 bg-yellow-100",
          };
          const statusClass = statusClasses[tx.status] || "bg-gray-100 text-gray-800";

          // Desktop row
          const row = document.createElement("div");
          row.className = "grid grid-cols-6 gap-4 p-6 text-sm text-gray-700";
          row.innerHTML = `
            <div>${tx.created_at}</div>
            <div>${tx.txid}</div>
            <div>${tx.payment_type}</div>
            <div>$${tx.amount}</div>
            <div><span class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}">${tx.status}</span></div>
            <div>
              <button
                class="text-blue-600 hover:underline font-medium"
                onclick='showTransactionDetails(${JSON.stringify(tx)})'
              >
                View
              </button>
            </div>
          `;
          desktopList.appendChild(row);

          // Mobile card
          const card = document.createElement("div");
          card.className = "p-4 border-b border-gray-200";
          card.innerHTML = `
            <div class="flex justify-between mb-2">
              <span class="text-sm text-gray-500">${tx.created_at}</span>
              <span class="text-xs ${statusClass} px-2 py-1 rounded-full">${tx.status}</span>
            </div>
            <div class="text-gray-700 font-medium text-sm mb-1">${tx.txid}</div>
            <div class="text-sm text-gray-600">Payment Type: ${tx.payment_type}</div>
            <div class="text-sm text-gray-600 mb-2">Amount: $${tx.amount}</div>
            <button
              class="text-blue-600 text-sm hover:underline font-medium"
              onclick='showTransactionDetails(${JSON.stringify(tx)})'
            >
              View Details
            </button>
          `;
          mobileList.appendChild(card);
        });
      } else {
        desktopList.innerHTML = "";
        mobileList.innerHTML = "";
        emptyState.classList.remove("hidden");
      }
    })
    .catch((error) => {
      console.error("Error fetching transaction history:", error);
    });
});

function showTransactionDetails(tx) {
  const modal = document.getElementById("pay-tx-modal");
  const content = document.getElementById("pay-tx-modal-content");

  content.innerHTML = `
    <div class="grid grid-cols-2 gap-3 text-sm">
      <div>
        <p class="text-gray-500">Transaction ID</p>
        <p class="font-medium text-gray-900">${tx.txid}</p>
      </div>
      <div>
        <p class="text-gray-500">Payment Type</p>
        <p class="font-medium text-gray-900">${tx.payment_type}</p>
      </div>
      <div>
        <p class="text-gray-500">Amount</p>
        <p class="font-medium text-gray-900">$${tx.amount}</p>
      </div>
      <div>
        <p class="text-gray-500">Status</p>
        <p class="font-medium text-gray-900 capitalize">${tx.status}</p>
      </div>
      <div class="col-span-2">
        <p class="text-gray-500">Date</p>
        <p class="font-medium text-gray-900">${tx.created_at}</p>
      </div>
    </div>
  `;

  // Store transaction in modal dataset
  modal.dataset.txid = tx.txid;
  modal.dataset.payment_type = tx.payment_type;
  modal.dataset.amount = tx.amount;
  modal.dataset.status = tx.status;
  modal.dataset.created_at = tx.created_at;

  modal.classList.remove("hidden");
}

function downloadReceipt() {
  const modal = document.getElementById("pay-tx-modal");
  const txid = modal.dataset.txid;
  const payment_type = modal.dataset.payment_type;
  const amount = modal.dataset.amount;
  const status = modal.dataset.status;
  const created_at = modal.dataset.created_at;

  const receiptText = `
Transaction Receipt
--------------------------
Transaction ID: ${txid}
Payment Type: ${payment_type}
Amount: $${amount}
Status: ${status}
Date: ${created_at}
  `.trim();

  const blob = new Blob([receiptText], { type: "text/plain" });
  const url = URL.createObjectURL(blob);

  const a = document.createElement("a");
  a.href = url;
  a.download = `${txid}_receipt.txt`;
  a.click();

  URL.revokeObjectURL(url);
}
</script>



            </div>
            </section>

            

            <!-- Right Column -->
            <div class="space-y-8">
              <!-- Subscriptions Control Panel -->
              <section>
              <h2 class="text-lg font-semibold text-gray-800 mb-4">
                Subscription Settings
              </h2>
              <div class="glassmorphism bg-white rounded-lg border border-gray-200 p-6">
                <div class="space-y-5">
                <div class="flex items-center justify-between">
                  <div>
                  <h3 class="text-sm font-medium text-gray-800">
                    Auto Renew Subscription
                  </h3>
                  <p class="text-xs text-gray-500 mt-0.5">
                    Automatically renew your subscription when it expires
                  </p>
                  </div>
                  <label class="toggle">
                  <input type="checkbox" id="auto-renew" checked />
                  <span class="slider"></span>
                  </label>
                </div>
                <div class="flex items-center justify-between">
                  <div>
                  <h3 class="text-sm font-medium text-gray-800">
                    Receive Email Invoices
                  </h3>
                  <p class="text-xs text-gray-500 mt-0.5">
                    Get invoice copies to your email after each payment
                  </p>
                  </div>
                  <label class="toggle">
                  <input type="checkbox" id="email-invoices" checked />
                  <span class="slider"></span>
                  </label>
                </div>
                <div class="flex items-center justify-between">
                  <div>
                  <h3 class="text-sm font-medium text-gray-800">
                    Enable Alerts for Failed Payments
                  </h3>
                  <p class="text-xs text-gray-500 mt-0.5">
                    Get notified when a payment fails to process
                  </p>
                  </div>
                  <label class="toggle">
                  <input type="checkbox" id="payment-alerts" checked />
                  <span class="slider"></span>
                  </label>
                </div>
                </div>
              </div>
              </section>
              <!-- QR Code Payment Support -->
              <section>
              <h2 class="text-lg font-semibold text-gray-800 mb-4">
                Quick Connect
              </h2>
              <div id="qr-section" class="glassmorphism bg-white rounded-lg border border-gray-200 p-6 qr-disabled">
                <div class="flex flex-col items-center">
                <!-- QR Payment Plan Selection -->
                <div class="mb-6 w-full">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                  Select Plan for QR Payment
                  </label>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                  <button type="button"
                    class="qr-plan-btn border border-gray-300 rounded-lg px-4 py-3 flex flex-col items-center hover:border-primary focus:outline-none transition-all"
                    data-plan="basic" disabled>
                    <span class="font-semibold text-gray-900 mb-1">Basic</span>
                    <span class="text-xs text-gray-500">USDT 20/mo</span>
                  </button>
                  <button type="button"
                    class="qr-plan-btn border border-gray-300 rounded-lg px-4 py-3 flex flex-col items-center hover:border-primary focus:outline-none transition-all"
                    data-plan="pro" disabled>
                    <span class="font-semibold text-gray-900 mb-1">Pro</span>
                    <span class="text-xs text-gray-500">USDT 50/mo</span>
                  </button>
                  <button type="button"
                    class="qr-plan-btn border border-gray-300 rounded-lg px-4 py-3 flex flex-col items-center hover:border-primary focus:outline-none transition-all"
                    data-plan="enterprise" disabled>
                    <span class="font-semibold text-gray-900 mb-1">Enterprise</span>
                    <span class="text-xs text-gray-500">USDT 100/mo</span>
                  </button>
                  </div>
                </div>
                <div class="w-48 h-48 bg-gray-100 rounded-lg flex items-center justify-center mb-4 relative">
                  <img id="qr-img"
                  src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=TRC20%20USDT%20Wallet%20Address%3A%20TVTkZq3ghDZgF8txTdDM3s8g76XM2pgiey"
                  alt="QR Code" class="w-40 h-40 object-contain" />
                  <div class="absolute inset-0 flex items-center justify-center bg-white/80 rounded-lg hidden"
                  id="qr-loading">
                  <div class="w-8 h-8 flex items-center justify-center text-primary animate-spin">
                    <i class="ri-loader-4-line ri-2x"></i>
                  </div>
                  </div>
                </div>
                <h3 class="text-sm font-medium text-gray-800 mb-1">
                  Scan to Connect Wallet
                </h3>
                <p class="text-xs text-gray-500 text-center mb-4">
                  Use your mobile wallet app to scan this QR code and
                  connect instantly
                </p>
                <!-- QR Payment Plan Summary -->
                <div class="mb-3 w-full">
                  <label class="block text-xs text-gray-500 mb-1">Selected Plan</label>
                  <span class="block font-mono text-xs text-gray-800" id="qr-selected-plan">
                  None
                  </span>
                </div>
                <div class="mb-3 w-full">
                  <label class="block text-xs text-gray-500 mb-1">Amount (USDT)</label>
                  <span class="block font-mono text-lg text-gray-900 font-bold" id="qr-amount">
                  0
                  </span>
                </div>
                <div class="mb-3 w-full">
                  <label class="block text-xs text-gray-500 mb-1">Order ID</label>
                  <span class="block font-mono text-xs text-primary font-semibold" id="qr-order-id">
                  ORD-QR-0
                  </span>
                </div>
                <form id="qr-txid-form" class="w-full mt-2">
                  <label for="qr-txid" class="block text-xs text-gray-500 mb-1">Paste your Transaction ID
                  (TXID)</label>
                  <input type="text" id="qr-txid" name="qr-txid"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm mb-3 font-mono"
                  placeholder="Paste your TXID here" required autocomplete="off" disabled />
                  <button type="submit" id="qr-txid-btn"
                  class="w-full py-3 text-sm font-semibold bg-primary text-white rounded-button hover:bg-primary/90 transition-colors mb-2"
                  disabled>
                  I've Sent the Payment
                  </button>
                </form>
                <div id="qr-txid-success" class="hidden mt-2 text-green-600 text-center text-sm font-medium">
                  Thank you! Your payment is being verified.
                </div>
                </div>
              </div>
              </section>

             
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dual Payment Modal (Crypto & Card) -->
    <div id="dual-payment-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      style="display:none;">
      <div class="bg-white rounded-xl p-4 max-w-sm w-full mx-2 relative shadow-xl fintech-modal">
      <button class="absolute top-3 right-3 text-gray-400 hover:text-gray-600" id="dual-modal-close">
        <i class="ri-close-line ri-lg"></i>
      </button>
      <h3 class="text-lg font-semibold text-gray-900 mb-3 text-center">Choose Payment Method</h3>
      <div class="flex gap-2 mb-4">
        <button id="dual-tab-crypto"
        class="flex-1 py-2 rounded-lg border border-primary bg-primary text-white font-medium transition-colors">Crypto</button>
        <button id="dual-tab-card"
        class="flex-1 py-2 rounded-lg border border-gray-300 text-gray-400 font-medium transition-colors opacity-50 cursor-not-allowed relative group"
        disabled>
        Card
        <div
          class="absolute right-[-2rem] bottom-[-3.5rem] z- flex items-center px-3 py-2 rounded-xl shadow-lg glass-effect-card-toast border border-primary bg-primary/80 text-base font-bold whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200"
          style="color: #1E293B;">
          Coming Soon!
        </div>
        <style>
          .glass-effect-card-toast {
          background: rgba(66, 82, 223, 0.85);
          border-radius: 16px;
          border: 2px solid #1E3A8A;
          color: #1E293B;
          font-weight: 600;
          box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
          }
          @media (max-width: 480px) {
          #dual-tab-card .absolute.glass-effect-card-toast {
            right: -2rem !important;
            bottom: -3.5rem !important;
            width: 30vw !important;
            font-size: 0.95rem !important;
            text-align: center !important;
            color: #1a202c !important;
            backdrop-filter: blur(5px) !important;
          }
          .fintech-modal {
            backdrop-filter: blur(5px) !important;
            background: rgba(255, 255, 255, 0.85) !important;
            color: #1a202c !important;
          }
          #dual-tab-card .absolute.glass-effect-card-toast {
            background: rgba(73, 104, 241, 0.85) !important;
            color: #0b0d0f !important;
          }
          }
        </style>
        </button>
      </div>
      <!-- Crypto Payment Panel -->
      <div id="dual-crypto-panel">
        <div class="flex flex-col items-center mb-3">
        <div class="w-28 h-28 bg-gray-100 rounded-lg flex items-center justify-center mb-2 border border-gray-200">
          <img id="dual-crypto-qr"
          src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=TRC20%20USDT%20Wallet%20Address%3A%20TVTkZq3ghDZgF8txTdDM3s8g76XM2pgiey"
          alt="TRC-20 Wallet QR" class="w-24 h-24 object-contain" />
        </div>
        <div class="w-full">
          <div class="mb-1">
          <span class="block text-xs text-gray-500">Order ID</span>
          <span class="block font-mono text-xs text-primary font-semibold" id="dual-crypto-orderid"></span>
          </div>
          <div class="mb-1">
          <span class="block text-xs text-gray-500">Amount (USDT)</span>
          <span class="block font-mono text-base text-gray-900 font-bold" id="dual-crypto-amount"></span>
          </div>
          <div class="mb-1">
          <span class="block text-xs text-gray-500">TRC-20 Wallet Address</span>
          <span class="block font-mono text-xs text-gray-800"
            id="dual-crypto-wallet">TVTkZq3ghDZgF8txTdDM3s8g76XM2pgiey</span>
          </div>
        </div>
        </div>
        <form id="dual-crypto-form" class="mt-1">
        <label for="dual-crypto-txid" class="block text-xs text-gray-500 mb-1">Paste your Transaction ID
          (TXID)</label>
        <input type="text" id="dual-crypto-txid" name="dual-crypto-txid"
          class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm mb-2 font-mono"
          placeholder="Paste your TXID here" required autocomplete="off" />
          <button type="button" id="dual-crypto-btn"
          class="w-full py-2 text-sm font-semibold bg-primary text-white rounded-button hover:bg-primary/90 transition-colors mb-1">
          I've Sent the Payment
          </button>
          </form>
          <!-- Loading Modal -->
          <div id="modal-loading" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50" style="display:none;">
          <div class="bg-white rounded-xl p-4 max-w-xs w-full mx-2 relative shadow-xl fintech-modal flex flex-col items-center">
          <div class="w-10 h-10 flex items-center justify-center mb-2">
            <i class="ri-loader-4-line text-2xl text-primary animate-spin"></i>
          </div>
          <span class="text-sm text-gray-700 font-medium">Processing...</span>
          </div>
          </div>
          <!-- Proof of Payment Modal -->
          <div id="proof-modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50" style="display:none;">
          <div class="bg-white rounded-xl p-4 max-w-xs w-full mx-2 relative shadow-xl fintech-modal">
          <button class="absolute top-2 right-2 text-gray-400 hover:text-gray-600" id="proof-modal-close" type="button">
            <i class="ri-close-line ri-lg"></i>
          </button>
          <h4 class="text-base font-semibold text-gray-900 mb-2 text-center">Upload Proof of Payment</h4>
          <form id="proof-upload-form" class="flex flex-col items-center space-y-3">
            <input type="file" id="proof-file" accept="image/*,application/pdf" class="block w-full text-xs text-gray-600 mb-1" required />
            <span id="proof-file-name" class="text-xs text-gray-500"></span>
            <button type="submit" id="finish-payment-btn"
            class="w-full py-1 text-xs font-semibold bg-primary text-white rounded-button hover:bg-primary/90 transition-colors">
            Finish payment
            </button>
          </form>
          </div>
          </div>
          <!-- Payment Complete Modal -->
          <div id="payment-complete-modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50" style="display:none;">
          <div class="bg-white rounded-xl p-4 max-w-xs w-full mx-2 relative shadow-xl fintech-modal text-center">
          <div class="w-10 h-10 mx-auto mb-2 flex items-center justify-center rounded-full bg-green-100">
          <i class="ri-checkbox-circle-line text-2xl text-green-600"></i>
          </div>
          <h4 class="text-base font-semibold text-gray-900 mb-1">Payment Completed</h4>
          <p class="text-xs text-gray-700 mb-1">Your payment is completed and will be reviewed within 24 hours.</p>
          <button type="button" id="payment-complete-close"
          class="mt-1 px-3 py-1 text-xs font-medium bg-primary text-white rounded-button hover:bg-primary/90 transition-colors">
          Close
          </button>
          </div>
          </div>
          <script>
          (function () {
          // Elements
          const cryptoForm = document.getElementById("dual-crypto-form");
          const cryptoBtn = document.getElementById("dual-crypto-btn");
          const proofModal = document.getElementById("proof-modal");
          const proofModalClose = document.getElementById("proof-modal-close");
          const proofUploadForm = document.getElementById("proof-upload-form");
          const proofFile = document.getElementById("proof-file");
          const proofFileName = document.getElementById("proof-file-name");
          const finishPaymentBtn = document.getElementById("finish-payment-btn");
          const paymentCompleteModal = document.getElementById("payment-complete-modal");
          const paymentCompleteClose = document.getElementById("payment-complete-close");
          const dualPaymentModal = document.getElementById("dual-payment-modal");
          const modalLoading = document.getElementById("modal-loading");
          if (!cryptoForm) return;

          // Utility: show/hide loading modal with delay
          function showLoading(duration, callback) {
          modalLoading.style.display = "flex";
          setTimeout(() => {
            modalLoading.style.display = "none";
            if (callback) callback();
          }, duration);
          }

          // Show proof modal on button click, with loading
          cryptoBtn.addEventListener("click", function (e) {
          e.preventDefault();
          showLoading(900, () => {
            proofModal.style.display = "flex";
          });
          });

          // Close proof modal
          if (proofModalClose) {
          proofModalClose.onclick = function () {
            proofModal.style.display = "none";
          };
          }
          // Dismiss proof modal on background click
          proofModal.onclick = function (e) {
          if (e.target === proofModal) proofModal.style.display = "none";
          };

          // Show selected file name
          if (proofFile) {
          proofFile.addEventListener("change", function () {
            proofFileName.textContent = proofFile.files.length ? proofFile.files[0].name : "";
          });
          }

          // Handle proof upload form submit, with loading
          if (proofUploadForm) {
          proofUploadForm.addEventListener("submit", function (e) {
            e.preventDefault();
            proofModal.style.display = "none";
            showLoading(1200, () => {
            paymentCompleteModal.style.display = "flex";
            });
          });
          }

          // Close payment complete modal
          if (paymentCompleteClose) {
          paymentCompleteClose.onclick = function () {
            paymentCompleteModal.style.display = "none";
            if (dualPaymentModal) dualPaymentModal.style.display = "none";
          };
          }
          // Dismiss payment complete modal on background click
          paymentCompleteModal.onclick = function (e) {
          if (e.target === paymentCompleteModal) paymentCompleteModal.style.display = "none";
          if (dualPaymentModal) dualPaymentModal.style.display = "none";
          };
          })();
          </script>
        </form>
        <div id="dual-crypto-success" class="hidden mt-1 text-green-600 text-center text-xs font-medium">
        Thank you! Your payment is being verified.
        </div>
      </div>
      <!-- Card Payment Panel -->
      <div id="dual-card-panel" class="hidden">
        <form id="dual-card-form" class="space-y-2">
        <div>
          <label for="dual-card-number" class="block text-xs text-gray-500 mb-1">Card Number</label>
          <input type="text" id="dual-card-number" maxlength="19" inputmode="numeric"
          class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm font-mono"
          placeholder="1234 5678 9012 3456" required autocomplete="cc-number" />
        </div>
        <div class="flex gap-1">
          <div class="flex-1">
          <label for="dual-card-exp" class="block text-xs text-gray-500 mb-1">Expiry</label>
          <input type="text" id="dual-card-exp" maxlength="5" inputmode="numeric"
            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm font-mono"
            placeholder="MM/YY" required autocomplete="cc-exp" />
          </div>
          <div class="flex-1">
          <label for="dual-card-cvc" class="block text-xs text-gray-500 mb-1">CVC</label>
          <input type="text" id="dual-card-cvc" maxlength="4" inputmode="numeric"
            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm font-mono"
            placeholder="CVC" required autocomplete="cc-csc" />
          </div>
        </div>
        <div>
          <label for="dual-card-name" class="block text-xs text-gray-500 mb-1">Cardholder Name</label>
          <input type="text" id="dual-card-name"
          class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm"
          placeholder="Name on card" required autocomplete="cc-name" />
        </div>
        <button type="submit" id="dual-card-btn"
          class="w-full py-2 text-sm font-semibold bg-primary text-white rounded-button hover:bg-primary/90 transition-colors mb-1">
          Pay Now
        </button>
        </form>
        <div id="dual-card-success" class="hidden mt-1 text-green-600 text-center text-xs font-medium">
        Payment successful! Thank you.
        </div>
      </div>
      </div>
    </div>

    
    <script>
      (function () {
        // Modal elements
        const modal = document.getElementById("dual-payment-modal");
        const closeBtn = document.getElementById("dual-modal-close");
        const tabCrypto = document.getElementById("dual-tab-crypto");
        const tabCard = document.getElementById("dual-tab-card");
        const cryptoPanel = document.getElementById("dual-crypto-panel");
        const cardPanel = document.getElementById("dual-card-panel");
        // Crypto fields
        const cryptoOrderId = document.getElementById("dual-crypto-orderid");
        const cryptoAmount = document.getElementById("dual-crypto-amount");
        const cryptoWallet = document.getElementById("dual-crypto-wallet");
        const cryptoQr = document.getElementById("dual-crypto-qr");
        const cryptoForm = document.getElementById("dual-crypto-form");
        const cryptoBtn = document.getElementById("dual-crypto-btn");
        const cryptoSuccess = document.getElementById("dual-crypto-success");
        const cryptoTxid = document.getElementById("dual-crypto-txid");
        // Card fields
        const cardForm = document.getElementById("dual-card-form");
        const cardBtn = document.getElementById("dual-card-btn");
        const cardSuccess = document.getElementById("dual-card-success");

        // Error message elements
        let cryptoTxidError = document.createElement("div");
        cryptoTxidError.className = "text-xs text-red-600 mb-2 font-medium";
        cryptoTxidError.style.display = "none";
        cryptoTxid && cryptoTxid.parentNode.insertBefore(cryptoTxidError, cryptoTxid.nextSibling);

        // Hint message
        let cryptoTxidHint = document.createElement("div");
        cryptoTxidHint.className = "text-xs text-gray-500 mb-2";
        cryptoTxidHint.textContent = "TRON TXID is 64 hexadecimal characters, sometimes starts with 0x.";
        cryptoTxid && cryptoTxid.parentNode.insertBefore(cryptoTxidHint, cryptoTxid);

        // Show modal function (call this externally with plan info)
        window.showDualPaymentModal = function ({ orderId, amount, wallet }) {
          // Set crypto info
          cryptoOrderId.textContent = orderId || "";
          cryptoAmount.textContent = amount || "";
          cryptoWallet.textContent = wallet || "TVTkZq3ghDZgF8txTdDM3s8g76XM2pgiey";
          cryptoQr.src = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" +
            encodeURIComponent("TRC20 USDT Wallet Address: " + (wallet || "TVTkZq3ghDZgF8txTdDM3s8g76XM2pgiey"));
          // Reset forms
          cryptoForm.reset();
          cardForm.reset();
          cryptoSuccess.classList.add("hidden");
          cardSuccess.classList.add("hidden");
          cryptoBtn.disabled = true; // Disable by default until input
          cardBtn.disabled = true;
          if (cryptoTxid) {
            cryptoTxid.classList.remove("border-red-500", "ring-2", "ring-red-500/20");
            cryptoTxidError.style.display = "none";
          }
          // Show crypto tab by default
          tabCrypto.classList.add("bg-primary", "text-white", "border-primary");
          tabCrypto.classList.remove("bg-white", "text-primary", "border-gray-300");
          tabCard.classList.remove("bg-primary", "text-white", "border-primary");
          tabCard.classList.add("bg-white", "text-primary", "border-gray-300");
          cryptoPanel.classList.remove("hidden");
          cardPanel.classList.add("hidden");
          // Show modal
          modal.style.display = "flex";
        };

        // Tab switching
        tabCrypto.onclick = function () {
          tabCrypto.classList.add("bg-primary", "text-white", "border-primary");
          tabCrypto.classList.remove("bg-white", "text-primary", "border-gray-300");
          tabCard.classList.remove("bg-primary", "text-white", "border-primary");
          tabCard.classList.add("bg-white", "text-primary", "border-gray-300");
          cryptoPanel.classList.remove("hidden");
          cardPanel.classList.add("hidden");
        };
        tabCard.onclick = function () {
          tabCard.classList.add("bg-primary", "text-white", "border-primary");
          tabCard.classList.remove("bg-white", "text-primary", "border-gray-300");
          tabCrypto.classList.remove("bg-primary", "text-white", "border-primary");
          tabCrypto.classList.add("bg-white", "text-primary", "border-gray-300");
          cardPanel.classList.remove("hidden");
          cryptoPanel.classList.add("hidden");
        };

        // Close modal
        closeBtn.onclick = function () {
          modal.style.display = "none";
        };
        // Dismiss modal on background click
        modal.onclick = function (e) {
          if (e.target === modal) modal.style.display = "none";
        };

        // Crypto TXID validation
        if (cryptoTxid) {
          cryptoTxid.addEventListener("input", function () {
            const value = this.value.trim();
            // TRON TXID: usually 64 hex chars, can start with 0x or not
            const valid = /^([0-9a-fA-F]{64}|0x[0-9a-fA-F]{64})$/.test(value);
            if (!value) {
              cryptoTxidError.textContent = "Transaction ID (TXID) is required.";
              cryptoTxidError.style.display = "block";
              this.classList.remove("border-green-500", "ring-2", "ring-green-500/20");
              this.classList.add("border-red-500", "ring-2", "ring-red-500/20");
              cryptoBtn.disabled = true;
            } else if (!valid) {
              cryptoTxidError.textContent = "Invalid TXID format. Must be 64 hexadecimal characters (optionally starting with 0x).";
              cryptoTxidError.style.display = "block";
              this.classList.remove("border-green-500", "ring-2", "ring-green-500/20");
              this.classList.add("border-red-500", "ring-2", "ring-red-500/20");
              cryptoBtn.disabled = true;
            } else {
              cryptoTxidError.style.display = "none";
              this.classList.remove("border-red-500", "ring-2", "ring-red-500/20");
              this.classList.add("border-green-500", "ring-2", "ring-green-500/20");
              cryptoBtn.disabled = false;
            }
          });
        }

        // Crypto form logic
        cryptoForm.onsubmit = function (e) {
          e.preventDefault();
          if (cryptoTxid) {
            const value = cryptoTxid.value.trim();
            const valid = /^([0-9a-fA-F]{64}|0x[0-9a-fA-F]{64})$/.test(value);
            if (!value) {
              cryptoTxidError.textContent = "Transaction ID (TXID) is required.";
              cryptoTxidError.style.display = "block";
              cryptoTxid.classList.add("border-red-500", "ring-2", "ring-red-500/20");
              cryptoTxid.focus();
              cryptoBtn.disabled = true;
              return;
            }
            if (!valid) {
              cryptoTxidError.textContent = "Invalid TXID format. Must be 64 hexadecimal characters (optionally starting with 0x).";
              cryptoTxidError.style.display = "block";
              cryptoTxid.classList.add("border-red-500", "ring-2", "ring-red-500/20");
              cryptoTxid.focus();
              cryptoBtn.disabled = true;
              return;
            }
            cryptoTxidError.style.display = "none";
          }
          cryptoBtn.disabled = true;
          cryptoSuccess.classList.remove("hidden");
          setTimeout(() => { modal.style.display = "none"; }, 1800);
        };

        // Card validation error messages and hints
        function addCardValidation(input, hintText, errorText, validator) {
          let hint = document.createElement("div");
          hint.className = "text-xs text-gray-500 mb-1";
          hint.textContent = hintText;
          input.parentNode.insertBefore(hint, input);

          let error = document.createElement("div");
          error.className = "text-xs text-red-600 mb-2 font-medium";
          error.style.display = "none";
          input.parentNode.insertBefore(error, input.nextSibling);

          input.addEventListener("input", function () {
            const value = input.value.trim();
            if (!value) {
              error.textContent = errorText.required;
              error.style.display = "block";
              input.classList.remove("border-green-500", "ring-2", "ring-green-500/20");
              input.classList.add("border-red-500", "ring-2", "ring-red-500/20");
            } else if (!validator(value)) {
              error.textContent = errorText.invalid;
              error.style.display = "block";
              input.classList.remove("border-green-500", "ring-2", "ring-green-500/20");
              input.classList.add("border-red-500", "ring-2", "ring-red-500/20");
            } else {
              error.style.display = "none";
              input.classList.remove("border-red-500", "ring-2", "ring-red-500/20");
              input.classList.add("border-green-500", "ring-2", "ring-green-500/20");
            }
            cardBtn.disabled = !validateCardForm();
          });
        }

        function validateCardForm() {
          // Card number: 16 digits, allow spaces
          const cardNumber = document.getElementById("dual-card-number");
          const exp = document.getElementById("dual-card-exp");
          const cvc = document.getElementById("dual-card-cvc");
          const name = document.getElementById("dual-card-name");
          const cardNumberValid = /^\d{4} ?\d{4} ?\d{4} ?\d{4}$/.test(cardNumber.value.trim());
          const expValid = /^(0[1-9]|1[0-2])\/\d{2}$/.test(exp.value.trim());
          const cvcValid = /^\d{3,4}$/.test(cvc.value.trim());
          const nameValid = name.value.trim().length > 0;
          return cardNumberValid && expValid && cvcValid && nameValid;
        }

        // Card number
        addCardValidation(
          document.getElementById("dual-card-number"),
          "Enter 16-digit card number (spaces allowed).",
          {
            required: "Card number is required.",
            invalid: "Invalid card number format. Must be 16 digits."
          },
          v => /^\d{4} ?\d{4} ?\d{4} ?\d{4}$/.test(v)
        );
        // Expiry
        addCardValidation(
          document.getElementById("dual-card-exp"),
          "Format: MM/YY (e.g. 09/26).",
          {
            required: "Expiry date is required.",
            invalid: "Invalid expiry format. Use MM/YY."
          },
          v => /^(0[1-9]|1[0-2])\/\d{2}$/.test(v)
        );
        // CVC
        addCardValidation(
          document.getElementById("dual-card-cvc"),
          "3 or 4 digit code from your card.",
          {
            required: "CVC is required.",
            invalid: "Invalid CVC. Must be 3 or 4 digits."
          },
          v => /^\d{3,4}$/.test(v)
        );
        // Name
        addCardValidation(
          document.getElementById("dual-card-name"),
          "Enter the name as shown on your card.",
          {
            required: "Cardholder name is required.",
            invalid: "Name must be at least 1 character."
          },
          v => v.length > 0
        );

        // Card form logic (demo only, no real payment)
        cardForm.onsubmit = function (e) {
          e.preventDefault();
          if (!validateCardForm()) {
            cardBtn.disabled = true;
            return;
          }
          cardBtn.disabled = true;
          cardSuccess.classList.remove("hidden");
          setTimeout(() => { modal.style.display = "none"; }, 1800);
        };

        // Enable crypto button only if valid input
        if (cryptoTxid) {
          cryptoTxid.addEventListener("input", function () {
            const value = this.value.trim();
            const valid = /^([0-9a-fA-F]{64}|0x[0-9a-fA-F]{64})$/.test(value);
            cryptoBtn.disabled = !valid;
          });
        }
      })();
    </script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        // Sidebar toggle
        const sidebar = document.getElementById("sidebar");
        const mainContent = document.getElementById("main-content");
        const sidebarToggle = document.getElementById("sidebar-toggle");
        const sidebarTexts = document.querySelectorAll(".sidebar-text");
        const sidebarMobileClose = document.getElementById("sidebar-mobile-close");

        function isMobile() {
          return window.innerWidth < 768;
        }

        function openSidebar() {
          sidebar.classList.add("sidebar-expanded");
          sidebar.classList.remove("sidebar-collapsed");
          mainContent.classList.add("main-content-expanded");
          mainContent.classList.remove("main-content-collapsed");
          sidebarTexts.forEach((text) => (text.style.display = ""));
          if (isMobile()) {
            sidebar.style.transform = "translateX(0)";
            if (sidebarMobileClose) sidebarMobileClose.style.display = "";
          }
        }

        function closeSidebar() {
          sidebar.classList.remove("sidebar-expanded");
          sidebar.classList.add("sidebar-collapsed");
          mainContent.classList.remove("main-content-expanded");
          mainContent.classList.add("main-content-collapsed");
          sidebarTexts.forEach((text) => (text.style.display = "none"));
          if (isMobile()) {
            sidebar.style.transform = "translateX(-100%)";
            if (sidebarMobileClose) sidebarMobileClose.style.display = "none";
          }
        }

        function handleResize() {
          if (isMobile()) {
            closeSidebar();
            if (sidebarMobileClose) sidebarMobileClose.style.display = "";
          } else {
            openSidebar();
            if (sidebarMobileClose) sidebarMobileClose.style.display = "none";
          }
        }

        window.addEventListener("DOMContentLoaded", handleResize);
        window.addEventListener("resize", handleResize);

        sidebarToggle.addEventListener("click", function () {
          if (sidebar.classList.contains("sidebar-expanded")) {
            closeSidebar();
          } else {
            openSidebar();
          }
        });

        if (sidebarMobileClose) {
          sidebarMobileClose.addEventListener("click", closeSidebar);
        }
      });
    </script><!--
   <script id="plan-selection-script">
      document.addEventListener("DOMContentLoaded", function () {
        const planGetStartedBtns = document.querySelectorAll(".plan-get-started");
        const planCards = document.querySelectorAll(".plan-card");
        const billingToggle = document.getElementById("billing-toggle");
        const TRC20_ADDRESS = "TVTkZq3ghDZgF8txTdDM3s8g76XM2pgiey";
        const planDisplayNames = {
          "basic-plan": "Basic",
          "pro-plan": "Pro",
          "enterprise-plan": "Enterprise"
        };
        const planAmounts = {
          "basic-plan": 20,
          "pro-plan": 50,
          "enterprise-plan": 100
        };

        // QR section elements
        const qrSection = document.getElementById("qr-section");
        const qrSelectedPlan = document.getElementById("qr-selected-plan");
        const qrAmount = document.getElementById("qr-amount");
        const qrOrderId = document.getElementById("qr-order-id");
        const qrTxidInput = document.getElementById("qr-txid");
        const qrTxidBtn = document.getElementById("qr-txid-btn");
        const qrImg = document.getElementById("qr-img");

        // Helper: generate order ID
        function generateOrderId() {
          return "ORD-" + Date.now() + "-" + Math.floor(Math.random() * 10000);
        }

        // Helper: get price for plan (monthly/yearly)
        function getPlanPrice(planId, isYearly) {
          const base = planAmounts[planId];
          if (base == null) return { price: "Custom", suffix: "" };
          if (isYearly) {
            const yearly = (base * 12 * 0.8).toFixed(2);
            return { price: yearly, suffix: "/yr" };
          }
          return { price: base, suffix: "/mo" };
        }

        // Helper: enable QR section and update info
        function enableQrSection(planId, orderId, isYearly) {
          qrSection.classList.remove("qr-disabled");
          qrSection.classList.add("qr-enabled");
          qrTxidInput.disabled = false;
          qrTxidBtn.disabled = false;
          qrSelectedPlan.textContent = planDisplayNames[planId] || "Custom";
          const priceObj = getPlanPrice(planId, isYearly);
          qrAmount.textContent = priceObj.price === "Custom" ? "Custom" : priceObj.price;
          qrOrderId.textContent = orderId;
          qrImg.src = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" +
            encodeURIComponent("TRC20 USDT Wallet Address: " + TRC20_ADDRESS);
        }

        // Helper: disable QR section
        function disableQrSection() {
          qrSection.classList.remove("qr-enabled");
          qrSection.classList.add("qr-disabled");
          qrTxidInput.disabled = true;
          qrTxidBtn.disabled = true;
          qrSelectedPlan.textContent = "None";
          qrAmount.textContent = "0";
          qrOrderId.textContent = "ORD-QR-0";
          // Remove plan highlight
          planCards.forEach(card => card.classList.remove("selected"));
        }

        // Initially disable QR section
        disableQrSection();

        // Plan selection logic
        planGetStartedBtns.forEach(btn => {
          btn.addEventListener("click", function (e) {
            e.stopPropagation();
            const planId = btn.closest(".plan-card").id;
            const isYearly = billingToggle && billingToggle.checked;
            const orderId = generateOrderId();

            // Highlight selected plan card
            planCards.forEach(card => card.classList.remove("selected"));
            btn.closest(".plan-card").classList.add("selected");

            // Enable and update QR section
            enableQrSection(planId, orderId, isYearly);

            // Show the dual payment modal
            let amount = getPlanPrice(planId, isYearly).price;
            // If price is "Custom", set to empty string
            if (amount === "Custom") amount = "";
            window.showDualPaymentModal({
              orderId: orderId,
              amount: amount,
              wallet: TRC20_ADDRESS
            });
          });
        });

        // If user toggles billing, update prices on cards and QR section if active
        function updatePlanPrices(isYearly) {
          planCards.forEach((card) => {
            const id = card.id;
            const priceSpan = card.querySelector(".text-3xl.font-bold");
            const perSpan = card.querySelector(".text-gray-500.ml-1");
            if (!priceSpan) return;
            if (planAmounts[id] == null) {
              priceSpan.textContent = "Custom";
              if (perSpan) perSpan.textContent = "";
            } else if (isYearly) {
              const yearly = (planAmounts[id] * 12 * 0.8).toFixed(2);
              priceSpan.textContent = `$${yearly}`;
              if (perSpan) perSpan.textContent = "/yr";
            } else {
              priceSpan.textContent = `$${planAmounts[id]}`;
              if (perSpan) perSpan.textContent = "/mo";
            }
          });
          // If QR section is enabled, update its price
          if (qrSection.classList.contains("qr-enabled")) {
            const selectedCard = Array.from(planCards).find(card => card.classList.contains("selected"));
            if (selectedCard) {
              const planId = selectedCard.id;
              const orderId = qrOrderId.textContent;
              enableQrSection(planId, orderId, isYearly);
            }
          }
        }

        // Listen for billing toggle changes
        if (billingToggle) {
          billingToggle.addEventListener("change", function () {
            updatePlanPrices(this.checked);
          });
          updatePlanPrices(billingToggle.checked);
        }
      });
    </script>-->
    <script id="wallet-form-script">
      document.addEventListener("DOMContentLoaded", function () {
        const walletForm = document.getElementById("wallet-form");
        const walletAddress = document.getElementById("wallet-address");
        if (!walletForm || !walletAddress) return;
        walletForm.addEventListener("submit", function (e) {
          e.preventDefault();
          // Simple validation
          if (!walletAddress.value) {
            walletAddress.classList.add("border-red-500", "ring-2", "ring-red-500/20");
            return;
          }
          // Show success message
          const successMessage = document.createElement("div");
          successMessage.className =
            "fixed top-4 right-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg shadow-lg flex items-center z-50";
          successMessage.innerHTML = `
<div class="w-5 h-5 flex items-center justify-center text-green-500 mr-2">
<i class="ri-check-line"></i>
</div>
<span class="text-sm font-medium">Wallet saved successfully!</span>
`;
          document.body.appendChild(successMessage);
          // Reset form
          walletForm.reset();
          walletAddress.classList.remove(
            "border-red-500",
            "ring-2",
            "ring-red-500/20"
          );
          // Remove message after 3 seconds
          setTimeout(() => {
            successMessage.remove();
          }, 3000);
        });
        // Validate wallet address format
        walletAddress.addEventListener("input", function () {
          const value = this.value.trim();
          if (value && value.startsWith("0x") && value.length === 42) {
            this.classList.remove(
              "border-red-500",
              "ring-2",
              "ring-red-500/20"
            );
            this.classList.add(
              "border-green-500",
              "ring-2",
              "ring-green-500/20"
            );
          } else if (value) {
            this.classList.remove(
              "border-green-500",
              "ring-2",
              "ring-green-500/20"
            );
            this.classList.add("border-red-500", "ring-2", "ring-red-500/20");
          } else {
            this.classList.remove(
              "border-red-500",
              "ring-2",
              "ring-red-500/20"
            );
            this.classList.remove(
              "border-green-500",
              "ring-2",
              "ring-green-500/20"
            );
          }
        });
      });
    </script>
    <script id="qr-code-script">
      document.addEventListener("DOMContentLoaded", function () {
        const refreshQrBtn = document.getElementById("refresh-qr");
        const connectWalletBtn = document.getElementById("connect-wallet");
        const qrLoading = document.getElementById("qr-loading");
        let connectedWallet = null;
        function showNotification(message, type = "success") {
          const colors = {
            success: "green",
            info: "blue",
            error: "red",
          };
          const color = colors[type] || "green";
          const notification = document.createElement("div");
          notification.className = `fixed top-4 right-4 bg-${color}-50 border border-${color}-200 text-${color}-800 px-4 py-3 rounded-lg shadow-lg flex items-center z-50`;
          notification.innerHTML = `
    <div class="w-5 h-5 flex items-center justify-center text-${color}-500 mr-2">
      <i class="ri-${type === "success"
              ? "check"
              : type === "info"
                ? "information"
                : "error-warning"
            }-line"></i>
    </div>
    <span class="text-sm font-medium">${message}</span>
  `;

          document.body.appendChild(notification);
          setTimeout(() => notification.remove(), 3000);
        }
        if (refreshQrBtn && qrLoading) {
          refreshQrBtn.addEventListener("click", function () {
            qrLoading.classList.remove("hidden");
            setTimeout(() => {
              qrLoading.classList.add("hidden");
              showNotification("QR code refreshed!", "info");
            }, 1500);
          });
        }
        if (connectWalletBtn && qrLoading) {
          connectWalletBtn.addEventListener("click", function () {
            if (connectedWallet) {
              showNotification("Wallet already connected!", "info");
              return;
            }
            qrLoading.classList.remove("hidden");
            setTimeout(() => {
              qrLoading.classList.add("hidden");
              connectedWallet = {
                address: "0x742d35Cc6634C0532925a3b844Bc454e4438f44e",
                balance: {
                  ETH: "2.5",
                  BTC: "0.15",
                },
              };
              showNotification("Wallet connected successfully!");
              connectWalletBtn.innerHTML = `
      <div class="w-4 h-4 flex items-center justify-center mr-1">
        <i class="ri-checkbox-circle-line"></i>
      </div>
      Connected
    `;
              connectWalletBtn.classList.remove("bg-primary");
              connectWalletBtn.classList.add("bg-green-600");
            }, 2000);
          });
        }
      });
    </script>
   
    <script id="qr-payment-section-script">
document.addEventListener('DOMContentLoaded', function () {
    const planContainer = document.getElementById('plan-container');
    const billingToggle = document.getElementById('billing-toggle');
    const billingPlanName = document.getElementById('billing-plan-name');
    const billingCycle = document.getElementById('billing-cycle');
    const billingNextDate = document.getElementById('billing-next-date');
    const editPlanBtn = document.getElementById('edit-plan-btn');
    const qrSection = document.getElementById('qr-section');
    const qrSelectedPlan = document.getElementById('qr-selected-plan');
    const qrAmount = document.getElementById('qr-amount');
    const qrOrderId = document.getElementById('qr-order-id');
    const qrTxidInput = document.getElementById('qr-txid');
    const qrTxidBtn = document.getElementById('qr-txid-btn');
    const qrImg = document.getElementById('qr-img');
    const qrTxidForm = document.getElementById('qr-txid-form'); // Assumes you have this ID
    const TRC20_ADDRESS = 'TVTkZq3ghDZgF8txTdDM3s8g76XM2pgiey';

    let isYearly = billingToggle.checked;
    let selectedPlanId = 'pro';
    let plans = [];

    // Fetch plans
    async function fetchPlans() {
        try {
            const response = await fetch('payment.php');
            const data = await response.json();

            if (!data.success) {
                planContainer.innerHTML = `<p class="text-red-600 text-center">${data.message}</p>`;
                return false;
            }

            plans = data.plans;
            return true;
        } catch (err) {
            console.error('Fetch error:', err);
            planContainer.innerHTML = `<p class="text-red-600 text-center">Failed to load plans. Please try again later.</p>`;
            return false;
        }
    }

    function getPlanPrice(planId, yearly) {
        const plan = plans.find(p => p.plan_name.toLowerCase() === planId);
        if (!plan || plan.plan_name.toLowerCase() === 'enterprise') return { price: 'Custom', suffix: '' };
        const price = yearly ? plan.yearly_price : plan.monthly_price;
        return { price: `$${price.toFixed(2)}`, suffix: yearly ? '/yr' : '/mo' };
    }

    function renderPlans() {
        planContainer.innerHTML = '';
        plans.forEach(plan => {
            const price = isYearly ? plan.yearly_price : plan.monthly_price;
            const isEnterprise = plan.plan_name.toLowerCase() === 'enterprise';
            const priceText = isEnterprise ? 'Custom' : `$${price.toFixed(2)}`;
            const suffix = isEnterprise ? '' : (isYearly ? '/yr' : '/mo');
            const isActive = selectedPlanId === plan.plan_name.toLowerCase();

            const card = document.createElement('div');
            card.id = `${plan.plan_name.toLowerCase()}-plan`;
            card.className = `plan-card glassmorphism bg-white rounded-lg border p-6 cursor-pointer relative ${
                plan.is_popular ? 'border-2 border-primary' : 'border-gray-200'
            } ${isActive ? 'active border-2 border-primary' : ''}`;

            card.innerHTML = `
                ${plan.is_popular ? '<div class="absolute -top-3 right-4 bg-primary text-white text-xs font-medium px-3 py-1 rounded-full">POPULAR</div>' : ''}
                <div class="mb-4">
                    <h3 class="text-xl font-semibold text-gray-900">${plan.plan_name}</h3>
                    <p class="text-sm text-gray-500 mt-1">${plan.description}</p>
                </div>
                <div class="mb-6">
                    <div class="flex items-baseline">
                        <span class="text-3xl font-bold text-gray-900">${priceText}</span>
                        <span class="text-gray-500 ml-1">${suffix}</span>
                    </div>
                </div>
                <ul class="space-y-3 mb-8">
                    ${plan.features.map(f => `
                        <li class="flex items-start">
                            <div class="w-5 h-5 flex items-center justify-center text-primary mt-0.5">
                                <i class="ri-check-line"></i>
                            </div>
                            <span class="text-sm text-gray-700 ml-2">${f}</span>
                        </li>
                    `).join('')}
                </ul>
                <button class="w-full py-3 text-sm font-medium ${plan.is_popular ? 'bg-primary text-white hover:bg-primary/90' : 'text-primary border border-primary hover:bg-primary/5'} rounded-button transition-colors plan-get-started" data-plan="${plan.plan_name.toLowerCase()}">
                    ${isEnterprise ? 'Contact sales' : 'Get started'}
                </button>
            `;

            planContainer.appendChild(card);
        });
    }

    function updateBillingSummary() {
        const plan = plans.find(p => p.plan_name.toLowerCase() === selectedPlanId);
        const { price, suffix } = getPlanPrice(selectedPlanId, isYearly);
        const date = new Date();
        date.setDate(isYearly ? date.getDate() + 365 : date.getDate() + 30);

        billingPlanName.textContent = `${plan.plan_name} (${price}${suffix})`;
        billingCycle.textContent = isYearly ? 'Yearly (Renews every 12 months)' : 'Monthly (Renews every 30 days)';
        billingNextDate.textContent = date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function generateOrderId() {
        return `ORD-${Date.now()}-${Math.floor(Math.random() * 10000)}`;
    }

    function enableQrSection(planId, orderId) {
        const { price } = getPlanPrice(planId, isYearly);
        const plan = plans.find(p => p.plan_name.toLowerCase() === planId);
        qrSection.classList.remove('qr-disabled');
        qrSection.classList.add('qr-enabled');
        qrTxidInput.disabled = false;
        qrTxidBtn.disabled = false;
        qrSelectedPlan.textContent = plan.plan_name;
        qrAmount.textContent = price;
        qrOrderId.textContent = orderId;
        qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent('TRC20 USDT Wallet Address: ' + TRC20_ADDRESS)}`;
    }

    function disableQrSection() {
        qrSection.classList.remove('qr-enabled');
        qrSection.classList.add('qr-disabled');
        qrTxidInput.disabled = true;
        qrTxidBtn.disabled = true;
        qrSelectedPlan.textContent = 'None';
        qrAmount.textContent = '0';
        qrOrderId.textContent = 'ORD-QR-0';
    }

    // Setup event listeners
    billingToggle.addEventListener('change', () => {
        isYearly = billingToggle.checked;
        renderPlans();
        updateBillingSummary();
    });

    planContainer.addEventListener('click', async e => {
        const button = e.target.closest('.plan-get-started');
        if (!button) return;
        const plan = button.dataset.plan;
        if (plan === 'enterprise') {
            window.location.href = 'contact-sales.html';
            return;
        }

        selectedPlanId = plan;
        document.querySelectorAll('.plan-card').forEach(card => card.classList.remove('active', 'border-2', 'border-primary'));
        document.getElementById(`${plan}-plan`).classList.add('active', 'border-2', 'border-primary');

        updateBillingSummary();
        const orderId = generateOrderId();
        enableQrSection(plan, orderId);

        window.showDualPaymentModal({
            orderId,
            amount: getPlanPrice(plan, isYearly).price.replace('$', ''),
            wallet: TRC20_ADDRESS
        });

        try {
            await fetch('dash-pay.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `selected_plan=${encodeURIComponent(plan)}`
            });
        } catch (err) {
            console.error('Error submitting plan:', err);
        }
    });

    editPlanBtn?.addEventListener('click', () => {
        const section = document.querySelector('section.mb-8');
        section?.scrollIntoView({ behavior: 'smooth' });
    });

    qrTxidForm?.addEventListener('submit', e => {
        e.preventDefault();
        const txid = qrTxidInput.value.trim();
        const valid = /^([0-9a-fA-F]{64}|0x[0-9a-fA-F]{64})$/.test(txid);
        if (!valid) {
            qrTxidInput.classList.add('border-red-500', 'ring-2', 'ring-red-500/20');
            return;
        }

        qrTxidForm.classList.add('pointer-events-none', 'opacity-60');
        document.getElementById('qr-txid-success')?.classList.remove('hidden');
        setTimeout(() => {
            qrTxidForm.reset();
            qrTxidForm.classList.remove('pointer-events-none', 'opacity-60');
            document.getElementById('qr-txid-success')?.classList.add('hidden');
            disableQrSection();
        }, 2500);
    });

    // Init sequence
    (async () => {
        const loaded = await fetchPlans();
        if (!loaded) return;
        renderPlans();
        updateBillingSummary();
        disableQrSection();
    })();
});
</script>

   
  </body>

</html>