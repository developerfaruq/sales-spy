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
                <span class="text-sm font-medium"><img src="<?= $avatarUrl ?>" alt="ss"></span>
              </div>
              <span class="ml-2 text-sm font-medium hidden md:block"><?= $adminName ?></span>
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
              <span class="text-sm font-medium"><img src="<?= $avatarUrl ?>" alt="ss"></span>
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
                    <a href="../payment/"
                        class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50">
                        <div class="w-5 h-5 flex items-center justify-center mr-3">
                            <i class="ri-exchange-dollar-line"></i>
                        </div>
                        <span>Payments</span>
                    </a>
                </li>
                <li>
                    <a href="../pend_payment/"
                        class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-primary bg-blue-50">
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
            <!-- Pending Payments Tab -->
            <div id="pending-tab" class="tab-content  p-4 md:p-6">
        <div class="mb-6">
          <h2 class="text-2xl font-bold text-slate-800 mb-2">Pending Payments</h2>
          <p class="text-slate-600">Review and approve user payment submissions</p>
        </div>
        <div id="paymentsContainer" class="space-y-4">
          <!-- Payment Card Template 1 -->
          <div class="payment-card glass rounded-lg px-3 py-2 sm:px-4 sm:py-3">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 md:gap-4">
              <div class="flex-1">
                <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 gap-1 sm:gap-2">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                      <span class="text-[10px] sm:text-xs font-semibold text-white">JD</span>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900 text-xs sm:text-sm">John Davidson</p>
                      <p class="text-[10px] sm:text-xs text-slate-600 font-mono">#ORD-2024-001</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex items-center gap-1">
                      <div class="w-3.5 h-3.5 sm:w-4 sm:h-4 flex items-center justify-center">
                        <i class="ri-bank-card-line text-blue-400 text-sm sm:text-base"></i>
                      </div>
                      <span class="text-[10px] sm:text-xs text-slate-600">Card</span>
                    </div>
                    <div class="text-sm sm:text-base font-bold text-green-500">$299.00</div>
                    <div class="text-[10px] sm:text-xs text-slate-600">Dec 15, 2024</div>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-1 sm:gap-2">
                <button
                  class="view-btn w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center glass rounded-lg hover:bg-slate-600/50 transition-colors"
                  data-payment='{"userId": "JD", "userName": "John Davidson", "orderId": "#ORD-2024-001", "paymentMethod": "Card", "amount": "$299.00", "date": "Dec 15, 2024", "transactionId": "TXN-4A7B9C2D", "screenshot": "https://readdy.ai/api/search-image?query=credit%20card%20payment%20receipt%20screenshot%20showing%20successful%20transaction%20with%20amount%20and%20date%2C%20clean%20white%20background%2C%20professional%20banking%20interface&width=400&height=300&seq=1&orientation=landscape"}'>
                  <i class="ri-eye-line text-slate-300"></i>
                </button>
                <button
                  class="approve-btn !rounded-button bg-green-600 hover:bg-green-700 px-3 py-1.5 text-xs font-medium whitespace-nowrap transition-colors">
                  <span class="btn-text">Approve</span>
                  <div class="btn-spinner hidden">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
                  </div>
                </button>
                <button
                  class="decline-btn !rounded-button bg-red-600 hover:bg-red-700 px-3 py-1.5 text-xs font-medium whitespace-nowrap transition-colors">
                  <span class="btn-text">Decline</span>
                  <div class="btn-spinner hidden">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
                  </div>
                </button>
              </div>
            </div>
          </div>
          <!-- Payment Card Template 2 -->
          <div class="payment-card glass rounded-lg px-4 py-3">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="flex-1">
                <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 gap-2">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-teal-600 rounded-full flex items-center justify-center">
                      <span class="text-xs font-semibold">SM</span>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900 text-sm">Sarah Mitchell</p>
                      <p class="text-xs text-slate-400 font-mono">#ORD-2024-002</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1">
                      <div class="w-4 h-4 flex items-center justify-center">
                        <i class="ri-btc-line text-orange-400 text-base"></i>
                      </div>
                      <span class="text-xs text-slate-300">Crypto</span>
                    </div>
                    <div class="text-base font-bold text-green-400">$599.00</div>
                    <div class="text-xs text-slate-400">Dec 14, 2024</div>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button
                  class="view-btn w-8 h-8 flex items-center justify-center glass rounded-lg hover:bg-slate-600/50 transition-colors"
                  data-payment='{"userId": "SM", "userName": "Sarah Mitchell", "orderId": "#ORD-2024-002", "paymentMethod": "Crypto", "amount": "$599.00", "date": "Dec 14, 2024", "transactionId": "0x7f8e9a1b2c3d4e5f6789abcdef012345", "screenshot": "https://readdy.ai/api/search-image?query=cryptocurrency%20bitcoin%20payment%20transaction%20screenshot%20showing%20wallet%20transfer%20with%20hash%20and%20amount%2C%20dark%20crypto%20exchange%20interface%2C%20professional%20blockchain%20payment&width=400&height=300&seq=2&orientation=landscape"}'>
                  <i class="ri-eye-line text-slate-300"></i>
                </button>
                <button
                  class="approve-btn !rounded-button bg-green-600 hover:bg-green-700 px-3 py-1.5 text-xs font-medium whitespace-nowrap transition-colors">
                  <span class="btn-text">Approve</span>
                  <div class="btn-spinner hidden">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
                  </div>
                </button>
                <button
                  class="decline-btn !rounded-button bg-red-600 hover:bg-red-700 px-3 py-1.5 text-xs font-medium whitespace-nowrap transition-colors">
                  <span class="btn-text">Decline</span>
                  <div class="btn-spinner hidden">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
                  </div>
                </button>
              </div>
            </div>
          </div>
          <!-- Payment Card Template 3 -->
          <div class="payment-card glass rounded-lg px-4 py-3">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="flex-1">
                <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 gap-2">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-600 rounded-full flex items-center justify-center">
                      <span class="text-xs font-semibold">MR</span>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900 text-sm">Michael Rodriguez</p>
                      <p class="text-xs text-slate-400 font-mono">#ORD-2024-003</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1">
                      <div class="w-4 h-4 flex items-center justify-center">
                        <i class="ri-bank-card-line text-blue-400 text-base"></i>
                      </div>
                      <span class="text-xs text-slate-300">Card</span>
                    </div>
                    <div class="text-base font-bold text-green-400">$149.00</div>
                    <div class="text-xs text-slate-400">Dec 13, 2024</div>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button
                  class="view-btn w-8 h-8 flex items-center justify-center glass rounded-lg hover:bg-slate-600/50 transition-colors"
                  data-payment='{"userId": "MR", "userName": "Michael Rodriguez", "orderId": "#ORD-2024-003", "paymentMethod": "Card", "amount": "$149.00", "date": "Dec 13, 2024", "transactionId": "TXN-8E9F1A2B", "screenshot": "https://readdy.ai/api/search-image?query=debit%20card%20payment%20confirmation%20screen%20showing%20successful%20transaction%20with%20amount%20and%20merchant%20details%2C%20clean%20banking%20app%20interface%2C%20white%20background&width=400&height=300&seq=3&orientation=landscape"}'>
                  <i class="ri-eye-line text-slate-300"></i>
                </button>
                <button
                  class="approve-btn !rounded-button bg-green-600 hover:bg-green-700 px-3 py-1.5 text-xs font-medium whitespace-nowrap transition-colors">
                  <span class="btn-text">Approve</span>
                  <div class="btn-spinner hidden">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
                  </div>
                </button>
                <button
                  class="decline-btn !rounded-button bg-red-600 hover:bg-red-700 px-3 py-1.5 text-xs font-medium whitespace-nowrap transition-colors">
                  <span class="btn-text">Decline</span>
                  <div class="btn-spinner hidden">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
                  </div>
                </button>
              </div>
            </div>
          </div>
          <!-- Payment Card Template 4 -->
          <div class="payment-card glass rounded-lg px-4 py-3">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="flex-1">
                <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 gap-2">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-600 rounded-full flex items-center justify-center">
                      <span class="text-xs font-semibold">EC</span>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900 text-sm">Emma Chen</p>
                      <p class="text-xs text-slate-400 font-mono">#ORD-2024-004</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1">
                      <div class="w-4 h-4 flex items-center justify-center">
                        <i class="ri-btc-line text-orange-400 text-base"></i>
                      </div>
                      <span class="text-xs text-slate-300">Crypto</span>
                    </div>
                    <div class="text-base font-bold text-green-400">$899.00</div>
                    <div class="text-xs text-slate-400">Dec 12, 2024</div>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button
                  class="view-btn w-8 h-8 flex items-center justify-center glass rounded-lg hover:bg-slate-600/50 transition-colors"
                  data-payment='{"userId": "EC", "userName": "Emma Chen", "orderId": "#ORD-2024-004", "paymentMethod": "Crypto", "amount": "$899.00", "date": "Dec 12, 2024", "transactionId": "0x9a8b7c6d5e4f3210abcdef987654321", "screenshot": "https://readdy.ai/api/search-image?query=ethereum%20cryptocurrency%20payment%20confirmation%20showing%20successful%20transaction%20with%20wallet%20address%20and%20gas%20fees%2C%20modern%20crypto%20exchange%20interface%2C%20dark%20theme&width=400&height=300&seq=4&orientation=landscape"}'>
                  <i class="ri-eye-line text-slate-300"></i>
                </button>
                <button
                  class="approve-btn !rounded-button bg-green-600 hover:bg-green-700 px-3 py-1.5 text-xs font-medium whitespace-nowrap transition-colors">
                  <span class="btn-text">Approve</span>
                  <div class="btn-spinner hidden">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
                  </div>
                </button>
                <button
                  class="decline-btn !rounded-button bg-red-600 hover:bg-red-700 px-3 py-1.5 text-xs font-medium whitespace-nowrap transition-colors">
                  <span class="btn-text">Decline</span>
                  <div class="btn-spinner hidden">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div id="emptyState" class="hidden empty-state text-center py-16">
          <div class="w-24 h-24 mx-auto mb-6 flex items-center justify-center glass rounded-full">
            <i class="ri-check-double-line text-4xl text-green-500"></i>
          </div>
          <h3 class="text-2xl font-semibold text-slate-800 mb-2">No pending payments at the moment</h3>
          <p class="text-slate-600 max-w-md mx-auto">All payments have been processed. Check back later for new submissions.
          </p>
        </div>
       
      
      
      
      
      </div>
            <!-- Modal -->
            <div id="modal" class="fixed inset-0 z-50 hidden modal-backdrop bg-black/50 backdrop-blur-sm">
          <div class="flex items-center justify-center min-h-screen p-4">
            <div class="modal-content glass-modal rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
              <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                  <h3 class="text-xl font-semibold text-slate-800">Payment Details</h3>
                  <button id="closeModal"
                    class="w-8 h-8 flex items-center justify-center hover:bg-slate-100/80 rounded-lg transition-colors">
                    <i class="ri-close-line text-slate-600"></i>
                  </button>
                </div>
                <div class="space-y-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                      <div>
                        <label class="text-sm text-slate-600">User Information</label>
                        <div class="flex items-center space-x-3 mt-2">
                          <div id="modalUserAvatar"
                            class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span id="modalUserInitials" class="font-semibold">JD</span>
                          </div>
                          <div>
                            <p id="modalUserName" class="font-semibold text-white">John Davidson</p>
                            <p id="modalUserId" class="text-sm text-slate-400">User ID: JD</p>
                          </div>
                        </div>
                      </div>
                      <div>
                        <label class="text-sm text-slate-400">Order ID</label>
                        <p id="modalOrderId" class="font-mono text-gray-700 mt-1">#ORD-2024-001</p>
                      </div>
                      <div>
                        <label class="text-sm text-slate-400">Payment Method</label>
                        <div class="flex items-center space-x-2 mt-1">
                          <div class="w-5 h-5 flex items-center justify-center">
                            <i id="modalPaymentIcon" class="ri-bank-card-line text-blue-400"></i>
                          </div>
                          <span id="modalPaymentMethod" class="text-gray-700">Card</span>
                        </div>
                      </div>
                    </div>
                    <div class="space-y-4">
                      <div>
                        <label class="text-sm text-slate-400">Amount</label>
                        <p id="modalAmount" class="text-2xl font-bold text-green-400 mt-1">$299.00</p>
                      </div>
                      <div>
                        <label class="text-sm text-slate-400">Submission Date</label>
                        <p id="modalDate" class="text-gray-700 mt-1">Dec 15, 2024</p>
                      </div>
                      <div>
                        <label class="text-sm text-slate-400">Transaction ID</label>
                        <p id="modalTransactionId" class="font-mono text-gray-700 mt-1 break-all">TXN-4A7B9C2D</p>
                      </div>
                    </div>
                  </div>
                  <div>
                    <label class="text-sm text-slate-400">Payment Screenshot</label>
                    <div class="mt-2 glass rounded-lg p-4">
                      <img id="modalScreenshot" src="" alt="Payment Screenshot"
                        class="w-full h-48 object-cover object-top rounded-lg">
                    </div>
                  </div>
                </div>
                <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-slate-200">
                  <button id="modalApproveBtn"
                    class="modal-approve-btn !rounded-button bg-green-600 hover:bg-green-700 px-6 py-2 font-medium whitespace-nowrap transition-colors">
                    <span class="btn-text">Approve Payment</span>
                    <div class="btn-spinner hidden">
                      <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
                    </div>
                  </button>
                  <button id="modalDeclineBtn"
                    class="modal-decline-btn !rounded-button bg-red-600 hover:bg-red-700 px-6 py-2 font-medium whitespace-nowrap transition-colors">
                    <span class="btn-text">Decline Payment</span>
                    <div class="btn-spinner hidden">
                      <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
                    </div>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>
        </main>
        
            <style>
              :where([class^="ri-"])::before {
                content: "\f3c2";
              }

              body {
                background: #f8f9fb;
                min-height: 100vh;
                color: #1f2937;
              }

              .glass,
              .glass-modal {
                background: #ffffff !important;
                backdrop-filter: none !important;
                border: 1px solid #e5e7eb !important;
                box-shadow: 0 4px 24px 0 rgba(31, 41, 55, 0.06);
              }

              .payment-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                box-shadow: 0 1.5px 6px 0 rgba(31, 41, 55, 0.04);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                padding: 0.75rem 0.75rem;
              }

              @media (min-width: 640px) {
                .payment-card {
                  padding: 1rem 1rem;
                }
              }

              .payment-card:hover {
                transform: scale(1.02);
                box-shadow: 0 8px 32px -8px rgba(31, 41, 55, 0.10);
              }

              .toast {
                /* Glassmorphism effect */
                background: rgba(255, 255, 255, 0.25);
                backdrop-filter: blur(12px) saturate(180%);
                border: 1px solid rgba(255,255,255,0.25);
                color: #1f2937;
                animation: slideIn 0.3s ease-out;
                box-shadow: 0 8px 32px -8px rgba(31,41,55,0.12);
              }
              .toast-success {
                background: #22c55e !important;
                color: #fff !important;
                border-left: 6px solid #16a34a;
              }
              .toast-error {
                background: #ef4444 !important;
                color: #fff !important;
                border-left: 6px solid #b91c1c;
              }

              @keyframes slideIn {
                from {
                  transform: translateX(100%);
                  opacity: 0;
                }
                to {
                  transform: translateX(0);
                  opacity: 1;
                }
              }

              .modal-backdrop {
                background: rgba(31, 41, 55, 0.25);
                animation: fadeIn 0.3s ease-out;
              }

              .modal-content {
                background: #fff;
                color: #1f2937;
                border-radius: 1rem;
                box-shadow: 0 8px 32px -8px rgba(31, 41, 55, 0.10);
                animation: scaleIn 0.3s ease-out;
              }

              @keyframes fadeIn {
                from {
                  opacity: 0;
                }
                to {
                  opacity: 1;
                }
              }

              @keyframes scaleIn {
                from {
                  opacity: 0;
                  transform: scale(0.9);
                }
                to {
                  opacity: 1;
                  transform: scale(1);
                }
              }

              .empty-state {
                animation: fadeInUp 0.6s ease-out;
              }

              @keyframes fadeInUp {
                from {
                  opacity: 0;
                  transform: translateY(20px);
                }
                to {
                  opacity: 1;
                  transform: translateY(0);
                }
              }

              .spinner {
                border-color: #e5e7eb;
                border-top-color: #2563eb;
                animation: spin 1s linear infinite;
              }

              @keyframes spin {
                from {
                  transform: rotate(0deg);
                }
                to {
                  transform: rotate(360deg);
                }
              }
            </style>


            <script id="modalHandler">
       document.addEventListener('DOMContentLoaded', function () {
    const paymentsContainer = document.getElementById('paymentsContainer');
    const emptyState = document.getElementById('emptyState');
    const modal = document.getElementById('modal');
    const closeModalBtn = document.getElementById('closeModal');
    let currentTransactionId = null;

    // Load pending payments on page load
    loadPendingPayments();

    // Auto-refresh every 30 seconds
    setInterval(loadPendingPayments, 30000);

    // Modal event listeners
    closeModalBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Modal action buttons
    document.getElementById('modalApproveBtn').addEventListener('click', function () {
        if (currentTransactionId) {
            handlePaymentAction('approve', this, currentTransactionId);
        }
    });

    document.getElementById('modalDeclineBtn').addEventListener('click', function () {
        if (currentTransactionId) {
            handlePaymentAction('decline', this, currentTransactionId);
        }
    });

    async function loadPendingPayments() {
        try {
            const response = await fetch('api/get_pend.php', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success) {
                renderPayments(data.transactions);
            } else {
                showToast('Failed to load payments: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error loading payments:', error);
            showToast('Failed to load payments', 'error');
        }
    }

    function renderPayments(transactions) {
        paymentsContainer.innerHTML = '';

        if (transactions.length === 0) {
            paymentsContainer.classList.add('hidden');
            emptyState.classList.remove('hidden');
            return;
        }

        paymentsContainer.classList.remove('hidden');
        emptyState.classList.add('hidden');

        transactions.forEach(transaction => {
            const paymentCard = createPaymentCard(transaction);
            paymentsContainer.appendChild(paymentCard);
        });
    }

    function createPaymentCard(transaction) {
        const div = document.createElement('div');
        div.className = 'payment-card glass rounded-lg px-3 py-2 sm:px-4 sm:py-3';
        div.setAttribute('data-transaction-id', transaction.id);

        // Generate gradient colors based on user initials
        const gradientColors = generateGradientColors(transaction.userId);
        const paymentIcon = transaction.paymentMethod === 'Crypto' ? 'ri-btc-line text-orange-400' : 'ri-bank-card-line text-blue-400';

        div.innerHTML = `
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 md:gap-4">
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 gap-1 sm:gap-2">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 ${gradientColors} rounded-full flex items-center justify-center">
                                <span class="text-[10px] sm:text-xs font-semibold text-white">${transaction.userId}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 text-xs sm:text-sm">${transaction.userName}</p>
                                <p class="text-[10px] sm:text-xs text-slate-600 font-mono">${transaction.orderId}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="flex items-center gap-1">
                                <div class="w-3.5 h-3.5 sm:w-4 sm:h-4 flex items-center justify-center">
                                    <i class="${paymentIcon} text-sm sm:text-base"></i>
                                </div>
                                <span class="text-[10px] sm:text-xs text-slate-600">${transaction.paymentMethod}</span>
                            </div>
                            <div class="text-sm sm:text-base font-bold text-green-500">${transaction.amount}</div>
                            <div class="text-[10px] sm:text-xs text-slate-600">${transaction.date}</div>
                            <div class="text-[10px] sm:text-xs text-blue-600 font-medium">${transaction.planName}</div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1 sm:gap-2">
                    <button class="view-btn w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center glass rounded-lg hover:bg-slate-600/50 transition-colors">
                        <i class="ri-eye-line text-slate-300"></i>
                    </button>
                    <button class="approve-btn !rounded-button bg-green-600 hover:bg-green-700 px-3 py-1.5 text-xs font-medium whitespace-nowrap transition-colors">
                        <span class="btn-text">Approve</span>
                        <div class="btn-spinner hidden">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
                        </div>
                    </button>
                    <button class="decline-btn !rounded-button bg-red-600 hover:bg-red-700 px-3 py-1.5 text-xs font-medium whitespace-nowrap transition-colors">
                        <span class="btn-text">Decline</span>
                        <div class="btn-spinner hidden">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spinner"></div>
                        </div>
                    </button>
                </div>
            </div>
        `;

        // Add event listeners
        const viewBtn = div.querySelector('.view-btn');
        const approveBtn = div.querySelector('.approve-btn');
        const declineBtn = div.querySelector('.decline-btn');

        viewBtn.addEventListener('click', () => openModal(transaction));
        approveBtn.addEventListener('click', () => handlePaymentAction('approve', approveBtn, transaction.id, div));
        declineBtn.addEventListener('click', () => handlePaymentAction('decline', declineBtn, transaction.id, div));

        return div;
    }

    function generateGradientColors(initials) {
        const gradients = [
            'bg-gradient-to-r from-blue-500 to-purple-600',
            'bg-gradient-to-r from-green-500 to-teal-600',
            'bg-gradient-to-r from-purple-500 to-pink-600',
            'bg-gradient-to-r from-orange-500 to-red-600',
            'bg-gradient-to-r from-indigo-500 to-blue-600',
            'bg-gradient-to-r from-pink-500 to-rose-600',
            'bg-gradient-to-r from-teal-500 to-cyan-600',
            'bg-gradient-to-r from-yellow-500 to-orange-600'
        ];
        const index = initials.charCodeAt(0) % gradients.length;
        return gradients[index];
    }

    function openModal(transaction) {
        currentTransactionId = transaction.id;

        // Populate modal with transaction data
        document.getElementById('modalUserInitials').textContent = transaction.userId;
        document.getElementById('modalUserName').textContent = transaction.userName;
        document.getElementById('modalUserId').textContent = `User ID: ${transaction.userId}`;
        document.getElementById('modalOrderId').textContent = transaction.orderId;
        document.getElementById('modalPaymentMethod').textContent = transaction.paymentMethod;
        document.getElementById('modalAmount').textContent = transaction.amount;
        document.getElementById('modalDate').textContent = transaction.date;
        document.getElementById('modalTransactionId').textContent = transaction.transactionId;

        // Set payment icon
        const paymentIcon = document.getElementById('modalPaymentIcon');
        if (transaction.paymentMethod === 'Crypto') {
            paymentIcon.className = 'ri-btc-line text-orange-400';
        } else {
            paymentIcon.className = 'ri-bank-card-line text-blue-400';
        }

        // Set screenshot
        const screenshot = document.getElementById('modalScreenshot');
        if (transaction.screenshot) {
            screenshot.src = transaction.screenshot;
            screenshot.alt = 'Payment Screenshot';
        } else {
            screenshot.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+CiAgPHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzZiNzI4MCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIFNjcmVlbnNob3QgUHJvdmlkZWQ8L3RleHQ+Cjwvc3ZnPgo=';
            screenshot.alt = 'No Screenshot';
        }

        // Set user avatar gradient
        const userAvatar = document.getElementById('modalUserAvatar');
        const gradientColors = generateGradientColors(transaction.userId);
        userAvatar.className = `w-12 h-12 ${gradientColors} rounded-full flex items-center justify-center`;

        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
        currentTransactionId = null;
    }

    async function handlePaymentAction(action, button, transactionId, paymentCard = null) {
        const btnText = button.querySelector('.btn-text');
        const btnSpinner = button.querySelector('.btn-spinner');

        // Show loading state
        btnText.classList.add('hidden');
        btnSpinner.classList.remove('hidden');
        button.disabled = true;

        try {
            const response = await fetch('api/modal.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    transaction_id: transactionId,
                    action: action
                })
            });

            const data = await response.json();

            if (data.success) {
                const actionText = action === 'approve' ? 'approved' : 'declined';
                const toastType = action === 'approve' ? 'success' : 'error';
                const toastIcon = action === 'approve' ? '✅' : '❌';
                
                showToast(
                    `${toastIcon} Payment ${actionText} successfully`,
                    toastType
                );

                // Remove payment card with animation
                if (paymentCard) {
                    paymentCard.style.opacity = '0';
                    paymentCard.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        paymentCard.remove();
                        checkEmptyState();
                    }, 300);
                }

                // Close modal if open
                if (!modal.classList.contains('hidden')) {
                    closeModal();
                }

                // Refresh the payments list after a short delay
                setTimeout(loadPendingPayments, 1000);

            } else {
                throw new Error(data.message || 'Failed to process payment');
            }

        } catch (error) {
            console.error(`Error ${action}ing payment:`, error);
            showToast(`❌ Failed to ${action} payment: ${error.message}`, 'error');
            
            // Reset button state
            btnText.classList.remove('hidden');
            btnSpinner.classList.add('hidden');
            button.disabled = false;
        }
    }

    function showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        
        let toastClass = 'toast glass px-4 py-3 rounded-lg shadow-lg max-w-sm';
        if (type === 'success') {
            toastClass += ' toast-success';
        } else if (type === 'error') {
            toastClass += ' toast-error';
        }

        toast.className = toastClass;
        toast.textContent = message;
        toastContainer.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }, 5000);
    }

    function checkEmptyState() {
        const paymentCards = paymentsContainer.querySelectorAll('.payment-card');
        if (paymentCards.length === 0) {
            paymentsContainer.classList.add('hidden');
            emptyState.classList.remove('hidden');
        }
    }

    // Handle logout functionality
    const logoutBtn = document.getElementById('logout-btn');
    const logoutModal = document.getElementById('logout-modal');
    const cancelLogoutBtn = document.getElementById('cancel-logout');
    const confirmLogoutBtn = document.getElementById('confirm-logout');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function () {
            logoutModal.classList.add('active');
        });
    }

    if (cancelLogoutBtn) {
        cancelLogoutBtn.addEventListener('click', function () {
            logoutModal.classList.remove('active');
        });
    }

    if (confirmLogoutBtn) {
        confirmLogoutBtn.addEventListener('click', function () {
            window.location.href = '/admin/logout/';
        });
    }
});
            </script>
    </body>

</html>