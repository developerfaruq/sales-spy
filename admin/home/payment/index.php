<?php
require '../../config/db.php';
require '../../home/subscription/api/auth_check.php';


if (isset($_SESSION['admin_id'])) {
    $stmt = $pdo->prepare("SELECT name FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        $adminName = htmlspecialchars($admin['name']);
    }
}


$avatarUrl = "https://ui-avatars.com/api/?name=" . 
                 urlencode( $adminName ) . 
                 "&background=1E3A8A&color=fff&length=1&size=128";

// Get counts from DB
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pendingTXIDs = $pdo->query("SELECT COUNT(*) FROM txid_requests WHERE status = 'pending'")->fetchColumn();
$activeSubscriptions = $pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status = 'active'")->fetchColumn();                 
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
      rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <style>
      :where([class^="ri-"])::before {
        content: "\f3c2";
      }

      body {
        font-family: "Inter", sans-serif;
        background-color: #f8f9fb;
      }

      .sidebar-transition {
        transition: width 0.3s ease, transform 0.3s ease;
      }

      input[type="number"]::-webkit-inner-spin-button,
      input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
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

      .switch-slider {
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

      .switch-slider:before {
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

      input:checked+.switch-slider {
        background-color: #3366ff;
      }

      input:checked+.switch-slider:before {
        transform: translateX(20px);
      }

      .custom-checkbox {
        position: relative;
        display: inline-block;
        width: 18px;
        height: 18px;
      }

      .custom-checkbox input {
        opacity: 0;
        width: 0;
        height: 0;
      }

      .checkbox-mark {
        position: absolute;
        top: 0;
        left: 0;
        height: 18px;
        width: 18px;
        background-color: #fff;
        border: 1px solid #d1d5db;
        border-radius: 4px;
      }

      .custom-checkbox input:checked~.checkbox-mark {
        background-color: #3366ff;
        border-color: #3366ff;
      }

      .checkbox-mark:after {
        content: "";
        position: absolute;
        display: none;
      }

      .custom-checkbox input:checked~.checkbox-mark:after {
        display: block;
      }

      .custom-checkbox .checkbox-mark:after {
        left: 6px;
        top: 2px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
      }

      .custom-radio {
        position: relative;
        display: inline-block;
        width: 18px;
        height: 18px;
      }

      .custom-radio input {
        opacity: 0;
        width: 0;
        height: 0;
      }

      .radio-mark {
        position: absolute;
        top: 0;
        left: 0;
        height: 18px;
        width: 18px;
        background-color: #fff;
        border: 1px solid #d1d5db;
        border-radius: 50%;
      }

      .custom-radio input:checked~.radio-mark {
        background-color: #fff;
        border-color: #3366ff;
      }

      .radio-mark:after {
        content: "";
        position: absolute;
        display: none;
      }

      .custom-radio input:checked~.radio-mark:after {
        display: block;
      }

      .custom-radio .radio-mark:after {
        top: 4px;
        left: 4px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #3366ff;
      }

      .pagination-item {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        cursor: pointer;
      }

      .pagination-item.active {
        background-color: #3366ff;
        color: white;
      }

      .custom-select {
        position: relative;
        display: inline-block;
      }

      .custom-select-trigger {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background-color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 120px;
      }

      .custom-select-options {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background-color: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-top: 4px;
        z-index: 10;
        display: none;
        max-height: 200px;
        overflow-y: auto;
      }

      .custom-select-option {
        padding: 8px 12px;
        cursor: pointer;
      }

      .custom-select-option:hover {
        background-color: #f1f5f9;
      }

      .custom-select.open .custom-select-options {
        display: block;
      }

      .modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 50;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
      }

      .modal.active {
        opacity: 1;
        visibility: visible;
      }

      .modal-content {
        background-color: white;
        border-radius: 12px;
        padding: 24px;
        width: 100%;
        max-width: 500px;
        transform: translateY(20px);
        transition: transform 0.3s ease;
      }

      .modal.active .modal-content {
        transform: translateY(0);
      }
    </style>
  </head>

  <body class="min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm fixed top-0 left-0 right-0 z-30">
      <div class="flex items-center justify-between px-4 h-16">
        <div class="flex items-center">
          <button id="sidebar-toggle"
            class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-primary mr-2">
            <i class="ri-menu-line ri-lg"></i>
          </button>
          <div class="font-['Pacifico'] text-xl text-primary"><img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Platform Logo" width="150"></div>
          <span class="ml-4 text-lg font-medium hidden md:block">
            Admin Dashboard
          </span>
        </div>
        <div class="flex items-center space-x-4">
          <div class="relative">
            <button id="notification-btn"
              class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-primary relative">
              <i class="ri-notification-3-line ri-lg"></i>
              <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>
          </div>
          <div class="relative">
            <button id="user-menu-btn" class="flex items-center">
              <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center">
                <span class="text-sm font-medium"><img src="<?= $avatarUrl ?>" alt="ss"></span>
              </div>
              <span class="ml-2 text-sm font-medium hidden md:block">
                <?= $adminName ?>
              </span>
              <i class="ri-arrow-down-s-line ml-1 text-gray-500"></i>
            </button>
          </div>
        </div>
      </div>
    </header>
    <!-- Sidebar toggle for mobile/tablet only -->
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const sidebarToggle = document.getElementById("sidebar-toggle");
        const sidebar = document.getElementById("sidebar");
        const main = document.querySelector("main");
        const menuIcon = sidebarToggle.querySelector("i");

        function setSidebar(open) {
          if (open) {
            sidebar.classList.remove("-translate-x-full");
            sidebar.classList.add("translate-x-0");
            menuIcon.classList.remove("ri-menu-line");
            menuIcon.classList.add("ri-close-line");
          } else {
            sidebar.classList.remove("translate-x-0");
            sidebar.classList.add("-translate-x-full");
            menuIcon.classList.remove("ri-close-line");
            menuIcon.classList.add("ri-menu-line");
          }
        }

        sidebarToggle.addEventListener("click", function () {
          const isClosed = sidebar.classList.contains("-translate-x-full");
          setSidebar(isClosed);
        });

        function handleResize() {
          if (window.innerWidth >= 768) {
            sidebar.classList.remove("-translate-x-full");
            sidebar.classList.add("translate-x-0");
            main.classList.add("md:pl-64");
            menuIcon.classList.remove("ri-close-line");
            menuIcon.classList.add("ri-menu-line");
          } else {
            sidebar.classList.remove("translate-x-0");
            sidebar.classList.add("-translate-x-full");
            main.classList.remove("md:pl-64");
            menuIcon.classList.remove("ri-close-line");
            menuIcon.classList.add("ri-menu-line");
          }
        }

        window.addEventListener("resize", handleResize);
        handleResize();
      });
    </script>
    <!-- Sidebar -->
    <aside id="sidebar"
      class="fixed top-16 left-0 bottom-0 w-64 bg-white shadow-sm z-20 sidebar-transition transform md:translate-x-0 -translate-x-full">
      <div class="h-full flex flex-col">
        <div class="p-4 border-b">
          <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center">
              <span class="text-sm font-medium"><img src="<?= $avatarUrl ?>" alt="ad"></span>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium"><?= $adminName ?></p>
              <p class="text-xs text-gray-500">Super Admin</p>
            </div>
          </div>
        </div>
        <nav class="flex-1 p-4 overflow-y-auto">
          <ul class="space-y-1">
            <li>
              <a href="../"
            class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50">
            <div class="w-5 h-5 flex items-center justify-center mr-3">
              <i class="ri-user-line"></i>
            </div>
            <span>Users</span>
              </a>
            </li>
            <li>
              <a href="../subscription/"
            class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50">
            <div class="w-5 h-5 flex items-center justify-center mr-3">
              <i class="ri-vip-crown-line"></i>
            </div>
            <span>Subscriptions</span>
              </a>
            </li>
            <li>
              <a href="../wallets/"
            class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50">
            <div class="w-5 h-5 flex items-center justify-center mr-3">
              <i class="ri-wallet-3-line"></i>
            </div>
            <span>Wallets</span>
              </a>
            </li>
            <li>
              <a
                href="../plan/"
                class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50"
              >
                <div class="w-5 h-5 flex items-center justify-center mr-3">
                  <i class="ri-list-settings-line"></i>
                </div>
                <span>Plans</span>
              </a>
            </li>
            <li>
              <a href="../payment/"
            class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-primary bg-blue-50">
            <div class="w-5 h-5 flex items-center justify-center mr-3">
              <i class="ri-exchange-dollar-line"></i>
            </div>
            <span>Payments</span>
              </a>
            </li>
            <li>
              <a href="../pend_payment/"
            class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50">
            <div class="w-5 h-5 flex items-center justify-center mr-3">
              <i class="ri-time-line"></i>
            </div>
            <span>Pending Payments</span>
              </a>
            </li>
            <li>
              <a href="../settings/"
            class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50">
            <div class="w-5 h-5 flex items-center justify-center mr-3">
              <i class="ri-settings-3-line"></i>
            </div>
            <span>Settings</span>
              </a>
            </li>
          </ul>
        </nav>
        <div class="p-4 border-t">
          <button id="logout-btn"
            class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-red-600 hover:bg-red-50">
            <div class="w-5 h-5 flex items-center justify-center mr-3">
              <i class="ri-logout-box-line"></i>
            </div>
            <span>Logout</span>
          </button>
        </div>
        <!-- Logout Confirmation Modal -->
        <div id="logout-modal" class="modal">
          <div class="modal-content max-w-sm">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mr-4">
                <i class="ri-logout-box-line ri-lg"></i>
              </div>
              <h3 class="text-lg font-medium text-gray-800">
                Are you sure you want to logout?
              </h3>
            </div>
            <div class="flex items-center justify-end space-x-3">
              <button id="cancel-logout"
                class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50">
                Cancel
              </button>
              <button id="confirm-logout"
                class="px-4 py-2 bg-red-600 text-white rounded-button whitespace-nowrap hover:bg-red-700">
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>
    </aside>
    <main class="pt-16 md:pl-64 transition-all duration-300">
      <!-- Payments Tab: TRC-20 USDT Only -->
      <div id="payments-tab" class="tab-content p-4 md:p-6">
        <div class="mb-6">
          <h1 class="text-2xl font-semibold text-gray-800">
            Payment Transactions
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Monitor and manage all USDT (TRC-20) payment transactions
          </p>
        </div>
        <!-- Filter/Search Section (Unchanged) -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
          <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
          >
            <div class="flex items-center gap-4">
              <div class="custom-select" id="transaction-date-filter">
                <div class="custom-select-trigger">
                  <span>Last 7 days</span>
                  <i class="ri-arrow-down-s-line ml-2"></i>
                </div>
                <div class="custom-select-options">
                  <div class="custom-select-option" data-value="today">
                    Today
                  </div>
                  <div class="custom-select-option" data-value="yesterday">
                    Yesterday
                  </div>
                  <div class="custom-select-option" data-value="7days">
                    Last 7 days
                  </div>
                  <div class="custom-select-option" data-value="30days">
                    Last 30 days
                  </div>
                  <div class="custom-select-option" data-value="custom">
                    Custom Range
                  </div>
                </div>
              </div>
              <div class="custom-select" id="transaction-status-filter">
                <div class="custom-select-trigger">
                  <span>All Status</span>
                  <i class="ri-arrow-down-s-line ml-2"></i>
                </div>
                <div class="custom-select-options">
                  <div class="custom-select-option" data-value="all">
                    All Status
                  </div>
                  <div class="custom-select-option" data-value="success">
                    Success
                  </div>
                  <div class="custom-select-option" data-value="pending">
                    Pending
                  </div>
                  <div class="custom-select-option" data-value="failed">
                    Failed
                  </div>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <div class="relative">
                <div
                  class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
                >
                  <i class="ri-search-line text-gray-400"></i>
                </div>
                <input
                  type="text"
                  id="transaction-search"
                  class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                  placeholder="Search transactions..."
                />
              </div>
              <button
                class="px-4 py-2 bg-primary text-white rounded-button whitespace-nowrap flex items-center"
                id="export-transactions-btn"
              >
                <i class="ri-download-2-line mr-2"></i>
                Export
              </button>
            </div>
          </div>
        </div>
        <!-- Transactions Table: USDT (TRC-20) Only -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
              <thead>
                <tr class="bg-gray-50">
                  <th class="px-4 py-3 text-left">
                    <label class="custom-checkbox">
                      <input type="checkbox" id="select-all-transactions" />
                      <span class="checkbox-mark"></span>
                    </label>
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Order ID
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    User
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Plan
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Amount (USDT)
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    TXID
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Date &amp; Time
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Status
                  </th>
                  <th
                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Receipt
                  </th>
                </tr>
              </thead>
              <tbody
                class="divide-y divide-gray-200"
                id="transactions-table-body"
              >
                <!-- Transaction rows will be rendered by JS -->
              </tbody>
            </table>
          </div>
          <!-- Pagination Section -->
          <div class="px-4 py-3 flex items-center justify-between border-t">
            <div class="flex items-center text-sm text-gray-500">
              <span>Showing</span>
              <div class="custom-select mx-2" id="transaction-page-size-select">
                <div class="custom-select-trigger">
                  <span>10</span>
                  <i class="ri-arrow-down-s-line ml-1"></i>
                </div>
                <div class="custom-select-options">
                  <div class="custom-select-option" data-value="10">10</div>
                  <div class="custom-select-option" data-value="20">20</div>
                  <div class="custom-select-option" data-value="50">50</div>
                </div>
              </div>
              <span id="transactions-total-count">of 0 transactions</span>
            </div>
            <div
              class="flex items-center space-x-1"
              id="transactions-pagination"
            >
              <button
                class="pagination-item text-gray-500 hover:bg-gray-100"
                id="transactions-prev-page"
              >
                <i class="ri-arrow-left-s-line"></i>
              </button>
              <!-- Page numbers will be injected here -->
              <button
                class="pagination-item text-gray-600 hover:bg-gray-100 transactions-page-btn"
                data-page="1"
              >
                1
              </button>
              <button
                class="pagination-item text-gray-600 hover:bg-gray-100 transactions-page-btn"
                data-page="2"
              >
                2
              </button>
              <button
                class="pagination-item text-gray-600 hover:bg-gray-100 transactions-page-btn"
                data-page="3"
              >
                3
              </button>
              <button
                class="pagination-item text-gray-600 hover:bg-gray-100 transactions-page-btn"
                data-page="4"
              >
                4
              </button>
              <button
                class="pagination-item text-gray-600 hover:bg-gray-100 transactions-page-btn"
                data-page="5"
              >
                5
              </button>
              <button
                class="pagination-item text-gray-500 hover:bg-gray-100"
                id="transactions-next-page"
              >
                <i class="ri-arrow-right-s-line"></i>
              </button>
            </div>
          </div>
        </div>
        <!-- Receipt Modal (hidden by default) -->
        <div id="receipt-modal" class="modal">
          <div class="modal-content max-w-lg">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-800">
                Transaction Receipt
              </h3>
              <button class="modal-close text-gray-400 hover:text-gray-500">
                <i class="ri-close-line ri-lg"></i>
              </button>
            </div>
            <div class="space-y-4" id="receipt-modal-body">
              <!-- Receipt details will be filled by JS -->
            </div>
          </div>
        </div>
      </div>
      <script>
        // =========================
