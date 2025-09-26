<?php
require '../../config/db.php';
require '../../home/subscription/api/auth_check.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
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
        <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>" />
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
                    <div class="font-['Pacifico'] text-xl text-primary">Sales-Sp</div>
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
                                class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50">
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
                                class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-primary bg-blue-50">
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
                            <div
                                class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mr-4">
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
            <!-- Settings Tab -->
            <div id="settings-tab" class="tab-content p-4 md:p-6">
                <div class="mb-6">
                    <h1 class="text-2xl font-sem ibold text-gray-800">
                        Platform Settings
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Manage platform settings and configurations
                    </p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2 space-y-6">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-medium text-gray-800 mb-4">Branding</h2>
                            <div class="space-y-4">
                                <div>
                                    <label for="platform-name"
                                        class="block text-sm font-medium text-gray-700 mb-1">Platform Name</label>
                                    <input type="text" id="platform-name"
                                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                        value="Sales-Spy" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Platform Logo</label>
                                    <div class="flex items-center">
                                        <div
                                            class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center mr-4">
                                            <img id="platform-logo" src="https://res.cloudinary.com/dtrn8j0sz/image/upload/v1749075914/SS_s4jkfw.jpg"
                                                alt="Logo" />
                                        </div>
                                        <div>
                                            <button id="upload-logo-btn"
                                                class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50 mb-2"
                                                data-tooltip="Upload a new logo for the platform">
                                                Upload New Logo
                                            </button>
                                            <p class="text-xs text-gray-500">
                                                Recommended size: 512x512px. Max file size: 2MB.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="favicon"
                                        class="block text-sm font-medium text-gray-700 mb-1">Favicon</label>
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center mr-4">
                                            <img id="platform-favicon" src="https://res.cloudinary.com/dtrn8j0sz/image/upload/v1749075914/SS_s4jkfw.jpg"
                                                alt="Favicon" />
                                        </div>
                                        <div>
                                            <button id="upload-favicon-btn"
                                                class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50 mb-2"
                                                data-tooltip="Upload a new favicon for the platform">
                                                Upload New Favicon
                                            </button>
                                            <p class="text-xs text-gray-500">
                                                Recommended size: 32x32px. Max file size: 1MB.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-medium text-gray-800 mb-4">
                                Payment Settings
                            </h2>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            Enable Crypto-Only Payments
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            If enabled, users can only pay with cryptocurrency.
                                        </p>
                                    </div>
                                    <label class="custom-switch">
                                        <input type="checkbox" checked />
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            Allow Multiple Wallets Per User
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            If enabled, users can connect multiple wallets to their
                                            account.
                                        </p>
                                    </div>
                                    <label class="custom-switch">
                                        <input type="checkbox" checked />
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            Require Wallet Verification
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            If enabled, wallet addresses must be verified before use.
                                        </p>
                                    </div>
                                    <label class="custom-switch">
                                        <input type="checkbox" />
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Accepted Tokens</label>
                                    <div class="space-y-2">

                                        <div class="flex items-center">
                                            <label class="custom-checkbox">
                                                <input type="checkbox" checked />
                                                <span class="checkbox-mark"></span>
                                            </label>
                                            <div class="ml-2 flex items-center">
                                                <div class="w-5 h-5 flex items-center justify-center mr-2">
                                                    <i class="ri-coin-line"></i>
                                                </div>
                                                <span class="text-sm text-gray-600">USD Tether (USDT)</span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-medium text-gray-800 mb-4">
                                Email Notifications
                            </h2>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            Payment Success
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Send email when a payment is successful.
                                        </p>
                                    </div>
                                    <label class="custom-switch">
                                        <input type="checkbox" checked />
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            Payment Failed
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Send email when a payment fails.
                                        </p>
                                    </div>
                                    <label class="custom-switch">
                                        <input type="checkbox" checked />
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            Subscription Renewal
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Send email before subscription renewal.
                                        </p>
                                    </div>
                                    <label class="custom-switch">
                                        <input type="checkbox" checked />
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            Wallet Connection
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Send email when a new wallet is connected.
                                        </p>
                                    </div>
                                    <label class="custom-switch">
                                        <input type="checkbox" />
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-medium text-gray-800 mb-4">
                                System Settings
                            </h2>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            Maintenance Mode
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Put the platform in maintenance mode.
                                        </p>
                                    </div>
                                    <label class="custom-switch">
                                        <input type="checkbox" />
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            User Registration
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Allow new user registrations.
                                        </p>
                                    </div>
                                    <label class="custom-switch">
                                        <input type="checkbox" checked />
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                                <div>
                                    <label for="session-timeout"
                                        class="block text-sm font-medium text-gray-700 mb-1">Admin Session Timeout
                                        (minutes)</label>
                                    <input type="number" id="session-timeout"
                                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                        value="30" />
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-medium text-gray-800 mb-4">Security</h2>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            Two-Factor Authentication
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Require 2FA for admin login.
                                        </p>
                                    </div>
                                    <label class="custom-switch">
                                        <input type="checkbox" checked />
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">
                                            IP Restriction
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Restrict admin access to specific IPs.
                                        </p>
                                    </div>
                                    <label class="custom-switch">
                                        <input type="checkbox" />
                                        <span class="switch-slider"></span>
                                    </label>
                                </div>
                                <div>
                                    <button id="change-password-btn"
                                        class="w-full px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50 flex items-center justify-center"
                                        data-tooltip="Change your admin password">
                                        <i class="ri-lock-password-line mr-2"></i>
                                        Change Admin Password
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-medium text-gray-800 mb-4">
                                Admin Activity Log
                            </h2>
                            <div id="activity-list" class="space-y-3"></div>
                            <div class="mt-4">
                                <button id="view-log-btn"
                                    class="w-full px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50"
                                    data-tooltip="View the full admin activity log">
                                    View Full Log
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <div class="flex items-center space-x-3">
                        <button id="reset-defaults-btn"
                            class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50"
                            data-tooltip="Reset all settings to their default values">
                            Reset to Defaults
                        </button>
                        <button id="save-changes-btn"
                            class="px-4 py-2 bg-primary text-white rounded-button whitespace-nowrap"
                            data-tooltip="Save all changes made to settings">
                            Save All Changes
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Modals -->
        <div id="upload-logo-modal" class="modal">
            <div class="modal-content">
                <h3 class="text-lg font-medium mb-4">Upload New Logo</h3>
                <input type="file" id="logo-file" accept="image/*" class="mb-4" />
                <div class="flex justify-end space-x-2">
                    <button class="modal-close px-4 py-2 border rounded-button">Cancel</button>
                    <button id="confirm-upload-logo"
                        class="px-4 py-2 bg-primary text-white rounded-button">Upload</button>
                </div>
            </div>
        </div>
        <div id="upload-favicon-modal" class="modal">
            <div class="modal-content">
                <h3 class="text-lg font-medium mb-4">Upload New Favicon</h3>
                <input type="file" id="favicon-file" accept="image/*" class="mb-4" />
                <div class="flex justify-end space-x-2">
                    <button class="modal-close px-4 py-2 border rounded-button">Cancel</button>
                    <button id="confirm-upload-favicon"
                        class="px-4 py-2 bg-primary text-white rounded-button">Upload</button>
                </div>
            </div>
        </div>
        <div id="change-password-modal" class="modal">
            <div class="modal-content">
                <h3 class="text-lg font-medium mb-4">Change Admin Password</h3>
                <input type="password" id="new-password" placeholder="New Password"
                    class="mb-2 w-full px-4 py-2 border rounded-lg" />
                <input type="password" id="confirm-password" placeholder="Confirm Password"
                    class="mb-4 w-full px-4 py-2 border rounded-lg" />
                <div class="flex justify-end space-x-2">
                    <button class="modal-close px-4 py-2 border rounded-button">Cancel</button>
                    <button id="confirm-change-password"
                        class="px-4 py-2 bg-primary text-white rounded-button">Change</button>
                </div>
            </div>
        </div>
        <div id="view-log-modal" class="modal">
            <div class="modal-content max-w-4xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium">Admin Activity Log</h3>
                    <button class="modal-close text-gray-400 hover:text-gray-500">
                        <i class="ri-close-line ri-lg"></i>
                    </button>
                </div>
                <div id="activity-modal-list" class="max-h-96 overflow-y-auto mb-4 space-y-3"></div>
                <div class="flex justify-end">
                    <button class="modal-close px-4 py-2 border rounded-button">Close</button>
                </div>
            </div>
        </div>
        <div id="reset-defaults-modal" class="modal">
            <div class="modal-content">
                <h3 class="text-lg font-medium mb-4">Reset to Defaults</h3>
                <p class="mb-4">Are you sure you want to reset all settings to their default values?</p>
                <div class="flex justify-end space-x-2">
                    <button class="modal-close px-4 py-2 border rounded-button">Cancel</button>
                    <button id="confirm-reset-defaults"
                        class="px-4 py-2 bg-red-600 text-white rounded-button">Reset</button>
                </div>
            </div>
        </div>
        <div id="save-changes-modal" class="modal">
            <div class="modal-content">
                <h3 class="text-lg font-medium mb-4">Save All Changes</h3>
                <p class="mb-4">Are you sure you want to save all changes?</p>
                <div class="flex justify-end space-x-2">
                    <button class="modal-close px-4 py-2 border rounded-button">Cancel</button>
                    <button id="confirm-save-changes"
                        class="px-4 py-2 bg-primary text-white rounded-button">Save</button>
                </div>
            </div>
        </div>
        <!-- Toasts -->
        <div id="toast-success"
            class="fixed top-6 right-2 px-4 py-2 bg-green-600 text-white rounded-lg shadow-lg z-50 translate-x-full transition-transform duration-300">
            <span id="toast-message">Action successful!</span>
        </div>
        <script id="modal-script">
            document.addEventListener("DOMContentLoaded", function () {
                const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const apiBase = 'api/settings_api.php';
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
                        window.location.href = "/admin/logout/"; // Redirect to login page
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
                // Bind close buttons and overlay click to close modals
                modalCloseButtons.forEach((btn) => {
                    btn.addEventListener("click", function () {
                        const modal = btn.closest(".modal");
                        if (modal) closeModal(modal);
                    });
                });
                modals.forEach((modal) => {
                    modal.addEventListener("click", function (e) {
                        if (e.target === modal) {
                            closeModal(modal);
                        }
                    });
                });
                // Toast logic
                function showToast(message) {
                    const toast = document.getElementById("toast-success");
                    const toastMsg = document.getElementById("toast-message");
                    toastMsg.textContent = message;
                    toast.classList.remove("translate-x-full");
                    setTimeout(() => {
                        toast.classList.add("translate-x-full");
                    }, 2500);
                }

                // Tooltips
                document.querySelectorAll("[data-tooltip]").forEach((el) => {
                    el.addEventListener("mouseenter", function (e) {
                        tooltip.textContent = el.getAttribute("data-tooltip");
                        tooltip.style.opacity = "1";
                        const rect = el.getBoundingClientRect();
                        tooltip.style.top = rect.top - 30 + "px";
                        tooltip.style.left =
                            rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px";
                    });
                    el.addEventListener("mouseleave", function () {
                        tooltip.style.opacity = "0";
                    });
                });

                // Upload Logo
                document.getElementById("upload-logo-btn").addEventListener("click", function () {
                    openModal("upload-logo-modal");
                });
                document.getElementById("confirm-upload-logo").addEventListener("click", async function () {
                    const fileInput = document.getElementById('logo-file');
                    if (!fileInput.files.length) { showToast("Please choose a file"); return; }
                    const form = new FormData();
                    form.append('action', 'upload_logo');
                    form.append('csrf_token', CSRF_TOKEN);
                    form.append('file', fileInput.files[0]);
                    const res = await fetch(apiBase, { method: 'POST', body: form, credentials: 'same-origin' });
                    const data = await res.json();
                    if (data.success) { showToast("Logo uploaded successfully!"); } else { showToast(data.error || 'Upload failed'); }
                    closeModal(document.getElementById("upload-logo-modal"));
                });

                // Upload Favicon
                document.getElementById("upload-favicon-btn").addEventListener("click", function () {
                    openModal("upload-favicon-modal");
                });
                document.getElementById("confirm-upload-favicon").addEventListener("click", async function () {
                    const fileInput = document.getElementById('favicon-file');
                    if (!fileInput.files.length) { showToast("Please choose a file"); return; }
                    const form = new FormData();
                    form.append('action', 'upload_favicon');
                    form.append('csrf_token', CSRF_TOKEN);
                    form.append('file', fileInput.files[0]);
                    const res = await fetch(apiBase, { method: 'POST', body: form, credentials: 'same-origin' });
                    const data = await res.json();
                    if (data.success) { showToast("Favicon uploaded successfully!"); } else { showToast(data.error || 'Upload failed'); }
                    closeModal(document.getElementById("upload-favicon-modal"));
                });

                // Change Password
                document.getElementById("change-password-btn").addEventListener("click", function () {
                    openModal("change-password-modal");
                });
                document.getElementById("confirm-change-password").addEventListener("click", async function () {
                    const pwd = document.getElementById('new-password').value.trim();
                    const cpwd = document.getElementById('confirm-password').value.trim();
                    if (!pwd || pwd !== cpwd) { showToast('Passwords do not match'); return; }
                    const res = await fetch(apiBase, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify({ action: 'change_password', csrf_token: CSRF_TOKEN, newPassword: pwd })
                    });
                    const data = await res.json();
                    if (data.success) { showToast("Password changed successfully!"); } else { showToast(data.error || 'Failed to change password'); }
                    closeModal(document.getElementById("change-password-modal"));
                });

                // View Full Log
                document.getElementById("view-log-btn").addEventListener("click", function () {
                    openModal("view-log-modal");
                });

                // Reset to Defaults
                document.getElementById("reset-defaults-btn").addEventListener("click", function () {
                    openModal("reset-defaults-modal");
                });
                document.getElementById("confirm-reset-defaults").addEventListener("click", async function () {
                    const res = await fetch(apiBase, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify({ action: 'reset_defaults', csrf_token: CSRF_TOKEN })
                    });
                    const data = await res.json();
                    if (data.success) { loadSettings(); showToast("Settings reset to defaults!"); } else { showToast(data.error || 'Reset failed'); }
                    closeModal(document.getElementById("reset-defaults-modal"));
                });

                // Save All Changes
                document.getElementById("save-changes-btn").addEventListener("click", function () {
                    openModal("save-changes-modal");
                });
                document.getElementById("confirm-save-changes").addEventListener("click", async function () {
                    const payload = {
                        action: 'save_settings',
                        csrf_token: CSRF_TOKEN,
                        platform_name: document.getElementById('platform-name').value.trim(),
                        payment_crypto_only: document.querySelectorAll('input[type="checkbox"]')[0].checked ? 1 : 0,
                        payment_multiple_wallets: document.querySelectorAll('input[type="checkbox"]')[1].checked ? 1 : 0,
                        payment_require_verification: document.querySelectorAll('input[type="checkbox"]')[2].checked ? 1 : 0,
                        notify_payment_success: document.querySelectorAll('input[type="checkbox"]')[3].checked ? 1 : 0,
                        notify_payment_failed: document.querySelectorAll('input[type="checkbox"]')[4].checked ? 1 : 0,
                        notify_subscription_renewal: document.querySelectorAll('input[type="checkbox"]')[5].checked ? 1 : 0,
                        notify_wallet_connection: document.querySelectorAll('input[type="checkbox"]')[6].checked ? 1 : 0,
                        system_maintenance_mode: document.querySelectorAll('input[type="checkbox"]')[7].checked ? 1 : 0,
                        system_allow_registration: document.querySelectorAll('input[type="checkbox"]')[8].checked ? 1 : 0,
                        admin_session_timeout: parseInt(document.getElementById('session-timeout').value || '30', 10),
                        security_require_2fa: document.querySelectorAll('input[type="checkbox"]')[9].checked ? 1 : 0,
                        security_ip_restriction: document.querySelectorAll('input[type="checkbox"]')[10].checked ? 1 : 0,
                    };
                    const res = await fetch(apiBase, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (data.success) { showToast("All changes saved!"); } else { showToast(data.error || 'Save failed'); }
                    closeModal(document.getElementById("save-changes-modal"));
                });

                async function loadActivity() {
                    try {
                        const res = await fetch(`${apiBase}?action=get_activity`, { credentials: 'same-origin' });
                        const data = await res.json();
                        if (!data.success) return;
                        const list = document.getElementById('activity-list');
                        const modalList = document.getElementById('activity-modal-list');
                        if (list) list.innerHTML = '';
                        if (modalList) modalList.innerHTML = '';
                        (data.activity || []).forEach(item => {
                            const when = new Date(item.created_at).toLocaleString();
                            
                            // Create enhanced activity item
                            const line = document.createElement('div');
                            line.className = 'border-l-2 border-primary pl-3 mb-3';
                            
                            // Get action icon and color
                            const actionConfig = getActionConfig(item.action);
                            
                            line.innerHTML = `
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full ${actionConfig.bgColor} flex items-center justify-center">
                                        <i class="${actionConfig.icon} text-sm ${actionConfig.textColor}"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800">${item.description}</p>
                                        <p class="text-xs text-gray-500">by ${item.admin_name} • ${when}</p>
                                        ${item.additional_info && item.additional_info.length > 0 ? `
                                            <div class="mt-1 space-y-1">
                                                ${item.additional_info.slice(0, 3).map(info => `
                                                    <span class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded mr-1">${info}</span>
                                                `).join('')}
                                                ${item.additional_info.length > 3 ? `<span class="text-xs text-gray-400">+${item.additional_info.length - 3} more</span>` : ''}
                                            </div>
                                        ` : ''}
                                        ${item.browser ? `<p class="text-xs text-gray-400 mt-1">${item.browser}</p>` : ''}
                                    </div>
                                </div>
                            `;
                            
                            if (list && list.childElementCount < 3) list.appendChild(line.cloneNode(true));
                            if (modalList) modalList.appendChild(line);
                        });
                    } catch (e) { /* silent */ }
                }
                
                function getActionConfig(action) {
                    const configs = {
                        'subscription_created': { icon: 'ri-add-circle-line', bgColor: 'bg-green-100', textColor: 'text-green-600' },
                        'subscription_paused': { icon: 'ri-pause-circle-line', bgColor: 'bg-yellow-100', textColor: 'text-yellow-600' },
                        'subscription_resumed': { icon: 'ri-play-circle-line', bgColor: 'bg-green-100', textColor: 'text-green-600' },
                        'subscription_cancelled': { icon: 'ri-close-circle-line', bgColor: 'bg-red-100', textColor: 'text-red-600' },
                        'subscription_plan_changed': { icon: 'ri-exchange-line', bgColor: 'bg-blue-100', textColor: 'text-blue-600' },
                        'transaction_approved': { icon: 'ri-check-circle-line', bgColor: 'bg-green-100', textColor: 'text-green-600' },
                        'transaction_declined': { icon: 'ri-close-circle-line', bgColor: 'bg-red-100', textColor: 'text-red-600' },
                        'wallet_updated': { icon: 'ri-wallet-line', bgColor: 'bg-purple-100', textColor: 'text-purple-600' },
                        'user_created': { icon: 'ri-user-add-line', bgColor: 'bg-blue-100', textColor: 'text-blue-600' },
                        'user_suspended': { icon: 'ri-user-unfollow-line', bgColor: 'bg-red-100', textColor: 'text-red-600' },
                        'user_activated': { icon: 'ri-user-follow-line', bgColor: 'bg-green-100', textColor: 'text-green-600' },
                        'admin_login': { icon: 'ri-login-circle-line', bgColor: 'bg-blue-100', textColor: 'text-blue-600' },
                        'admin_logout': { icon: 'ri-logout-circle-line', bgColor: 'bg-gray-100', textColor: 'text-gray-600' },
                        'password_changed': { icon: 'ri-lock-password-line', bgColor: 'bg-orange-100', textColor: 'text-orange-600' },
                        'settings_updated': { icon: 'ri-settings-3-line', bgColor: 'bg-indigo-100', textColor: 'text-indigo-600' }
                    };
                    return configs[action] || { icon: 'ri-information-line', bgColor: 'bg-gray-100', textColor: 'text-gray-600' };
                }

                async function loadSettings() {
                    try {
                        const res = await fetch(`${apiBase}?action=get_settings`, { credentials: 'same-origin' });
                        const data = await res.json();
                        if (!data.success) return;
                        const s = data.settings;
                        document.getElementById('platform-name').value = s.platform_name || 'Sales-Spy';
                        if (s.logo_path) {
                            const logoImg = document.getElementById('platform-logo');
                            if (logoImg) logoImg.src = s.logo_path;
                        }
                        if (s.favicon_path) {
                            const favImg = document.getElementById('platform-favicon');
                            if (favImg) favImg.src = s.favicon_path;
                        }
                        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
                        const values = [
                            s.payment_crypto_only,
                            s.payment_multiple_wallets,
                            s.payment_require_verification,
                            s.notify_payment_success,
                            s.notify_payment_failed,
                            s.notify_subscription_renewal,
                            s.notify_wallet_connection,
                            s.system_maintenance_mode,
                            s.system_allow_registration,
                            s.security_require_2fa,
                            s.security_ip_restriction,
                        ];
                        checkboxes.forEach((cb, idx) => { if (typeof values[idx] !== 'undefined') cb.checked = !!Number(values[idx]); });
                        document.getElementById('session-timeout').value = s.admin_session_timeout || 30;
                        loadActivity();
                    } catch (e) { /* silent */ }
                }

                loadSettings();
            });
        </script>
    </body>

</html>