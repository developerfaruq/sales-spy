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
                class="nav-link w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-primary bg-blue-50"
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
      <!-- Wallets Tab -->
      <div id="wallets-tab" class="tab-content p-4 md:p-6 ">
        <div class="mb-6">
          <h1 class="text-2xl font-semibold text-gray-800">
            Wallet Management
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Manage user wallets and platform wallet settings
          </p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-6 relative">
          <h2 class="text-lg font-medium text-gray-800 mb-4">
            Platform Wallet
          </h2>
          <!-- Edit Icon Button -->
          <button
            id="edit-wallet-btn"
            class="absolute top-6 right-6 text-gray-400 hover:text-primary transition-colors"
            type="button"
            data-tooltip="Edit wallet info"
          >
            <i class="ri-edit-2-line ri-lg"></i>
          </button>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
              <div class="space-y-4">
                <div>
                  <label
                    for="wallet-name"
                    class="block text-sm font-medium text-gray-700 mb-1"
                    >Wallet Name</label
                  >
                  <input
                    type="text"
                    id="wallet-name"
                    class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                    value="USDT Receiving Wallet"
                    disabled
                  />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label
                      for="blockchain"
                      class="block text-sm font-medium text-gray-700 mb-1"
                      >Blockchain</label
                    >
                    <div class="custom-select" id="blockchain-select">
                      <div class="custom-select-trigger">
                        <div class="flex items-center">
                          <div
                            class="w-5 h-5 flex items-center justify-center mr-2"
                          >
                            <i class="ri-coin-line"></i>
                          </div>
                          <span>USDT</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div>
                    <label
                      for="token-standard"
                      class="block text-sm font-medium text-gray-700 mb-1"
                      >Token Standard</label
                    >
                    <div class="custom-select" id="token-standard-select">
                      <div class="custom-select-trigger">
                        <span>TRC-20</span>
                      </div>
                    </div>
                  </div>
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
                      class="w-full pl-4 pr-24 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                      value="TVTkZq3ghDZgF8txTdDM3s8g76XM2pgiey"
                      disabled
                    />
                    <div
                      class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center space-x-2"
                    >
                      <button
                        class="text-gray-400 hover:text-primary"
                        id="copy-address"
                      >
                        <i class="ri-file-copy-line"></i>
                      </button>
                    </div>
                  </div>

                  <script id="qr-code-script">
                    document.addEventListener("DOMContentLoaded", function () {
  // Wallet management variables
  let currentWalletData = null;
  
  // DOM elements
  const walletNameInput = document.getElementById("wallet-name");
  const walletAddressInput = document.getElementById("wallet-address");
  const blockchainSelect = document.getElementById("blockchain-select");
  const tokenStandardSelect = document.getElementById("token-standard-select");
  const editBtn = document.getElementById("edit-wallet-btn");
  const cancelBtn = document.querySelector("#wallets-tab button:not([id]):not([id='download-qr'])");
  const saveBtn = document.querySelector("#wallets-tab button.bg-primary");
  const qrcodeContainer = document.getElementById("qrcode");
  
  // Modal elements
  const confirmModal = document.getElementById("confirm-save-modal");
  const confirmSaveBtn = document.getElementById("confirm-save-btn");
  const cancelSaveBtn = document.getElementById("cancel-save-btn");
  const successModal = document.getElementById("success-modal");
  const successCloseBtn = document.getElementById("success-close-btn");
  const toast = document.getElementById("wallet-toast-success");
  
  let qrcode = null;

  // Load wallet data on page load
  loadWalletData();

  function loadWalletData() {
    showLoading(true);
    
    fetch('api/wallet_api.php', {
      method: 'GET',
      credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
      if (data.success && data.wallet) {
        currentWalletData = data.wallet;
        populateWalletForm(data.wallet);
        updateQRCode(data.wallet.wallet_address || '');
      } else {
        showError('Failed to load wallet data');
      }
    })
    .catch(error => {
      console.error('Error loading wallet data:', error);
      showError('Failed to load wallet data');
    })
    .finally(() => {
      showLoading(false);
    });
  }

  function populateWalletForm(wallet) {
    // Map the database fields to form fields
    const walletName = `${wallet.currency || 'USDT'} Receiving Wallet`;
    const blockchain = wallet.currency || 'USDT';
    const tokenStandard = wallet.network || 'TRC-20';
    
    walletNameInput.value = walletName;
    walletAddressInput.value = wallet.wallet_address || '';
    
    // Update blockchain display
    const blockchainTrigger = blockchainSelect.querySelector('.custom-select-trigger span:last-child');
    if (blockchainTrigger) {
      blockchainTrigger.textContent = blockchain;
    }
    
    // Update token standard display
    const tokenStandardTrigger = tokenStandardSelect.querySelector('.custom-select-trigger span');
    if (tokenStandardTrigger) {
      tokenStandardTrigger.textContent = tokenStandard;
    }
  }

  function updateQRCode(address) {
    if (!address) return;
    
    if (qrcodeContainer) {
      qrcodeContainer.innerHTML = '';
      qrcode = new QRCode(qrcodeContainer, {
        text: address,
        width: 192,
        height: 192,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H,
      });
    }
  }

  function setEditable(editable) {
    walletNameInput.disabled = !editable;
    walletAddressInput.disabled = !editable;
    
    if (editable) {
      walletNameInput.classList.add("border-primary");
      walletAddressInput.classList.add("border-primary");
      saveBtn.classList.remove("opacity-50", "pointer-events-none");
      cancelBtn.classList.remove("opacity-50", "pointer-events-none");
    } else {
      walletNameInput.classList.remove("border-primary");
      walletAddressInput.classList.remove("border-primary");
      saveBtn.classList.add("opacity-50", "pointer-events-none");
      cancelBtn.classList.add("opacity-50", "pointer-events-none");
    }
  }

  function showLoading(show) {
    if (show) {
      // Add loading state to the wallet section
      const walletSection = document.querySelector('.bg-white.rounded-lg.shadow-sm');
      if (walletSection && !walletSection.querySelector('.loading-overlay')) {
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'loading-overlay absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10';
        loadingOverlay.innerHTML = '<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>';
        walletSection.style.position = 'relative';
        walletSection.appendChild(loadingOverlay);
      }
    } else {
      const loadingOverlay = document.querySelector('.loading-overlay');
      if (loadingOverlay) {
        loadingOverlay.remove();
      }
    }
  }

  function showError(message) {
    // Create error toast
    const errorToast = document.createElement('div');
    errorToast.className = 'fixed top-6 right-6 z-50 px-4 py-2 bg-red-600 text-white rounded-lg shadow-lg flex items-center space-x-2';
    errorToast.innerHTML = `
      <i class="ri-error-warning-line"></i>
      <span>${message}</span>
    `;
    document.body.appendChild(errorToast);
    
    setTimeout(() => {
      errorToast.remove();
    }, 5000);
  }

  function showSuccessToast(message) {
    if (toast) {
      const toastMessage = toast.querySelector('span');
      if (toastMessage) {
        toastMessage.textContent = message;
      }
      toast.classList.remove("translate-x-full");
      setTimeout(() => {
        toast.classList.add("translate-x-full");
      }, 3000);
    }
  }

  // Initialize form as non-editable
  setEditable(false);

  // Event listeners
  if (editBtn) {
    editBtn.addEventListener("click", function () {
      setEditable(true);
    });
  }

  if (cancelBtn) {
    cancelBtn.addEventListener("click", function (e) {
      e.preventDefault();
      setEditable(false);
      // Reset form to original values
      if (currentWalletData) {
        populateWalletForm(currentWalletData);
        updateQRCode(currentWalletData.wallet_address || '');
      }
    });
  }

  if (saveBtn) {
    saveBtn.addEventListener("click", function (e) {
      if (saveBtn.classList.contains("opacity-50")) {
        e.preventDefault();
        return;
      }
      
      // Validate form data
      const walletName = walletNameInput.value.trim();
      const walletAddress = walletAddressInput.value.trim();
      
      if (!walletName || !walletAddress) {
        showError('Please fill in all required fields');
        return;
      }
      
      // Basic wallet address validation
      if (walletAddress.length < 20 || walletAddress.length > 50) {
        showError('Invalid wallet address format');
        return;
      }
      
      // Show confirmation modal
      confirmModal.classList.add("active");
      document.body.style.overflow = "hidden";
    });
  }

  if (confirmSaveBtn) {
    confirmSaveBtn.addEventListener("click", function () {
      confirmModal.classList.remove("active");
      document.body.style.overflow = "";
      saveWalletData();
    });
  }

  if (cancelSaveBtn) {
    cancelSaveBtn.addEventListener("click", function () {
      confirmModal.classList.remove("active");
      document.body.style.overflow = "";
    });
  }

  if (successCloseBtn) {
    successCloseBtn.addEventListener("click", function () {
      successModal.classList.remove("active");
      document.body.style.overflow = "";
      showSuccessToast('Wallet info saved successfully!');
    });
  }

  function saveWalletData() {
    const formData = {
      wallet_name: walletNameInput.value.trim(),
      wallet_address: walletAddressInput.value.trim(),
      blockchain: blockchainSelect.querySelector('.custom-select-trigger span:last-child').textContent.trim(),
      token_standard: tokenStandardSelect.querySelector('.custom-select-trigger span').textContent.trim()
    };
    
    showLoading(true);
    
    fetch('api/wallet_api.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'same-origin',
      body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        currentWalletData = data.wallet;
        setEditable(false);
        updateQRCode(data.wallet.wallet_address);
        
        // Show success modal
        successModal.classList.add("active");
        document.body.style.overflow = "hidden";
        
        // Auto-close success modal after 1.5 seconds
        setTimeout(() => {
          successModal.classList.remove("active");
          document.body.style.overflow = "";
          showSuccessToast(data.message || 'Wallet info saved successfully!');
        }, 1500);
        
      } else {
        showError(data.error || 'Failed to save wallet information');
      }
    })
    .catch(error => {
      console.error('Error saving wallet data:', error);
      showError('Failed to save wallet information');
    })
    .finally(() => {
      showLoading(false);
    });
  }

  // Copy address functionality
  const copyAddressBtn = document.getElementById("copy-address");
  if (copyAddressBtn) {
    copyAddressBtn.addEventListener("click", function () {
      const walletAddress = walletAddressInput.value;
      if (!walletAddress) {
        showError('No wallet address to copy');
        return;
      }
      
      navigator.clipboard.writeText(walletAddress).then(() => {
        const originalIcon = this.innerHTML;
        this.innerHTML = '<i class="ri-check-line text-green-500"></i>';
        setTimeout(() => {
          this.innerHTML = originalIcon;
        }, 2000);
      }).catch(() => {
        showError('Failed to copy address');
      });
    });
  }

  // Download QR functionality
  const downloadQRBtn = document.getElementById("download-qr");
  if (downloadQRBtn) {
    downloadQRBtn.addEventListener("click", function () {
      const canvas = qrcodeContainer.querySelector("canvas");
      if (canvas) {
        const link = document.createElement("a");
        link.download = "wallet-qr.png";
        link.href = canvas.toDataURL("image/png");
        link.click();
      } else {
        showError('QR code not available');
      }
    });
  }

  // Update QR code when address changes
  if (walletAddressInput) {
    walletAddressInput.addEventListener("input", function() {
      if (this.value) {
        updateQRCode(this.value);
      }
    });
  }

  // Tooltip for edit icon
  if (editBtn) {
    editBtn.addEventListener("mouseenter", function () {
      const tooltip = document.createElement("div");
      tooltip.id = "edit-tooltip";
      tooltip.className = "fixed px-2 py-1 text-xs text-white bg-gray-900 rounded pointer-events-none opacity-0 transition-opacity duration-200 z-50";
      tooltip.textContent = editBtn.getAttribute("data-tooltip") || "Edit wallet info";
      document.body.appendChild(tooltip);
      
      const rect = editBtn.getBoundingClientRect();
      tooltip.style.top = rect.top - 32 + "px";
      tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px";
      tooltip.style.opacity = "1";
      editBtn._tooltip = tooltip;
    });

    editBtn.addEventListener("mouseleave", function () {
      if (editBtn._tooltip) {
        editBtn._tooltip.remove();
        editBtn._tooltip = null;
      }
    });
  }
});
                  </script>

                  <p class="mt-1 text-xs text-gray-500">
                    USDT TRC-20 wallet address for receiving payments
                  </p>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-3">
                  <button
                    class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50 opacity-50 pointer-events-none"
                  >
                    Cancel
                  </button>
                  <button
                    class="px-4 py-2 bg-primary text-white rounded-button whitespace-nowrap opacity-50 pointer-events-none"
                  >
                    Save Changes
                  </button>
                </div>
              </div>
            </div>
            <div
              class="flex flex-col items-center justify-center p-4 sm:p-6 border border-gray-200 rounded-lg"
            >
              <div
                id="qrcode"
                class="w-48 h-48 bg-white rounded-lg flex items-center justify-center mb-4"
              ></div>
              <p class="text-sm text-gray-500 text-center">
                Scan to get the wallet address
              </p>
              <button
                class="mt-4 px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50 flex items-center"
                id="download-qr"
              >
                <i class="ri-download-2-line mr-2"></i>
                Download QR
              </button>
            </div>
          </div>
        </div>

        <!-- Confirmation Modal -->
        <div id="confirm-save-modal" class="modal">
          <div class="modal-content max-w-sm">
            <div class="flex items-center mb-4">
              <div
                class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-4"
              >
                <i class="ri-question-line ri-lg"></i>
              </div>
              <h3 class="text-lg font-medium text-gray-800">Confirm Changes</h3>
            </div>
            <p class="text-sm text-gray-600 mb-6">
              Are you sure you want to save these wallet changes? This will
              update the platform wallet info for all users.
            </p>
            <div class="flex items-center justify-end space-x-3">
              <button
                id="cancel-save-btn"
                class="px-4 py-2 border border-gray-200 text-gray-600 rounded-button whitespace-nowrap hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                id="confirm-save-btn"
                class="px-4 py-2 bg-primary text-white rounded-button whitespace-nowrap hover:bg-blue-700"
              >
                Yes, Save
              </button>
            </div>
          </div>
        </div>

        <!-- Success Modal -->
        <div id="success-modal" class="modal">
          <div class="modal-content max-w-sm flex flex-col items-center">
            <div
              class="w-16 h-16 rounded-full bg-green-100 text-green-600 flex items-center justify-center mb-4"
            >
              <i class="ri-checkbox-circle-line ri-2x"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">
              Wallet Info Updated!
            </h3>
            <p class="text-sm text-gray-600 mb-6 text-center">
              The platform wallet information has been successfully updated.
            </p>
            <button
              id="success-close-btn"
              class="px-4 py-2 bg-primary text-white rounded-button whitespace-nowrap hover:bg-blue-700"
            >
              Close
            </button>
          </div>
        </div>

        <!-- Success Toast -->
        <div
          id="wallet-toast-success"
          class="fixed top-6 right-0 z-50 px-4 py-2 bg-green-600 text-white rounded-lg shadow-lg flex items-center space-x-2 transition-transform duration-300 translate-x-full"
        >
          <i class="ri-checkbox-circle-line"></i>
          <span>Wallet info saved successfully!</span>
        </div>
      </div>
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
