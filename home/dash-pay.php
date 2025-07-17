<?php
 require '../auth/auth_check.php';
$user_id = $_SESSION['user_id'];

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

}
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

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sales-Spy - Crypto Payment Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css"
    />
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
        background-color: #f9fafb; /* match home */
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
      input:checked + .slider {
        background-color: #2d7ff9;
      }
      input:checked + .slider:before {
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
      .custom-radio input[type="radio"]:checked + label:before {
        border-color: #2d7ff9;
      }
      .custom-radio input[type="radio"]:checked + label:after {
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
      .custom-checkbox input[type="checkbox"]:checked + label:before {
        background-color: #2d7ff9;
        border-color: #2d7ff9;
      }
      .custom-checkbox input[type="checkbox"]:checked + label:after {
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
        body, html {
          width: 100vw;
          overflow-x: hidden;
        }
      }
    </style>
  </head>
  <body>
    <div class="flex h-screen bg-gray-50">
      <!-- Sidebar -->
      <div
        id="sidebar"
        class="sidebar-expanded fixed h-full bg-white shadow-lg z-20 transition-all duration-700 ease-in-out"
      >
        <div class="flex flex-col h-full">
          <!-- Logo -->
          <div class="p-4 border-b flex items-center justify-center relative">
            <img
              src="https://res.cloudinary.com/dtrn8j0sz/image/upload/v1749075914/SS_s4jkfw.jpg"
              alt="Logo"
              id="sidebar-logo-img"
              class="w-8 h-8 mr-0 hidden"
            />
            <h1
              id="sidebar-logo-text"
              class="font-['Pacifico'] text-2xl text-primary"
            >
              Sales-Spy
            </h1>
            <!-- Mobile-only collapse button -->
            <button
              id="sidebar-mobile-close"
              class="absolute right-2 top-2 p-2 rounded-full hover:bg-gray-100 md:hidden"
              aria-label="Close sidebar"
            >
              <i class="ri-close-line text-xl"></i>
            </button>
          </div>
          <!-- Navigation -->
          <nav class="flex-1 overflow-y-auto py-4">
            <ul>
              <li class="mb-2">
                <a
                  href="index.php"
                   class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors"
                >
                  <div class="w-6 h-6 flex items-center justify-center mr-3">
                    <i class="ri-dashboard-line"></i>
                  </div>
                  <span class="sidebar-text">Dashboard</span>
                </a>
              </li>
              <li class="mb-2">
                <a
                  href="Dashboard-com.html"
                  class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors"
                >
                  <div class="w-6 h-6 flex items-center justify-center mr-3">
                    <i class="ri-global-line"></i>
                  </div>
                  <span class="sidebar-text">Websites</span>
                </a>
              </li>
              <li class="mb-2">
                <a
                  href="Dashboard-ecc.html"
                  class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors"
                >
                  <div class="w-6 h-6 flex items-center justify-center mr-3">
                    <i class="ri-shopping-cart-line"></i>
                  </div>
                  <span class="sidebar-text">E-commerce</span>
                </a>
              </li>

              <li class="mb-2">
                <a
                  href="#"
                  class="flex items-center px-4 py-3 text-primary bg-blue-50 rounded-r-lg border-l-4 border-primary"
                >
                  <div class="w-6 h-6 flex items-center justify-center mr-3">
                    <i class="ri-bank-card-line"></i>
                  </div>
                  <span class="sidebar-text">Payment</span>
                </a>
              </li>

              <li class="mb-2">
                <a
                  href="settings.php"
                  class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors"
                >
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
            <a href="Dashboard-pay.html">
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
      <div
        id="main-content"
        class="main-content-expanded flex-1 transition-all duration-700 ease-in-out"
      >
        <!-- Header  -->
        <header class="bg-white shadow-sm sticky top-0 z-10">
          <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center">
              <button
                id="sidebar-toggle"
                class="p-2 rounded-full hover:bg-gray-100 mr-4"
              >
                <div class="w-5 h-5 flex items-center justify-center">
                  <i class="ri-menu-line"></i>
                </div>
              </button>
            </div>
            <div class="flex items-center space-x-4">
              <div class="flex items-center bg-gray-100 rounded-full px-3 py-1">
                <div
                  class="w-5 h-5 flex items-center justify-center mr-2 text-primary"
                >
                  <i class="ri-coin-line"></i>
                </div>
                <span class="text-sm font-medium"><?= number_format($stats['credits_remaining']) ?> credits</span>
              </div>
              <a href="Dashboard-pay.html">
                <button
                  class="bg-primary text-white py-2 px-4 rounded-button whitespace-nowrap hover:bg-blue-600 transition-colors"
                >
                  <span>Upgrade</span>
                </button>
              </a>
              <div class="relative">
                <button class="flex items-center space-x-2">
                  <div
                    class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden"
                  >
                    <img
                      src="<?= htmlspecialchars($avatarUrl) ?>"
                      alt="User avatar"
                      class="w-full h-full object-cover"
                    />
                  </div>
                </button>
              </div>
            </div>
          </div>
        </header>
        <!-- Main Dashboard Content -->
        <div class="p-6 max-w-7xl mx-auto">
          <div class="mb-6">

            <h1 class="text-2xl font-bold text-primary mb-2" style="color: #1E3A8A;">Crypto Payment</h1>
            <p class="text-gray-600">
              Manage your <span class="font-bold " style="color: #1E3A8A;">subscription</span> plans and  <span class="font-bold " style="color: #1E3A8A;">payment</span>methods.
            </p>
            <h1 class="text-2xl font-bold text-gray-900">
              
            </h1>
            <p class="text-gray-500 mt-1">
              
            </p>
          </div>
          <!-- Plan Selection Section -->
          <section class="mb-8">
            <div class="flex items-center justify-between mb-6 px-2 sm:px-0">
              <h2 class="text-xl font-semibold text-gray-900">Select a Plan</h2>
               <!-- duration toggle section -->
              <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600">Monthly</span>
                <label class="toggle">
                  <input type="checkbox" id="billing-toggle" />
                  <span class="slider"></span>
                </label>
                <span class="text-sm text-gray-600"
                  >Yearly
                  <span class="text-xs text-primary">(Save 20%)</span></span
                >
              </div>
            </div>
            <div
              class="flex gap-4 overflow-x-auto pb-2 -mx-2 px-2 md:grid md:grid-cols-3 md:gap-6 md:overflow-x-visible md:mx-0 md:px-0"
              style="scrollbar-width: thin; -webkit-overflow-scrolling: touch;"
            >
              <!-- Plan Cards (wrap in glassmorphism for home style) -->
              <div
                id="basic-plan"
                class="plan-card glassmorphism bg-white rounded-lg border border-gray-200 p-6 cursor-pointer"
              >
                <div class="mb-4">
                  <h3 class="text-xl font-semibold text-gray-900">Basic</h3>
                  <p class="text-sm text-gray-500 mt-1">
                    Perfect for small businesses just getting started with lead
                    generation
                  </p>
                </div>
                <div class="mb-6">
                  <div class="flex items-baseline">
                    <span class="text-3xl font-bold text-gray-900">$115</span>
                    <span class="text-gray-500 ml-1">/mo</span>
                  </div>
                </div>
                <ul class="space-y-3 mb-8">
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >500 leads per month</span
                    >
                  </li>
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >Basic filtering options</span
                    >
                  </li>
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >Email and phone support</span
                    >
                  </li>
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >Weekly database updates</span
                    >
                  </li>
                </ul>
                <button
                  class="w-full py-3 text-sm font-medium text-primary border border-primary rounded-button hover:bg-primary/5 transition-colors whitespace-nowrap"
                >
                  Get started
                </button>
              </div>
              <div
                id="pro-plan"
                class="plan-card active glassmorphism bg-white rounded-lg border-2 border-primary p-6 cursor-pointer relative"
              >
                <div
                  class="absolute -top-3 right-4 bg-primary text-white text-xs font-medium px-3 py-1 rounded-full"
                >
                  POPULAR
                </div>
                <div class="mb-4">
                  <h3 class="text-xl font-semibold text-gray-900">Pro</h3>
                  <p class="text-sm text-gray-500 mt-1">
                    Ideal for growing businesses with serious lead generation
                    needs
                  </p>
                </div>
                <div class="mb-6">
                  <div class="flex items-baseline">
                    <span class="text-3xl font-bold text-gray-900">$225</span>
                    <span class="text-gray-500 ml-1">/mo</span>
                  </div>
                </div>
                <ul class="space-y-3 mb-8">
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >2,000 leads per month</span
                    >
                  </li>
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >Advanced filtering options</span
                    >
                  </li>
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >Priority support</span
                    >
                  </li>
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >Daily database updates</span
                    >
                  </li>
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >CRM integration</span
                    >
                  </li>
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >Email sequence automation</span
                    >
                  </li>
                </ul>
                <button
                  class="w-full py-3 text-sm font-medium bg-primary text-white rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap"
                >
                  Get started
                </button>
              </div>
              <div
                id="enterprise-plan"
                class="plan-card glassmorphism bg-white rounded-lg border border-gray-200 p-6 cursor-pointer"
              >
                <div class="mb-4">
                  <h3 class="text-xl font-semibold text-gray-900">
                    Enterprise
                  </h3>
                  <p class="text-sm text-gray-500 mt-1">
                    For large organizations with custom lead generation
                    requirements
                  </p>
                </div>
                <div class="mb-6">
                  <div class="flex items-baseline">
                    <span class="text-3xl font-bold text-gray-900">Custom</span>
                  </div>
                </div>
                <ul class="space-y-3 mb-8">
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >Unlimited leads</span
                    >
                  </li>
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >Custom filtering options</span
                    >
                  </li>
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >Dedicated account manager</span
                    >
                  </li>
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >Real-time database updates</span
                    >
                  </li>
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >Advanced API access</span
                    >
                  </li>
                  <li class="flex items-start">
                    <div
                      class="w-5 h-5 flex items-center justify-center text-primary mt-0.5"
                    >
                      <i class="ri-check-line"></i>
                    </div>
                    <span class="text-sm text-gray-700 ml-2"
                      >Custom integration development</span
                    >
                  </li>
                </ul>
                <button
                  class="w-full py-3 text-sm font-medium text-primary border border-primary rounded-button hover:bg-primary/5 transition-colors whitespace-nowrap"
                >
                  Contact sales
                </button>
              </div>
            </div>
          </section>
          <!-- Billing Summary Panel -->
          <section class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">
              Billing Summary
            </h2>
            <div
              class="glassmorphism bg-white rounded-lg border border-gray-200 p-6 relative"
            >
              <button
                id="edit-plan-btn"
                class="absolute top-6 right-6 text-sm font-medium text-primary hover:text-primary/80 flex items-center whitespace-nowrap"
                type="button"
              >
                <div class="w-4 h-4 flex items-center justify-center mr-1">
                  <i class="ri-edit-line"></i>
                </div>
                Edit Plan
              </button>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">
                      Selected Plan
                    </h3>
                    <div class="flex items-center"></div>
                      <div
                        class="w-5 h-5 flex items-center justify-center text-primary mr-2"
                      >
                        <i class="ri-rocket-line"></i>
                      </div>
                      <span id="billing-plan-name" class="text-base font-medium text-gray-900">
                        Pro Plan ($50/mo)
                      </span>
                    </div>
                  </div>
                  <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">
                      Payment Method
                    </h3>
                    <div class="flex items-center">
                      <div
                        class="w-5 h-5 flex items-center justify-center text-primary mr-2"
                      >
                        <i class="ri-bank-card-line"></i>
                      </div>
                      <span class="text-base font-medium text-gray-900"
                        >TRC-20 USDT</span
                      >
                    </div>
                  </div>
                </div>
                <div class="space-y-4">
                  <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">
                      Billing Cycle
                    </h3>
                    <div class="flex items-center">
                      <div
                        class="w-5 h-5 flex items-center justify-center text-primary mr-2"
                      >
                        <i class="ri-calendar-line"></i>
                      </div>
                      <span id="billing-cycle" class="text-base font-medium text-gray-900">
                        Monthly (Renews every 30 days)
                      </span>
                    </div>
                  </div>
                  <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">
                      Next Payment Date
                    </h3>
                    <div class="flex items-center">
                      <div
                        class="w-5 h-5 flex items-center justify-center text-primary mr-2"
                      >
            </div>
          </section>
          <script>
            document.addEventListener("DOMContentLoaded", function () {
              // Plan info
              const planData = {
                "basic-plan": {
                  name: "Basic Plan",
                  price: 20,
                },
                "pro-plan": {
                  name: "Pro Plan",
                  price: 50,
                },
                "enterprise-plan": {
                  name: "Enterprise Plan",
                  price: null, // Custom
                },
              };

              // Elements
              const billingPlanName = document.getElementById("billing-plan-name");
              const billingCycle = document.getElementById("billing-cycle");
              const billingNextDate = document.getElementById("billing-next-date");
              const billingToggle = document.getElementById("billing-toggle");
              const planCards = document.querySelectorAll(".plan-card");
              const editPlanBtn = document.getElementById("edit-plan-btn");

              // Helper: get selected plan card
              function getSelectedPlanCard() {
                return document.querySelector(".plan-card.active");
              }

              // Helper: get selected plan id
              function getSelectedPlanId() {
                const card = getSelectedPlanCard();
                return card ? card.id : "pro-plan";
              }

              // Helper: get plan price (monthly or yearly)
              function getPlanPrice(planId, isYearly) {
                const plan = planData[planId];
                if (!plan) return { price: "Custom", suffix: "" };
                if (plan.price === null) return { price: "Custom", suffix: "" };
                if (isYearly) {
                  // 20% off for yearly, 12 months
                  const yearly = (plan.price * 12 * 0.8).toFixed(2);
                  return { price: `$${yearly}`, suffix: "/yr" };
                } else {
                  return { price: `$${plan.price}`, suffix: "/mo" };
                }
              }

              // Helper: get billing cycle text
              function getBillingCycleText(isYearly) {
                return isYearly
                  ? "Yearly (Renews every 12 months)"
                  : "Monthly (Renews every 30 days)";
              }

              // Helper: get next payment date
              function getNextPaymentDate(isYearly) {
                const now = new Date();
                if (isYearly) {
                  now.setFullYear(now.getFullYear() + 1);
                } else {
                  now.setDate(now.getDate() + 30);
                }
                return now.toLocaleDateString(undefined, {
                  year: "numeric",
                  month: "long",
                  day: "numeric",
                });
              }

              // Update billing summary UI
              function updateBillingSummary() {
                const planId = getSelectedPlanId();
                const isYearly = billingToggle && billingToggle.checked;
                const plan = planData[planId];
                const { price, suffix } = getPlanPrice(planId, isYearly);

                if (billingPlanName) {
                  if (plan && plan.price !== null) {
                    billingPlanName.textContent = `${plan.name} (${price}${suffix})`;
                  } else {
                    billingPlanName.textContent = `${plan ? plan.name : "Custom Plan"} (Custom)`;
                  }
                }
                if (billingCycle) {
                  billingCycle.textContent = getBillingCycleText(isYearly);
                }
                if (billingNextDate) {
                  billingNextDate.textContent = getNextPaymentDate(isYearly);
                }
              }

              // Listen for plan selection changes
              planCards.forEach((card) => {
                card.addEventListener("click", updateBillingSummary);
              });

              // Listen for billing toggle changes
              if (billingToggle) {
                billingToggle.addEventListener("change", updateBillingSummary);
              }

              // Edit Plan button scrolls to plan selection
              if (editPlanBtn) {
                editPlanBtn.addEventListener("click", function () {
                  const planSection = document.querySelector('section.mb-8');
                  if (planSection) {
                    planSection.scrollIntoView({ behavior: "smooth" });
                  }
                });
              }

              // Initial update
              updateBillingSummary();
            });
          </script>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Save Wallet Form -->
            <section>
              <h2 class="text-lg font-semibold text-gray-800 mb-4">
                TRC-20 USDT Payment Wallet
              </h2>
              <div
                class="glassmorphism bg-white rounded-lg border border-gray-200 p-6"
              >
                <form id="wallet-form">
                  <div class="space-y-5">
                    <div>
                      <label
                        for="wallet-name"
                        class="block text-sm font-medium text-gray-700 mb-1"
                        >Wallet Name</label
                      >
                      <input
                        type="text"
                        id="wallet-name"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm"
                        placeholder="e.g. My TRC-20 USDT Wallet"
                        value="TRC-20 USDT Wallet"
                        readonly
                      />
                    </div>
                    <div>
                      <label
                        for="blockchain"
                        class="block text-sm font-medium text-gray-700 mb-1"
                        >Blockchain</label
                      >
                      <input
                        type="text"
                        id="blockchain"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-100 text-gray-700 text-sm"
                        value="TRON (TRC-20)"
                        readonly
                      />
                    </div>
                    <div>
                      <label
                        class="block text-sm font-medium text-gray-700 mb-2"
                        >Token Standard</label
                      >
                      <input
                        type="text"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-100 text-gray-700 text-sm"
                        value="USDT (TRC-20)"
                        readonly
                      />
                    </div>
                    <div>
                      <label
                        for="wallet-address"
                        class="block text-sm font-medium text-gray-700 mb-1"
                        >Wallet Address</label
                      >
                      <div class="relative">
                        <input
                          type="text"
                          id="wallet-address"
                          class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm font-mono"
                          placeholder="TVTkZq3ghDZgF8txTdDM3s8g76XM2pgiey"
                          value="TVTkZq3ghDZgF8txTdDM3s8g76XM2pgiey"
                          readonly
                        />
                        <div
                          class="absolute right-3 top-1/2 transform -translate-y-1/2 tooltip"
                        >
                          <button
                            type="button"
                            class="w-5 h-5 flex items-center justify-center text-gray-400"
                            title="Copy address"
                            onclick="navigator.clipboard.writeText('TVTkZq3ghDZgF8txTdDM3s8g76XM2pgiey')"
                            tabindex="-1"
                          >
                            <i class="ri-file-copy-line"></i>
                          </button>
                            <span class="tooltip-text text-xs" style="z-index: 1000;"
                            >Copy Payment wallet address</span
                            >
                        </div>
                      </div>
                      <p class="text-xs text-gray-500 mt-1">
                        This is the static TRC-20 USDT wallet address for payments.
                      </p>
                    </div>
                    <div class="flex items-center justify-end">
                      <button
                        type="button"
                        class="px-5 py-2.5 text-sm font-medium bg-primary text-white rounded-button hover:bg-primary/90 transition-colors flex items-center whitespace-nowrap"
                        onclick="navigator.clipboard.writeText('TQJv1kQ2w1v8kQ2w1v8kQ2w1v8kQ2w1v8k')"
                      >
                        <div class="w-4 h-4 flex items-center justify-center mr-1">
                          <i class="ri-file-copy-line"></i>
                        </div>
                        Copy Address
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </section>
            <!-- Right Column -->
            <div class="space-y-8">
              <!-- Subscriptions Control Panel -->
              <section>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                  Subscription Settings
                </h2>
                <div
                  class="glassmorphism bg-white rounded-lg border border-gray-200 p-6"
                >
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
                <div
                  class="glassmorphism bg-white rounded-lg border border-gray-200 p-6"
                >
                  <div class="flex flex-col items-center">
                    <!-- QR Payment Plan Selection -->
                    <div class="mb-6 w-full">
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Plan for QR Payment
                      </label>
                      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <button
                          type="button"
                          class="qr-plan-btn border border-gray-300 rounded-lg px-4 py-3 flex flex-col items-center hover:border-primary focus:outline-none transition-all"
                          data-plan="basic"
                        >
                          <span class="font-semibold text-gray-900 mb-1">Basic</span>
                          <span class="text-xs text-gray-500">USDT 115/mo</span>
                        </button>
                        <button
                          type="button"
                          class="qr-plan-btn border border-gray-300 rounded-lg px-4 py-3 flex flex-col items-center hover:border-primary focus:outline-none transition-all"
                          data-plan="pro"
                        >
                          <span class="font-semibold text-gray-900 mb-1">Pro</span>
                          <span class="text-xs text-gray-500">USDT 225/mo</span>
                        </button>
                        <button
                          type="button"
                          class="qr-plan-btn border border-gray-300 rounded-lg px-4 py-3 flex flex-col items-center hover:border-primary focus:outline-none transition-all"
                          data-plan="enterprise"
                        >
                          <span class="font-semibold text-gray-900 mb-1">Enterprise</span>
                          <span class="text-xs text-gray-500">Custom</span>
                        </button>
                      </div>
                    </div>
                    <div
                      class="w-48 h-48 bg-gray-100 rounded-lg flex items-center justify-center mb-4 relative"
                    >
                      <img
                        src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=TRC20%20USDT%20Wallet%20Address%3A%20TVTkZq3ghDZgF8txTdDM3s8g76XM2pgiey"
                        alt="QR Code"
                        class="w-40 h-40 object-contain"
                      />
                      <div
                        class="absolute inset-0 flex items-center justify-center bg-white/80 rounded-lg hidden"
                        id="qr-loading"
                      >
                        <div
                          class="w-8 h-8 flex items-center justify-center text-primary animate-spin"
                        >
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
                      <label for="qr-txid" class="block text-xs text-gray-500 mb-1">Paste your Transaction ID (TXID)</label>
                      <input
                        type="text"
                        id="qr-txid"
                        name="qr-txid"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm mb-3 font-mono"
                        placeholder="Paste your TXID here"
                        required
                        autocomplete="off"
                      />
                      <button
                        type="submit"
                        class="w-full py-3 text-sm font-semibold bg-primary text-white rounded-button hover:bg-primary/90 transition-colors mb-2"
                      >
                        I’ve Sent the Payment
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
    </script>
    <script id="plan-selection-script">
  document.addEventListener("DOMContentLoaded", function () {
    const planCards = document.querySelectorAll(".plan-card");
    const billingToggle = document.getElementById("billing-toggle");
    // Static TRC-20 wallet address for demo
    const TRC20_ADDRESS = "TVTkZq3ghDZgF8txTdDM3s8g76XM2pgiey";

    // Store original monthly prices for each plan
    const planPrices = {
      "basic-plan": 20,
      "pro-plan": 50,
      "enterprise-plan": 100, // Custom
    };

    // Helper to update displayed prices
    function updatePlanPrices(isYearly) {
      planCards.forEach((card) => {
        const id = card.id;
        const priceSpan = card.querySelector(".text-3xl.font-bold");
        const perSpan = card.querySelector(".text-gray-500.ml-1");
        if (!priceSpan) return;
        if (planPrices[id] === null) {
          priceSpan.textContent = "Custom";
          if (perSpan) perSpan.textContent = "";
        } else if (isYearly) {
          // 20% off for yearly, 12 months
          const yearly = (planPrices[id] * 12 * 0.8).toFixed(2);
          priceSpan.textContent = `$${yearly}`;
          if (perSpan) perSpan.textContent = "/yr";
        } else {
          priceSpan.textContent = `$${planPrices[id]}`;
          if (perSpan) perSpan.textContent = "/mo";
        }
      });
    }

    // Utility: generate a simple unique order ID
    function generateOrderId() {
      return "ORD-" + Date.now() + "-" + Math.floor(Math.random() * 10000);
    }

    planCards.forEach((card) => {
      card.addEventListener("click", function () {
        planCards.forEach((c) => {
          c.classList.remove("active");
          c.classList.remove("border-2");
          c.classList.add("border");
        });
        this.classList.add("active");
        this.classList.remove("border");
        this.classList.add("border-2", "border-primary");
        showCheckoutModal(this);
      });
    });

    function showCheckoutModal(selectedCard) {
      // Remove existing modal if any
      const existingModal = document.getElementById("checkout-modal");
      if (existingModal) existingModal.remove();

      const orderId = generateOrderId();

      // Get price from selected plan card
      let price = "0.00";
      const priceEl = selectedCard.querySelector(".text-3xl.font-bold");
      if (priceEl) {
        // Remove $ and /mo or /yr if present
        price = priceEl.textContent.replace(/[^0-9.]/g, "");
      }

      const modal = document.createElement("div");
      modal.id = "checkout-modal";
      modal.className =
        "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50";

      const modalContent = document.createElement("div");
      modalContent.className =
        "bg-white rounded-xl p-6 max-w-md w-full mx-4 relative shadow-xl fintech-modal";

      const closeButton = document.createElement("button");
      closeButton.className =
        "absolute top-4 right-4 text-gray-400 hover:text-gray-600";
      closeButton.innerHTML = '<i class="ri-close-line ri-lg"></i>';
      closeButton.onclick = () => modal.remove();

      modalContent.innerHTML = `
        <h3 class="text-xl font-semibold text-gray-900 mb-2 text-center">TRC-20 USDT Payment</h3>
        <p class="text-gray-500 text-center mb-6">Please send the exact amount to the wallet below. Your order will be processed after confirmation.</p>
        <div class="flex flex-col items-center mb-4">
          <div class="w-40 h-40 bg-gray-100 rounded-lg flex items-center justify-center mb-3 border border-gray-200">
            <img
              src="https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(
                TRC20_ADDRESS
              )}&size=160x160"
              alt="TRC-20 Wallet QR"
              class="w-36 h-36 object-contain"
            />
          </div>
          <div class="w-full">
            <div class="mb-2">
              <span class="block text-xs text-gray-500">Order ID</span>
              <span class="block font-mono text-sm text-primary font-semibold">${orderId}</span>
            </div>
            <div class="mb-2">
              <span class="block text-xs text-gray-500">Amount (USDT)</span>
              <span class="block font-mono text-lg text-gray-900 font-bold">${price}</span>
            </div>
            <div class="mb-2">
              <span class="block text-xs text-gray-500">TRC-20 Wallet Address</span>
              <div class="flex items-center gap-2">
                <span class="block font-mono text-xs text-gray-800 truncate" id="trc20-address">${TRC20_ADDRESS}</span>
                <button type="button" class="text-primary hover:text-blue-700" title="Copy" onclick="navigator.clipboard.writeText('${TRC20_ADDRESS}')">
                  <i class="ri-file-copy-line"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <form id="txid-form" class="mt-4">
          <label for="txid" class="block text-xs text-gray-500 mb-1">Paste your Transaction ID (TXID)</label>
          <input
            type="text"
            id="txid"
            name="txid"
            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm mb-3 font-mono"
            placeholder="Paste your TXID here"
            required
            autocomplete="off"
          />
          <button
            type="submit"
            class="w-full py-3 text-sm font-semibold bg-primary text-white rounded-button hover:bg-primary/90 transition-colors mb-2"
          >
            I’ve Sent the Payment
          </button>
        </form>
        <div id="txid-success" class="hidden mt-2 text-green-600 text-center text-sm font-medium">
          Thank you! Your payment is being verified.
        </div>
      `;

      modalContent.appendChild(closeButton);
      modal.appendChild(modalContent);
      document.body.appendChild(modal);

      // TXID form handling
      const txidForm = modalContent.querySelector("#txid-form");
      const txidInput = modalContent.querySelector("#txid");
      const txidSuccess = modalContent.querySelector("#txid-success");
      txidForm.addEventListener("submit", function (e) {
        e.preventDefault();
        txidForm.classList.add("pointer-events-none", "opacity-60");
        txidSuccess.classList.remove("hidden");
        setTimeout(() => {
          modal.remove();
        }, 2500);
      });
    }

    // Duration toggle logic
    billingToggle.addEventListener("change", function () {
      updatePlanPrices(this.checked);
    });

    // Set initial prices
    updatePlanPrices(billingToggle.checked);
  });
</script>
    <style>
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
    </style>
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
      <i class="ri-${
        type === "success"
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
  document.addEventListener("DOMContentLoaded", function () {
    // Plan data for QR payment
    const qrPlans = {
      basic: { name: "Basic", price: 20 },
      pro: { name: "Pro", price: 50 },
      enterprise: { name: "Enterprise", price: 100 }
    };
    const qrPlanBtns = document.querySelectorAll(".qr-plan-btn");
    const qrSelectedPlan = document.getElementById("qr-selected-plan");
    const qrAmount = document.getElementById("qr-amount");
    const qrOrderId = document.getElementById("qr-order-id");
    const qrTxidForm = document.getElementById("qr-txid-form");
    const qrTxidSuccess = document.getElementById("qr-txid-success");

    let currentPlan = null;

    // Helper to generate a unique order ID
    function generateOrderId() {
      return "ORD-QR-" + Date.now() + "-" + Math.floor(Math.random() * 10000);
    }

    // Update the QR payment panel UI
    function updateQRPaymentPanel(planKey) {
      if (!qrPlans[planKey]) {
        qrSelectedPlan.textContent = "None";
        qrAmount.textContent = "0";
        qrOrderId.textContent = "ORD-QR-0";
        return;
      }
      qrSelectedPlan.textContent = qrPlans[planKey].name;
      qrAmount.textContent = qrPlans[planKey].price !== null ? qrPlans[planKey].price : "Custom";
      qrOrderId.textContent = generateOrderId();
    }

    // Plan button selection logic
    qrPlanBtns.forEach((btn) => {
      btn.addEventListener("click", function () {
        qrPlanBtns.forEach((b) => b.classList.remove("border-primary", "ring-2", "ring-primary/20"));
        this.classList.add("border-primary", "ring-2", "ring-primary/20");
        currentPlan = this.getAttribute("data-plan");
        updateQRPaymentPanel(currentPlan);
      });
    });

    // Set default state (no plan selected)
    updateQRPaymentPanel(null);

    // TXID form logic
    if (qrTxidForm && qrTxidSuccess) {
      qrTxidForm.addEventListener("submit", function (e) {
        e.preventDefault();
        qrTxidForm.classList.add("pointer-events-none", "opacity-60");
        qrTxidSuccess.classList.remove("hidden");
        setTimeout(() => {
          qrTxidForm.reset();
          qrTxidForm.classList.remove("pointer-events-none", "opacity-60");
          qrTxidSuccess.classList.add("hidden");
        }, 2500);
      });
    }
  });
</script>
</body>
</html>