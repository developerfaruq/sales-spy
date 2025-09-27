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
          <button
            id="sidebar-toggle"
            class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-primary mr-2"
          >
            <i class="ri-menu-line ri-lg"></i>
          </button>
          <div class="font-['Pacifico'] text-xl text-primary"><img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Platform Logo" width="150"></div>
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
                <span class="text-sm font-medium"><img src="<?= $avatarUrl ?>" alt="ss"></span>
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
                class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-primary hover:bg-blue-50"
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
                href="#"
                class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-primary bg-blue-50"
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
      <div id="users-tab" class="tab-content p-4 md:p-6">
      <div class="mb-6 relative">
          <h1 class="text-2xl font-semibold text-gray-800">User Plans</h1>
          <p class="text-sm text-gray-500 mt-1">
            Manage all plan pricing and features
          </p>
        </div>
        <!-- User Plans Management Section -->
        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-8 relative">
          <!-- Edit Icon Button -->
          
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
            <div class="flex items-center gap-2">
              <h2 class="text-lg font-semibold text-gray-800 mr-2">Plans Management</h2>
              <button
                type="button"
                id="edit-plans-btn"
                class="inline-flex items-center justify-center text-gray-400 hover:text-primary transition-colors rounded-full p-2 border border-transparent hover:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                title="Edit Plan cards"
                style="height: 36px; width: 36px;"
              >
                <i class="ri-edit-2-line ri-lg"></i>
              </button>
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="bg-primary text-white px-4 py-2 rounded-button hover:bg-blue-700 flex items-center gap-2 shadow-sm transition disabled:opacity-60"
                id="add-new-plan-btn"
                disabled
                tabindex="-1"
              >
                <i class="ri-add-line"></i> Add New Plan
              </button>
              <button
                type="button"
                class="bg-primary text-white px-4 py-2 rounded-button hover:bg-blue-700 flex items-center gap-2 shadow-sm transition disabled:opacity-60"
                id="add-tag-btn"
                disabled
                tabindex="-1"
              >
                <i class="ri-price-tag-3-line"></i> Add Tag
              </button>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="plans-container">
            <!-- Basic Plan Card -->
            <div class="glassmorphism bg-white rounded-lg border border-gray-200 p-6 relative plan-card" id="plan-basic" data-plan="basic">
              <div class="flex flex-wrap gap-2 absolute -top-3 left-4" id="basic-tags-container"></div>
              <!-- Editable Plan Name -->
              <input
          type="text"
          class="w-full text-xl font-semibold text-gray-900 mb-2 px-2 py-1 rounded border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20"
          value="Basic"
          id="basic-name"
          disabled
              />
              <div class="mb-4 flex items-baseline">
          <span class="text-3xl font-bold text-primary">$20</span>
          <span class="text-gray-500 ml-1">/mo</span>
              </div>
              <ul class="space-y-2 mb-6 text-sm text-gray-600" id="basic-features">
          <li class="flex items-center gap-2">
            <input
              type="text"
              class="w-full flex-1 px-2 py-1 rounded border border-gray-300"
              value="Up to 2,000 credits/month"
              disabled
            />
            <button
              type="button"
              class="text-red-500 hover:text-red-700 remove-feature-btn"
              disabled
              tabindex="-1"
            >
              <i class="ri-delete-bin-line"></i>
            </button>
          </li>
          <li class="flex items-center gap-2">
            <input
              type="text"
              class="w-full flex-1 px-2 py-1 rounded border border-gray-300"
              value="Email support"
              disabled
            />
            <button
              type="button"
              class="text-red-500 hover:text-red-700 remove-feature-btn"
              disabled
              tabindex="-1"
            >
              <i class="ri-delete-bin-line"></i>
            </button>
          </li>
          <li class="flex items-center gap-2">
            <input
              type="text"
              class="w-full flex-1 px-2 py-1 rounded border border-gray-300"
              value="Access to dashboard"
              disabled
            />
            <button
              type="button"
              class="text-red-500 hover:text-red-700 remove-feature-btn"
              disabled
              tabindex="-1"
            >
              <i class="ri-delete-bin-line"></i>
            </button>
          </li>
          <li class="flex items-center gap-2">
            <input
              type="text"
              class="w-full flex-1 px-2 py-1 rounded border border-gray-300"
              value="Single website integration"
              disabled
            />
            <button
              type="button"
              class="text-red-500 hover:text-red-700 remove-feature-btn"
              disabled
              tabindex="-1"
            >
              <i class="ri-delete-bin-line"></i>
            </button>
          </li>
              </ul>
              <button
          type="button"
          class="text-primary text-xs font-medium mb-4 add-feature-btn"
          data-target="basic-features"
          disabled
          tabindex="-1"
              >
          <i class="ri-add-line"></i> Add Feature
              </button>
              <div class="flex flex-col gap-2">
          <label class="block text-xs text-gray-500 mb-1">Credits</label>
          <input
            type="number"
            min="0"
            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm"
            value="2000"
            id="basic-credits"
            disabled
          />
          <label class="block text-xs text-gray-500 mb-1"
            >Monthly Price (USDT)</label
          >
          <input
            type="number"
            min="0"
            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm"
            value="20"
            id="basic-price"
            disabled
          />
              </div>
              <button
          class="mt-4 w-full py-2 bg-primary text-white rounded-button hover:bg-blue-700 transition-colors save-plan-btn"
          data-plan="basic"
          disabled
          tabindex="-1"
              >
          Save Basic Plan
              </button>
            </div>
            <!-- Pro Plan Card -->
            <div class="glassmorphism bg-white rounded-lg border border-gray-200 p-6 relative flex flex-col plan-card" id="plan-pro" data-plan="pro">
              <div class="flex flex-wrap gap-2 absolute -top-3 right-4" id="pro-tags-container"></div>
              <input
          type="text"
          class="w-full text-xl font-semibold text-gray-900 mb-2 px-2 py-1 rounded border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20"
          value="Pro"
          id="pro-name"
          disabled
              />
              <div class="mb-4 flex items-baseline">
          <span class="text-3xl font-bold text-primary">$50</span>
          <span class="text-gray-500 ml-1">/mo</span>
              </div>
              <ul class="space-y-2 mb-6 text-sm text-gray-600" id="pro-features">
          <li class="flex items-center gap-2">
            <input
              type="text"
              class="w-full flex-1 px-2 py-1 rounded border border-gray-300"
              value="Up to 10,000 credits/month"
              disabled
            />
            <button
              type="button"
              class="text-red-500 hover:text-red-700 remove-feature-btn"
              disabled
              tabindex="-1"
            >
              <i class="ri-delete-bin-line"></i>
            </button>
          </li>
          <li class="flex items-center gap-2">
            <input
              type="text"
              class="w-full flex-1 px-2 py-1 rounded border border-gray-300"
              value="Priority email support"
              disabled
            />
            <button
              type="button"
              class="text-red-500 hover:text-red-700 remove-feature-btn"
              disabled
              tabindex="-1"
            >
              <i class="ri-delete-bin-line"></i>
            </button>
          </li>
          <li class="flex items-center gap-2">
            <input
              type="text"
              class="w-full flex-1 px-2 py-1 rounded border border-gray-300"
              value="Multiple website integrations"
              disabled
            />
            <button
              type="button"
              class="text-red-500 hover:text-red-700 remove-feature-btn"
              disabled
              tabindex="-1"
            >
              <i class="ri-delete-bin-line"></i>
            </button>
          </li>
          <li class="flex items-center gap-2">
            <input
              type="text"
              class="w-full flex-1 px-2 py-1 rounded border border-gray-300"
              value="Advanced analytics"
              disabled
            />
            <button
              type="button"
              class="text-red-500 hover:text-red-700 remove-feature-btn"
              disabled
              tabindex="-1"
            >
              <i class="ri-delete-bin-line"></i>
            </button>
          </li>
              </ul>
              <button
          type="button"
          class="text-primary text-xs font-medium mb-4 add-feature-btn"
          data-target="pro-features"
          disabled
          tabindex="-1"
              >
          <i class="ri-add-line"></i> Add Feature
              </button>
              <div class="flex flex-col gap-2">
          <label class="block text-xs text-gray-500 mb-1">Credits</label>
          <input
            type="number"
            min="0"
            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm"
            value="10000"
            id="pro-credits"
            disabled
          />
          <label class="block text-xs text-gray-500 mb-1"
            >Monthly Price (USDT)</label
          >
          <input
            type="number"
            min="0"
            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm"
            value="50"
            id="pro-price"
            disabled
          />
              </div>
              <button
          class="mt-4 w-full py-2 bg-primary text-white rounded-button hover:bg-blue-700 transition-colors save-plan-btn"
          data-plan="pro"
          disabled
          tabindex="-1"
              >
          Save Pro Plan
              </button>
            </div>
            <!-- Enterprise Plan Card -->
            <div class="glassmorphism bg-white rounded-lg border border-gray-200 p-6 flex flex-col relative plan-card" id="plan-enterprise" data-plan="enterprise">
              <div class="flex flex-wrap gap-2 absolute -top-3 left-4" id="enterprise-tags-container"></div>
              <input
          type="text"
          class="w-full text-xl font-semibold text-gray-900 mb-2 px-2 py-1 rounded border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20"
          value="Enterprise"
          id="enterprise-name"
          disabled
              />
              <div class="mb-4 flex items-baseline">
          <span class="text-3xl font-bold text-primary">Custom</span>
          <span class="text-gray-500 ml-1"></span>
              </div>
              <ul class="space-y-2 mb-6 text-sm text-gray-600" id="enterprise-features">
          <li class="flex items-center gap-2">
            <input
              type="text"
              class="w-full flex-1 px-2 py-1 rounded border border-gray-300"
              value="Unlimited credits"
              disabled
            />
            <button
              type="button"
              class="text-red-500 hover:text-red-700 remove-feature-btn"
              disabled
              tabindex="-1"
            >
              <i class="ri-delete-bin-line"></i>
            </button>
          </li>
          <li class="flex items-center gap-2">
            <input
              type="text"
              class="w-full flex-1 px-2 py-1 rounded border border-gray-300"
              value="Dedicated support"
              disabled
            />
            <button
              type="button"
              class="text-red-500 hover:text-red-700 remove-feature-btn"
              disabled
              tabindex="-1"
            >
              <i class="ri-delete-bin-line"></i>
            </button>
          </li>
          <li class="flex items-center gap-2">
            <input
              type="text"
              class="w-full flex-1 px-2 py-1 rounded border border-gray-300"
              value="Custom integrations"
              disabled
            />
            <button
              type="button"
              class="text-red-500 hover:text-red-700 remove-feature-btn"
              disabled
              tabindex="-1"
            >
              <i class="ri-delete-bin-line"></i>
            </button>
          </li>
          <li class="flex items-center gap-2">
            <input
              type="text"
              class="w-full flex-1 px-2 py-1 rounded border border-gray-300"
              value="Team management"
              disabled
            />
            <button
              type="button"
              class="text-red-500 hover:text-red-700 remove-feature-btn"
              disabled
              tabindex="-1"
            >
              <i class="ri-delete-bin-line"></i>
            </button>
          </li>
              </ul>
              <button
          type="button"
          class="text-primary text-xs font-medium mb-4 add-feature-btn"
          data-target="enterprise-features"
          disabled
          tabindex="-1"
              >
          <i class="ri-add-line"></i> Add Feature
              </button>
              <div class="flex flex-col gap-2">
          <label class="block text-xs text-gray-500 mb-1">Credits</label>
          <input
            type="number"
            min="0"
            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm"
            placeholder="Enter credits"
            id="enterprise-credits"
            disabled
          />
          <label class="block text-xs text-gray-500 mb-1"
            >Monthly Price (USDT)</label
          >
          <input
            type="number"
            min="0"
            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm"
            placeholder="Custom price"
            id="enterprise-price"
            disabled
          />
              </div>
              <button
          class="mt-4 w-full py-2 bg-primary text-white rounded-button hover:bg-blue-700 transition-colors save-plan-btn"
          data-plan="enterprise"
          disabled
          tabindex="-1"
              >
          Save Enterprise Plan
              </button>
            </div>
          </div>
            <!-- Save All Changes Button -->
            <div class="flex justify-end mt-8"></div>
            <button
              id="save-all-changes-btn"
              class="bg-green-600 text-white px-6 py-3 rounded-button hover:bg-green-700 flex items-center gap-2 font-semibold shadow transition"
              style="display:none"
            >
              <i class="ri-save-3-line"></i> Save All Changes
            </button>
            </div>
          </div>

          <!-- Save All Changes Confirmation Modal -->
          <div id="save-all-confirm-modal" class="modal">
            <div class="modal-content max-w-lg">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-4">
              <i class="ri-question-line ri-lg"></i>
              </div>
              <h3 class="text-lg font-medium text-gray-800">
              Confirm All Changes
              </h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              Please review the following changes before saving. This will update all plans for all users.
            </p>
            <ul id="all-changes-list" class="mb-6 text-sm text-gray-700 list-disc pl-6 max-h-60 overflow-y-auto"></ul>
            <div class="flex items-center justify-end space-x-3">
              <button
              id="cancel-save-all-btn"
              class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50"
              >
              Cancel
              </button>
              <button
              id="confirm-save-all-btn"
              class="px-4 py-2 bg-green-600 text-white rounded-button whitespace-nowrap hover:bg-green-700"
              >
              Yes, Save All
              </button>
            </div>
            </div>
          </div>
          <!-- Save All Success Modal -->
          <div id="save-all-success-modal" class="modal">
            <div class="modal-content max-w-sm flex flex-col items-center">
            <div class="w-16 h-16 rounded-full bg-green-100 text-green-600 flex items-center justify-center mb-4">
              <i class="ri-checkbox-circle-line ri-2x"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">
              All Changes Saved!
            </h3>
            <p class="text-sm text-gray-600 mb-6 text-center">
              All your plan changes have been successfully saved.
            </p>
            <button
              id="save-all-success-close-btn"
              class="px-4 py-2 bg-primary text-white rounded-button whitespace-nowrap hover:bg-blue-700"
            >
              Close
            </button>
            </div>
          </div>
          <script>
          // --- Save All Changes Logic ---
          // Track changes in a global array
         // Global variables