// Payments Tab JS: Real Backend Integration
// =========================
(function () {
  // --- State ---
  let currentPage = 1;
  let pageSize = 10;
  let statusFilter = "all";
  let dateFilter = "7days";
  let searchQuery = "";
  let totalTransactions = 0;

  // --- DOM Elements ---
  const tbody = document.getElementById("transactions-table-body");
  const pageSizeSelect = document.getElementById("transaction-page-size-select");
  const statusFilterSelect = document.getElementById("transaction-status-filter");
  const dateFilterSelect = document.getElementById("transaction-date-filter");
  const searchInput = document.getElementById("transaction-search");
  const pagination = document.getElementById("transactions-pagination");
  const prevBtn = document.getElementById("transactions-prev-page");
  const nextBtn = document.getElementById("transactions-next-page");
  const totalCount = document.getElementById("transactions-total-count");

  // --- Plan Colors ---
  const planColors = {
    free: "bg-gray-100 text-gray-600",
    pro: "bg-blue-100 text-primary",
    enterprise: "bg-purple-100 text-purple-600",
  };

  // --- API Functions ---
  async function fetchTransactions() {
    try {
      const params = new URLSearchParams({
        page: currentPage,
        limit: pageSize,
        status: statusFilter,
        search: searchQuery,
        date_filter: dateFilter,
      });

      const response = await fetch(`api/modal.php?action=get_transactions&${params}`);
      const result = await response.json();

      if (!result.success) {
        throw new Error(result.error || "Failed to fetch transactions");
      }

      return result;
    } catch (error) {
      console.error("Error fetching transactions:", error);
      showNotification("Error loading transactions: " + error.message, "error");
      return { data: [], pagination: { total: 0, pages: 0 } };
    }
  }

  async function updateTransactionStatus(transactionId, status) {
    try {
      const response = await fetch("api/modal.php?action=update_transaction_status", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          transaction_id: transactionId,
          status: status,
        }),
      });

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.error || "Failed to update transaction");
      }

      showNotification("Transaction updated successfully", "success");
      return true;
    } catch (error) {
      console.error("Error updating transaction:", error);
      showNotification("Error updating transaction: " + error.message, "error");
      return false;
    }
  }

  async function fetchTransactionDetails(transactionId) {
    try {
      const response = await fetch(`api/modal.php?action=get_transaction_details&id=${transactionId}`);
      const result = await response.json();

      if (!result.success) {
        throw new Error(result.error || "Failed to fetch transaction details");
      }

      return result.data;
    } catch (error) {
      console.error("Error fetching transaction details:", error);
      showNotification("Error loading transaction details: " + error.message, "error");
      return null;
    }
  }

  async function exportTransactions() {
    try {
      const params = new URLSearchParams({
        status: statusFilter,
        search: searchQuery,
        date_filter: dateFilter,
      });

      const response = await fetch(`api/modal.php?action=export_transactions&${params}`);
      const result = await response.json();

      if (!result.success) {
        throw new Error(result.error || "Failed to export transactions");
      }

      // Create CSV content
      let csv = "Order ID,User,Plan,Amount (USDT),TXID,Date & Time,Status\n";
      result.data.forEach((tx) => {
        const date = new Date(tx.created_at).toLocaleString();
        csv += `"${tx.order_id}","${tx.user_name}","${tx.plan_name}","${tx.amount}","${tx.txid}","${date}","${tx.status}"\n`;
      });

      // Download CSV
      const blob = new Blob([csv], { type: "text/csv" });
      const a = document.createElement("a");
      a.href = URL.createObjectURL(blob);
      a.download = `transactions_${new Date().toISOString().slice(0, 10)}.csv`;
      a.click();

      showNotification("Transactions exported successfully", "success");
    } catch (error) {
      console.error("Error exporting transactions:", error);
      showNotification("Error exporting transactions: " + error.message, "error");
    }
  }

  // --- Render Functions ---
  async function renderTable() {
    // Show loading state
    tbody.innerHTML = '<tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">Loading transactions...</td></tr>';

    const result = await fetchTransactions();
    const transactions = result.data;
    totalTransactions = result.pagination.total;

    if (transactions.length === 0) {
      tbody.innerHTML = '<tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">No transactions found</td></tr>';
      totalCount.textContent = "of 0 transactions";
      renderPagination(0);
      return;
    }

    // Render rows
    tbody.innerHTML = transactions
      .map(
        (tx) => `
      <tr>
        <td class="px-4 py-3 whitespace-nowrap">
          <label class="custom-checkbox">
            <input type="checkbox" class="transaction-checkbox" />
            <span class="checkbox-mark"></span>
          </label>
        </td>
        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">${tx.order_id || 'N/A'}</td>
        <td class="px-4 py-3 whitespace-nowrap">
          <div class="flex items-center">
            <div class="w-8 h-8 rounded-full ${tx.user_color} flex items-center justify-center">
              <span class="text-sm font-medium">${tx.user_initials}</span>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-800">${tx.user_name}</p>
              <p class="text-xs text-gray-500">${tx.user_email}</p>
            </div>
          </div>
        </td>
        <td class="px-4 py-3 whitespace-nowrap">
          <span class="px-2 py-1 text-xs font-medium ${
            planColors[tx.plan_name] || "bg-gray-100 text-gray-600"
          } rounded-full">${tx.plan_display_name}</span>
        </td>
        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-800">$${parseFloat(tx.amount).toFixed(2)} USDT</td>
        <td class="px-4 py-3 whitespace-nowrap">
          <a href="https://tronscan.org/#/transaction/${tx.txid}" target="_blank" 
             class="text-primary underline break-all text-xs" title="${tx.txid}">${tx.short_txid}</a>
        </td>
        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">${tx.formatted_date}</td>
        <td class="px-4 py-3 whitespace-nowrap">
          <div class="flex items-center space-x-2">
            <span class="px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(tx.status)}">${getStatusText(tx.status)}</span>
            ${getStatusActions(tx.id, tx.status)}
          </div>
        </td>
        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
          <div class="flex items-center space-x-1">
            <button class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-primary rounded-full hover:bg-blue-50 receipt-btn" 
                    data-id="${tx.id}" title="View Receipt">
              <i class="ri-eye-line"></i>
            </button>
            ${tx.screenshot_path ? `
              <button class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-green-600 rounded-full hover:bg-green-50 screenshot-btn" 
                      data-path="${tx.screenshot_path}" title="View Screenshot">
                <i class="ri-image-line"></i>
              </button>
            ` : ''}
          </div>
        </td>
      </tr>
    `
      )
      .join("");

    // Update total count
    totalCount.textContent = `of ${totalTransactions} transactions`;

    // Render pagination
    renderPagination(result.pagination.pages);

    // Attach event listeners
    attachEventListeners();
  }

  function getStatusColor(status) {
    switch (status) {
      case "success":
        return "bg-green-50 text-green-600";
      case "pending":
        return "bg-yellow-50 text-yellow-600";
      case "failed":
        return "bg-red-50 text-red-600";
      default:
        return "bg-gray-50 text-gray-600";
    }
  }

  function getStatusText(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
  }

  function getStatusActions(transactionId, currentStatus) {
    if (currentStatus === "pending") {
      return `
        <button class="approve-btn px-2 py-1 text-xs bg-green-100 text-green-700 hover:bg-green-200 rounded" 
                data-id="${transactionId}" title="Approve Transaction">
          <i class="ri-check-line"></i>
        </button>
        <button class="reject-btn px-2 py-1 text-xs bg-red-100 text-red-700 hover:bg-red-200 rounded" 
                data-id="${transactionId}" title="Reject Transaction">
          <i class="ri-close-line"></i>
        </button>
      `;
    }
    return "";
  }

  function renderPagination(totalPages) {
    // Remove old page buttons
    pagination.querySelectorAll(".transactions-page-btn").forEach((btn) => btn.remove());

    // Insert new page buttons
    for (let i = 1; i <= totalPages; i++) {
      const btn = document.createElement("button");
      btn.className = "pagination-item text-gray-600 hover:bg-gray-100 transactions-page-btn";
      btn.textContent = i;
      btn.setAttribute("data-page", i);
      if (i === currentPage) btn.classList.add("active", "bg-blue-50", "text-primary");
      btn.onclick = function () {
        currentPage = i;
        renderTable();
      };
      pagination.insertBefore(btn, nextBtn);
    }

    // Prev/next
    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages || totalPages === 0;
    prevBtn.classList.toggle("opacity-50", prevBtn.disabled);
    prevBtn.classList.toggle("cursor-not-allowed", prevBtn.disabled);
    nextBtn.classList.toggle("opacity-50", nextBtn.disabled);
    nextBtn.classList.toggle("cursor-not-allowed", nextBtn.disabled);
  }

  function attachEventListeners() {
    // Receipt buttons
    document.querySelectorAll(".receipt-btn").forEach((btn) => {
      btn.onclick = async function () {
        const transactionId = this.getAttribute("data-id");
        await showReceiptModal(transactionId);
      };
    });

    // Screenshot buttons
    document.querySelectorAll(".screenshot-btn").forEach((btn) => {
      btn.onclick = function () {
        const screenshotPath = this.getAttribute("data-path");
        showScreenshotModal(screenshotPath);
      };
    });

    // Approve buttons
    document.querySelectorAll(".approve-btn").forEach((btn) => {
      btn.onclick = async function () {
        const transactionId = this.getAttribute("data-id");
        if (confirm("Are you sure you want to approve this transaction?")) {
          const success = await updateTransactionStatus(transactionId, "success");
          if (success) {
            renderTable();
          }
        }
      };
    });

    // Reject buttons
    document.querySelectorAll(".reject-btn").forEach((btn) => {
      btn.onclick = async function () {
        const transactionId = this.getAttribute("data-id");
        if (confirm("Are you sure you want to reject this transaction?")) {
          const success = await updateTransactionStatus(transactionId, "failed");
          if (success) {
            renderTable();
          }
        }
      };
    });
  }

  // --- Modal Functions ---
  // Replace the showReceiptModal function in your JavaScript with this fixed version:

async function showReceiptModal(transactionId) {
    const transaction = await fetchTransactionDetails(transactionId);
    if (!transaction) return;

    const modal = document.getElementById("receipt-modal");
    const body = document.getElementById("receipt-modal-body");

    // Handle null user_name safely
    const userName = transaction.user_name || 'Unknown User';
    const userEmail = transaction.user_email || 'No email';
    
    // Generate user initials safely
    let initials = 'UN'; // Default initials
    if (userName && userName !== 'Unknown User') {
        const names = userName.split(' ').filter(name => name.trim() !== '');
        if (names.length > 0) {
            initials = names.map(name => name.charAt(0).toUpperCase()).join('').slice(0, 2);
        }
    }
    
    // Generate user color
    const colors = [
        'bg-blue-100 text-blue-600', 'bg-green-100 text-green-600', 'bg-red-100 text-red-600',
        'bg-purple-100 text-purple-600', 'bg-orange-100 text-orange-600', 'bg-pink-100 text-pink-600'
    ];
    const userColor = colors[transaction.user_id % colors.length];

    body.innerHTML = `
        <div>
            <p class="text-sm text-gray-500">Order ID</p>
            <p class="text-sm font-medium text-gray-800">${transaction.order_id || 'N/A'}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">User</p>
            <div class="flex items-center mt-1">
                <div class="w-8 h-8 rounded-full ${userColor} flex items-center justify-center">
                    <span class="text-sm font-medium">${initials}</span>
                </div>
                <div class="ml-3">
                    <span class="text-sm font-medium text-gray-800">${userName}</span>
                    <p class="text-xs text-gray-500">${userEmail}</p>
                </div>
            </div>
        </div>
        <div>
            <p class="text-sm text-gray-500">Plan</p>
            <span class="px-2 py-1 text-xs font-medium ${planColors[transaction.plan_name?.toLowerCase()] || "bg-gray-100 text-gray-600"} rounded-full">
                ${transaction.plan_name ? transaction.plan_name.charAt(0).toUpperCase() + transaction.plan_name.slice(1) : 'Free'}
            </span>
        </div>
        <div>
            <p class="text-sm text-gray-500">Amount</p>
            <p class="text-sm font-medium text-gray-800">${parseFloat(transaction.amount).toFixed(2)} USDT</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">TXID</p>
            <a href="https://tronscan.org/#/transaction/${transaction.txid}" target="_blank" 
               class="text-primary underline break-all text-xs">${transaction.txid}</a>
        </div>
        <div>
            <p class="text-sm text-gray-500">Date & Time</p>
            <p class="text-sm font-medium text-gray-800">${new Date(transaction.created_at).toLocaleString("en-US", {
                month: "short",
                day: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            })}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Status</p>
            <span class="px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(transaction.status)}">${getStatusText(transaction.status)}</span>
        </div>
        ${transaction.screenshot_path ? `
            <div>
                <p class="text-sm text-gray-500">Payment Screenshot</p>
                <button onclick="showScreenshotModal('${transaction.screenshot_path}')" 
                        class="mt-2 px-3 py-1 text-xs bg-blue-100 text-blue-700 hover:bg-blue-200 rounded">
                    <i class="ri-image-line mr-1"></i>View Screenshot
                </button>
            </div>
        ` : ''}
    `;

    modal.classList.add("active");
    document.body.style.overflow = "hidden";

    // Close modal logic
    modal.querySelectorAll(".modal-close").forEach((btn) => {
        btn.onclick = function () {
            modal.classList.remove("active");
            document.body.style.overflow = "";
        };
    });

    modal.onclick = function (e) {
        if (e.target === modal) {
            modal.classList.remove("active");
            document.body.style.overflow = "";
        }
    };
}
  function showScreenshotModal(screenshotPath) {
    // Create screenshot modal if it doesn't exist
    let screenshotModal = document.getElementById("screenshot-modal");
    if (!screenshotModal) {
      screenshotModal = document.createElement("div");
      screenshotModal.id = "screenshot-modal";
      screenshotModal.className = "modal";
      screenshotModal.innerHTML = `
        <div class="modal-content max-w-2xl">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-800">Payment Screenshot</h3>
            <button class="modal-close text-gray-400 hover:text-gray-500">
              <i class="ri-close-line ri-lg"></i>
            </button>
          </div>
          <div class="text-center">
            <img id="screenshot-image" src="" alt="Payment Screenshot" class="max-w-full h-auto rounded-lg shadow-lg">
          </div>
        </div>
      `;
      document.body.appendChild(screenshotModal);
    }

    const image = screenshotModal.querySelector("#screenshot-image");
    image.src = "../../../home/payment/api/" + screenshotPath; // Adjust path as needed

    screenshotModal.classList.add("active");
    document.body.style.overflow = "hidden";

    // Close modal logic
    screenshotModal.querySelectorAll(".modal-close").forEach((btn) => {
      btn.onclick = function () {
        screenshotModal.classList.remove("active");
        document.body.style.overflow = "";
      };
    });

    screenshotModal.onclick = function (e) {
      if (e.target === screenshotModal) {
        screenshotModal.classList.remove("active");
        document.body.style.overflow = "";
      }
    };
  }

  // --- Utility Functions ---
  function showNotification(message, type = "info") {
    // Create notification element
    const notification = document.createElement("div");
    notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transform transition-transform duration-300 translate-x-full ${
      type === "success" ? "bg-green-500 text-white" : 
      type === "error" ? "bg-red-500 text-white" : 
      "bg-blue-500 text-white"
    }`;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Show notification
    setTimeout(() => {
      notification.classList.remove("translate-x-full");
    }, 100);

    // Hide and remove notification
    setTimeout(() => {
      notification.classList.add("translate-x-full");
      setTimeout(() => {
        document.body.removeChild(notification);
      }, 300);
    }, 3000);
  }

  // --- Event Listeners ---
  prevBtn.onclick = function () {
    if (currentPage > 1) {
      currentPage--;
      renderTable();
    }
  };

  nextBtn.onclick = function () {
    const totalPages = Math.ceil(totalTransactions / pageSize);
    if (currentPage < totalPages) {
      currentPage++;
      renderTable();
    }
  };

  // Page size select
  pageSizeSelect.addEventListener("change", function () {
    const value = pageSizeSelect.getAttribute("data-value");
    if (value) {
      pageSize = parseInt(value, 10);
      currentPage = 1;
      renderTable();
    }
  });

  // Status filter
  statusFilterSelect.addEventListener("change", function () {
    statusFilter = statusFilterSelect.getAttribute("data-value") || "all";
    currentPage = 1;
    renderTable();
  });

  // Date filter
  dateFilterSelect.addEventListener("change", function () {
    dateFilter = dateFilterSelect.getAttribute("data-value") || "7days";
    currentPage = 1;
    renderTable();
  });

  // Search
  let searchTimeout;
  searchInput.addEventListener("input", function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      searchQuery = searchInput.value.trim().toLowerCase();
      currentPage = 1;
      renderTable();
    }, 500); // Debounce search
  });

  // Export CSV
  document.getElementById("export-transactions-btn").onclick = exportTransactions;

  // Select all checkbox
  document.getElementById("select-all-transactions").addEventListener("change", function () {
    const checkboxes = document.querySelectorAll(".transaction-checkbox");
    checkboxes.forEach((checkbox) => {
      checkbox.checked = this.checked;
    });
  });

  // --- Custom Select Enhancement ---
  document.querySelectorAll("#payments-tab .custom-select").forEach((select) => {
    const trigger = select.querySelector(".custom-select-trigger");
    const options = select.querySelectorAll(".custom-select-option");

    if (trigger) {
      trigger.addEventListener("click", function (e) {
        e.stopPropagation();
        document.querySelectorAll("#payments-tab .custom-select").forEach((other) => {
          if (other !== select) other.classList.remove("open");
        });
        select.classList.toggle("open");
      });
    }

    options.forEach((option) => {
      option.addEventListener("click", function () {
        const value = this.getAttribute("data-value");
        select.setAttribute("data-value", value);
        select.classList.remove("open");

        // Update trigger text
        const triggerSpan = trigger.querySelector("span");
        if (triggerSpan) triggerSpan.textContent = this.textContent.trim();

        // Dispatch change event
        const event = new CustomEvent("change", { detail: { value } });
        select.dispatchEvent(event);
      });
    });
  });

  // Close selects when clicking outside
  document.addEventListener("click", function () {
    document.querySelectorAll("#payments-tab .custom-select").forEach((select) => 
      select.classList.remove("open")
    );
  });

  // --- Initialize ---
  renderTable();
})();
        // =========================
        // End Payments Tab JS
        // =========================
      </script>
     
    </main>
      <script id="modal-script">
      document.addEventListener("DOMContentLoaded", function () {
        const modals = document.querySelectorAll(".modal");
        const modalCloseButtons = document.querySelectorAll(".modal-close");
        const toast = document.getElementById("toast-success");

        function showToast() {
          toast.classList.remove("translate-x-full");
          setTimeout(() => {
            toast.classList.add("translate-x-full");
          }, 3000);
        }

        // Handle wallet verification
        const verifyWalletButtons = document.querySelectorAll(
          '[data-action="verify"]'
        );
        const verifyWalletModal = document.getElementById(
          "verify-wallet-modal"
        );
        const confirmVerifyWalletBtn = document.getElementById(
          "confirm-verify-wallet"
        );

        verifyWalletButtons.forEach((button) => {
          button.addEventListener("click", function () {
            const row = this.closest("tr");
            const userName = row.querySelector(
              ".text-sm.font-medium.text-gray-800"
            ).textContent;
            const blockchain = row.querySelector(
              ".text-sm.text-gray-600"
            ).textContent;
            const walletAddress = row.querySelector(
              ".text-sm.text-gray-600:nth-child(2)"
            ).textContent;

            document.getElementById("verify-wallet-user").textContent =
              userName;
            document.getElementById("verify-wallet-blockchain").textContent =
              blockchain;
            document.getElementById("verify-wallet-address").textContent =
              walletAddress;

            openModal("verify-wallet-modal");
          });
        });

        if (confirmVerifyWalletBtn) {
          confirmVerifyWalletBtn.addEventListener("click", function () {
            const walletAddress = document.getElementById(
              "verify-wallet-address"
            ).textContent;
            const statusElements = document.querySelectorAll(
              "td:nth-child(6) span"
            );

            statusElements.forEach((element) => {
              if (
                element
                  .closest("tr")
                  .querySelector(".text-sm.text-gray-600:nth-child(2)")
                  .textContent === walletAddress
              ) {
                element.className =
                  "px-2 py-1 text-xs font-medium bg-green-50 text-green-600 rounded-full";
                element.textContent = "Valid";
              }
            });

            closeModal(verifyWalletModal);
            showToast();
          });
        }
        // Add tooltip container to body
        const tooltip = document.createElement("div");
        tooltip.className =
          "fixed px-2 py-1 text-xs text-white bg-gray-900 rounded pointer-events-none opacity-0 transition-opacity duration-200 z-50";
        document.body.appendChild(tooltip);
        const logoutBtn = document.getElementById("logout-btn");
        const logoutModal = document.getElementById("logout-modal");
        const cancelLogoutBtn = document.getElementById("cancel-logout");
        const confirmLogoutBtn = document.getElementById("confirm-logout");
        // Handle logout flow
        if (logoutBtn) {
          logoutBtn.addEventListener("click", function () {
            openModal("logout-modal");
          });
        }
        if (cancelLogoutBtn) {
          cancelLogoutBtn.addEventListener("click", function () {
            closeModal(logoutModal);
          });
        }
        if (confirmLogoutBtn) {
          confirmLogoutBtn.addEventListener("click", function () {
            // Clear any session data here
            window.location.href = "/sales-spy/admin/logout/"; // Redirect to login page
          });
        }
        // Action buttons for user actions
        const viewUserButtons = document.querySelectorAll(
          '[data-action="view"]'
        );
        const deleteUserButtons = document.querySelectorAll(
          '[data-action="delete"]'
        );
        const suspendUserButtons = document.querySelectorAll(
          '[data-action="suspend"]'
        );
        function openModal(modalId) {
          const modal = document.getElementById(modalId);
          if (modal) {
            modal.classList.add("active");
            document.body.style.overflow = "hidden";
          }
        }
        function closeModal(modal) {
          modal.classList.remove("active");
          document.body.style.overflow = "";
        }
        // Close modal when clicking close button
        modalCloseButtons.forEach((button) => {
          button.addEventListener("click", function () {
            const modal = this.closest(".modal");
            closeModal(modal);
          });
        });
        // Close modal when clicking outside the modal content
        modals.forEach((modal) => {
          modal.addEventListener("click", function (e) {
            if (e.target === this) {
              closeModal(this);
            }
          });
        });
        // Open user action modals
        viewUserButtons.forEach((button) => {
          button.addEventListener("click", function () {
            const userId = this.getAttribute("data-user-id");
            openModal("view-user-modal");
          });
        });
        // Handle subscription action buttons
        const subscriptionButtons = document.querySelectorAll(
          ".subscription-action"
        );
        subscriptionButtons.forEach((button) => {
          // Tooltip handling
          button.addEventListener("mouseenter", function (e) {
            const tooltipText = this.getAttribute("data-tooltip");
            tooltip.textContent = tooltipText;
            tooltip.style.opacity = "1";
            // Position tooltip above the button
            const rect = this.getBoundingClientRect();
            tooltip.style.top = rect.top - 30 + "px";
            tooltip.style.left =
              rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px";
          });
          button.addEventListener("mouseleave", function () {
            tooltip.style.opacity = "0";
          });
          // Modal handling
          button.addEventListener("click", function () {
            const action = this.getAttribute("data-action");
            switch (action) {
              case "history":
                openModal("subscription-history-modal");
                break;
              case "pause":
                openModal("pause-subscription-modal");
                break;
              case "cancel":
                openModal("cancel-subscription-modal");
                break;
            }
          });
        });
        deleteUserButtons.forEach((button) => {
          button.addEventListener("click", function () {
            const userId = this.getAttribute("data-user-id");
            openModal("delete-user-modal");
          });
        });
        suspendUserButtons.forEach((button) => {
          button.addEventListener("click", function () {
            const userId = this.getAttribute("data-user-id");
            openModal("suspend-user-modal");
          });
        });
      });
    </script>
  </body>
</html>