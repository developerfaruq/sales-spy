<?php
require '../auth/auth_check.php';


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
        <title>Admin Dashboard - Users</title>
        <script src="https://cdn.tailwindcss.com/3.4.16"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
            rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
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
        <!-- Header and Sidebar (optional for standalone) -->
         <header class="bg-white shadow-sm fixed top-0 left-0 right-0 z-30">
      <div class="flex items-center justify-between px-4 h-16">
        <div class="flex items-center">
          <button id="sidebar-toggle"
            class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-primary mr-2">
            <i class="ri-menu-line ri-lg"></i>
          </button>
          <div class="font-['Pacifico'] text-xl text-primary">Sales-Spy</div>
          <span class="ml-4 text-lg font-medium hidden md:block">Admin Dashboard</span>
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
                <span class="text-sm font-medium">JD</span>
              </div>
              <span class="ml-2 text-sm font-medium hidden md:block"><?= $adminName ?></span>
              <i class="ri-arrow-down-s-line ml-1 text-gray-500"></i>
            </button>
          </div>
        </div>
      </div>
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
    </header>
    <!-- Sidebar -->
    <aside id="sidebar"
      class="fixed top-16 left-0 bottom-0 w-64 bg-white shadow-sm z-20 sidebar-transition transform md:translate-x-0 -translate-x-full">
      <div class="h-full flex flex-col">
        <div class="p-4 border-b">
          <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center">
              <span class="text-sm font-medium">JD</span>
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
              <a href="#"
                class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-primary bg-blue-50">
                <div class="w-5 h-5 flex items-center justify-center mr-3">
                  <i class="ri-user-line"></i>
                </div>
                <span>Users</span>
              </a>
            </li>
            <li>
              <a href="subscription/"
                class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50">
                <div class="w-5 h-5 flex items-center justify-center mr-3">
                  <i class="ri-vip-crown-line"></i>
                </div>
                <span>Subscriptions</span>
              </a>
            </li>
            <li>
              <a href="wallets/"
                class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50">
                <div class="w-5 h-5 flex items-center justify-center mr-3">
                  <i class="ri-wallet-3-line"></i>
                </div>
                <span>Wallets</span>
              </a>
            </li>
            <li>
              <a href="payment/"
                class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50">
                <div class="w-5 h-5 flex items-center justify-center mr-3">
                  <i class="ri-exchange-dollar-line"></i>
                </div>
                <span>Payments</span>
              </a>
            </li>
            <li>
              <a href="pend_payment/"
                class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50">
                <div class="w-5 h-5 flex items-center justify-center mr-3">
                  <i class="ri-time-line"></i>
                </div>
                <span>Pending Payments</span>
              </a>
            </li>
            <li>
              <a href="settings/"
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
            <!-- Users Tab -->
            <div id="users-tab" class="tab-content p-4 md:p-6">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-gray-800">Users Management</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Manage all registered users and their accounts
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="ri-search-line text-gray-400"></i>
                            </div>
                            <input type="text" id="user-search"
                                class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                placeholder="Search users by name or email..." />
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="custom-select" id="plan-filter">
                                <div class="custom-select-trigger">
                                    <span>All Plans</span>
                                    <i class="ri-arrow-down-s-line ml-2"></i>
                                </div>
                                <div class="custom-select-options">
                                    <div class="custom-select-option" data-value="all">
                                        All Plans
                                    </div>
                                    <div class="custom-select-option" data-value="free">
                                        Free
                                    </div>
                                    <div class="custom-select-option" data-value="pro">Pro</div>
                                    <div class="custom-select-option" data-value="enterprise">
                                        Enterprise
                                    </div>
                                </div>
                            </div>
                            <div class="custom-select" id="status-filter">
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
                                    <div class="custom-select-option" data-value="suspended">
                                        Suspended
                                    </div>
                                </div>
                            </div>
                            <button
                                class="px-4 py-2 bg-primary text-white rounded-button whitespace-nowrap flex items-center"
                                id="export-users-btn" style="color: white; background-color:#3366ff; border-radius: 8px;">
                                <i class="ri-download-2-line mr-2"></i>
                                Export
                            </button>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full" id="users-table">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-3 text-left">
                                        <label class="custom-checkbox">
                                            <input type="checkbox" id="select-all-users" />
                                            <span class="checkbox-mark"></span>
                                        </label>
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Plan
                                    </th>
                                   <!-- <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Wallet
                                    </th>-->
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Joined
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" id="users-table-body">
                                <!-- Filled by JS -->
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3 flex items-center justify-between border-t">
                        <div class="flex items-center text-sm text-gray-500" id="users-pagination-info">
                            <span>Showing</span>
                            <div class="custom-select mx-2" id="page-size-select">
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
                            <span id="users-total-count">of 50 users</span>
                        </div>
                        <div class="flex items-center space-x-1" id="users-pagination">
                            <button class="pagination-item text-gray-500 hover:bg-gray-100" id="users-prev-page">
                                <i class="ri-arrow-left-s-line"></i>
                            </button>
                            <!-- Page numbers will be injected here -->
                            <button class="pagination-item text-gray-600 hover:bg-gray-100 users-page-btn"
                                data-page="1">
                                1
                            </button>
                            <button class="pagination-item text-gray-600 hover:bg-gray-100 users-page-btn"
                                data-page="2">
                                2
                            </button>
                            <button class="pagination-item text-gray-600 hover:bg-gray-100 users-page-btn"
                                data-page="3">
                                3
                            </button>
                            <button class="pagination-item text-gray-600 hover:bg-gray-100 users-page-btn"
                                data-page="4">
                                4
                            </button>
                            <button class="pagination-item text-gray-600 hover:bg-gray-100 users-page-btn"
                                data-page="5">
                                5
                            </button>
                            <button class="pagination-item text-gray-500 hover:bg-gray-100" id="users-next-page">
                                <i class="ri-arrow-right-s-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            
        </main>

        <!-- Modals -->
         <!-- Unsuspend User Modal -->
            <div id="unsuspend-user-modal" class="modal">
                <div class="modal-content">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-800">Unsuspend User</h3>
                        <button class="modal-close text-gray-400 hover:text-gray-500">
                            <i class="ri-close-line ri-lg"></i>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div
                                class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                                <i class="ri-play-circle-line ri-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-800">
                                    Are you sure you want to unsuspend this user?
                                </h4>
                                <p class="text-sm text-gray-500">
                                    The user will regain access to their account immediately.
                                </p>
                            </div>
                        </div>
                        <div>
                            <label for="unsuspend-reason" class="block text-sm font-medium text-gray-700 mb-1">Reason
                                for unsuspension
                                (optional)</label>
                            <textarea id="unsuspend-reason" rows="3"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                placeholder="Enter reason for unsuspension..."></textarea>
                        </div>
                        <div class="flex items-center">
                            <label class="custom-checkbox">
                                <input type="checkbox" checked />
                                <span class="checkbox-mark"></span>
                            </label>
                            <span class="ml-2 text-sm text-gray-600">Send email notification to user</span>
                        </div>
                        <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                            <button
                                class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50 modal-close">
                                Cancel
                            </button>
                            <button class="px-4 py-2 bg-green-600 text-white rounded-button whitespace-nowrap"
                                id="confirm-unsuspend-user">
                                Unsuspend User
                            </button>
                        </div>
                    </div>
                </div>
            </div>

             <div id="view-user-modal" class="modal">
      <div class="modal-content">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-800">User Details</h3>
          <button class="modal-close text-gray-400 hover:text-gray-500">
            <i class="ri-close-line ri-lg"></i>
          </button>
        </div>
        <div class="space-y-4">
          <div class="flex items-center">
            <div class="w-16 h-16 rounded-full bg-blue-100 text-primary flex items-center justify-center">
              <span class="text-xl font-medium">EB</span>
            </div>
            <div class="ml-4">
              <h4 class="text-xl font-medium text-gray-800">Emma Brown</h4>
              <p class="text-sm text-gray-500">emma.brown@example.com</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Subscription</p>
              <p class="text-sm font-medium text-gray-800">Pro Plan</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Status</p>
              <p class="text-sm font-medium text-green-600">Active</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Joined</p>
              <p class="text-sm font-medium text-gray-800">Jun 12, 2025</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Wallet</p>
              <p class="text-sm font-medium text-green-600">Connected</p>
            </div>
          </div>
          <div>
            <p class="text-sm text-gray-500">Wallet Address</p>
            <div class="flex items-center mt-1">
              <p class="text-sm font-medium text-gray-800 truncate">
                0x71C7656EC7ab88b098defB751B7401B5f6d8976F
              </p>
              <button class="ml-2 text-gray-400 hover:text-primary">
                <i class="ri-file-copy-line"></i>
              </button>
            </div>
          </div>
          <div>
            <p class="text-sm text-gray-500">Recent Transactions</p>
            <div class="mt-2 space-y-2">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                    <i class="ri-arrow-down-line"></i>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium text-gray-800">
                      Pro Plan Subscription
                    </p>
                    <p class="text-xs text-gray-500">Jun 25, 2025</p>
                  </div>
                </div>
                <p class="text-sm font-medium text-gray-800">0.05 ETH</p>
              </div>
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                    <i class="ri-arrow-down-line"></i>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium text-gray-800">
                      Pro Plan Subscription
                    </p>
                    <p class="text-xs text-gray-500">May 25, 2025</p>
                  </div>
                </div>
                <p class="text-sm font-medium text-gray-800">0.05 ETH</p>
              </div>
            </div>
          </div>
          <div class="flex items-center justify-end space-x-3 pt-4 border-t">
            <button
              class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50 modal-close">
              Close
            </button>
            <button
              class="px-4 py-2 border border-yellow-200 text-yellow-600 rounded-button whitespace-nowrap hover:bg-yellow-50">
              Suspend User
            </button>
            <button
              class="px-4 py-2 border border-red-200 text-red-600 rounded-button whitespace-nowrap hover:bg-red-50">
              Delete User
            </button>
          </div>
        </div>
      </div>
    </div>
    <div id="delete-user-modal" class="modal">
      <div class="modal-content">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-800">Delete User</h3>
          <button class="modal-close text-gray-400 hover:text-gray-500">
            <i class="ri-close-line ri-lg"></i>
          </button>
        </div>
        <div class="space-y-4">
          <div class="flex items-center">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
              <i class="ri-delete-bin-line ri-lg"></i>
            </div>
            <div class="ml-4">
              <h4 class="text-lg font-medium text-gray-800">
                Are you sure you want to delete this user?
              </h4>
              <p class="text-sm text-gray-500">
                This action cannot be undone. All user data, subscriptions, and
                wallet information will be permanently removed.
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
                  <p>
                    Deleting a user with active subscriptions may result in
                    payment issues. Consider suspending the user instead.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div>
            <label for="delete-confirmation" class="block text-sm font-medium text-gray-700 mb-1">Type "DELETE" to
              confirm</label>
            <input type="text" id="delete-confirmation"
              class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
              placeholder="DELETE" />
          </div>
          <div class="flex items-center justify-end space-x-3 pt-4 border-t">
            <button
              class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50 modal-close">
              Cancel
            </button>
            <button class="px-4 py-2 bg-red-600 text-white rounded-button whitespace-nowrap">
              Delete User
            </button>
          </div>
        </div>
      </div>
    </div>
    <div id="suspend-user-modal" class="modal">
      <div class="modal-content">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-800">Suspend User</h3>
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
                Are you sure you want to suspend this user?
              </h4>
              <p class="text-sm text-gray-500">
                The user will not be able to access their account until you
                reactivate it.
              </p>
            </div>
          </div>
          <div>
            <label for="suspension-reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for suspension
              (optional)</label>
            <textarea id="suspension-reason" rows="3"
              class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
              placeholder="Enter reason for suspension..."></textarea>
          </div>
          <div>
            <label for="suspension-duration" class="block text-sm font-medium text-gray-700 mb-1">Suspension
              Duration</label>
            <div class="custom-select" id="suspension-duration-select">
              <div class="custom-select-trigger">
                <span>1 week</span>
                <i class="ri-arrow-down-s-line ml-2"></i>
              </div>
              <div class="custom-select-options">
                <div class="custom-select-option" data-value="1day">1 day</div>
                <div class="custom-select-option" data-value="1week">
                  1 week
                </div>
                <div class="custom-select-option" data-value="1month">
                  1 month
                </div>
                <div class="custom-select-option" data-value="indefinite">
                  Indefinite
                </div>
              </div>
            </div>
          </div>
          <div class="flex items-center">
            <label class="custom-checkbox">
              <input type="checkbox" />
              <span class="checkbox-mark"></span>
            </label>
            <span class="ml-2 text-sm text-gray-600">Send email notification to user</span>
          </div>
          <div class="flex items-center justify-end space-x-3 pt-4 border-t">
            <button
              class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50 modal-close">
              Cancel
            </button>
            <button class="px-4 py-2 bg-yellow-600 text-white rounded-button whitespace-nowrap">
              Suspend User
            </button>
          </div>
        </div>
      </div>
    </div>
        <script>
            // Replace the existing script section in index.php with this updated version