let allPlanChanges = [];
let editMode = false;
let plans = {};

// API configuration
const API_BASE_URL = 'api/plans_api.php';

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    loadPlans();
    initializeEventListeners();
    initializeChangeTracking();
});

// Load plans from database
async function loadPlans() {
    try {
        const response = await fetch(API_BASE_URL);
        const result = await response.json();
        
        if (result.success) {
            plans = {};
            result.data.forEach(plan => {
                plans[plan.id] = plan;
            });
            renderPlans(result.data);
        } else {
            showErrorToast('Failed to load plans: ' + result.message);
        }
    } catch (error) {
        console.error('Error loading plans:', error);
        showErrorToast('Failed to load plans from server');
    }
}

// Render plans in the UI
function renderPlans(plansData) {
    const container = document.getElementById('plans-container');
    container.innerHTML = '';
    
    plansData.forEach(plan => {
        const planCard = createPlanCard(plan);
        container.appendChild(planCard);
    });
    
    // Re-attach event listeners after rendering
    attachPlanEventListeners();
}

// Create a plan card element
function createPlanCard(plan) {
    const planCard = document.createElement('div');
    planCard.className = 'glassmorphism bg-white rounded-lg border border-gray-200 p-6 relative plan-card';
    planCard.id = `plan-${plan.id}`;
    planCard.setAttribute('data-plan', plan.id);
    
    const features = plan.features || [];
    const displayPrice = plan.monthly_price == 0 ? 'Custom' : `$${plan.monthly_price}`;
    
    planCard.innerHTML = `
        <div class="flex flex-wrap gap-2 absolute -top-3 left-4" id="plan-${plan.id}-tags-container">
            ${plan.is_popular ? '<span class="text-xs font-medium px-3 py-1 rounded-full flex items-center tag-item" style="background:#22c55e;color:#fff">Popular</span>' : ''}
        </div>
        
        <input
            type="text"
            class="w-full text-xl font-semibold text-gray-900 mb-2 px-2 py-1 rounded border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 plan-name-input"
            value="${escapeHtml(plan.plan_name)}"
            disabled
        />
        
        <div class="mb-4 flex items-baseline">
            <span class="text-3xl font-bold text-primary plan-price-display">${displayPrice}</span>
            <span class="text-gray-500 ml-1">${plan.monthly_price > 0 ? '/mo' : ''}</span>
        </div>
        
        <ul class="space-y-2 mb-6 text-sm text-gray-600 plan-features-list">
            ${features.map(feature => `
                <li class="flex items-center gap-2">
                    <input
                        type="text"
                        class="w-full flex-1 px-2 py-1 rounded border border-gray-300 feature-input"
                        value="${escapeHtml(feature)}"
                        disabled
                    />
                    <button
                        type="button"
                        class="text-red-500 hover:text-red-700 remove-feature-btn"
                        disabled
                        tabindex="-1"
                    >
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </li>
            `).join('')}
        </ul>
        
        <button
            type="button"
            class="text-primary text-xs font-medium mb-4 add-feature-btn"
            disabled
            tabindex="-1"
        >
            <i class="ri-add-line"></i> Add Feature
        </button>
        
        <div class="flex flex-col gap-2">
            <label class="block text-xs text-gray-500 mb-1">Credits</label>
            <input
                type="number"
                min="0"
                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm credits-input"
                value="${plan.credits_per_month || 0}"
                disabled
            />
            <label class="block text-xs text-gray-500 mb-1">Monthly Price (USDT)</label>
            <input
                type="number"
                min="0"
                step="0.01"
                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm price-input"
                value="${plan.monthly_price || 0}"
                ${plan.monthly_price == 0 ? 'placeholder="Custom price"' : ''}
                disabled
            />
        </div>
        
        <button
            class="mt-4 w-full py-2 bg-primary text-white rounded-button hover:bg-blue-700 transition-colors save-plan-btn"
            data-plan="${plan.id}"
            disabled
            tabindex="-1"
        >
            Save ${escapeHtml(plan.plan_name)} Plan
        </button>
    `;
    
    return planCard;
}

