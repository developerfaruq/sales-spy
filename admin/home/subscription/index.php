<?php
require '../../config/db.php';
require 'api/auth_check.php';


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
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css"
    />
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

      input:checked + .switch-slider {
        background-color: #3366ff;
      }

      input:checked + .switch-slider:before {
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

      .custom-checkbox input:checked ~ .checkbox-mark {
        background-color: #3366ff;
        border-color: #3366ff;
      }

      .checkbox-mark:after {
        content: "";
        position: absolute;
        display: none;
      }

      .custom-checkbox input:checked ~ .checkbox-mark:after {
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

      .custom-radio input:checked ~ .radio-mark {
        background-color: #fff;
        border-color: #3366ff;
      }

      .radio-mark:after {
        content: "";
        position: absolute;
        display: none;
      }

      .custom-radio input:checked ~ .radio-mark:after {
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

  max-height: 80vh;     /* Prevent modal from taking entire screen */
  overflow-y: auto;
      }

      .modal.active .modal-content {
        transform: translateY(0);
      }
    </style>
  </head>

  <body class="min-h-screen">
    <!-- Header and Sidebar (optional for standalone) -->
    <header class="bg-white shadow-sm fixed top-0 left-0 right-0 z-30">
      <div class="flex items-center justify-between px-4 h-16">
        <div class="flex items-center">
          <button
            id="sidebar-toggle"
            class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-primary mr-2"
          >
            <i class="ri-menu-line ri-lg"></i>
          </button>
          <div class="font-['Pacifico'] text-xl text-primary">Sales-Spy</div>
          <span class="ml-4 text-lg font-medium hidden md:block"
            >Admin Dashboard</span
          >
        </div>
        <div class="flex items-center space-x-4">
          <div class="relative">
            <button
              id="notification-btn"
              class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-primary relative"
            >
              <i class="ri-notification-3-line ri-lg"></i>
              <span
                class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full"
              ></span>
            </button>
          </div>
          <div class="relative">
            <button id="user-menu-btn" class="flex items-center">
              <div
                class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center"
              >
                <span class="text-sm font-medium"><img src="<?= $avatarUrl ?>" alt="ad"></span>
              </div>
              <span class="ml-2 text-sm font-medium hidden md:block"
                ><?= $adminName ?></span
              >
              <i class="ri-arrow-down-s-line ml-1 text-gray-500"></i>
            </button>
          </div>
        </div>
      </div>
    </header>
    <!-- Sidebar -->
    <aside
      id="sidebar"
      class="fixed top-16 left-0 bottom-0 w-64 bg-white shadow-sm z-20 sidebar-transition transform md:translate-x-0 -translate-x-full"
    >
      <div class="h-full flex flex-col">
        <div class="p-4 border-b">
          <div class="flex items-center">
            <div
              class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center"
            >
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
              <a
          href="../"
          class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50"
              >
          <div class="w-5 h-5 flex items-center justify-center mr-3">
            <i class="ri-user-line"></i>
          </div>
          <span>Users</span>
              </a>
            </li>
            <li>
              <a
          href="../subscription/"
          class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-primary bg-blue-50"
              >
          <div class="w-5 h-5 flex items-center justify-center mr-3">
            <i class="ri-vip-crown-line"></i>
          </div>
          <span>Subscriptions</span>
              </a>
            </li>
            <li>
              <a
          href="../wallets/"
          class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50"
              >
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
              <a
          href="../payment/"
          class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50"
              >
          <div class="w-5 h-5 flex items-center justify-center mr-3">
            <i class="ri-exchange-dollar-line"></i>
          </div>
          <span>Payments</span>
              </a>
            </li>
            <li>
              <a
          href="../pend_payment/"
          class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50"
              >
          <div class="w-5 h-5 flex items-center justify-center mr-3">
            <i class="ri-time-line"></i>
          </div>
          <span>Pending Payments</span>
              </a>
            </li>
            <li>
              <a
          href="../settings/"
          class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50"
              >
          <div class="w-5 h-5 flex items-center justify-center mr-3">
            <i class="ri-settings-3-line"></i>
          </div>
          <span>Settings</span>
              </a>
            </li>
          </ul>
        </nav>
        <div class="p-4 border-t">
          <button
            id="logout-btn"
            class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-red-600 hover:bg-red-50"
          >
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
              <div
                class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mr-4"
              >
                <i class="ri-logout-box-line ri-lg"></i>
              </div>
              <h3 class="text-lg font-medium text-gray-800">
                Are you sure you want to logout?
              </h3>
            </div>
            <div class="flex items-center justify-end space-x-3">
              <button
                id="cancel-logout"
                class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                id="confirm-logout"
                class="px-4 py-2 bg-red-600 text-white rounded-button whitespace-nowrap hover:bg-red-700"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>
    </aside>
    <main class="pt-16 md:pl-64 transition-all duration-300">
      <!-- Subscriptions Tab -->
      <div id="subscriptions-tab" class="tab-content p-4 md:p-6">
        <div class="mb-6">
          <h1 class="text-2xl font-semibold text-gray-800">
            Subscription Management
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Manage user subscriptions and plans
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center">
              <div
                class="w-12 h-12 rounded-full bg-blue-100 text-primary flex items-center justify-center"
              >
                <i class="ri-user-line ri-lg"></i>
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="text-2xl font-semibold" id="subs-total-users">
                  1,254
                </p>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center">
              <div
                class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center"
              >
                <i class="ri-vip-crown-line ri-lg"></i>
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-500">Active Subscriptions</p>
                <p class="text-2xl font-semibold" id="subs-active-count">876</p>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center">
              <div
                class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center"
              >
                <i class="ri-money-dollar-circle-line ri-lg"></i>
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-500">Monthly Revenue</p>
                <p class="text-2xl font-semibold" id="subs-monthly-revenue">
                  $45,890
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow-sm p-4 md:col-span-2">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-medium text-gray-800">
                Subscription Distribution
              </h2>
              <div class="custom-select" id="chart-period-select">
                <div class="custom-select-trigger">
                  <span>This Month</span>
                  <i class="ri-arrow-down-s-line ml-2"></i>
                </div>
                <div class="custom-select-options">
                  <div class="custom-select-option" data-value="week">
                    This Week
                  </div>
                  <div class="custom-select-option" data-value="month">
                    This Month
                  </div>
                  <div class="custom-select-option" data-value="quarter">
                    This Quarter
                  </div>
                  <div class="custom-select-option" data-value="year">
                    This Year
                  </div>
                </div>
              </div>
            </div>
            <div id="subscription-chart" class="w-full h-64"></div>
          </div>
          <div class="bg-white rounded-lg shadow-sm p-4">
            <h2 class="text-lg font-medium text-gray-800 mb-4">
              Plan Distribution
            </h2>
            <div id="plan-distribution-chart" class="w-full h-64"></div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
          <div class="p-4 border-b">
            <div
              class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
            >
              <h2 class="text-lg font-medium text-gray-800">
                Active Subscriptions
              </h2>
              <div class="flex flex-wrap items-center gap-3">
                <div class="custom-select" id="subscription-plan-filter">
                  <div class="custom-select-trigger">
                    <span>All Plans</span>
                    <i class="ri-arrow-down-s-line ml-2"></i>
                  </div>
                  <div class="custom-select-options">
                    <div class="custom-select-option" data-value="all">
                      All Plans
                    </div>
                    <div class="custom-select-option" data-value="basic">
                      Basic
                    </div>
                    <div class="custom-select-option" data-value="pro">Pro</div>
                    <div class="custom-select-option" data-value="enterprise">
                      Enterprise
                    </div>
                  </div>
                </div>
                <div class="custom-select" id="subscription-status-filter">
                  <div class="custom-select-trigger">
                    <span>All Status</span>
                    <i class="ri-arrow-down-s-line ml-2"></i>
                  </div>
                  <div class="custom-select-options">
                    <div class="custom-select-option" data-value="all">
                      All Status
                    </div>
                    <div class="custom-select-option" data-value="active">
                      Active
                    </div>
                    <div class="custom-select-option" data-value="trial">
                      Trial
                    </div>
                    <div class="custom-select-option" data-value="expired">
                      Expired
                    </div>
                  </div>
                </div>
                <button id="open-modal-btn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        Open New Subscription Modal
    </button>
              </div>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50">
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
                    Status
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Start Date
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    End Date
                  </th>
                  <th
                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Auto Renew
                  </th>
                  <th
                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody
                class="divide-y divide-gray-200"
                id="subscriptions-table-body"
              >
                <!-- Filled by JS -->
              </tbody>
            </table>
          </div>
          <div class="px-4 py-3 flex items-center justify-between border-t">
            <div class="flex items-center text-sm text-gray-500">
              <span>Showing</span>
              <div
                class="custom-select mx-2"
                id="subscription-page-size-select"
              >
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
              <span id="subscriptions-total-count">of 45 subscriptions</span>
            </div>
            <div
              class="flex items-center space-x-1"
              id="subscriptions-pagination"
            >
              <button
                class="pagination-item text-gray-500 hover:bg-gray-100"
                id="subscriptions-prev-page"
              >
                <i class="ri-arrow-left-s-line"></i>
              </button>
              <!-- Page numbers will be injected here -->
              <button
                class="pagination-item text-gray-600 hover:bg-gray-100 subscriptions-page-btn"
                data-page="1"
              >
                1
              </button>
              <button
                class="pagination-item text-gray-600 hover:bg-gray-100 subscriptions-page-btn"
                data-page="2"
              >
                2
              </button>
              <button
                class="pagination-item text-gray-600 hover:bg-gray-100 subscriptions-page-btn"
                data-page="3"
              >
                3
              </button>
              <button
                class="pagination-item text-gray-600 hover:bg-gray-100 subscriptions-page-btn"
                data-page="4"
              >
                4
              </button>
              <button
                class="pagination-item text-gray-600 hover:bg-gray-100 subscriptions-page-btn"
                data-page="5"
              >
                5
              </button>
              <button
                class="pagination-item text-gray-500 hover:bg-gray-100"
                id="subscriptions-next-page"
              >
                <i class="ri-arrow-right-s-line"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
   <!-- New Subscription Modal -->
    <div id="new-subscription-modal" class="modal">
        <div class="modal-content max-w-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-800">Create New Subscription</h3>
                <button class="modal-close text-gray-400 hover:text-gray-500">
                    <i class="ri-close-line ri-lg"></i>
                </button>
            </div>
            <form id="new-subscription-form" class="space-y-4">
                <div>
                    <label for="user-email" class="block text-sm font-medium text-gray-700 mb-1">
                        User Email <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="user-email"
                        required
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        placeholder="Enter user email address"
                    />
                    <p class="text-xs text-gray-500 mt-1">User must already exist in the system</p>
                    <div id="email-error" class="error-message" style="display: none;"></div>
                </div>
                
                <div>
                    <label for="subscription-plan" class="block text-sm font-medium text-gray-700 mb-1">
                        Subscription Plan <span class="text-red-500">*</span>
                    </label>
                    <div class="custom-select" id="subscription-plan-select">
                        <div class="custom-select-trigger">
                            <span>Select Plan</span>
                            <i class="ri-arrow-down-s-line ml-2"></i>
                        </div>
                        <div class="custom-select-options">
                            <div class="custom-select-option" data-value="free">
                                <div class="flex items-center justify-between">
                                    <span>Free Plan</span>
                                    <span class="text-xs text-gray-500">1,000 credits</span>
                                </div>
                            </div>
                            <div class="custom-select-option" data-value="pro">
                                <div class="flex items-center justify-between">
                                    <span>Pro Plan</span>
                                    <span class="text-xs text-gray-500">2,000 credits</span>
                                </div>
                            </div>
                            <div class="custom-select-option" data-value="enterprise">
                                <div class="flex items-center justify-between">
                                    <span>Enterprise Plan</span>
                                    <span class="text-xs text-gray-500">10,000 credits</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="plan-error" class="error-message" style="display: none;"></div>
                </div>
                
                <div>
                    <label for="subscription-duration" class="block text-sm font-medium text-gray-700 mb-1">
                        Duration
                    </label>
                    <div class="custom-select" id="subscription-duration-select">
                        <div class="custom-select-trigger">
                            <span>1 Month</span>
                            <i class="ri-arrow-down-s-line ml-2"></i>
                        </div>
                        <div class="custom-select-options">
                            <div class="custom-select-option" data-value="1month">1 Month</div>
                            <div class="custom-select-option" data-value="3months">3 Months</div>
                            <div class="custom-select-option" data-value="6months">6 Months</div>
                            <div class="custom-select-option" data-value="1year">1 Year</div>
                            <div class="custom-select-option" data-value="lifetime">Lifetime</div>
                        </div>
                    </div>
                    <div id="duration-error" class="error-message" style="display: none;"></div>
                </div>
                
                <div>
                    <label for="subscription-notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Notes (Optional)
                    </label>
                    <textarea
                        id="subscription-notes"
                        rows="3"
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        placeholder="Add any notes about this subscription..."
                    ></textarea>
                </div>
                
                <div class="flex items-center">
                    <label class="custom-checkbox">
                        <input type="checkbox" id="send-welcome-email" checked />
                        <span class="checkbox-mark"></span>
                    </label>
                    <span class="ml-2 text-sm text-gray-600">Send welcome email to user</span>
                </div>
                
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <button
                        type="button"
                        class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 modal-close"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        id="create-subscription-btn"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center"
                    >
                        <span id="submit-text">Create Subscription</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast-notification" class="fixed top-4 right-4 px-4 py-3 rounded-lg text-white transform transition-transform duration-300 translate-x-full z-50">
    </div>

    <script>
        // Custom Select Handler
        class CustomSelect {
            constructor(element) {
                this.element = element;
                this.trigger = element.querySelector('.custom-select-trigger');
                this.options = element.querySelectorAll('.custom-select-option');
                this.value = '';
                this.init();
            }

            init() {
                // Handle trigger click
                this.trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.toggle();
                });

                // Handle option clicks
                this.options.forEach(option => {
                    option.addEventListener('click', () => {
                        this.selectOption(option);
                    });
                });

                // Close when clicking outside
                document.addEventListener('click', () => {
                    this.close();
                });
            }

            toggle() {
                const isOpen = this.element.classList.contains('open');
                // Close all other selects
                document.querySelectorAll('.custom-select').forEach(select => {
                    select.classList.remove('open');
                });
                
                if (!isOpen) {
                    this.element.classList.add('open');
                }
            }

            close() {
                this.element.classList.remove('open');
            }

            selectOption(option) {
                const value = option.getAttribute('data-value');
                const text = option.textContent.trim();
                
                this.value = value;
                this.trigger.querySelector('span').textContent = text;
                this.element.setAttribute('data-value', value);
                this.close();
                
                // Trigger change event
                const event = new CustomEvent('change', {
                    detail: { value, text }
                });
                this.element.dispatchEvent(event);
            }

            getValue() {
                return this.value;
            }

            setValue(value, text) {
                this.value = value;
                this.trigger.querySelector('span').textContent = text;
                this.element.setAttribute('data-value', value);
            }

            reset() {
                this.value = '';
                this.element.removeAttribute('data-value');
            }
        }

        // Initialize application
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize custom selects
            const planSelect = new CustomSelect(document.getElementById('subscription-plan-select'));
            const durationSelect = new CustomSelect(document.getElementById('subscription-duration-select'));
            
            // Set default values
            durationSelect.setValue('1month', '1 Month');

            // Modal handlers
            const modal = document.getElementById('new-subscription-modal');
            const openBtn = document.getElementById('open-modal-btn');
            const closeButtons = document.querySelectorAll('.modal-close');
            const form = document.getElementById('new-subscription-form');

            // Open modal
            openBtn.addEventListener('click', function() {
                openModal();
            });

            // Close modal buttons
            closeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    closeModal();
                });
            });

            // Close modal on backdrop click
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });

            // Form submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                await handleFormSubmit();
            });

            function openModal() {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.remove('active');
                document.body.style.overflow = '';
                resetForm();
            }

            function resetForm() {
                form.reset();
                planSelect.reset();
                planSelect.trigger.querySelector('span').textContent = 'Select Plan';
                durationSelect.setValue('1month', '1 Month');
                clearErrors();
            }

            function clearErrors() {
                const errorElements = document.querySelectorAll('.error-message');
                errorElements.forEach(el => {
                    el.style.display = 'none';
                    el.textContent = '';
                });

                const inputs = document.querySelectorAll('.error-input');
                inputs.forEach(input => {
                    input.classList.remove('error-input');
                });
            }

            function showError(fieldName, message) {
                const errorElement = document.getElementById(fieldName + '-error');
                const inputElement = document.getElementById('user-' + fieldName) || 
                                   document.getElementById('subscription-' + fieldName + '-select');
                
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.style.display = 'block';
                }
                
                if (inputElement) {
                    inputElement.classList.add('error-input');
                }
            }

            function validateForm() {
                clearErrors();
                let isValid = true;

                // Validate email
                const email = document.getElementById('user-email').value.trim();
                if (!email) {
                    showError('email', 'Email is required');
                    isValid = false;
                } else if (!isValidEmail(email)) {
                    showError('email', 'Please enter a valid email address');
                    isValid = false;
                }

                // Validate plan
                const plan = planSelect.getValue();
                if (!plan) {
                    showError('plan', 'Please select a subscription plan');
                    isValid = false;
                }

                return isValid;
            }

            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            async function handleFormSubmit() {
                if (!validateForm()) {
                    return;
                }

                const submitBtn = document.getElementById('create-subscription-btn');
                const submitText = document.getElementById('submit-text');
                
                // Show loading state
                submitBtn.disabled = true;
                submitText.innerHTML = '<div class="spinner"></div>Creating...';

                try {
                    const formData = {
                        email: document.getElementById('user-email').value.trim(),
                        plan: planSelect.getValue(),
                        duration: durationSelect.getValue() || '1month',
                        notes: document.getElementById('subscription-notes').value.trim(),
                        send_email: document.getElementById('send-welcome-email').checked
                    };

                    console.log('Sending data:', formData); // Debug log

                    const response = await fetch('api/create_subscription.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();
                    console.log('Response data:', data); // Debug log

                    if (data.success) {
                        showToast('Subscription created successfully!', 'success');
                        closeModal();
                    } else {
                        showToast(data.message || 'Failed to create subscription', 'error');
                    }

                } catch (error) {
                    console.error('Error creating subscription:', error);
                    showToast('Network error. Please try again.', 'error');
                } finally {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitText.innerHTML = 'Create Subscription';
                }
            }

            function showToast(message, type = 'success') {
                const toast = document.getElementById('toast-notification');
                
                // Set background color based on type
                toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg text-white transform transition-transform duration-300 translate-x-full z-50 ${
                    type === 'success' ? 'bg-green-500' : 'bg-red-500'
                }`;
                
                toast.textContent = message;
                
                // Show toast
                toast.classList.remove('translate-x-full');
                
                // Hide toast after 3 seconds
                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                }, 3000);
            }
        });
    </script>
    <!-- Modals for Subscriptions -->
    <div id="subscription-history-modal" class="modal">
      <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-800">
            Subscription History
          </h3>
          <button class="modal-close text-gray-400 hover:text-gray-500">
            <i class="ri-close-line ri-lg"></i>
          </button>
        </div>
        <div class="space-y-4">
          <div
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
          >
            <div>
              <p class="text-sm font-medium text-gray-800">Current Plan: Pro</p>
              <p class="text-xs text-gray-500">Active since: Jun 01, 2025</p>
            </div>
            <span
              class="px-2 py-1 text-xs font-medium bg-green-50 text-green-600 rounded-full"
              >Active</span
            >
          </div>
          <div class="space-y-3">
            <div class="border-l-2 border-primary pl-3">
              <p class="text-sm font-medium text-gray-800">
                Plan upgraded to Pro
              </p>
              <p class="text-xs text-gray-500">Jun 01, 2025 • $49.99/month</p>
            </div>
            <div class="border-l-2 border-gray-200 pl-3">
              <p class="text-sm font-medium text-gray-800">
                Payment successful
              </p>
              <p class="text-xs text-gray-500">Jun 01, 2025 • 0.05 ETH</p>
            </div>
            <div class="border-l-2 border-gray-200 pl-3">
              <p class="text-sm font-medium text-gray-800">Trial started</p>
              <p class="text-xs text-gray-500">May 25, 2025 • Basic Plan</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Replace the modal buttons in your HTML with these fixed versions -->

<!-- Pause Subscription Modal - Updated Button -->
<div id="pause-subscription-modal" class="modal">
  <div class="modal-content">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-800">Pause Subscription</h3>
      <button class="modal-close text-gray-400 hover:text-gray-500">
        <i class="ri-close-line ri-lg"></i>
      </button>
    </div>
    <div class="space-y-4">
      <div class="flex items-center">
        <div class="w-12 h-12 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center">
          <i class="ri-pause-circle-line ri-lg"></i>
        </div>
        <div class="ml-4">
          <h4 class="text-lg font-medium text-gray-800">
            Are you sure you want to pause this subscription?
          </h4>
          <p class="text-sm text-gray-500">
            The user will lose access to premium features until the subscription is resumed.
          </p>
        </div>
      </div>
      <div>
        <label for="pause-reason" class="block text-sm font-medium text-gray-700 mb-1">
          Reason for pausing (optional)
        </label>
        <textarea
          id="pause-reason"
          rows="3"
          class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
          placeholder="Enter reason for pausing..."
        ></textarea>
      </div>
      <div>
        <label for="pause-duration" class="block text-sm font-medium text-gray-700 mb-1">
          Pause Duration
        </label>
        <div class="custom-select" id="pause-duration-select">
          <div class="custom-select-trigger">
            <span>1 month</span>
            <i class="ri-arrow-down-s-line ml-2"></i>
          </div>
          <div class="custom-select-options">
            <div class="custom-select-option" data-value="1week">1 week</div>
            <div class="custom-select-option" data-value="2weeks">2 weeks</div>
            <div class="custom-select-option" data-value="1month">1 month</div>
            <div class="custom-select-option" data-value="3months">3 months</div>
          </div>
        </div>
      </div>
      <div class="flex items-center">
        <label class="custom-checkbox">
          <input type="checkbox" checked />
          <span class="checkbox-mark"></span>
        </label>
        <span class="ml-2 text-sm text-gray-600">Send email notification to user</span>
      </div>
      <div class="flex items-center justify-end space-x-3 pt-4 border-t">
        <button class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50 modal-close">
          Cancel
        </button>
        <button 
          id="confirm-pause-subscription"
          class="px-4 py-2 bg-yellow-600 text-white rounded-button whitespace-nowrap hover:bg-yellow-700">
          Pause Subscription
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Cancel Subscription Modal - Updated Button -->
<div id="cancel-subscription-modal" class="modal">
  <div class="modal-content">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-800">Cancel Subscription</h3>
      <button class="modal-close text-gray-400 hover:text-gray-500">
        <i class="ri-close-line ri-lg"></i>
      </button>
    </div>
    <div class="space-y-4">
      <div class="flex items-center">
        <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
          <i class="ri-close-circle-line ri-lg"></i>
        </div>
        <div class="ml-4">
          <h4 class="text-lg font-medium text-gray-800">
            Are you sure you want to cancel this subscription?
          </h4>
          <p class="text-sm text-gray-500">
            The user will immediately lose access to all premium features.
          </p>
        </div>
      </div>
      <div class="bg-yellow-50 p-4 rounded-lg">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="ri-error-warning-line text-yellow-600"></i>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
            <div class="mt-2 text-sm text-yellow-700">
              <p>This action cannot be undone. Consider pausing the subscription instead.</p>
            </div>
          </div>
        </div>
      </div>
      <div>
        <label for="cancel-reason" class="block text-sm font-medium text-gray-700 mb-1">
          Reason for cancellation (optional)
        </label>
        <textarea
          id="cancel-reason"
          rows="3"
          class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
          placeholder="Enter reason for cancellation..."
        ></textarea>
      </div>
      <div class="flex items-center">
        <label class="custom-checkbox">
          <input type="checkbox" checked />
          <span class="checkbox-mark"></span>
        </label>
        <span class="ml-2 text-sm text-gray-600">Send email notification to user</span>
      </div>
      <div class="flex items-center justify-end space-x-3 pt-4 border-t">
        <button class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50 modal-close">
          Cancel
        </button>
        <button 
          id="confirm-cancel-subscription"
          class="px-4 py-2 bg-red-600 text-white rounded-button whitespace-nowrap hover:bg-red-700">
          Cancel Subscription
        </button>
      </div>
    </div>
  </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize pause duration select
    const pauseDurationSelect = document.getElementById('pause-duration-select');
    if (pauseDurationSelect) {
        patchCustomSelect(pauseDurationSelect);
        pauseDurationSelect.setAttribute('data-value', '1month'); // Set default value
    }
    
    // Pause subscription button handler
    const confirmPauseBtn = document.getElementById('confirm-pause-subscription');
    if (confirmPauseBtn) {
        confirmPauseBtn.addEventListener('click', function() {
            const modal = document.getElementById('pause-subscription-modal');
            const userId = modal.getAttribute('data-user-id');
            const reason = document.getElementById('pause-reason')?.value || '';
            const durationSelect = document.getElementById('pause-duration-select');
            const duration = durationSelect?.getAttribute('data-value') || '1month';
            
            if (userId) {
                // Show loading state
                this.disabled = true;
                this.textContent = 'Processing...';
                
                pauseSubscription(userId, reason, duration).finally(() => {
                    this.disabled = false;
                    this.textContent = 'Pause Subscription';
                });
            } else {
                showToast('User ID not found', 'error');
            }
        });
    }
    
    // Cancel subscription button handler
    const confirmCancelBtn = document.getElementById('confirm-cancel-subscription');
    if (confirmCancelBtn) {
        confirmCancelBtn.addEventListener('click', function() {
            const modal = document.getElementById('cancel-subscription-modal');
            const userId = modal.getAttribute('data-user-id');
            const reason = document.getElementById('cancel-reason')?.value || '';
            
            if (userId) {
                // Show loading state
                this.disabled = true;
                this.textContent = 'Processing...';
                
                cancelSubscription(userId, reason).finally(() => {
                    this.disabled = false;
                    this.textContent = 'Cancel Subscription';
                });
            } else {
                showToast('User ID not found', 'error');
            }
        });
    }
    
    
    async function pauseSubscription(userId, reason, duration) {
        try {
            const response = await fetch('api/update_subscription.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    action: 'pause',
                    reason: reason,
                    duration: duration
                })
            });

            const data = await response.json();
            
            if (data.success) {
                showToast('Subscription paused successfully');
                fetchSubscriptions(); // Refresh data
                closeAllModals();
            } else {
                showToast(data.message || 'Failed to pause subscription', 'error');
            }
        } catch (error) {
            console.error('Error pausing subscription:', error);
            showToast('Error pausing subscription', 'error');
        }
    }

    async function cancelSubscription(userId, reason) {
        try {
            const response = await fetch('api/update_subscription.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    action: 'cancel',
                    reason: reason
                })
            });

            const data = await response.json();
            
            if (data.success) {
                showToast('Subscription cancelled successfully');
                fetchSubscriptions(); // Refresh data
                closeAllModals();
            } else {
                showToast(data.message || 'Failed to cancel subscription', 'error');
            }
        } catch (error) {
            console.error('Error cancelling subscription:', error);
            showToast('Error cancelling subscription', 'error');
        }
    }
    
    function closeAllModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
    
    function showToast(message, type = 'success') {
        let toast = document.getElementById("toast-notification");
        if (!toast) {
            toast = document.createElement("div");
            toast.id = "toast-notification";
            toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg text-white transform transition-transform duration-300 translate-x-full z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
            document.body.appendChild(toast);
        }
        
        toast.textContent = message;
        toast.classList.remove("translate-x-full");
        
        setTimeout(() => {
            toast.classList.add("translate-x-full");
        }, 3000);
    }
});
</script>
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
    <script id="charts-script">
    document.addEventListener("DOMContentLoaded", function () {
    // Global chart instances
    let subscriptionChart = null;
    let planDistributionChart = null;
    let currentPeriod = 'month';
    
    // Initialize charts
    initializeCharts();
    
    // Set up period selector
    setupPeriodSelector();
    
    // Load initial data
    loadChartData(currentPeriod);
    
    function initializeCharts() {
        // Initialize Subscription Distribution Chart
        const subscriptionChartElement = document.getElementById("subscription-chart");
        if (subscriptionChartElement) {
            subscriptionChart = echarts.init(subscriptionChartElement);
            console.log('Subscription chart initialized');
        }
        
        // Initialize Plan Distribution Chart  
        const planDistributionChartElement = document.getElementById("plan-distribution-chart");
        if (planDistributionChartElement) {
            planDistributionChart = echarts.init(planDistributionChartElement);
            console.log('Plan distribution chart initialized');
        }
        
        // Handle window resize
        window.addEventListener("resize", function () {
            if (subscriptionChart) subscriptionChart.resize();
            if (planDistributionChart) planDistributionChart.resize();
        });
    }
    
    function setupPeriodSelector() {
        const periodSelect = document.getElementById('chart-period-select');
        if (periodSelect) {
            // Initialize custom select if not already done
            if (!periodSelect.hasAttribute('data-initialized')) {
                patchCustomSelect(periodSelect);
                periodSelect.setAttribute('data-initialized', 'true');
            }
            
            // Listen for period changes
            periodSelect.addEventListener('change', function(e) {
                const newPeriod = e.detail.value;
                console.log('Period changed to:', newPeriod);
                if (newPeriod && newPeriod !== currentPeriod) {
                    currentPeriod = newPeriod;
                    loadChartData(currentPeriod);
                }
            });
        }
    }
    
    async function loadChartData(period = 'month') {
        try {
            console.log('Loading chart data for period:', period);
            
            // Show loading state
            showChartLoading();
            
            const response = await fetch(`api/subscription_stats.php?period=${period}`);
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Received data:', data);
            
            if (data.success) {
                hideChartLoading();
                updateSubscriptionChart(data.data.distribution);
                updatePlanDistributionChart(data.data.plan_distribution);
                updateStatsCards(data.data.stats);
                console.log('Charts updated successfully');
            } else {
                console.error('Error loading chart data:', data.message);
                console.error('Debug info:', data.debug_error);
                showChartError(data.message || 'Unknown error');
            }
        } catch (error) {
            console.error('Error fetching chart data:', error);
            hideChartLoading();
            showChartError('Failed to load chart data');
        }
    }
    
    function updateSubscriptionChart(distributionData) {
        if (!subscriptionChart || !distributionData) {
            console.warn('Subscription chart or data not available');
            return;
        }
        
        console.log('Updating subscription chart with:', distributionData);
        
        const colors = {
            free: "#9ca3af",      // Gray for free
            pro: "#3b82f6",       // Blue for pro  
            enterprise: "#a855f7" // Purple for enterprise
        };
        
        const series = [];
        
        // Create series for each plan type
        Object.keys(distributionData.series || {}).forEach(plan => {
            const planData = distributionData.series[plan];
            if (planData && planData.some(value => value > 0)) {
                series.push({
                    name: plan.charAt(0).toUpperCase() + plan.slice(1),
                    type: "line",
                    smooth: true,
                    symbol: "circle",
                    symbolSize: 6,
                    lineStyle: {
                        width: 3,
                    },
                    areaStyle: {
                        opacity: 0.1,
                    },
                    emphasis: {
                        focus: "series",
                    },
                    color: colors[plan] || "#6b7280",
                    data: planData
                });
            }
        });
        
        // If no series have data, create empty series to show the chart structure
        if (series.length === 0) {
            Object.keys(colors).forEach(plan => {
                series.push({
                    name: plan.charAt(0).toUpperCase() + plan.slice(1),
                    type: "line",
                    smooth: true,
                    symbol: "circle",
                    symbolSize: 6,
                    lineStyle: {
                        width: 3,
                    },
                    areaStyle: {
                        opacity: 0.1,
                    },
                    color: colors[plan],
                    data: new Array(distributionData.labels?.length || 6).fill(0)
                });
            });
        }
        
        const option = {
            animation: true,
            animationDuration: 1000,
            tooltip: {
                trigger: "axis",
                backgroundColor: "rgba(255, 255, 255, 0.95)",
                borderColor: "#e2e8f0",
                borderRadius: 8,
                textStyle: {
                    color: "#1f2937",
                },
                formatter: function(params) {
                    if (!params || params.length === 0) return '';
                    
                    let html = `<div style="font-weight: 600; margin-bottom: 4px;">${params[0].axisValue}</div>`;
                    params.forEach(param => {
                        html += `
                            <div style="display: flex; align-items: center; margin: 2px 0;">
                                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background-color: ${param.color}; margin-right: 8px;"></span>
                                <span style="flex: 1;">${param.seriesName}:</span>
                                <span style="font-weight: 600; margin-left: 8px;">${param.value || 0}</span>
                            </div>
                        `;
                    });
                    return html;
                }
            },
            legend: {
                data: series.map(s => s.name),
                bottom: 10,
                textStyle: {
                    color: "#1f2937",
                },
                itemGap: 20
            },
            grid: {
                left: 20,
                right: 20,
                top: 20,
                bottom: 60,
                containLabel: true,
            },
            xAxis: {
                type: "category",
                boundaryGap: false,
                data: distributionData.labels || [],
                axisLine: {
                    lineStyle: {
                        color: "#e5e7eb",
                    },
                },
                axisLabel: {
                    color: "#6b7280",
                    fontSize: 12
                },
                axisTick: {
                    show: false
                }
            },
            yAxis: {
                type: "value",
                axisLine: {
                    show: false,
                },
                axisLabel: {
                    color: "#6b7280",
                    fontSize: 12
                },
                splitLine: {
                    lineStyle: {
                        color: "#f3f4f6",
                    },
                },
                axisTick: {
                    show: false
                }
            },
            series: series
        };
        
        subscriptionChart.setOption(option, true);
    }
    
    function updatePlanDistributionChart(planData) {
        if (!planDistributionChart || !planData) {
            console.warn('Plan distribution chart or data not available');
            return;
        }
        
        console.log('Updating plan distribution chart with:', planData);
        
        const colors = {
            'Free': "#9ca3af",
            'Pro': "#3b82f6", 
            'Enterprise': "#a855f7"
        };
        
        // Add colors to data
        const dataWithColors = planData.map(item => ({
            ...item,
            itemStyle: {
                color: colors[item.name] || "#6b7280"
            }
        }));
        
        const option = {
            animation: true,
            animationDuration: 1000,
            tooltip: {
                trigger: "item",
                backgroundColor: "rgba(255, 255, 255, 0.95)",
                borderColor: "#e2e8f0",
                borderRadius: 8,
                textStyle: {
                    color: "#1f2937",
                },
                formatter: function(params) {
                    const percent = Math.round(params.percent || 0);
                    return `
                        <div style="font-weight: 600; margin-bottom: 4px;">${params.name}</div>
                        <div style="display: flex; align-items: center;">
                            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background-color: ${params.color}; margin-right: 8px;"></span>
                            <span>Subscribers: <strong>${params.value || 0}</strong></span>
                        </div>
                        <div style="margin-top: 2px; color: #6b7280;">
                            <span>${percent}% of total</span>
                        </div>
                    `;
                }
            },
            legend: {
                orient: "vertical",
                right: 20,
                top: "center",
                textStyle: {
                    color: "#1f2937",
                    fontSize: 12
                },
                formatter: function(name) {
                    const item = planData.find(d => d.name === name);
                    return item ? `${name} (${item.value || 0})` : name;
                }
            },
            series: [
                {
                    name: "Plan Distribution",
                    type: "pie",
                    radius: ["45%", "75%"],
                    center: ["35%", "50%"],
                    avoidLabelOverlap: false,
                    itemStyle: {
                        borderRadius: 8,
                        borderColor: "#fff",
                        borderWidth: 2,
                    },
                    label: {
                        show: false,
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: 14,
                            fontWeight: 'bold',
                            formatter: function(params) {
                                return `${Math.round(params.percent || 0)}%`;
                            }
                        },
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.2)'
                        }
                    },
                    labelLine: {
                        show: false,
                    },
                    data: dataWithColors
                },
            ],
        };
        
        planDistributionChart.setOption(option, true);
    }
    
    function updateStatsCards(stats) {
        console.log('Updating stats cards with:', stats);
        
        // Update stats cards
        const statsElements = {
            totalUsers: document.getElementById("subs-total-users"),
            activeCount: document.getElementById("subs-active-count"), 
            monthlyRevenue: document.getElementById("subs-monthly-revenue")
        };
        
        if (statsElements.totalUsers) {
            animateNumber(statsElements.totalUsers, stats.total_users || 0);
        }
        if (statsElements.activeCount) {
            animateNumber(statsElements.activeCount, stats.active_subscriptions || 0);
        }
        if (statsElements.monthlyRevenue) {
            animateNumber(statsElements.monthlyRevenue, stats.monthly_revenue || 0, true);
        }
    }
    
    function animateNumber(element, targetValue, isCurrency = false) {
        const currentValue = parseInt(element.textContent.replace(/[^0-9]/g, '')) || 0;
        const increment = (targetValue - currentValue) / 20;
        let current = currentValue;
        
        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= targetValue) || (increment < 0 && current <= targetValue)) {
                current = targetValue;
                clearInterval(timer);
            }
            
            if (isCurrency) {
                element.textContent = `$${Math.round(current).toLocaleString()}`;
            } else {
                element.textContent = Math.round(current).toLocaleString();
            }
        }, 50);
    }
    
    function showChartLoading() {
        console.log('Showing chart loading');
        
        if (subscriptionChart) {
            subscriptionChart.showLoading({
                text: 'Loading...',
                color: '#3b82f6',
                textColor: '#6b7280',
                maskColor: 'rgba(255, 255, 255, 0.8)'
            });
        }
        
        if (planDistributionChart) {
            planDistributionChart.showLoading({
                text: 'Loading...',
                color: '#3b82f6', 
                textColor: '#6b7280',
                maskColor: 'rgba(255, 255, 255, 0.8)'
            });
        }
    }
    
    function hideChartLoading() {
        console.log('Hiding chart loading');
        
        if (subscriptionChart) {
            subscriptionChart.hideLoading();
        }
        
        if (planDistributionChart) {
            planDistributionChart.hideLoading();
        }
    }
    
    function showChartError(message = 'Error loading chart data') {
        console.log('Showing chart error:', message);
        
        hideChartLoading();
        
        const errorOption = {
            title: {
                text: message,
                left: 'center',
                top: 'middle',
                textStyle: {
                    color: '#ef4444',
                    fontSize: 16
                }
            },
            xAxis: { show: false },
            yAxis: { show: false },
            series: []
        };
        
        if (subscriptionChart) {
            subscriptionChart.setOption(errorOption, true);
        }
        
        if (planDistributionChart) {
            planDistributionChart.setOption(errorOption, true);
        }
    }
    
    // Utility function to initialize custom selects (if not already defined)
    function patchCustomSelect(select) {
        if (select.hasAttribute('data-select-patched')) return;
        
        const trigger = select.querySelector(".custom-select-trigger");
        const options = select.querySelectorAll(".custom-select-option");
        
        if (trigger) {
            trigger.addEventListener("click", function (e) {
                e.stopPropagation();
                document.querySelectorAll(".custom-select").forEach((other) => {
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
                
                const triggerSpan = trigger.querySelector("span");
                if (triggerSpan) {
                    triggerSpan.textContent = this.textContent.trim();
                }
                
                const event = new CustomEvent("change", { detail: { value } });
                select.dispatchEvent(event);
            });
        });
        
        select.setAttribute('data-select-patched', 'true');
    }
    
    // Close all selects when clicking outside
    document.addEventListener("click", function () {
        document.querySelectorAll(".custom-select").forEach((select) => 
            select.classList.remove("open")
        );
    });
    
    // Auto-refresh charts every 5 minutes
    setInterval(() => {
        console.log('Auto-refreshing charts...');
        loadChartData(currentPeriod);
    }, 5 * 60 * 1000);
});
    </script>
    <script>

(function () {
    // State
    let filteredSubs = [];
    let subsCurrentPage = 1;
    let subsPageSize = 10;
    let subsPlanFilter = "all";
    let subsStatusFilter = "all";
    let allSubscriptions = [];

    // DOM elements
    const subsTbody = document.getElementById("subscriptions-table-body");
    const subsPageSizeSelect = document.getElementById("subscription-page-size-select");
    const subsPlanFilterSelect = document.getElementById("subscription-plan-filter");
    const subsStatusFilterSelect = document.getElementById("subscription-status-filter");
    const subsPagination = document.getElementById("subscriptions-pagination");
    const subsPrevBtn = document.getElementById("subscriptions-prev-page");
    const subsNextBtn = document.getElementById("subscriptions-next-page");
    const subsTotalCount = document.getElementById("subscriptions-total-count");

    // Stats elements
    const statsElements = {
        totalUsers: document.getElementById("subs-total-users"),
        activeCount: document.getElementById("subs-active-count"),
        monthlyRevenue: document.getElementById("subs-monthly-revenue")
    };

    // Utility functions
    function showToast(message, type = 'success') {
        // Create toast if it doesn't exist
        let toast = document.getElementById("toast-notification");
        if (!toast) {
            toast = document.createElement("div");
            toast.id = "toast-notification";
            toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg text-white transform transition-transform duration-300 translate-x-full z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
            document.body.appendChild(toast);
        }
        
        toast.textContent = message;
        toast.classList.remove("translate-x-full");
        
        setTimeout(() => {
            toast.classList.add("translate-x-full");
        }, 3000);
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString("en-US", {
            month: "short",
            day: "2-digit",
            year: "numeric"
        });
    }

    function getInitials(name) {
        return name.split(' ').map(n => n[0]).join('').toUpperCase();
    }

    function getPlanColorClass(plan) {
        switch (plan.toLowerCase()) {
            case 'pro': return 'bg-blue-50 text-primary';
            case 'enterprise': return 'bg-purple-50 text-purple-600';
            case 'basic': return 'bg-orange-50 text-orange-600';
            default: return 'bg-gray-100 text-gray-600';
        }
    }

    function getStatusColorClass(status) {
        switch (status.toLowerCase()) {
            case 'active': return 'bg-green-50 text-green-600';
            case 'expired': return 'bg-red-50 text-red-600';
            case 'cancelled': return 'bg-red-50 text-red-600';
            case 'paused': return 'bg-yellow-50 text-yellow-600';
            default: return 'bg-gray-50 text-gray-600';
        }
    }

    // API functions
    async function fetchSubscriptions() {
        try {
            const response = await fetch('api/subscriptions.php');
            const data = await response.json();
            
            if (data.success) {
                allSubscriptions = data.subscriptions;
                updateStats(data.stats);
                renderSubsTable();
            } else {
                showToast('Failed to load subscriptions', 'error');
            }
        } catch (error) {
            console.error('Error fetching subscriptions:', error);
            showToast('Error loading subscriptions', 'error');
        }
    }

    async function updateSubscriptionPlan(userId, newPlan) {
        try {
            const response = await fetch('api/update_subscription.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    action: 'change_plan',
                    plan: newPlan
                })
            });

            const data = await response.json();
            
            if (data.success) {
                showToast('Subscription plan updated successfully');
                fetchSubscriptions(); // Refresh data
            } else {
                showToast(data.message || 'Failed to update subscription', 'error');
            }
        } catch (error) {
            console.error('Error updating subscription:', error);
            showToast('Error updating subscription', 'error');
        }
    }

    async function pauseSubscription(userId, reason, duration) {
        try {
            const response = await fetch('api/update_subscription.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    action: 'pause',
                    reason: reason,
                    duration: duration
                })
            });

            const data = await response.json();
            
            if (data.success) {
                showToast('Subscription paused successfully');
                fetchSubscriptions();
                closeAllModals();
            } else {
                showToast(data.message || 'Failed to pause subscription', 'error');
            }
        } catch (error) {
            console.error('Error pausing subscription:', error);
            showToast('Error pausing subscription', 'error');
        }
    }

    async function cancelSubscription(userId, reason) {
        try {
            const response = await fetch('api/update_subscription.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    action: 'cancel',
                    reason: reason
                })
            });

            const data = await response.json();
            
            if (data.success) {
                showToast('Subscription cancelled successfully');
                fetchSubscriptions();
                closeAllModals();
            } else {
                showToast(data.message || 'Failed to cancel subscription', 'error');
            }
        } catch (error) {
            console.error('Error cancelling subscription:', error);
            showToast('Error cancelling subscription', 'error');
        }
    }

    async function fetchSubscriptionHistory(userId) {
        try {
            const response = await fetch(`api/subscription_history.php?user_id=${userId}`);
            const data = await response.json();
            
            if (data.success) {
                displaySubscriptionHistory(data.history);
            } else {
                showToast('Failed to load subscription history', 'error');
            }
        } catch (error) {
            console.error('Error fetching subscription history:', error);
            showToast('Error loading subscription history', 'error');
        }
    }

    function displaySubscriptionHistory(history) {
        const modal = document.getElementById('subscription-history-modal');
        const historyContainer = modal.querySelector('.space-y-4');
        
        historyContainer.innerHTML = history.map(item => `
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-800">${item.event_type}</p>
                    <p class="text-xs text-gray-500">${formatDate(item.created_at)}</p>
                    ${item.details ? `<p class="text-xs text-gray-600 mt-1">${item.details}</p>` : ''}
                </div>
                <span class="px-2 py-1 text-xs font-medium ${getStatusColorClass(item.status)} rounded-full">
                    ${item.status}
                </span>
            </div>
        `).join('');
    }

    function updateStats(stats) {
        if (statsElements.totalUsers) {
            statsElements.totalUsers.textContent = stats.total_users || '0';
        }
        if (statsElements.activeCount) {
            statsElements.activeCount.textContent = stats.active_subscriptions || '0';
        }
        if (statsElements.monthlyRevenue) {
            statsElements.monthlyRevenue.textContent = `$${(stats.monthly_revenue || 0).toLocaleString()}`;
        }
    }

    function closeAllModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    // --- Custom Select Patch ---
    function patchCustomSelect(select) {
        const trigger = select.querySelector(".custom-select-trigger");
        const options = select.querySelectorAll(".custom-select-option");
        
        if (trigger) {
            trigger.addEventListener("click", function (e) {
                e.stopPropagation();
                document.querySelectorAll("#subscriptions-tab .custom-select").forEach((other) => {
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
                
                const triggerSpan = trigger.querySelector("span");
                if (triggerSpan) {
                    triggerSpan.textContent = this.textContent.trim();
                }
                
                const event = new CustomEvent("change", { detail: { value } });
                select.dispatchEvent(event);
            });
        });
    }

    function renderSubsTable() {
        // Filter subscriptions
        filteredSubs = allSubscriptions.filter((sub) => {
            let matchesPlan = subsPlanFilter === "all" || sub.plan_name.toLowerCase() === subsPlanFilter;
            let matchesStatus = subsStatusFilter === "all" || sub.status.toLowerCase() === subsStatusFilter;
            return matchesPlan && matchesStatus;
        });

        // Pagination
        const total = filteredSubs.length;
        const totalPages = Math.max(1, Math.ceil(total / subsPageSize));
        if (subsCurrentPage > totalPages) subsCurrentPage = totalPages;
        
        const start = (subsCurrentPage - 1) * subsPageSize;
        const end = Math.min(start + subsPageSize, total);
        const subsToShow = filteredSubs.slice(start, end);

        // Render table rows
        subsTbody.innerHTML = subsToShow.map(sub => `
            <tr data-user-id="${sub.user_id}">
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full ${getPlanColorClass(sub.plan_name)} flex items-center justify-center">
                            <span class="text-sm font-medium">${getInitials(sub.full_name)}</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">${sub.full_name}</p>
                            <p class="text-xs text-gray-500">${sub.email}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="custom-select subscription-plan-select" data-user-id="${sub.user_id}">
                        <div class="custom-select-trigger">
                            <span class="px-2 py-1 text-xs font-medium ${getPlanColorClass(sub.plan_name)} rounded-full">
                                ${sub.plan_name.charAt(0).toUpperCase() + sub.plan_name.slice(1)}
                            </span>
                            <i class="ri-arrow-down-s-line ml-2"></i>
                        </div>
                        <div class="custom-select-options">
                            <div class="custom-select-option" data-value="free">
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Free</span>
                            </div>
                            <div class="custom-select-option" data-value="pro">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-50 text-primary rounded-full">Pro</span>
                            </div>
                            <div class="custom-select-option" data-value="enterprise">
                                <span class="px-2 py-1 text-xs font-medium bg-purple-50 text-purple-600 rounded-full">Enterprise</span>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-medium ${getStatusColorClass(sub.status)} rounded-full">
                        ${sub.status.charAt(0).toUpperCase() + sub.status.slice(1)}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                    ${formatDate(sub.start_date)}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                    ${sub.end_date ? formatDate(sub.end_date) : 'N/A'}
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <label class="custom-switch">
                        <input type="checkbox" disabled>
                        <span class="switch-slider opacity-50"></span>
                    </label>
                    <span class="text-xs text-gray-400 ml-2">Coming Soon</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end space-x-2">
                        <button class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-primary rounded-full hover:bg-blue-50 subscription-action" 
                                data-action="history" data-user-id="${sub.user_id}" data-tooltip="View subscription history">
                            <i class="ri-history-line"></i>
                        </button>
                        <button class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-yellow-500 rounded-full hover:bg-yellow-50 subscription-action" 
                                data-action="pause" data-user-id="${sub.user_id}" data-tooltip="Pause subscription"
                                ${sub.status === 'paused' ? 'disabled style="opacity: 0.5;"' : ''}>
                            <i class="ri-pause-circle-line"></i>
                        </button>
                        <button class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-red-500 rounded-full hover:bg-red-50 subscription-action" 
                                data-action="cancel" data-user-id="${sub.user_id}" data-tooltip="Cancel subscription"
                                ${sub.status === 'cancelled' ? 'disabled style="opacity: 0.5;"' : ''}>
                            <i class="ri-close-circle-line"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        // Update pagination info
        subsTotalCount.textContent = `of ${total} subscriptions`;
        renderSubsPagination(totalPages);
        
        // Re-attach event listeners
        attachTableEventListeners();
    }

    function attachTableEventListeners() {
        // Plan change selects
        document.querySelectorAll('.subscription-plan-select').forEach(select => {
            patchCustomSelect(select);
            
            select.addEventListener('change', function(e) {
                const userId = this.getAttribute('data-user-id');
                const newPlan = this.getAttribute('data-value');
                
                if (confirm(`Are you sure you want to change this user's plan to ${newPlan}?`)) {
                    updateSubscriptionPlan(userId, newPlan);
                }
            });
        });

        // Action buttons
        document.querySelectorAll('.subscription-action').forEach(button => {
            button.onclick = function(e) {
                e.preventDefault();
                const action = this.getAttribute('data-action');
                const userId = this.getAttribute('data-user-id');
                
                switch (action) {
                    case 'history':
                        fetchSubscriptionHistory(userId);
                        openModal('subscription-history-modal');
                        break;
                    case 'pause':
                        if (!this.disabled) {
                            document.getElementById('pause-subscription-modal').setAttribute('data-user-id', userId);
                            openModal('pause-subscription-modal');
                        }
                        break;
                    case 'cancel':
                        if (!this.disabled) {
                            document.getElementById('cancel-subscription-modal').setAttribute('data-user-id', userId);
                            openModal('cancel-subscription-modal');
                        }
                        break;
                }
            };

            // Tooltip handling
            button.onmouseenter = function(e) {
                const tooltip = document.querySelector("body > .fixed.px-2");
                if (tooltip) {
                    tooltip.textContent = this.getAttribute("data-tooltip");
                    tooltip.style.opacity = "1";
                    const rect = this.getBoundingClientRect();
                    tooltip.style.top = rect.top - 30 + "px";
                    tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px";
                }
            };
            
            button.onmouseleave = function() {
                const tooltip = document.querySelector("body > .fixed.px-2");
                if (tooltip) tooltip.style.opacity = "0";
            };
        });
    }

    function renderSubsPagination(totalPages) {
        // Remove old page buttons
        subsPagination.querySelectorAll(".subscriptions-page-btn").forEach((btn) => btn.remove());
        
        // Add new page buttons
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement("button");
            btn.className = "pagination-item text-gray-600 hover:bg-gray-100 subscriptions-page-btn";
            btn.textContent = i;
            btn.setAttribute("data-page", i);
            
            if (i === subsCurrentPage) {
                btn.classList.add("active", "bg-blue-50", "text-primary");
            }
            
            btn.onclick = function () {
                subsCurrentPage = i;
                renderSubsTable();
            };
            
            subsPagination.insertBefore(btn, subsNextBtn);
        }

        // Update prev/next buttons
        subsPrevBtn.disabled = subsCurrentPage === 1;
        subsNextBtn.disabled = subsCurrentPage === totalPages;
        subsPrevBtn.classList.toggle("opacity-50", subsPrevBtn.disabled);
        subsPrevBtn.classList.toggle("cursor-not-allowed", subsPrevBtn.disabled);
        subsNextBtn.classList.toggle("opacity-50", subsNextBtn.disabled);
        subsNextBtn.classList.toggle("cursor-not-allowed", subsNextBtn.disabled);
    }

    // Initialize custom selects
    document.querySelectorAll("#subscriptions-tab .custom-select").forEach(patchCustomSelect);

    // Close selects when clicking outside
    document.addEventListener("click", function () {
        document.querySelectorAll("#subscriptions-tab .custom-select").forEach((select) => 
            select.classList.remove("open")
        );
    });

    // Event listeners for filters and pagination
    subsPrevBtn.onclick = function () {
        if (subsCurrentPage > 1) {
            subsCurrentPage--;
            renderSubsTable();
        }
    };

    subsNextBtn.onclick = function () {
        const total = filteredSubs.length;
        const totalPages = Math.max(1, Math.ceil(total / subsPageSize));
        if (subsCurrentPage < totalPages) {
            subsCurrentPage++;
            renderSubsTable();
        }
    };

    // Page size change
    subsPageSizeSelect.addEventListener("change", function () {
        const value = this.getAttribute("data-value");
        if (value) {
            subsPageSize = parseInt(value, 10);
            subsCurrentPage = 1;
            renderSubsTable();
        }
    });

    // Plan filter change
    subsPlanFilterSelect.addEventListener("change", function () {
        subsPlanFilter = this.getAttribute("data-value") || "all";
        subsCurrentPage = 1;
        renderSubsTable();
    });

    // Status filter change
    subsStatusFilterSelect.addEventListener("change", function () {
        subsStatusFilter = this.getAttribute("data-value") || "all";
        subsCurrentPage = 1;
        renderSubsTable();
    });

    // Modal action handlers
    document.getElementById('confirm-pause-subscription')?.addEventListener('click', function() {
        const modal = document.getElementById('pause-subscription-modal');
        const userId = modal.getAttribute('data-user-id');
        const reason = document.getElementById('pause-reason')?.value || '';
        const duration = document.querySelector('[id*="pause-duration"]')?.getAttribute('data-value') || '1month';
        
        pauseSubscription(userId, reason, duration);
    });

    document.getElementById('confirm-cancel-subscription')?.addEventListener('click', function() {
        const modal = document.getElementById('cancel-subscription-modal');
        const userId = modal.getAttribute('data-user-id');
        const reason = document.getElementById('cancel-reason')?.value || '';
        
        cancelSubscription(userId, reason);
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        fetchSubscriptions();
    });

    // Auto-refresh every 30 seconds
    setInterval(fetchSubscriptions, 30000);

})();
    </script>
  </body>
</html>