<?php
 require 'api/auth_check.php';
$user_id = $_SESSION['user_id'];

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
    $avatarUrl = '../../uploads/profile_pictures/' . $filename;
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


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sales-Spy Websites Dashboard</title>
  <script src="https://cdn.tailwindcss.com/3.4.16"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: "#1E3A8A",
            secondary: "#5BC0EB"
          },
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
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
  <style>
    :where([class^="ri-"])::before {
      content: "\f3c2";
    }

    body {
      font-family: "Inter", sans-serif;
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
      background-color: #3b82f6;
    }

    input:checked+.slider:before {
      transform: translateX(20px);
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
            class="absolute right-2 top-2 p-2 rounded-full hover:bg-gray-100 md:hidden"
            aria-label="Close sidebar">
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4">
          <ul>
            <li class="mb-2">
              <a href="../"
                class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                <div class="w-6 h-6 flex items-center justify-center mr-3">
                  <i class="ri-dashboard-line"></i>
                </div>
                <span class="sidebar-text">Dashboard</span>
              </a>
            </li>
            <li class="mb-2">
              <a href="#"
                class="   flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                <div class="w-6 h-6 flex items-center justify-center mr-3">
                  <i class="ri-global-line"></i>
                </div>
                <span class="sidebar-text">Websites</span>
              </a>
            </li>
            <li class="mb-2">
              <a href="#"
                class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                <div class="w-6 h-6 flex items-center justify-center mr-3">
                  <i class="ri-shopping-cart-line"></i>
                </div>
                <span class="sidebar-text">E-commerce</span>
              </a>
            </li>
            <li class="mb-2">
              <a href="../payment/"
                class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                <div class="w-6 h-6 flex items-center justify-center mr-3">
                  <i class="ri-bank-card-line"></i>
                </div>
                <span class="sidebar-text">Payment</span>
              </a>
            </li>
            <li class="mb-2">
              <a href="#"
                class="flex items-center px-4 py-3 text-primary bg-blue-50 rounded-r-lg border-l-4 border-primary">
                <div class="w-6 h-6 flex items-center justify-center mr-3">
                  <i class="ri-file-list-3-line"></i>
                </div>
                <span class="sidebar-text">Transaction History</span>
              </a>
            </li>
            <li class="mb-2">
              <a href="../settings/"
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
            <p class="text-sm text-gray-600 mb-2 sidebar-text">Credits remaining</p>
            <div class="flex items-center justify-between">
              <span class="font-semibold text-lg sidebar-text"><?= number_format($stats['credits_remaining']) ?></span>
              <span class="text-xs text-gray-500 sidebar-text">of <?= number_format($stats['credits_total']) ?></span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
              <div class="bg-primary rounded-full h-2" style="width: <?= $stats['credits_percentage'] ?>%"></div>
            </div>
          </div>
          <a href="Dashboard-pay.html">
            <button id="upgrade-btn-expanded"
              class="w-full bg-primary text-white py-2 px-4 rounded-button flex items-center justify-center whitespace-nowrap hover:bg-blue-600 transition-colors">
              <div class="w-5 h-5 flex items-center justify-center mr-2">
                <i class="ri-arrow-up-line"></i>
              </div>
              <span class="sidebar-text">Upgrade Plan</span>
            </button>
          </a>
          <a href="Dashboard-pay.html">
            <button id="upgrade-btn-collapsed"
              class="hidden bg-primary text-white w-10 h-10 rounded-full flex items-center justify-center mx-auto mt-2 hover:bg-blue-600 transition-colors"
              title="Upgrade">
              <i class="ri-arrow-up-line"></i>
            </button>
          </a>
        </div>
        <style>
          @media (max-width: 768px) {
            #upgrade-section .sidebar-text {
              display: none !important;
            }

            .sidebar-expanded #upgrade-section .sidebar-text {
              display: inline !important;
            }
          }
        </style>
        <script>
          document.addEventListener("DOMContentLoaded", function() {
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

            // Initial state
            updateUpgradeSection();
            updateLogo();

            // Listen for sidebar toggle
            const sidebarToggle = document.getElementById("sidebar-toggle");
            sidebarToggle.addEventListener("click", function() {
              setTimeout(() => {
                updateUpgradeSection();
                updateLogo();
              }, 310); // Wait for transition
            });

            // Also update on resize (for responsive)
            window.addEventListener("resize", function() {
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
    <div id="main-content"
      class="main-content-expanded flex-1 transition-all duration-700 ease-in-out overflow-x-auto">
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
              <button
                class="bg-primary text-white py-2 px-4 rounded-button whitespace-nowrap hover:bg-blue-600 transition-colors">
                <span>Upgrade</span>
              </button>
            </a>
            <div class="relative">
              <button class="flex items-center space-x-2">
                <div
                  class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                  <img src="<?= htmlspecialchars($avatarUrl) ?>"
                    alt="User avatar" class="w-full h-full object-cover" />
                </div>
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 p-6">
        <div class="max-w-7xl mx-auto">
          <!-- Header Section -->
          <div class="mb-6">
            <h1 class="text-2xl font-bold text-primary mb-2" style="color: #1e3a8a">
              Transaction History
            </h1>
            <p class="text-gray-600">
              View and manage your recent
              <span class="font-bold" style="color: #1e3a8a">transactions</span>, including payment
              details, statuses, and receipts.
            </p>
          </div>
          <!-- Filter Controls -->
          <div class="glassmorphism rounded-2xl p-6 mb-6 shadow-lg bg-white/95" style="position: relative">
            <form id="transactionFilterForm" onsubmit="return false;">
              <!-- Search Bar -->
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-800 mb-2">Search Transactions</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="ri-search-line text-gray-400"></i>
                  </div>
                  <input type="text" id="searchInput" name="search"
                    placeholder="Search by transaction ID or amount..."
                    class="w-full bg-white border border-gray-300 rounded-lg pl-10 pr-4 py-3 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/50" />
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-800 mb-2">Date Range</label>
                  <div class="flex gap-2">
                    <input type="date" id="dateFrom" name="dateFrom"
                      class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/50" />
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-800 mb-2">Payment Type</label>
                  <div class="relative">
                    <button type="button" id="paymentTypeBtn"
                      class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 text-left text-gray-900 flex items-center justify-between hover:bg-gray-100 transition-all"
                      onclick="toggleDropdown('paymentType')">
                      <span id="paymentTypeSelected">All Types</span>
                      <div class="w-4 h-4 flex items-center justify-center">
                        <i class="ri-arrow-down-s-line"></i>
                      </div>
                    </button>
                    <div id="paymentTypeDropdown"
                      class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg z-[100] shadow-lg">
                      <div class="p-2">
                        <div class="px-3 py-2 hover:bg-gray-100 rounded cursor-pointer text-gray-800"
                          data-value="">
                          All Types
                        </div>
                        <div class="px-3 py-2 hover:bg-gray-100 rounded cursor-pointer text-gray-800"
                          data-value="Credit Card">
                          Credit Card
                        </div>
                        <div class="px-3 py-2 hover:bg-gray-100 rounded cursor-pointer text-gray-800"
                          data-value="Cryptocurrency">
                          Cryptocurrency
                        </div>
                      </div>
                    </div>
                    <input type="hidden" id="paymentType" name="paymentType" value="" />
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-800 mb-2">Status</label>
                  <div class="relative">
                    <button type="button" id="statusBtn"
                      class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 text-left text-gray-900 flex items-center justify-between hover:bg-gray-100 transition-all"
                      onclick="toggleDropdown('status')">
                      <span id="statusSelected">All Status</span>
                      <div class="w-4 h-4 flex items-center justify-center">
                        <i class="ri-arrow-down-s-line"></i>
                      </div>
                    </button>
                    <div id="statusDropdown"
                      class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg z-[100] shadow-lg">
                      <div class="p-2">
                        <div class="px-3 py-2 hover:bg-gray-100 rounded cursor-pointer text-gray-800"
                          data-value="">
                          All Status
                        </div>
                        <div class="px-3 py-2 hover:bg-gray-100 rounded cursor-pointer text-gray-800"
                          data-value="Success">
                          Success
                        </div>
                        <div class="px-3 py-2 hover:bg-gray-100 rounded cursor-pointer text-gray-800"
                          data-value="Pending">
                          Pending
                        </div>
                        <div class="px-3 py-2 hover:bg-gray-100 rounded cursor-pointer text-gray-800"
                          data-value="Failed">
                          Failed
                        </div>
                      </div>
                    </div>
                    <input type="hidden" id="status" name="status" value="" />
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-800 mb-2">Sort By</label>
                  <div class="relative">
                    <button type="button" id="sortBtn"
                      class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 text-left text-gray-900 flex items-center justify-between hover:bg-gray-100 transition-all"
                      onclick="toggleDropdown('sort')">
                      <span id="sortSelected">Newest First</span>
                      <div class="w-4 h-4 flex items-center justify-center">
                        <i class="ri-arrow-down-s-line"></i>
                      </div>
                    </button>
                    <div id="sortDropdown"
                      class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg z-[100] shadow-lg">
                      <div class="p-2">
                        <div class="px-3 py-2 hover:bg-gray-100 rounded cursor-pointer text-gray-800"
                          data-value="newest">
                          Newest First
                        </div>
                        <div class="px-3 py-2 hover:bg-gray-100 rounded cursor-pointer text-gray-800"
                          data-value="oldest">
                          Oldest First
                        </div>
                        <div class="px-3 py-2 hover:bg-gray-100 rounded cursor-pointer text-gray-800"
                          data-value="amountHigh">
                          Amount High to Low
                        </div>
                        <div class="px-3 py-2 hover:bg-gray-100 rounded cursor-pointer text-gray-800"
                          data-value="amountLow">
                          Amount Low to High
                        </div>
                      </div>
                    </div>
                    <input type="hidden" id="sort" name="sort" value="newest" />
                  </div>
                </div>
              </div>
              <div class="flex justify-between items-center mt-4">
                <div class="flex items-center gap-2">
                  <label class="text-sm font-medium text-gray-800">Show:</label>
                  <select id="pageSize"
                    class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/50">
                    <option value="10">10 per page</option>
                    <option value="20" selected>20 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                  </select>
                </div>
                <div class="flex gap-2">
                  <button type="button" id="resetFilters"
                    class="bg-gray-100 text-gray-900 px-6 py-2 rounded-button hover:bg-gray-200 transition-all">
                    Reset
                  </button>
                  <button type="button" id="applyFilters"
                    class="bg-primary text-white px-6 py-2 rounded-button hover:bg-blue-700 transition-all">
                    Apply Filter
                  </button>
                </div>
              </div>
            </form>
          </div>

          <script>
            // Global variables for transaction management
            let transactions = [];
            let currentPage = 1;
            let totalPages = 1;
            let isLoading = false;
            let currentFilters = {
              status: '',
              payment_type: '',
              date_from: '',
              date_to: '',
              search: '',
              amount_min: null,
              amount_max: null
            };

            // Payment type mapping (robust)
            function mapPaymentType(type) {
              if (!type) return "";
              const t = type.toLowerCase();
              if (t.includes("credit")) return "Credit Card";
              if (t.includes("bitcoin") || t.includes("ethereum")) return "Cryptocurrency";
              if (t.includes("crypto")) return "Cryptocurrency";
              return type;
            }

            // API functions
            async function fetchTransactions(page = 1, filters = {}) {
              if (isLoading) return;

              isLoading = true;
              showLoadingState();

              try {
                const pageSize = document.getElementById("pageSize") ? document.getElementById(
                  "pageSize").value : 20;
                const params = new URLSearchParams({
                  page: page,
                  limit: pageSize,
                  ...filters
                });

                const response = await fetch(`api/transactions.php?${params}`);
                const data = await response.json();

                if (data.success) {
                  transactions = data.data;
                  currentPage = data.pagination.current_page;
                  totalPages = data.pagination.total_pages;
                  currentFilters = {
                    ...filters
                  };

                  renderTransactions(transactions);
                  updatePagination(data.pagination);
                } else {
                  showError(data.message || 'Failed to fetch transactions');
                }
              } catch (error) {
                console.error('Error fetching transactions:', error);
                showError('Network error occurred while fetching transactions');
              } finally {
                isLoading = false;
                hideLoadingState();
              }
            }

            // Show loading state
            function showLoadingState() {
              const container = document.getElementById("transactionList");
              if (container) {
                container.innerHTML = `
                  <div class="flex flex-col items-center justify-center py-16 text-center text-gray-500">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mb-4"></div>
                    <div class="text-lg font-semibold mb-1">Loading transactions...</div>
                    <div class="text-sm">Please wait while we fetch your transaction history.</div>
                  </div>
                `;
              }
            }

            // Hide loading state
            function hideLoadingState() {
              // Loading state will be replaced by renderTransactions
            }

            // Show error message
            function showError(message) {
              const container = document.getElementById("transactionList");
              if (container) {
                container.innerHTML = `
                  <div class="flex flex-col items-center justify-center py-16 text-center text-red-500">
                    <i class="ri-error-warning-line text-5xl mb-4 text-red-300"></i>
                    <div class="text-lg font-semibold mb-1">Error Loading Transactions</div>
                    <div class="text-sm">${message}</div>
                    <button onclick="fetchTransactions(currentPage, currentFilters)"
                            class="mt-4 bg-primary text-white px-4 py-2 rounded-button hover:bg-blue-700 transition-all">
                      Try Again
                    </button>
                  </div>
                `;
              }
            }

            // Render transactions to DOM
            function renderTransactions(list) {
              const container = document.getElementById("transactionList");
              if (!container) return;
              container.innerHTML = "";

              if (!list.length) {
                container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-16 text-center text-gray-500">
                  <i class="ri-file-search-line text-5xl mb-4 text-gray-300"></i>
                  <div class="text-lg font-semibold mb-1">No transactions found</div>
                  <div class="text-sm">Try adjusting your filters or date range.</div>
                </div>
                `;
                return;
              }

              list.forEach((tx) => {
                // Payment type icon
                let icon = "";
                let iconColor = "";
                if (tx.payment_type === "Credit Card") {
                  icon = "ri-bank-card-line";
                  iconColor = "text-blue-600";
                } else if (tx.payment_type.toLowerCase().includes("bitcoin")) {
                  icon = "ri-bit-coin-line";
                  iconColor = "text-orange-500";
                } else if (tx.payment_type.toLowerCase().includes("ethereum")) {
                  icon = "ri-ethereum-line";
                  iconColor = "text-purple-600";
                } else if (tx.payment_type.toLowerCase().includes("crypto")) {
                  icon = "ri-bit-coin-line";
                  iconColor = "text-orange-500";
                } else {
                  icon = "ri-bank-card-line";
                  iconColor = "text-gray-400";
                }

                // Status
                let statusClass = "";
                let statusBg = "";
                let statusText = "";
                let statusIcon = "";
                if (tx.status === "Success") {
                  statusClass = "status-success";
                  statusBg = "bg-green-100 text-green-800";
                  statusIcon = "ri-check-line";
                } else if (tx.status === "Pending") {
                  statusClass = "status-pending";
                  statusBg = "bg-yellow-100 text-yellow-800";
                  statusIcon = "ri-time-line";
                } else {
                  statusClass = "status-failed";
                  statusBg = "bg-red-100 text-red-800";
                  statusIcon = "ri-close-line";
                }

                // Desktop row
                const desktopRow = `
                <div class="hidden md:grid md:grid-cols-6 gap-4 p-6 items-center">
                  <div class="text-gray-900">
                  <div class="font-medium">${tx.date}</div>
                  <div class="text-sm text-gray-500">${tx.time}</div>
                  </div>
                  <div class="font-mono text-sm text-gray-700 break-words truncate max-w-[120px]">${tx.txid}</div>
                  <div class="flex items-center gap-2">
                  <div class="w-6 h-6 flex items-center justify-center">
                    <i class="${icon} ${iconColor}"></i>
                  </div>
                  <span class="text-gray-900">${tx.payment_type}</span>
                  </div>
                  <div class="font-semibold text-gray-900">${tx.formatted_amount}</div>
                  <div>
                  <span class="${statusClass} px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1 w-fit ${statusBg}">
                    <div class="w-3 h-3 flex items-center justify-center">
                    <i class="${statusIcon} ri-xs"></i>
                    </div>
                    ${tx.status}
                  </span>
                  </div>
                  <div>
                  <button
                    class="rounded-button px-4 py-2 hover:bg-gray-100 transition-all whitespace-nowrap !rounded-button text-gray-900"
                    onclick="showTransactionDetails('${tx.id}')"
                  >
                    <div class="w-4 h-4 flex items-center justify-center">
                    <i class="ri-eye-line"></i>
                    </div>
                  </button>
                  </div>
                </div>
                `;

                // Mobile card
                const mobileRow = `
                <div class="md:hidden glassmorphism rounded-2xl m-4 p-6 bg-white/95 shadow">
                  <div class="flex justify-between items-start mb-4">
                  <div>
                    <div class="font-semibold text-gray-900 text-lg">${tx.formatted_amount}</div>
                    <div class="text-sm text-gray-600">${tx.date} • ${tx.time}</div>
                  </div>
                  <span class="${statusClass} px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1 ${statusBg}">
                    <div class="w-3 h-3 flex items-center justify-center">
                    <i class="${statusIcon} ri-xs"></i>
                    </div>
                    ${tx.status}
                  </span>
                  </div>
                  <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <div class="w-6 h-6 flex items-center justify-center">
                    <i class="${icon} ${iconColor}"></i>
                    </div>
                    <span class="text-gray-900">${tx.payment_type}</span>
                  </div>
                  <button
                    class="glassmorphism rounded-button px-4 py-2 hover:bg-gray-100 transition-all whitespace-nowrap !rounded-button text-gray-900"
                    onclick="showTransactionDetails('${tx.id}')"
                  >
                    View Details
                  </button>
                  </div>
                  <div class="mt-3 pt-3 border-t border-gray-200">
                  <div class="text-xs text-gray-700 font-mono break-words">${tx.txid}</div>
                  </div>
                </div>
                `;

                // Wrap both in a transaction-row for filtering
                const wrapper = document.createElement("div");
                wrapper.className =
                  "transaction-row transition-all duration-300 cursor-pointer hover:bg-gray-100";
                wrapper.innerHTML = desktopRow + mobileRow;
                container.appendChild(wrapper);
              });
            }

            // Filtering logic (debounced for live UX)
            let filterTimeout;

            function filterTransactions(live = false) {
              if (live) {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => filterTransactions(false), 300);
                return;
              }

              // Get filter values
              const dateFrom = document.getElementById("dateFrom").value;
              const paymentType = document.getElementById("paymentType").value;
              const status = document.getElementById("status").value;
              const sort = document.getElementById("sort").value;
              const search = document.getElementById("searchInput").value.trim();

              // Build filters object
              const filters = {};
              if (dateFrom) filters.date_from = dateFrom;
              if (paymentType) filters.payment_type = paymentType;
              if (status) filters.status = status.toLowerCase();
              if (search) filters.search = search;

              // Fetch transactions with filters
              fetchTransactions(1, filters);
            }

            // Pagination functions
            function updatePagination(pagination) {
              const paginationContainer = document.getElementById("pagination");
              if (!paginationContainer) {
                // Create pagination container if it doesn't exist
                const container = document.createElement("div");
                container.id = "pagination";
                container.className = "flex justify-center items-center space-x-2 mt-6 mb-4";

                const transactionList = document.getElementById("transactionList");
                if (transactionList && transactionList.parentNode) {
                  transactionList.parentNode.insertBefore(container, transactionList.nextSibling);
                }
              }

              const container = document.getElementById("pagination");
              if (!container) return;

              if (pagination.total_pages <= 1) {
                container.innerHTML = '';
                return;
              }

              let paginationHTML = '';

              // Previous button
              if (pagination.has_prev) {
                paginationHTML += `
                  <button onclick="changePage(${pagination.current_page - 1})"
                          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
                    Previous
                  </button>
                `;
              } else {
                paginationHTML += `
                  <button disabled class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-300 rounded-l-md cursor-not-allowed">
                    Previous
                  </button>
                `;
              }

              // Page numbers
              const startPage = Math.max(1, pagination.current_page - 2);
              const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);

              for (let i = startPage; i <= endPage; i++) {
                if (i === pagination.current_page) {
                  paginationHTML += `
                    <button class="px-3 py-2 text-sm font-medium text-white bg-primary border border-primary">
                      ${i}
                    </button>
                  `;
                } else {
                  paginationHTML += `
                    <button onclick="changePage(${i})"
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50">
                      ${i}
                    </button>
                  `;
                }
              }

              // Next button
              if (pagination.has_next) {
                paginationHTML += `
                  <button onclick="changePage(${pagination.current_page + 1})"
                          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
                    Next
                  </button>
                `;
              } else {
                paginationHTML += `
                  <button disabled class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-300 rounded-r-md cursor-not-allowed">
                    Next
                  </button>
                `;
              }

              container.innerHTML = paginationHTML;
            }

            function changePage(page) {
              if (page < 1 || page > totalPages || isLoading) return;
              fetchTransactions(page, currentFilters);
            }

            // Dropdown logic
            function toggleDropdown(dropdownId) {
              const dropdown = document.getElementById(dropdownId + "Dropdown");
              const allDropdowns = document.querySelectorAll('[id$="Dropdown"]');
              allDropdowns.forEach((dd) => {
                if (dd !== dropdown) dd.classList.add("hidden");
              });
              dropdown.classList.toggle("hidden");
            }
            document.addEventListener("click", function(event) {
              if (
                !event.target.closest('[onclick*="toggleDropdown"]') &&
                !event.target.closest(".relative")
              ) {
                const allDropdowns = document.querySelectorAll('[id$="Dropdown"]');
                allDropdowns.forEach((dd) => dd.classList.add("hidden"));
              }
            });

            // Dropdown selection logic
            document.addEventListener("DOMContentLoaded", function() {
              document
                .querySelectorAll("#paymentTypeDropdown [data-value]")
                .forEach((item) => {
                  item.addEventListener("click", function() {
                    document.getElementById("paymentTypeSelected").textContent =
                      this.textContent;
                    document.getElementById("paymentType").value =
                      this.getAttribute("data-value");
                    document
                      .getElementById("paymentTypeDropdown")
                      .classList.add("hidden");
                    filterTransactions(true);
                  });
                });
              document
                .querySelectorAll("#statusDropdown [data-value]")
                .forEach((item) => {
                  item.addEventListener("click", function() {
                    document.getElementById("statusSelected").textContent =
                      this.textContent;
                    document.getElementById("status").value =
                      this.getAttribute("data-value");
                    document
                      .getElementById("statusDropdown")
                      .classList.add("hidden");
                    filterTransactions(true);
                  });
                });
              document
                .querySelectorAll("#sortDropdown [data-value]")
                .forEach((item) => {
                  item.addEventListener("click", function() {
                    document.getElementById("sortSelected").textContent =
                      this.textContent;
                    document.getElementById("sort").value =
                      this.getAttribute("data-value");
                    document
                      .getElementById("sortDropdown")
                      .classList.add("hidden");
                    filterTransactions(true);
                  });
                });

              // Live filtering for date and search
              document.getElementById("dateFrom").addEventListener("input", () => filterTransactions(
                true));
              document.getElementById("searchInput").addEventListener("input", () =>
                filterTransactions(true));
            });

            // Reset button
            document.addEventListener("DOMContentLoaded", function() {
              document
                .getElementById("resetFilters")
                .addEventListener("click", function() {
                  document.getElementById("dateFrom").value = "";
                  document.getElementById("searchInput").value = "";
                  document.getElementById("paymentType").value = "";
                  document.getElementById("status").value = "";
                  document.getElementById("sort").value = "newest";
                  document.getElementById("paymentTypeSelected").textContent =
                    "All Types";
                  document.getElementById("statusSelected").textContent =
                    "All Status";
                  document.getElementById("sortSelected").textContent =
                    "Newest First";
                  filterTransactions();
                });

              // Apply Filter button
              document
                .getElementById("applyFilters")
                .addEventListener("click", () => filterTransactions());

              // Initial render
              // Add a container for transaction list
              if (!document.getElementById("transactionList")) {
                const div = document.createElement("div");
                div.id = "transactionList";
                div.className = "divide-y divide-gray-200";
                // Insert after the table header
                const tableHeader = document.querySelector(
                  ".max-w-7xl .bg-white.border-b"
                );
                if (tableHeader && tableHeader.parentNode) {
                  tableHeader.parentNode.insertBefore(div, tableHeader.nextSibling);
                }
              }

              // Load initial transactions
              fetchTransactions(1, {});
            });
          </script>

          <!-- Desktop Table Header -->
          <div class="hidden md:block bg-white border-b border-gray-200">
            <div class="grid grid-cols-6 gap-4 p-6 text-sm font-semibold text-gray-900">
              <div>Date & Time</div>
              <div>Transaction ID</div>
              <div>Payment Type</div>
              <div>Amount</div>
              <div>Status</div>
              <div>Actions</div>
            </div>
          </div>
          <!-- Transaction Items -->
          <div id="transactionList" class="divide-y divide-gray-200"></div>
        </div>
      </main>
    </div>
    <!-- Transaction Details Modal -->
    <div id="transactionModal"
      class="hidden fixed inset-0 bg-gray-900/40 backdrop-blur-[3px] z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 fade-in shadow-2xl border border-gray-200">
        <!-- Modal Header -->
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-semibold text-gray-900">
            Transaction Details
          </h3>
          <button onclick="closeTransactionModal()"
            class="bg-gray-100 hover:bg-gray-200 rounded-button p-2 transition-all !rounded-button">
            <div class="w-5 h-5 flex items-center justify-center">
              <i class="ri-close-line"></i>
            </div>
          </button>
        </div>
        <!-- Modal Content (populated by JS) -->
        <div id="modalContent" class="space-y-4">
          <!-- Content will be populated by JavaScript -->
        </div>
        <!-- Modal Actions -->
        <div class="flex gap-3 mt-6">
          <button
            class="flex-1 bg-primary hover:bg-primary/80 text-white px-4 py-3 rounded-button font-medium transition-all whitespace-nowrap !rounded-button">
            Download Receipt
          </button>
          <button onclick="closeTransactionModal()"
            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-900 px-4 py-3 rounded-button font-medium transition-all whitespace-nowrap !rounded-button">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Keep only the relevant scripts for your dashboard, charts, advanced filters, and column visibility dropdown. Remove duplicate or unused scripts. Place this at the end of your <body> -->

  <script>
    // Sidebar toggle and responsive behavior
    document.addEventListener("DOMContentLoaded", function() {
      const sidebar = document.getElementById("sidebar");
      const mainContent = document.getElementById("main-content");
      const sidebarToggle = document.getElementById("sidebar-toggle");
      const sidebarTexts = document.querySelectorAll(".sidebar-text");
      const sidebarMobileClose = document.getElementById(
        "sidebar-mobile-close"
      );

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

      sidebarToggle.addEventListener("click", function() {
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

  <script id="transactionModal">
    function showTransactionDetails(transactionId) {
      // Find transaction in current transactions array
      const transaction = transactions.find(tx => tx.id === transactionId);
      if (!transaction) return;

      const statusClass =
        transaction.status === "Success" ?
        "status-success" :
        transaction.status === "Pending" ?
        "status-pending" :
        "status-failed";

      const statusIcon =
        transaction.status === "Success" ?
        "ri-check-line" :
        transaction.status === "Pending" ?
        "ri-time-line" :
        "ri-close-line";

      const statusColor =
        transaction.status === "Success" ?
        "green" :
        transaction.status === "Pending" ?
        "yellow" :
        "red";

      const modalContent = document.getElementById("modalContent");
      modalContent.innerHTML = `
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-800">Transaction ID</span>
                            <span class="text-gray-500 font-mono text-sm break-words">${transaction.txid}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-800">Date & Time</span>
                            <span class="text-gray-500">${transaction.date} • ${transaction.time}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-800">Payment Type</span>
                            <span class="text-gray-500">${transaction.payment_type}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-800">Amount</span>
                            <span class="text-gray-500 font-semibold text-lg">${transaction.formatted_amount}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-800">Status</span>
                            <span class="${statusClass} px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1 w-fit bg-${statusColor}-100 text-${statusColor}-800">
                                <div class="w-3 h-3 flex items-center justify-center">
                                    <i class="${statusIcon} ri-xs"></i>
                                </div>
                                ${transaction.status}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-800">Description</span>
                            <span class="text-gray-500">${transaction.description || 'Payment Transaction'}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-800">Created</span>
                            <span class="text-gray-500 text-sm">${transaction.created_at}</span>
                        </div>
                    </div>
                `;
      document.getElementById("transactionModal").classList.remove("hidden");
    }

    function closeTransactionModal() {
      document.getElementById("transactionModal").classList.add("hidden");
    }

    document.addEventListener("keydown", function(event) {
      if (event.key === "Escape") {
        closeTransactionModal();
      }
    });
  </script>
</body>

</html>