// Initialize all event listeners
function initializeEventListeners() {
    // Edit mode toggle
    const editBtn = document.getElementById('edit-plans-btn');
    editBtn.addEventListener('click', toggleEditMode);
    
    // Add new plan button
    const addPlanBtn = document.getElementById('add-new-plan-btn');
    addPlanBtn.addEventListener('click', openAddPlanModal);
    
    // Add tag button
    const addTagBtn = document.getElementById('add-tag-btn');
    addTagBtn.addEventListener('click', openAddTagModal);
    
    // Save all changes button
    const saveAllBtn = document.getElementById('save-all-changes-btn');
    saveAllBtn.addEventListener('click', openSaveAllConfirmModal);
    
    // Modal event listeners
    initializeModalEventListeners();
    
    // Tag modal event listeners
    initializeTagModalEventListeners();
    
    // Add plan modal event listeners
    initializeAddPlanModalEventListeners();
}

// Initialize comprehensive change tracking
function initializeChangeTracking() {
    // Listen for changes on all plan-card inputs
    document.addEventListener('input', function(e) {
        if (!editMode) return;
        
        const input = e.target;
        if (input.closest('.plan-card')) {
            trackPlanChanges({ target: input });
        }
    });
    
    document.addEventListener('change', function(e) {
        if (!editMode) return;
        
        const input = e.target;
        if (input.closest('.plan-card')) {
            trackPlanChanges({ target: input });
        }
    });
    
    // Features add/remove tracking
    document.addEventListener('click', function(e) {
        if (!editMode) return;
        
        if (e.target.closest('.remove-feature-btn')) {
            const li = e.target.closest('li');
            if (li) {
                const card = li.closest('.plan-card');
                const planId = card.getAttribute('data-plan');
                const featureInput = li.querySelector('input[type="text"]');
                if (featureInput && featureInput.value.trim()) {
                    addPlanChange({ 
                        id: planId + '-feature-removed-' + featureInput.value + '-' + Date.now(), 
                        desc: `Feature <b>${featureInput.value}</b> removed` 
                    });
                }
            }
        }
        
        // Tag removal tracking
        if (e.target.closest('.remove-tag-btn')) {
            const card = e.target.closest('.plan-card');
            if (card) {
                const planId = card.getAttribute('data-plan');
                addPlanChange({ 
                    id: planId + '-tag-removed-' + Date.now(), 
                    desc: `Tag removed` 
                });
            }
        }
    });
    
    // Add feature tracking
    document.addEventListener('click', function(e) {
        if (!editMode) return;
        
        if (e.target.closest('.add-feature-btn')) {
            const card = e.target.closest('.plan-card');
            if (card) {
                const planId = card.getAttribute('data-plan');
                addPlanChange({ 
                    id: planId + '-feature-added-' + Date.now(), 
                    desc: `Feature added` 
                });
            }
        }
    });
}

// Attach event listeners to plan cards
function attachPlanEventListeners() {
    // Save plan buttons
    document.querySelectorAll('.save-plan-btn').forEach(btn => {
        btn.addEventListener('click', handleSavePlan);
    });
    
    // Add feature buttons
    document.querySelectorAll('.add-feature-btn').forEach(btn => {
        btn.addEventListener('click', handleAddFeature);
    });
    
    // Remove feature buttons (event delegation)
    document.querySelectorAll('.plan-features-list').forEach(list => {
        list.addEventListener('click', handleRemoveFeature);
    });
}

// Toggle edit mode
function toggleEditMode() {
    editMode = !editMode;
    const editBtn = document.getElementById('edit-plans-btn');
    const addPlanBtn = document.getElementById('add-new-plan-btn');
    const addTagBtn = document.getElementById('add-tag-btn');
    
    // Toggle icon
    editBtn.innerHTML = editMode 
        ? '<i class="ri-check-line ri-lg"></i>' 
        : '<i class="ri-edit-2-line ri-lg"></i>';
    editBtn.title = editMode ? "Done Editing" : "Edit Plan cards";
    
    // Enable/disable inputs and buttons
    document.querySelectorAll('.plan-card input, .plan-card button').forEach(el => {
        if (el === editBtn) return;
        
        el.disabled = !editMode;
        el.tabIndex = editMode ? 0 : -1;
    });
    
    addPlanBtn.disabled = !editMode;
    addPlanBtn.tabIndex = editMode ? 0 : -1;
    addTagBtn.disabled = !editMode;
    addTagBtn.tabIndex = editMode ? 0 : -1;
    
    updateSaveAllBtn();
}