(function () {
    // State
    let users = [];
    let filteredUsers = [];
    let currentPage = 1;
    let pageSize = 10;
    let planFilter = "all";
    let statusFilter = "all";
    let searchQuery = "";
    let totalUsers = 0;
    let totalPages = 1;
    let loading = false;

    // DOM Elements
    const tbody = document.getElementById("users-table-body");
    const pageSizeSelect = document.getElementById("page-size-select");
    const planFilterSelect = document.getElementById("plan-filter");
    const statusFilterSelect = document.getElementById("status-filter");
    const searchInput = document.getElementById("user-search");
    const pagination = document.getElementById("users-pagination");
    const prevBtn = document.getElementById("users-prev-page");
    const nextBtn = document.getElementById("users-next-page");
    const pageInfo = document.getElementById("users-pagination-info");
    const totalCount = document.getElementById("users-total-count");

    // For modals
    let currentUserId = null;

    // API Functions - Fixed with better error handling and correct paths
    async function fetchUsers() {
        if (loading) return;
        
        loading = true;
        showLoadingState();

        try {
            const params = new URLSearchParams({
                page: currentPage,
                limit: pageSize,
                search: searchQuery,
                plan: planFilter,
                status: statusFilter
            });

            console.log('Fetching users with params:', {
                page: currentPage,
                limit: pageSize,
                search: searchQuery,
                plan: planFilter,
                status: statusFilter
            });

            // Determine the correct API path
            let apiPath = 'api/users.php';
            
            // Check if we're in admin directory and adjust path accordingly
            const currentPath = window.location.pathname;
            if (currentPath.includes('/admin/home/') || currentPath.includes('/admin/')) {
                apiPath = 'api/users.php';
            }

            console.log('Using API path:', apiPath);

            const response = await fetch(`${apiPath}?${params}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                // Add cache busting
                cache: 'no-cache'
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('API Error Response:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('API Response:', data);
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            users = data.users || [];
            totalUsers = data.total || 0;
            totalPages = data.totalPages || 1;
            currentPage = data.page || 1;
            
            console.log(`Loaded ${users.length} users out of ${totalUsers} total`);
            
            renderTable();
            renderPagination();
            updatePaginationInfo();
            
        } catch (error) {
            console.error('Error fetching users:', error);
            showError(`Failed to load users: ${error.message}`);
        } finally {
            loading = false;
        }
    }

    async function performUserAction(action, userId, data = {}) {
        try {
            let apiPath = 'api/users.php';
            
            // Adjust path based on current location
            const currentPath = window.location.pathname;
            if (currentPath.includes('/admin/home/') || currentPath.includes('/admin/')) {
                apiPath = 'api/users.php';
            }

            const response = await fetch(apiPath, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    userId: userId,
                    ...data
                })
            });

            const responseText = await response.text();
            console.log('API Response:', responseText);

            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error('Failed to parse JSON response:', responseText);
                throw new Error('Invalid JSON response from server');
            }
            
            if (!response.ok) {
                throw new Error(result.error || `HTTP error! status: ${response.status}`);
            }

            return result;
        } catch (error) {
            console.error('Error performing user action:', error);
            throw error;
        }
    }

    async function fetchUserDetails(userId) {
        try {
            let apiPath = 'api/users.php';
            
            // Adjust path based on current location
            const currentPath = window.location.pathname;
            if (currentPath.includes('/admin/home/') || currentPath.includes('/admin/')) {
                apiPath = 'api/users.php';
            }

            const response = await fetch(apiPath, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'getUserDetails',
                    userId: userId
                })
            });

            const responseText = await response.text();
            console.log('User Details Response:', responseText);

            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error('Failed to parse JSON response:', responseText);
                throw new Error('Invalid JSON response from server');
            }
            
            if (!response.ok) {
                throw new Error(result.error || 'Failed to fetch user details');
            }

            return result.user;
        } catch (error) {
            console.error('Error fetching user details:', error);
            throw error;
        }
    }

    // UI Functions
    function showLoadingState() {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-4 py-8 text-center">
                    <div class="flex items-center justify-center space-x-2">
                        <div class="w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-gray-500">Loading users...</span>
                    </div>
                </td>
            </tr>
        `;
    }

    function showError(message) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-4 py-8 text-center">
                    <div class="flex flex-col items-center space-y-2">
                        <div class="flex items-center space-x-2 text-red-600">
                            <i class="ri-error-warning-line"></i>
                            <span>${message}</span>
                        </div>
                        <button onclick="location.reload()" class="mt-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600">
                            Retry
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    function showToast(message, type = 'success') {
        // Create toast if it doesn't exist
        let toast = document.getElementById('toast-notification');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toast-notification';
            toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-transform transform translate-x-full ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            document.body.appendChild(toast);
        }
        
        // Update toast class and message
        toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-transform transform ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        
        toast.textContent = message;
        toast.classList.remove('translate-x-full');
        
        setTimeout(() => {
            toast.classList.add('translate-x-full');
        }, 3000);
    }

    function renderTable() {
        if (users.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        ${searchQuery ? `No users found matching "${searchQuery}"` : 'No users found'}
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = users
            .map(
                (user) => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 whitespace-nowrap">
                    <label class="custom-checkbox">
                        <input type="checkbox" class="user-checkbox" data-user-id="${user.id}">
                        <span class="checkbox-mark"></span>
                    </label>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex items-center">
                        ${user.profile_picture ? 
                            `<img src="../../${user.profile_picture}" alt="${user.name}" class="w-8 h-8 rounded-full">` :
                            `<div class="w-8 h-8 rounded-full ${user.planColor.includes('blue') ? 'bg-blue-100 text-primary' : 
                                user.planColor.includes('purple') ? 'bg-purple-100 text-purple-600' : 
                                user.planColor.includes('orange') ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-600'
                            } flex items-center justify-center">
                                <span class="text-sm font-medium">${user.initials}</span>
                            </div>`
                        }
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">${user.name}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">${user.email}</td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-medium ${user.planColor} rounded-full">${user.plan}</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">${user.joined}</td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-medium ${user.statusColor} rounded-full">${user.status}</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end space-x-2">
                        <button class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-primary rounded-full hover:bg-blue-50" 
                                data-action="view" data-user-id="${user.id}" title="View Details">
                            <i class="ri-eye-line"></i>
                        </button>
                        ${user.status === "Suspended"
                            ? `<button class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-green-500 rounded-full hover:bg-green-50" 
                                       data-action="unsuspend" data-user-id="${user.id}" title="Unsuspend User">
                                    <i class="ri-play-circle-line"></i>
                                </button>`
                            : `<button class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-yellow-500 rounded-full hover:bg-yellow-50" 
                                       data-action="suspend" data-user-id="${user.id}" title="Suspend User">
                                    <i class="ri-pause-circle-line"></i>
                                </button>`
                        }
                        <button class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-red-500 rounded-full hover:bg-red-50" 
                                data-action="delete" data-user-id="${user.id}" title="Delete User">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `
            )
            .join("");

        // Reattach event listeners
        attachTableEventListeners();
    }

    function attachTableEventListeners() {
        // Select all checkbox
        const selectAll = document.getElementById("select-all-users");
        const checkboxes = tbody.querySelectorAll(".user-checkbox");
        
        if (selectAll) {
            selectAll.checked = false;
            selectAll.onchange = function () {
                checkboxes.forEach((cb) => (cb.checked = selectAll.checked));
            };
        }

        // Action buttons
        const actionButtons = tbody.querySelectorAll('[data-action]');
        actionButtons.forEach(button => {
            button.onclick = function(e) {
                e.preventDefault();
                const action = this.getAttribute('data-action');
                const userId = this.getAttribute('data-user-id');
                currentUserId = userId;
                
                handleUserAction(action, userId);
            };
        });
    }

    function handleUserAction(action, userId) {
        switch (action) {
            case 'view':
                openViewUserModal(userId);
                break;
            case 'suspend':
                openModal('suspend-user-modal');
                break;
            case 'unsuspend':
                openModal('unsuspend-user-modal');
                break;
            case 'delete':
                openModal('delete-user-modal');
                break;
        }
    }

    async function openViewUserModal(userId) {
        try {
            showLoadingInModal('view-user-modal');
            const user = await fetchUserDetails(userId);
            populateViewUserModal(user);
            openModal('view-user-modal');
        } catch (error) {
            console.error('Failed to load user details:', error);
            showToast('Failed to load user details', 'error');
        }
    }

    function showLoadingInModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.querySelector('.modal-content').innerHTML = `
                <div class="flex items-center justify-center py-8">
                    <div class="w-8 h-8 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                    <span class="ml-3">Loading...</span>
                </div>
            `;
        }
    }

    function populateViewUserModal(user) {
        const modal = document.getElementById('view-user-modal');
        if (!modal) return;

        const walletAddress = user.wallet_address || '0x71C7656EC7ab88b098defB751B7401B5f6d8976F';
        const recentTransactions = user.transactions || [];

        modal.querySelector('.modal-content').innerHTML = `
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-800">User Details</h3>
                <button class="modal-close text-gray-400 hover:text-gray-500">
                    <i class="ri-close-line ri-lg"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div class="flex items-center">
                    <div class="w-16 h-16 rounded-full bg-blue-100 text-primary flex items-center justify-center">
                        <span class="text-xl font-medium">${user.initials}</span>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-xl font-medium text-gray-800">${user.full_name}</h4>
                        <p class="text-sm text-gray-500">${user.email}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Subscription</p>
                        <p class="text-sm font-medium text-gray-800">${user.plan_name || 'Free'}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="text-sm font-medium ${user.account_status === 'active' ? 'text-green-600' : 'text-yellow-600'}">
                            ${user.account_status === 'active' ? 'Active' : 'Suspended'}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Joined</p>
                        <p class="text-sm font-medium text-gray-800">${user.joined}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Leads Balance</p>
                        <p class="text-sm font-medium text-blue-600">${user.leads_balance || 0}</p>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="text-sm font-medium text-gray-800">${user.phone || 'Not provided'}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Paid</p>
                    <p class="text-sm font-medium text-green-600">${user.total_paid || '0.00'}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Recent Transactions</p>
                    <div class="mt-2 space-y-2 max-h-32 overflow-y-auto">
                        ${recentTransactions.length > 0 ? 
                            recentTransactions.map(tx => `
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full ${tx.status === 'success' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600'} flex items-center justify-center">
                                            <i class="ri-arrow-${tx.status === 'success' ? 'down' : 'right'}-line"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-800">${tx.payment_type || 'Payment'}</p>
                                            <p class="text-xs text-gray-500">${new Date(tx.created_at).toLocaleDateString()}</p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-medium text-gray-800">${tx.amount}</p>
                                </div>
                            `).join('') : 
                            '<p class="text-sm text-gray-500">No recent transactions</p>'
                        }
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <button class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50 modal-close">
                        Close
                    </button>
                    <button class="px-4 py-2 border border-yellow-200 text-yellow-600 rounded-button whitespace-nowrap hover:bg-yellow-50"
                            onclick="closeModal(document.getElementById('view-user-modal')); handleUserAction('${user.account_status === 'active' ? 'suspend' : 'unsuspend'}', '${user.id}')">
                        ${user.account_status === 'active' ? 'Suspend' : 'Unsuspend'} User
                    </button>
                    <button class="px-4 py-2 border border-red-200 text-red-600 rounded-button whitespace-nowrap hover:bg-red-50"
                            onclick="closeModal(document.getElementById('view-user-modal')); handleUserAction('delete', '${user.id}')">
                        Delete User
                    </button>
                </div>
            </div>
        `;

        // Reattach modal close listeners
        attachModalListeners();
    }

    function renderPagination() {
        // Clear existing page buttons
        const existingPageButtons = pagination.querySelectorAll(".users-page-btn");
        existingPageButtons.forEach(btn => btn.remove());
        
        // Calculate page range
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        // Adjust start if we're near the end
        if (endPage - startPage < maxVisiblePages - 1) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        // Add new page buttons
        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement("button");
            btn.className = `pagination-item text-gray-600 hover:bg-gray-100 users-page-btn ${
                i === currentPage ? 'active bg-primary text-white' : ''
            }`;
            btn.textContent = i;
            btn.setAttribute("data-page", i);
            
            btn.onclick = function () {
                if (currentPage !== i) {
                    currentPage = i;
                    fetchUsers();
                }
            };
            
            pagination.insertBefore(btn, nextBtn);
        }

        // Update prev/next buttons
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        prevBtn.classList.toggle("opacity-50", prevBtn.disabled);
        prevBtn.classList.toggle("cursor-not-allowed", prevBtn.disabled);
        nextBtn.classList.toggle("opacity-50", nextBtn.disabled);
        nextBtn.classList.toggle("cursor-not-allowed", nextBtn.disabled);
    }

    function updatePaginationInfo() {
        const start = totalUsers === 0 ? 0 : (currentPage - 1) * pageSize + 1;
        const end = Math.min(currentPage * pageSize, totalUsers);
        
        totalCount.textContent = `of ${totalUsers} users`;
        
        // Update the page size display
        const pageSizeTrigger = pageSizeSelect.querySelector('.custom-select-trigger span');
        if (pageSizeTrigger) {
            pageSizeTrigger.textContent = pageSize.toString();
        }
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    function attachModalListeners() {
        // Modal close buttons
        document.querySelectorAll('.modal-close').forEach(button => {
            button.onclick = function() {
                const modal = this.closest('.modal');
                closeModal(modal);
            };
        });

        // Click outside to close
        document.querySelectorAll('.modal').forEach(modal => {
            modal.onclick = function(e) {
                if (e.target === this) {
                    closeModal(this);
                }
            };
        });
    }

    // Event Listeners
    function attachEventListeners() {
        // Search input with improved debouncing
        let searchTimeout;
        searchInput.addEventListener("input", function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const newSearchQuery = this.value.trim();
                console.log('Search query changed to:', newSearchQuery);
                searchQuery = newSearchQuery;
                currentPage = 1;
                fetchUsers();
            }, 300);
        });

        // Also trigger search on Enter key
        searchInput.addEventListener("keypress", function(e) {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                const newSearchQuery = this.value.trim();
                console.log('Search triggered by Enter:', newSearchQuery);
                searchQuery = newSearchQuery;
                currentPage = 1;
                fetchUsers();
            }
        });

        // Pagination
        prevBtn.onclick = function () {
            if (currentPage > 1 && !loading) {
                currentPage--;
                fetchUsers();
            }
        };

        nextBtn.onclick = function () {
            if (currentPage < totalPages && !loading) {
                currentPage++;
                fetchUsers();
            }
        };

        // Export button
        document.getElementById("export-users-btn").onclick = async function () {
            try {
                this.disabled = true;
                this.innerHTML = '<i class="ri-loader-4-line mr-2 animate-spin"></i>Exporting...';
                
                // Determine API path
                let apiPath = 'api/users.php';
                const currentPath = window.location.pathname;
                if (currentPath.includes('/admin/home/') || currentPath.includes('/admin/')) {
                    apiPath = 'api/users.php';
                }
                
                // Fetch all users for export (without pagination)
                const params = new URLSearchParams({
                    limit: 1000,
                    search: searchQuery,
                    plan: planFilter,
                    status: statusFilter
                });
                
                const response = await fetch(`${apiPath}?${params}`);
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                let csv = "Name,Email,Plan,Joined,Status\n";
                data.users.forEach((u) => {
                    csv += `"${u.name}","${u.email}","${u.plan}","${u.joined}","${u.status}"\n`;
                });
                
                const blob = new Blob([csv], { type: "text/csv" });
                const a = document.createElement("a");
                a.href = URL.createObjectURL(blob);
                a.download = `users_${new Date().toISOString().split('T')[0]}.csv`;
                a.click();
                
                showToast('Users exported successfully');
            } catch (error) {
                console.error('Export error:', error);
                showToast('Export failed: ' + error.message, 'error');
            } finally {
                this.disabled = false;
                this.innerHTML = '<i class="ri-download-2-line mr-2"></i>Export';
            }
        };

        // Modal action handlers
        setupModalHandlers();
    }

    function setupModalHandlers() {
        // Suspend user
        const confirmSuspendBtn = document.querySelector('#suspend-user-modal .bg-yellow-600');
        if (confirmSuspendBtn) {
            confirmSuspendBtn.onclick = async function() {
                try {
                    this.disabled = true;
                    this.textContent = 'Suspending...';
                    
                    const reason = document.getElementById('suspension-reason')?.value || '';
                    const durationSelect = document.querySelector('#suspension-duration-select');
                    const duration = durationSelect?.getAttribute('data-value') || '1week';
                    const sendNotification = document.querySelector('#suspend-user-modal input[type="checkbox"]')?.checked || false;

                    await performUserAction('suspend', currentUserId, {
                        reason,
                        duration,
                        sendNotification
                    });

                    closeModal(document.getElementById('suspend-user-modal'));
                    showToast('User suspended successfully');
                    fetchUsers(); // Refresh the table
                } catch (error) {
                    console.error('Suspend error:', error);
                    showToast(error.message || 'Failed to suspend user', 'error');
                } finally {
                    this.disabled = false;
                    this.textContent = 'Suspend User';
                }
            };
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
        // Unsuspend user
        const confirmUnsuspendBtn = document.getElementById('confirm-unsuspend-user');
        if (confirmUnsuspendBtn) {
            confirmUnsuspendBtn.onclick = async function() {
                try {
                    this.disabled = true;
                    this.textContent = 'Unsuspending...';
                    
                    const reason = document.getElementById('unsuspend-reason')?.value || '';
                    const sendNotification = document.querySelector('#unsuspend-user-modal input[type="checkbox"]')?.checked || false;

                    await performUserAction('unsuspend', currentUserId, {
                        reason,
                        sendNotification
                    });

                    closeModal(document.getElementById('unsuspend-user-modal'));
                    showToast('User unsuspended successfully');
                    fetchUsers(); // Refresh the table
                } catch (error) {
                    console.error('Unsuspend error:', error);
                    showToast(error.message || 'Failed to unsuspend user', 'error');
                } finally {
                    this.disabled = false;
                    this.textContent = 'Unsuspend User';
                }
            };
        }

        // Delete user
        const confirmDeleteBtn = document.querySelector('#delete-user-modal .bg-red-600');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.onclick = async function() {
                try {
                    this.disabled = true;
                    this.textContent = 'Deleting...';
                    
                    const confirmation = document.getElementById('delete-confirmation')?.value || '';
                    
                    if (confirmation !== 'DELETE') {
                        showToast('Please type "DELETE" to confirm', 'error');
                        return;
                    }

                    await performUserAction('delete', currentUserId, { confirmation });

                    closeModal(document.getElementById('delete-user-modal'));
                    showToast('User deleted successfully');
                    fetchUsers(); // Refresh the table
                } catch (error) {
                    console.error('Delete error:', error);
                    showToast(error.message || 'Failed to delete user', 'error');
                } finally {
                    this.disabled = false;
                    this.textContent = 'Delete User';
                }
            };
        }
    }

    // Custom select handlers
    function setupCustomSelects() {
        // Page size select
        setupCustomSelect('page-size-select', function(value) {
            pageSize = parseInt(value, 10);
            currentPage = 1;
            console.log('Page size changed to:', pageSize);
            fetchUsers();
        });

        // Plan filter
        setupCustomSelect('plan-filter', function(value) {
            planFilter = value;
            currentPage = 1;
            console.log('Plan filter changed to:', planFilter);
            fetchUsers();
        });

        // Status filter
        setupCustomSelect('status-filter', function(value) {
            statusFilter = value;
            currentPage = 1;
            console.log('Status filter changed to:', statusFilter);
            fetchUsers();
        });

        // Suspension duration select
        setupCustomSelect('suspension-duration-select', function(value) {
            // This just sets the data attribute for later use
            console.log('Suspension duration selected:', value);
        });
    }

    function setupCustomSelect(selectId, onChange) {
        const select = document.getElementById(selectId);
        if (!select) return;

        const trigger = select.querySelector('.custom-select-trigger');
        const options = select.querySelector('.custom-select-options');
        const optionElements = select.querySelectorAll('.custom-select-option');

        if (!trigger || !options) return;

        trigger.onclick = function(e) {
            e.stopPropagation();
            // Close other selects
            document.querySelectorAll('.custom-select.open').forEach(otherSelect => {
                if (otherSelect !== select) {
                    otherSelect.classList.remove('open');
                }
            });
            select.classList.toggle('open');
        };

        optionElements.forEach(option => {
            option.onclick = function(e) {
                e.stopPropagation();
                const value = this.getAttribute('data-value');
                const text = this.textContent.trim();
                
                trigger.querySelector('span').textContent = text;
                select.setAttribute('data-value', value);
                select.classList.remove('open');
                
                if (onChange) onChange(value);
            };
        });

        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (!select.contains(e.target)) {
                select.classList.remove('open');
            }
        });
    }

    // Utility functions
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Make some functions globally available for inline onclick handlers
    window.handleUserAction = handleUserAction;
    window.closeModal = closeModal;

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        console.log('User management script initializing...');
        attachEventListeners();
        setupCustomSelects();
        attachModalListeners();
        fetchUsers(); // Initial load
    });

    // Also initialize immediately if DOM is already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }

    function initialize() {
        console.log('User management script initializing...');
        attachEventListeners();
        setupCustomSelects();
        attachModalListeners();
        fetchUsers(); // Initial load
    }
})();
    </script>

     <script id="checkbox-script">
      document.addEventListener("DOMContentLoaded", function () {
        // Select all checkboxes
        const selectAllUsers = document.getElementById("select-all-users");
        const userCheckboxes = document.querySelectorAll(".user-checkbox");
        const selectAllWallets = document.getElementById("select-all-wallets");
        const walletCheckboxes = document.querySelectorAll(".wallet-checkbox");
        const selectAllTransactions = document.getElementById(
          "select-all-transactions"
        );
        const transactionCheckboxes = document.querySelectorAll(
          ".transaction-checkbox"
        );
        // Handle select all users
        if (selectAllUsers) {
          selectAllUsers.addEventListener("change", function () {
            userCheckboxes.forEach((checkbox) => {
              checkbox.checked = this.checked;
            });
          });
        }
        // Handle select all wallets
        if (selectAllWallets) {
          selectAllWallets.addEventListener("change", function () {
            walletCheckboxes.forEach((checkbox) => {
              checkbox.checked = this.checked;
            });
          });
        }
        // Handle select all transactions
        if (selectAllTransactions) {
          selectAllTransactions.addEventListener("change", function () {
            transactionCheckboxes.forEach((checkbox) => {
              checkbox.checked = this.checked;
            });
          });
        }
      });
    </script>
    </body>

</html>