// Track changes for save all functionality
function trackPlanChanges(e) {
    if (!editMode) return;
    
    const input = e.target;
    const card = input.closest('.plan-card');
    if (!card) return;
    
    const planId = card.getAttribute('data-plan');
    let changeDesc = '';
    
    if (input.classList.contains('plan-name-input')) {
        changeDesc = `Plan name changed to <b>${input.value}</b>`;
    } else if (input.classList.contains('credits-input')) {
        changeDesc = `Credits set to <b>${input.value}</b>`;
    } else if (input.classList.contains('price-input')) {
        changeDesc = `Monthly price set to <b>$${input.value}</b>`;
    } else if (input.classList.contains('feature-input')) {
        changeDesc = `Feature changed to <b>${input.value}</b>`;
    }
    
    if (changeDesc) {
        addPlanChange({
            id: `${planId}-${input.className.split(' ')[0]}-${input.value}-${Date.now()}`,
            desc: changeDesc
        });
    }
}

// Helper functions for change tracking
function addPlanChange(change) {
    if (!allPlanChanges.some(c => c.id === change.id)) {
        allPlanChanges.push(change);
        updateSaveAllBtn();
    }
}

function removePlanChange(change) {
    allPlanChanges = allPlanChanges.filter(c => !(c.id === change.id && c.desc === change.desc));
    updateSaveAllBtn();
}

function clearAllPlanChanges() {
    allPlanChanges = [];
    updateSaveAllBtn();
}

function updateSaveAllBtn() {
    const btn = document.getElementById('save-all-changes-btn');
    if (editMode) {
        btn.style.display = 'flex';
    } else {
        btn.style.display = allPlanChanges.length > 0 ? 'flex' : 'none';
    }
}

// Handle save individual plan
async function handleSavePlan(e) {
    const button = e.target;
    const planId = button.getAttribute('data-plan');
    const card = document.getElementById(`plan-${planId}`);
    
    // Collect plan data
    const planData = collectPlanData(card, planId);
    
    // Show confirmation modal
    document.getElementById('plan-confirm-title').textContent = 'Confirm Changes';
    document.getElementById('plan-confirm-desc').textContent = 
        `Are you sure you want to save changes to the ${planData.plan_name} plan? This will update the plan for all users.`;
    
    // Store plan data in modal for confirmation
    document.getElementById('plan-confirm-modal').setAttribute('data-plan-data', JSON.stringify(planData));
    
    openModal('plan-confirm-modal');
}

// Collect plan data from form
function collectPlanData(card, planId) {
    const nameInput = card.querySelector('.plan-name-input');
    const creditsInput = card.querySelector('.credits-input');
    const priceInput = card.querySelector('.price-input');
    const featureInputs = card.querySelectorAll('.feature-input');
    
    const features = Array.from(featureInputs)
        .map(input => input.value.trim())
        .filter(feature => feature);
    
    return {
        id: planId,
        plan_name: nameInput.value.trim(),
        credits_per_month: parseInt(creditsInput.value) || 0,
        monthly_price: parseFloat(priceInput.value) || 0,
        features: features
    };
}

// Save plan to database
async function savePlanToDatabase(planData) {
    try {
        const response = await fetch(API_BASE_URL, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(planData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Update local plans data
            plans[planData.id] = { ...plans[planData.id], ...planData };
            
            // Update price display
            const card = document.getElementById(`plan-${planData.id}`);
            const priceDisplay = card.querySelector('.plan-price-display');
            priceDisplay.textContent = planData.monthly_price == 0 ? 'Custom' : `$${planData.monthly_price}`;
            
            showSuccessToast('Plan updated successfully!');
            return true;
        } else {
            showErrorToast('Failed to update plan: ' + result.message);
            return false;
        }
    } catch (error) {
        console.error('Error saving plan:', error);
        showErrorToast('Failed to save plan to server');
        return false;
    }
}

// Handle add feature
function handleAddFeature(e) {
    const button = e.target;
    const card = button.closest('.plan-card');
    const featuresList = card.querySelector('.plan-features-list');
    
    const li = document.createElement('li');
    li.className = 'flex items-center gap-2';
    li.innerHTML = `
        <input
            type="text"
            class="w-full flex-1 px-2 py-1 rounded border border-gray-300 feature-input"
            placeholder="New feature"
            ${editMode ? '' : 'disabled'}
        />
        <button
            type="button"
            class="text-red-500 hover:text-red-700 remove-feature-btn"
            ${editMode ? '' : 'disabled tabindex="-1"'}
        >
            <i class="ri-delete-bin-line"></i>
        </button>
    `;
    
    featuresList.appendChild(li);
    
    // Focus on new input
    const newInput = li.querySelector('.feature-input');
    newInput.focus();
    
    // Track change
    if (editMode) {
        const planId = card.getAttribute('data-plan');
        addPlanChange({
            id: `${planId}-feature-added-${Date.now()}`,
            desc: 'Feature added'
        });
    }
}

// Handle remove feature (event delegation)
function handleRemoveFeature(e) {
    if (e.target.closest('.remove-feature-btn')) {
        const li = e.target.closest('li');
        const card = e.target.closest('.plan-card');
        const featureInput = li.querySelector('.feature-input');
        
        if (featureInput && editMode && featureInput.value.trim()) {
            const planId = card.getAttribute('data-plan');
            addPlanChange({
                id: `${planId}-feature-removed-${featureInput.value}-${Date.now()}`,
                desc: `Feature <b>${featureInput.value}</b> removed`
            });
        }
        
        li.remove();
    }
}

// Modal event listeners
function initializeModalEventListeners() {
    // Plan confirmation modal
    document.getElementById('plan-cancel-btn').addEventListener('click', () => {
        closeModal(document.getElementById('plan-confirm-modal'));
    });
    
    document.getElementById('plan-confirm-btn').addEventListener('click', async () => {
        const modal = document.getElementById('plan-confirm-modal');
        const planData = JSON.parse(modal.getAttribute('data-plan-data'));
        
        closeModal(modal);
        
        const success = await savePlanToDatabase(planData);
        if (success) {
            document.getElementById('plan-success-title').textContent = `${planData.plan_name} Plan Updated!`;
            document.getElementById('plan-success-desc').textContent = 
                `The ${planData.plan_name} plan information has been successfully updated.`;
            
            openModal('plan-success-modal');
            setTimeout(() => {
                closeModal(document.getElementById('plan-success-modal'));
            }, 2000);
        }
    });
    
    document.getElementById('plan-success-close-btn').addEventListener('click', () => {
        closeModal(document.getElementById('plan-success-modal'));
    });
    
    // Save all confirmation modal
    document.getElementById('cancel-save-all-btn').addEventListener('click', () => {
        closeModal(document.getElementById('save-all-confirm-modal'));
    });
    
    document.getElementById('confirm-save-all-btn').addEventListener('click', saveAllPlans);
    
    document.getElementById('save-all-success-close-btn').addEventListener('click', () => {
        closeModal(document.getElementById('save-all-success-modal'));
    });
}

// Save all plans functionality
function openSaveAllConfirmModal() {
    const ul = document.getElementById('all-changes-list');
    ul.innerHTML = '';
    
    if (allPlanChanges.length === 0) {
        ul.innerHTML = '<li>No changes detected.</li>';
    } else {
        allPlanChanges.forEach(change => {
            const li = document.createElement('li');
            li.innerHTML = change.desc;
            ul.appendChild(li);
        });
    }
    
    openModal('save-all-confirm-modal');
}

async function saveAllPlans() {
    closeModal(document.getElementById('save-all-confirm-modal'));
    
    const planCards = document.querySelectorAll('.plan-card');
    let successCount = 0;
    
    for (const card of planCards) {
        const planId = card.getAttribute('data-plan');
        const planData = collectPlanData(card, planId);
        
        const success = await savePlanToDatabase(planData);
        if (success) successCount++;
    }
    
    // Clear changes and show success
    clearAllPlanChanges();
    
    openModal('save-all-success-modal');
    setTimeout(() => {
        closeModal(document.getElementById('save-all-success-modal'));
    }, 2000);
}

// Tag modal functionality
function initializeTagModalEventListeners() {
    const tagForm = document.getElementById('tag-form');
    
    document.getElementById('cancel-tag-btn').addEventListener('click', () => {
        closeModal(document.getElementById('tag-modal'));
    });
    
    // Color palette selection
    document.querySelectorAll('.tag-color-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            document.getElementById('tag-color').value = color;
            document.querySelectorAll('.tag-color-btn').forEach(b => 
                b.classList.remove('ring', 'ring-offset-2'));
            this.classList.add('ring', 'ring-offset-2');
        });
    });
    
    document.getElementById('tag-custom-color').addEventListener('input', function() {
        document.getElementById('tag-color').value = this.value;
        document.querySelectorAll('.tag-color-btn').forEach(b => 
            b.classList.remove('ring', 'ring-offset-2'));
    });
    
    tagForm.addEventListener('submit', handleAddTag);
}

function openAddTagModal() {
    updateTagPlanOptions();
    document.getElementById('tag-form').reset();
    document.getElementById('tag-color').value = '#2563eb';
    
    // Select first color
    document.querySelectorAll('.tag-color-btn').forEach(btn => 
        btn.classList.remove('ring', 'ring-offset-2'));
    const firstColorBtn = document.querySelector('.tag-color-btn[data-color="#2563eb"]');
    if (firstColorBtn) {
        firstColorBtn.classList.add('ring', 'ring-offset-2');
    }
    
    openModal('tag-modal');
}

function updateTagPlanOptions() {
    const select = document.getElementById('tag-plan');
    const current = select.value;
    select.innerHTML = '';
    
    Object.values(plans).forEach(plan => {
        const option = document.createElement('option');
        option.value = plan.id;
        option.textContent = plan.plan_name;
        select.appendChild(option);
    });
    
    if ([...select.options].some(o => o.value === current)) {
        select.value = current;
    }
}

function handleAddTag(e) {
    e.preventDefault();
    
    const tagName = document.getElementById('tag-name').value.trim();
    const tagColor = document.getElementById('tag-color').value;
    const planId = document.getElementById('tag-plan').value;
    
    // Find plan card and add tag
    const planCard = document.getElementById(`plan-${planId}`);
    let tagContainer = planCard.querySelector(`#plan-${planId}-tags-container`);
    
    // Clear existing tags (one at a time)
    tagContainer.innerHTML = '';
    
    // Add new tag
    const tag = document.createElement('span');
    tag.className = 'text-xs font-medium px-3 py-1 rounded-full flex items-center tag-item';
    tag.style.background = tagColor;
    tag.style.color = '#fff';
    tag.innerHTML = `
        <span class="tag-label">${escapeHtml(tagName)}</span>
        <button type="button" class="ml-1 text-white/80 hover:text-red-200 remove-tag-btn" title="Remove Tag">
            <i class="ri-close-line"></i>
        </button>
    `;
    tagContainer.appendChild(tag);
    
    // Update plan card border
    planCard.style.borderColor = tagColor;
    planCard.style.borderWidth = '2px';
    
    closeModal(document.getElementById('tag-modal'));
    
    // Track change
    addPlanChange({
        id: `plan-${planId}-tag-added-${tagName}-${Date.now()}`,
        desc: `Tag <b>${tagName}</b> added to ${plans[planId].plan_name}`
    });
}

// Add plan modal functionality
function initializeAddPlanModalEventListeners() {
    const addPlanForm = document.getElementById('add-plan-form');
    
    document.getElementById('cancel-add-plan').addEventListener('click', () => {
        closeModal(document.getElementById('add-plan-modal'));
        addPlanForm.reset();
        document.getElementById('new-plan-features').innerHTML = '';
    });
    
    // Add feature for new plan
    document.getElementById('add-new-plan-feature').addEventListener('click', () => {
        const ul = document.getElementById('new-plan-features');
        const li = document.createElement('li');
        li.className = 'flex items-center gap-2';
        li.innerHTML = `
            <input type="text" class="flex-1 px-2 py-1 rounded border border-gray-300" placeholder="Feature" />
            <button type="button" class="text-red-500 hover:text-red-700 remove-feature-btn">
                <i class="ri-delete-bin-line"></i>
            </button>
        `;
        ul.appendChild(li);
    });
    
    // Remove feature for new plan (event delegation)
    document.getElementById('new-plan-features').addEventListener('click', (e) => {
        if (e.target.closest('.remove-feature-btn')) {
            const li = e.target.closest('li');
            if (li) li.remove();
        }
    });
    
    addPlanForm.addEventListener('submit', handleCreateNewPlan);
}

function openAddPlanModal() {
    const form = document.getElementById('add-plan-form');
    form.reset();
    document.getElementById('new-plan-features').innerHTML = '';
    openModal('add-plan-modal');
}

async function handleCreateNewPlan(e) {
    e.preventDefault();
    
    const name = document.getElementById('new-plan-name').value.trim();
    const credits = parseInt(document.getElementById('new-plan-credits').value) || 0;
    const price = parseFloat(document.getElementById('new-plan-price').value) || 0;
    const features = Array.from(document.querySelectorAll('#new-plan-features input'))
        .map(input => input.value.trim())
        .filter(feature => feature);
    
    const planData = {
        plan_name: name,
        credits_per_month: credits,
        monthly_price: price,
        features: features,
        description: `${name} plan with ${credits} credits per month`,
        is_active: true,
        is_popular: false
    };
    
    try {
        const response = await fetch(API_BASE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(planData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeModal(document.getElementById('add-plan-modal'));
            showSuccessToast('New plan created successfully!');
            
            // Reload plans to get the new plan with its ID
            await loadPlans();
            
            // Track change
            addPlanChange({
                id: `new-plan-${result.plan_id}-${Date.now()}`,
                desc: `New plan <b>${name}</b> created`
            });
        } else {
            showErrorToast('Failed to create plan: ' + result.message);
        }
    } catch (error) {
        console.error('Error creating plan:', error);
        showErrorToast('Failed to create plan');
    }
}

// Remove tag functionality (event delegation)
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-tag-btn')) {
        const tagContainer = e.target.closest('.flex.flex-wrap[id$="tags-container"]');
        if (!tagContainer) return;
        
        const planCard = tagContainer.closest('.plan-card');
        tagContainer.innerHTML = '';
        
        // Reset border
        planCard.style.borderColor = '#e5e7eb';
        planCard.style.borderWidth = '1px';
        
        // Track change
        const planId = planCard.getAttribute('data-plan');
        addPlanChange({
            id: `plan-${planId}-tag-removed-${Date.now()}`,
            desc: 'Tag removed'
        });
    }
});

// Utility functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modal) {
    if (typeof modal === 'string') {
        modal = document.getElementById(modal);
    }
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function showSuccessToast(message) {
    const toast = document.getElementById('plan-toast-success');
    const textEl = document.getElementById('plan-toast-text');
    
    if (toast && textEl) {
        textEl.textContent = message;
        toast.classList.remove('translate-x-full');
        setTimeout(() => {
            toast.classList.add('translate-x-full');
        }, 3000);
    }
}

function showErrorToast(message) {
    // Create error toast if it doesn't exist
    let errorToast = document.getElementById('plan-toast-error');
    if (!errorToast) {
        errorToast = document.createElement('div');
        errorToast.id = 'plan-toast-error';
        errorToast.className = 'fixed top-6 right-0 z-50 px-4 py-2 bg-red-600 text-white rounded-lg shadow-lg flex items-center space-x-2 transition-transform duration-300 translate-x-full';
        errorToast.innerHTML = `
            <i class="ri-error-warning-line"></i>
            <span id="error-toast-text"></span>
        `;
        document.body.appendChild(errorToast);
    }
    
    const textEl = document.getElementById('error-toast-text');
    textEl.textContent = message;
    errorToast.classList.remove('translate-x-full');
    
    setTimeout(() => {
        errorToast.classList.add('translate-x-full');
    }, 4000);
}

function escapeHtml(text) {
    if (typeof text !== 'string') return '';
    return text.replace(/[&<>"']/g, function(match) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        }[match] || match;
    });
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}
          // If you want to also clear changes after individual plan save, you can call clearAllPlanChanges() there.
          </script>
        <script>
          // Edit mode logic for plan cards
          document.addEventListener('DOMContentLoaded', function () {
            const editBtn = document.getElementById('edit-plans-btn');
            const addPlanBtn = document.getElementById('add-new-plan-btn');
            const addTagBtn = document.getElementById('add-tag-btn');
            let editMode = false;
            function setEditMode(enabled) {
              // Toggle icon
              editBtn.innerHTML = enabled
          ? '<i class="ri-check-line ri-lg"></i>'
          : '<i class="ri-edit-2-line ri-lg"></i>';
              editBtn.title = enabled ? "Done Editing" : "Edit Plan cards";
              // Toggle all inputs/buttons in plan cards
              document.querySelectorAll('.plan-card input, .plan-card button, .plan-card textarea').forEach(el => {
          // Don't enable the edit icon itself
          if (el === editBtn) return;
          // Only enable/disable if not a remove-plan-btn (for new plans)
          if (el.classList.contains('remove-plan-btn')) {
            el.disabled = !enabled;
            el.tabIndex = enabled ? 0 : -1;
            return;
          }
          // Only enable add/remove feature, save, and inputs
          if (
            el.tagName === 'INPUT' ||
            el.classList.contains('add-feature-btn') ||
            el.classList.contains('remove-feature-btn') ||
            el.classList.contains('save-plan-btn')
          ) {
            el.disabled = !enabled;
            el.tabIndex = enabled ? 0 : -1;
          }
              });
              // Also toggle add plan and add tag buttons
              addPlanBtn.disabled = !enabled;
              addPlanBtn.tabIndex = enabled ? 0 : -1;
              addTagBtn.disabled = !enabled;
              addTagBtn.tabIndex = enabled ? 0 : -1;
            }
            setEditMode(false);
            editBtn.addEventListener('click', function () {
              editMode = !editMode;
              setEditMode(editMode);
            });
          });
        </script>

        <!-- Tag Modal -->
        <div id="tag-modal" class="modal">
          <div class="modal-content max-w-sm">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Add Tag to Plan</h3>
            <form id="tag-form" class="flex flex-col gap-3">
              <label class="block text-xs text-gray-500 mb-1">Tag Name</label>
              <input
          type="text"
          class="px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm"
          id="tag-name"
          required
              />
              <label class="block text-xs text-gray-500 mb-1">Tag Color</label>
              <div class="flex gap-2 mb-2" id="tag-color-palette">
          <button type="button" class="w-8 h-8 rounded-full border-2 border-gray-300 focus:border-primary tag-color-btn" style="background:#2563eb" data-color="#2563eb"></button>
          <button type="button" class="w-8 h-8 rounded-full border-2 border-gray-300 focus:border-primary tag-color-btn" style="background:#22c55e" data-color="#22c55e"></button>
          <button type="button" class="w-8 h-8 rounded-full border-2 border-gray-300 focus:border-primary tag-color-btn" style="background:#f59e42" data-color="#f59e42"></button>
          <button type="button" class="w-8 h-8 rounded-full border-2 border-gray-300 focus:border-primary tag-color-btn" style="background:#ef4444" data-color="#ef4444"></button>
          <button type="button" class="w-8 h-8 rounded-full border-2 border-gray-300 focus:border-primary tag-color-btn" style="background:#6366f1" data-color="#6366f1"></button>
          <input type="color" id="tag-custom-color" class="w-8 h-8 border-2 border-gray-300 rounded-full" title="Custom color" />
              </div>
              <input type="hidden" id="tag-color" value="#2563eb" />
              <label class="block text-xs text-gray-500 mb-1">Target Plan</label>
              <select id="tag-plan" class="px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm" required>
          <option value="basic">Basic</option>
          <option value="pro">Pro</option>
          <option value="enterprise">Enterprise</option>
              </select>
              <div class="flex items-center justify-end space-x-3 mt-4">
          <button
            type="button"
            class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50"
            id="cancel-tag-btn"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-primary text-white rounded-button whitespace-nowrap hover:bg-blue-700"
          >
            Add Tag
          </button>
              </div>
            </form>
          </div>
        </div>

        <script>
          // Tag modal logic
          const tagModal = document.getElementById('tag-modal');
          const tagForm = document.getElementById('tag-form');
          let selectedTagColor = "#2563eb";

          // Open tag modal
          document.getElementById('add-tag-btn').addEventListener('click', function() {
            tagModal.classList.add('active');
            document.body.style.overflow = "hidden";
            tagForm.reset();
            document.getElementById('tag-color').value = "#2563eb";
            selectedTagColor = "#2563eb";
            // Set first color as selected
            document.querySelectorAll('.tag-color-btn').forEach(btn => btn.classList.remove('ring', 'ring-offset-2'));
            document.querySelector('.tag-color-btn[data-color="#2563eb"]').classList.add('ring', 'ring-offset-2');
            // Update plan select for new plans
            updateTagPlanOptions();
          });

          // Cancel tag modal
          document.getElementById('cancel-tag-btn').addEventListener('click', function() {
            tagModal.classList.remove('active');
            document.body.style.overflow = "";
          });

          // Color palette logic
          document.querySelectorAll('.tag-color-btn').forEach(btn => {
            btn.addEventListener('click', function() {
              selectedTagColor = btn.getAttribute('data-color');
              document.getElementById('tag-color').value = selectedTagColor;
              document.querySelectorAll('.tag-color-btn').forEach(b => b.classList.remove('ring', 'ring-offset-2'));
              btn.classList.add('ring', 'ring-offset-2');
            });
          });
          document.getElementById('tag-custom-color').addEventListener('input', function() {
            selectedTagColor = this.value;
            document.getElementById('tag-color').value = selectedTagColor;
            document.querySelectorAll('.tag-color-btn').forEach(b => b.classList.remove('ring', 'ring-offset-2'));
          });

          // Helper to update plan options in tag modal (for new plans)
          function updateTagPlanOptions() {
            const select = document.getElementById('tag-plan');
            // Save current value
            const current = select.value;
            // Remove all options
            select.innerHTML = "";
            // Find all plan cards
            document.querySelectorAll('.plan-card').forEach(card => {
              let planId = card.id.replace('plan-', '');
              // Try to get plan name from input[type="text"].text-xl or h3
              let planName = planId.charAt(0).toUpperCase() + planId.slice(1);
              const nameInput = card.querySelector('input[type="text"].text-xl');
              if (nameInput && nameInput.value) planName = nameInput.value;
              else {
          // fallback for new plans (h3)
          const h3 = card.querySelector('h3');
          if (h3 && h3.textContent) planName = h3.textContent;
              }
              // Add option
              const opt = document.createElement('option');
              opt.value = planId;
              opt.textContent = planName;
              select.appendChild(opt);
            });
            // Restore previous value if possible
            if ([...select.options].some(o => o.value === current)) select.value = current;
          }

          // Add tag to plan and update border
          tagForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const tagName = document.getElementById('tag-name').value.trim();
            const tagColor = document.getElementById('tag-color').value;
            const plan = document.getElementById('tag-plan').value;
            // Find the plan card and tag container
            const planCard = document.getElementById('plan-' + plan);
            let tagContainer = planCard.querySelector('.flex.flex-wrap[id$="tags-container"]');
            // If not found, create and insert
            if (!tagContainer) {
              tagContainer = document.createElement('div');
              tagContainer.className = 'flex flex-wrap gap-2 absolute -top-3 left-4';
              tagContainer.id = plan + '-tags-container';
              planCard.insertBefore(tagContainer, planCard.firstChild);
            }
            // Remove existing tags and border if any (only one tag at a time)
            tagContainer.innerHTML = "";

            // Add tag
            const tag = document.createElement('span');
            tag.className = 'text-xs font-medium px-3 py-1 rounded-full flex items-center tag-item';
            tag.style.background = tagColor;
            tag.style.color = "#fff";
            tag.innerHTML = `
              <span class="tag-label">${tagName}</span>
              <button type="button" class="ml-1 text-white/80 hover:text-red-200 remove-tag-btn" title="Remove Tag"><i class="ri-close-line"></i></button>
            `;
            tagContainer.appendChild(tag);

            // Set border color
            planCard.style.borderColor = tagColor;
            planCard.style.borderWidth = "2px";

            // Close modal
            tagModal.classList.remove('active');
            document.body.style.overflow = "";
          });

          // Remove tag and border logic (for all plans, including new ones)
          document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-tag-btn')) {
              // Find the plan card
              let tagContainer = e.target.closest('.flex.flex-wrap[id$="tags-container"]');
              if (!tagContainer) return;
              const planCard = tagContainer.closest('.plan-card');
              tagContainer.innerHTML = "";
              planCard.style.borderColor = "#e5e7eb"; // Tailwind gray-200
              planCard.style.borderWidth = "1px";
            }
          });

          // When a new plan is added, update tag modal plan select
          const plansContainer = document.getElementById('plans-container');
          const observer = new MutationObserver(updateTagPlanOptions);
          observer.observe(plansContainer, { childList: true, subtree: false });
        </script>
        </div>
            </div>
            <!-- Add New Plan Modal -->
            <div id="add-plan-modal" class="modal">
        <div class="modal-content max-w-sm">
          <h3 class="text-lg font-medium text-gray-800 mb-4">Add New Plan</h3>
          <form id="add-plan-form" class="flex flex-col gap-3">
            <label class="block text-xs text-gray-500 mb-1">Plan Name</label>
            <input
              type="text"
              class="px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm"
              id="new-plan-name"
              required
            />
            <label class="block text-xs text-gray-500 mb-1">Credits</label>
            <input
              type="number"
              min="0"
              class="px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm"
              id="new-plan-credits"
              required
            />
            <label class="block text-xs text-gray-500 mb-1">Monthly Price (USDT)</label>
            <input
              type="number"
              min="0"
              class="px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm"
              id="new-plan-price"
              required
            />
            <label class="block text-xs text-gray-500 mb-1">Features</label>
            <ul id="new-plan-features" class="space-y-2 mb-2 text-sm text-gray-600"></ul>
            <button
              type="button"
              class="text-primary text-xs font-medium add-feature-btn"
              id="add-new-plan-feature"
            >
              <i class="ri-add-line"></i> Add Feature
            </button>
            <div class="flex items-center justify-end space-x-3 mt-4">
              <button
          type="button"
          class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50"
          id="cancel-add-plan"
              >
          Cancel
              </button>
              <button
          type="submit"
          class="px-4 py-2 bg-primary text-white rounded-button whitespace-nowrap hover:bg-blue-700"
              >
          Add Plan
              </button>
            </div>
          </form>
        </div>
            </div>
            <!-- Confirmation Modal for Plans -->
            <div id="plan-confirm-modal" class="modal">
        <div class="modal-content max-w-sm">
          <div class="flex items-center mb-4">
            <div
              class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-4"
            >
              <i class="ri-question-line ri-lg"></i>
            </div>
            <h3
              class="text-lg font-medium text-gray-800"
              id="plan-confirm-title"
            >
              Confirm Changes
            </h3>
          </div>
          <p class="text-sm text-gray-600 mb-6" id="plan-confirm-desc">
            Are you sure you want to save changes to this plan? This will update
            the plan for all users.
          </p>
          <div class="flex items-center justify-end space-x-3">
            <button
              id="plan-cancel-btn"
              class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              id="plan-confirm-btn"
              class="px-4 py-2 bg-primary text-white rounded-button whitespace-nowrap hover:bg-blue-700"
            >
              Yes, Save
            </button>
          </div>
        </div>
            </div>
            <!-- Success Modal for Plans -->
            <div id="plan-success-modal" class="modal">
        <div class="modal-content max-w-sm flex flex-col items-center">
          <div
            class="w-16 h-16 rounded-full bg-green-100 text-green-600 flex items-center justify-center mb-4"
          >
            <i class="ri-checkbox-circle-line ri-2x"></i>
          </div>
          <h3
            class="text-lg font-medium text-gray-800 mb-2"
            id="plan-success-title"
          >
            Plan Updated!
          </h3>
          <p
            class="text-sm text-gray-600 mb-6 text-center"
            id="plan-success-desc"
          >
            The plan information has been successfully updated.
          </p>
          <button
            id="plan-success-close-btn"
            class="px-4 py-2 bg-primary text-white rounded-button whitespace-nowrap hover:bg-blue-700"
          >
            Close
          </button>
        </div>
            </div>
            <!-- Success Toast for Plans -->
            <div
        id="plan-toast-success"
        class="fixed top-6 right-0 z-50 px-4 py-2 bg-green-600 text-white rounded-lg shadow-lg flex items-center space-x-2 transition-transform duration-300 translate-x-full"
            >
        <i class="ri-checkbox-circle-line"></i>
        <span id="plan-toast-text">Plan saved successfully!</span>
            </div>

            <script>
        document.addEventListener("DOMContentLoaded", function () {
          // Add feature logic for existing plans
          document.querySelectorAll(".add-feature-btn").forEach(function (btn) {
            btn.addEventListener("click", function () {
              const targetId = btn.getAttribute("data-target");
              if (!targetId) return;
              const ul = document.getElementById(targetId);
              if (!ul) return;
              const li = document.createElement("li");
              li.className = "flex items-center gap-2";
              li.innerHTML =
          '<input type="text" class="flex-1 px-2 py-1 rounded border border-gray-300" placeholder="New feature" />' +
          '<button type="button" class="text-red-500 hover:text-red-700 remove-feature-btn"><i class="ri-delete-bin-line"></i></button>';
              ul.appendChild(li);
            });
          });
          // Remove feature logic (event delegation)
          document
            .querySelectorAll(
              "#basic-features, #pro-features, #enterprise-features"
            )
            .forEach(function (ul) {
              ul.addEventListener("click", function (e) {
          if (e.target.closest(".remove-feature-btn")) {
            const li = e.target.closest("li");
            if (li) li.remove();
          }
              });
            });

          // Add feature logic for new plan modal
          document.getElementById("add-new-plan-feature").addEventListener("click", function () {
            const ul = document.getElementById("new-plan-features");
            const li = document.createElement("li");
            li.className = "flex items-center gap-2";
            li.innerHTML =
              '<input type="text" class="flex-1 px-2 py-1 rounded border border-gray-300" placeholder="Feature" />' +
              '<button type="button" class="text-red-500 hover:text-red-700 remove-feature-btn"><i class="ri-delete-bin-line"></i></button>';
            ul.appendChild(li);
          });
          document.getElementById("new-plan-features").addEventListener("click", function (e) {
            if (e.target.closest(".remove-feature-btn")) {
              const li = e.target.closest("li");
              if (li) li.remove();
            }
          });

          // Add New Plan Modal logic
          const addPlanBtn = document.getElementById("add-new-plan-btn");
          const addPlanModal = document.getElementById("add-plan-modal");
          const cancelAddPlanBtn = document.getElementById("cancel-add-plan");
          addPlanBtn.addEventListener("click", function () {
            openModal("add-plan-modal");
          });
          cancelAddPlanBtn.addEventListener("click", function () {
            closeModal(addPlanModal);
            document.getElementById("add-plan-form").reset();
            document.getElementById("new-plan-features").innerHTML = "";
          });

          // Handle new plan submission
          document.getElementById("add-plan-form").addEventListener("submit", function (e) {
            e.preventDefault();
            // Get values
            const name = document.getElementById("new-plan-name").value.trim();
            const credits = document.getElementById("new-plan-credits").value;
            const price = document.getElementById("new-plan-price").value;
            const features = Array.from(document.querySelectorAll("#new-plan-features input")).map(
              (input) => input.value.trim()
            ).filter(f => f);

            // Generate a unique id for features list
            const planId = "plan-" + Date.now();

            // Create plan card
            const planCard = document.createElement("div");
            planCard.className = "glassmorphism bg-white rounded-lg border border-gray-200 p-6 relative plan-card";
            planCard.id = planId;
            planCard.innerHTML = `
              <h3 class="text-xl font-semibold text-gray-900 mb-2">${escapeHtml(name)}</h3>
              <div class="mb-4 flex items-baseline">
          <span class="text-3xl font-bold text-primary">$${escapeHtml(price)}</span>
          <span class="text-gray-500 ml-1">/mo</span>
              </div>
              <ul class="space-y-2 mb-6 text-sm text-gray-600" id="${planId}-features">
          ${features.map(f => `
            <li class="flex items-center gap-2">
              <input type="text" class="flex-1 px-2 py-1 rounded border border-gray-300" value="${escapeHtml(f)}" />
              <button type="button" class="text-red-500 hover:text-red-700 remove-feature-btn"><i class="ri-delete-bin-line"></i></button>
            </li>
          `).join("")}
              </ul>
              <button type="button" class="text-primary text-xs font-medium mb-4 add-feature-btn" data-target="${planId}-features">
          <i class="ri-add-line"></i> Add Feature
              </button>
              <div class="flex flex-col gap-2">
          <label class="block text-xs text-gray-500 mb-1">Credits</label>
          <input type="number" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm" value="${escapeHtml(credits)}" />
          <label class="block text-xs text-gray-500 mb-1">Monthly Price (USDT)</label>
          <input type="number" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm" value="${escapeHtml(price)}" />
              </div>
              <button class="mt-4 w-full py-2 bg-primary text-white rounded-button hover:bg-blue-700 transition-colors save-plan-btn" data-plan="${planId}">
          Save ${escapeHtml(name)} Plan
              </button>
              <button class="absolute top-2 right-2 text-gray-400 hover:text-red-500 remove-plan-btn" title="Remove Plan">
          <i class="ri-close-line"></i>
              </button>
            `;
            // Add to plans container
            document.getElementById("plans-container").appendChild(planCard);

            // Attach feature add/remove logic for this card
            planCard.querySelector(".add-feature-btn").addEventListener("click", function () {
              const ul = planCard.querySelector(`#${planId}-features`);
              const li = document.createElement("li");
              li.className = "flex items-center gap-2";
              li.innerHTML =
          '<input type="text" class="flex-1 px-2 py-1 rounded border border-gray-300" placeholder="New feature" />' +
          '<button type="button" class="text-red-500 hover:text-red-700 remove-feature-btn"><i class="ri-delete-bin-line"></i></button>';
              ul.appendChild(li);
            });
            planCard.querySelector(`#${planId}-features`).addEventListener("click", function (e) {
              if (e.target.closest(".remove-feature-btn")) {
          const li = e.target.closest("li");
          if (li) li.remove();
              }
            });

            // Attach save logic for this card
            planCard.querySelector(".save-plan-btn").addEventListener("click", function () {
              currentPlan = planId;
              document.getElementById("plan-confirm-title").textContent =
          "Confirm Changes";
              document.getElementById("plan-confirm-desc").textContent =
          "Are you sure you want to save changes to the " +
          escapeHtml(name) +
          " plan? This will update the plan for all users.";
              openModal("plan-confirm-modal");
            });

            // Remove plan logic
            planCard.querySelector(".remove-plan-btn").addEventListener("click", function () {
              planCard.remove();
              // Also update tag modal plan select when a plan is removed
              updateTagPlanOptions();
            });

            // Update tag modal plan select when a new plan is added
            updateTagPlanOptions();

            // Reset and close modal
            closeModal(addPlanModal);
            document.getElementById("add-plan-form").reset();
            document.getElementById("new-plan-features").innerHTML = "";
            showToast("New plan added!");
          });

          // Plan Save logic for existing plans
          let currentPlan = null;
          document.querySelectorAll(".save-plan-btn").forEach(function (btn) {
            btn.addEventListener("click", function () {
              currentPlan = btn.getAttribute("data-plan");
              // Fill modal info
              document.getElementById("plan-confirm-title").textContent =
          "Confirm Changes";
              document.getElementById("plan-confirm-desc").textContent =
          "Are you sure you want to save changes to the " +
          capitalize(currentPlan) +
          " plan? This will update the plan for all users.";
              openModal("plan-confirm-modal");
            });
          });

          document
            .getElementById("plan-cancel-btn")
            .addEventListener("click", function () {
              closeModal(document.getElementById("plan-confirm-modal"));
            });

          document
            .getElementById("plan-confirm-btn")
            .addEventListener("click", function () {
              closeModal(document.getElementById("plan-confirm-modal"));
              // Simulate save, show success modal
              document.getElementById("plan-success-title").textContent =
          capitalize(currentPlan) + " Plan Updated!";
              document.getElementById("plan-success-desc").textContent =
          "The " +
          capitalize(currentPlan) +
          " plan information has been successfully updated.";
              openModal("plan-success-modal");
              // Show toast after modal closes
              setTimeout(() => {
          closeModal(document.getElementById("plan-success-modal"));
          showToast("Plan saved successfully!");
              }, 1500);
            });

          document
            .getElementById("plan-success-close-btn")
            .addEventListener("click", function () {
              closeModal(document.getElementById("plan-success-modal"));
              showToast("Plan saved successfully!");
            });

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
          function showToast(msg) {
            const toast = document.getElementById("plan-toast-success");
            document.getElementById("plan-toast-text").textContent = msg;
            toast.classList.remove("translate-x-full");
            setTimeout(() => {
              toast.classList.add("translate-x-full");
            }, 3000);
          }
          function capitalize(str) {
            if (!str) return "";
            return str.charAt(0).toUpperCase() + str.slice(1);
          }
          function escapeHtml(text) {
            return text.replace(/[&<>"']/g, function (m) {
              return (
          {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;",
          }[m] || m
              );
            });
          }
          // Expose updateTagPlanOptions globally so it can be called from new plan logic
          window.updateTagPlanOptions = updateTagPlanOptions;
        });
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
