<?php
require '../auth/auth_check.php';

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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sales-Spy Settings Dashboard</title>
<script src="https://cdn.tailwindcss.com/3.4.16"></script>
<script>tailwind.config={theme:{extend:{colors:{primary:'#1E3A8A',secondary:'#5BC0EB'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet">
<style>
:where([class^="ri-"])::before { content: "\f3c2"; }
body {
font-family: 'Inter', sans-serif;
background-color: #f8f9fa;
}
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
-webkit-appearance: none;
margin: 0;
}
.custom-checkbox {
position: relative;
display: inline-block;
width: 20px;
height: 20px;
background-color: white;
border: 2px solid #e2e8f0;
border-radius: 4px;
cursor: pointer;
transition: all 0.2s;
}
.custom-checkbox.checked {
background-color: #3b82f6;
border-color: #3b82f6;
}
.custom-checkbox.checked::after {
content: '';
position: absolute;
left: 6px;
top: 2px;
width: 6px;
height: 10px;
border: solid white;
border-width: 0 2px 2px 0;
transform: rotate(45deg);
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
transition: .4s;
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
transition: .4s;
border-radius: 50%;
}
input:checked + .switch-slider {
background-color: #3b82f6;
}
input:checked + .switch-slider:before {
transform: translateX(20px);
}
.dropdown {
position: relative;
display: inline-block;
}
.dropdown-content {
display: none;
position: absolute;
background-color: white;
min-width: 160px;
box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
z-index: 1;
border-radius: 8px;
right: 0;
}
.dropdown-content a {
color: black;
padding: 12px 16px;
text-decoration: none;
display: block;
}
.dropdown-content a:hover {
background-color: #f8f9fa;
}
.dropdown:hover .dropdown-content {
display: block;
}

/* Sidebar transition for smooth expand/collapse (match Dashboard-home.html) */
#sidebar {
  width: 240px;
  min-width: 240px;
  max-width: 240px;
  transition: width 0.6s cubic-bezier(0.4,0,0.2,1), min-width 0.6s cubic-bezier(0.4,0,0.2,1), max-width 0.6s cubic-bezier(0.4,0,0.2,1);
  overflow-x: hidden;
}
#sidebar.sidebar-collapsed {
  width: 80px;
  min-width: 80px;
  max-width: 80px;
}
#sidebar .sidebar-logo-text {
  transition: opacity 0.3s;
}
#sidebar.sidebar-collapsed .sidebar-logo-text {
  opacity: 0;
  pointer-events: none;
}
#sidebar .sidebar-logo-img {
  display: none;
  transition: opacity 0.3s;
}
#sidebar.sidebar-collapsed .sidebar-logo-img {
  display: block !important;
  opacity: 1;
}
#sidebar .sidebar-logo-text {
  display: block;
  transition: opacity 0.3s;
}
#sidebar.sidebar-collapsed .sidebar-logo-text {
  display: none !important;
  opacity: 0;
  pointer-events: none;
}
#sidebar .sidebar-logo-img {
  display: none;
}
.sidebar-collapsed .sidebar-text {
  display: none !important;
}
.sidebar-collapsed .mr-3,
.sidebar-collapsed .sidebar-icon {
  margin-right: 0 !important;
}
.sidebar-collapsed nav ul li a {
  justify-content: center;
  padding-left: 0.5rem !important;
  padding-right: 0.5rem !important;
}
.sidebar-collapsed .rounded-r-lg {
  border-radius: 8px !important;
}
.sidebar-collapsed .border-l-4 {
  border-left-width: 0 !important;
}
.sidebar-collapsed .sidebar-logo {
  justify-content: center !important;
}
.sidebar-collapsed .sidebar-logo-text {
  display: none !important;
}
.sidebar-collapsed .sidebar-logo-img {
  display: block !important;
}
.sidebar-logo-img {
  display: none;
}
.sidebar-logo-text {
  display: block;
}
@media (max-width: 1024px) {
  #sidebar {
    position: fixed;
    left: 0;
    top: 0;
    z-index: 40;
    height: 100vh;
    transform: translateX(-100%);
    transition: transform 0.6s cubic-bezier(0.4,0,0.2,1), width 0.6s cubic-bezier(0.4,0,0.2,1), min-width 0.6s cubic-bezier(0.4,0,0.2,1), max-width 0.6s cubic-bezier(0.4,0,0.2,1);
  }
  #sidebar.open {
    transform: translateX(0);
  }
  .sidebar-backdrop {
    display: block;
  }
}
@media (min-width: 1025px) {
  .sidebar-backdrop {
    display: none !important;
  }
}
.sidebar-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.3);
  z-index: 30;
  display: none;
}
</style>
</head>
<body class="flex h-screen bg-gray-50">
<!-- Sidebar Backdrop for mobile -->
<div class="sidebar-backdrop hidden" id="sidebar-backdrop"></div>
<!-- Sidebar -->
<div id="sidebar" class="sidebar-expanded bg-white shadow-lg z-20 flex flex-col transition-all duration-700 ease-in-out">
  <!-- Logo and mobile close button -->
  <div class="p-4 border-b flex items-center justify-center relative sidebar-logo">
    <img src="https://res.cloudinary.com/dtrn8j0sz/image/upload/v1749075914/SS_s4jkfw.jpg" alt="Logo" id="sidebar-logo-img" class="sidebar-logo-img w-8 h-8 mr-0">
    <h1 id="sidebar-logo-text" class="sidebar-logo-text font-['Pacifico'] text-2xl text-primary ml-2 transition-opacity duration-300">Sales-Spy</h1>
    <!-- Mobile-only collapse button -->
    <button id="sidebar-mobile-close" class="absolute right-2 top-2 p-2 rounded-full hover:bg-gray-100 md:hidden" style="display: none;" aria-label="Close sidebar">
      <i class="ri-close-line text-xl"></i>
    </button>
  </div>
  <nav class="flex-1 overflow-y-auto py-4">
    <ul>
      <li class="mb-2">
        <a href="index.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
          <div class="w-6 h-6 flex items-center justify-center mr-3 sidebar-icon">
            <i class="ri-dashboard-line"></i>
          </div>
          <span class="sidebar-text">Dashboard</span>
        </a>
      </li>
      <li class="mb-2">
        <a href="Dashboard-com.html" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
          <div class="w-6 h-6 flex items-center justify-center mr-3 sidebar-icon">
            <i class="ri-global-line"></i>
          </div>
          <span class="sidebar-text">Websites</span>
        </a>
      </li> 
      <li class="mb-2">
        <a href="Dashboard-ecc.html" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
          <div class="w-6 h-6 flex items-center justify-center mr-3 sidebar-icon">
            <i class="ri-shopping-cart-line"></i>
          </div>
          <span class="sidebar-text">E-commerce</span>
        </a>
      </li>
       <li class="mb-2">
                <a href="Dash-pay.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-r-lg hover:text-primary transition-colors">
                  <div class="w-6 h-6 flex items-center justify-center mr-3"> <i class="ri-bank-card-line"></i></div>
                  <span class="sidebar-text">Payment</span>
                </a>
              </li>
      <li class="mb-2">
        <a href="#" class="flex items-center px-4 py-3 text-primary bg-blue-50 rounded-r-lg border-l-4 border-primary">
          <div class="w-6 h-6 flex items-center justify-center mr-3 sidebar-icon">
            <i class="ri-settings-line"></i>
          </div>
          <span class="sidebar-text">Settings</span>
        </a>
      </li>
    </ul>
  </nav>
  <!-- Upgrade section (unchanged) -->
  <div id="upgrade-section" class="p-4 border-t">
                    <div id="upgrade-expanded" class="bg-gray-50 rounded-lg p-4 mb-3">
                        <p class="text-sm text-gray-600 mb-2">Credits remaining</p>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-lg"><?= number_format($stats['credits_remaining']) ?></span>
                            <span class="text-xs text-gray-500">of <?= number_format($stats['credits_total']) ?></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-primary rounded-full h-2" style="width: <?= $stats['credits_percentage'] ?>%"></div>
                        </div>
                    </div>
                    <a href="Dash-pay.php">
                    <button id="upgrade-btn-expanded" class="w-full bg-primary text-white py-2 px-4 rounded-button flex items-center justify-center whitespace-nowrap hover:bg-blue-600 transition-colors">
                        <div class="w-5 h-5 flex items-center justify-center mr-2">
                            <i class="ri-arrow-up-line"></i>
                        </div>
                        <span>Upgrade Plan</span>
                    </button>
                    </a>
                    <a href="Dash-pay.php">
                    <button id="upgrade-btn-collapsed" class="hidden bg-primary text-white w-10 h-10 rounded-full flex items-center justify-center mx-auto mt-2 hover:bg-blue-600 transition-colors" title="Upgrade">
                        <i class="ri-arrow-up-line"></i>
                    </button>
                    </a>
                </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const sidebar = document.getElementById('sidebar');
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const sidebarBackdrop = document.getElementById('sidebar-backdrop');
  const sidebarMobileClose = document.getElementById('sidebar-mobile-close');
  const logoImg = document.getElementById('sidebar-logo-img');
  const logoText = document.getElementById('sidebar-logo-text');
  const upgradeExpanded = document.getElementById('upgrade-expanded');
  const upgradeBtnExpanded = document.getElementById('upgrade-btn-expanded');
  const upgradeBtnCollapsed = document.getElementById('upgrade-btn-collapsed');
  const mq = window.matchMedia('(max-width: 1024px)');

  function updateSidebarText() {
    const sidebarTexts = sidebar.querySelectorAll('.sidebar-text');
    if (sidebar.classList.contains('sidebar-collapsed')) {
      sidebarTexts.forEach(el => el.style.display = 'none');
    } else {
      sidebarTexts.forEach(el => el.style.display = '');
    }
  }
  function updateUpgradeSection() {
    if (sidebar.classList.contains('sidebar-collapsed')) {
      upgradeExpanded.style.display = 'none';
      upgradeBtnExpanded.style.display = 'none';
      upgradeBtnCollapsed.classList.remove('hidden');
    } else {
      upgradeExpanded.style.display = '';
      upgradeBtnExpanded.style.display = '';
      upgradeBtnCollapsed.classList.add('hidden');
    }
  }
  function updateLogo() {
    if (sidebar.classList.contains('sidebar-collapsed')) {
      logoImg.classList.remove('hidden');
      logoText.classList.add('hidden');
    } else {
      logoImg.classList.add('hidden');
      logoText.classList.remove('hidden');
    }
  }
  function openSidebarMobile() {
    sidebar.classList.add('open');
    sidebarBackdrop.classList.remove('hidden');
    sidebar.classList.remove('sidebar-collapsed');
    updateSidebarText();
    updateUpgradeSection();
    updateLogo();
    if (sidebarMobileClose) sidebarMobileClose.style.display = '';
  }
  function closeSidebarMobile() {
    sidebar.classList.remove('open');
    sidebarBackdrop.classList.add('hidden');
    sidebar.classList.add('sidebar-collapsed');
    updateSidebarText();
    updateUpgradeSection();
    updateLogo();
    if (sidebarMobileClose) sidebarMobileClose.style.display = 'none';
  }
  function toggleSidebarDesktop() {
    sidebar.classList.toggle('sidebar-collapsed');
    updateSidebarText();
    updateUpgradeSection();
    updateLogo();
  }
  function handleSidebarToggle() {
    if (mq.matches) {
      if (!sidebar.classList.contains('open')) {
        openSidebarMobile();
      } else {
        closeSidebarMobile();
      }
    } else {
      toggleSidebarDesktop();
    }
  }
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', handleSidebarToggle);
  }
  if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', closeSidebarMobile);
  }
  if (sidebarMobileClose) {
    sidebarMobileClose.addEventListener('click', closeSidebarMobile);
  }
  function handleResize() {
    if (mq.matches) {
      sidebar.classList.add('sidebar-collapsed');
      sidebar.classList.remove('open');
      sidebarBackdrop.classList.add('hidden');
      if (sidebarMobileClose) sidebarMobileClose.style.display = 'none';
    } else {
      sidebar.classList.remove('open');
      sidebarBackdrop.classList.add('hidden');
      if (sidebarMobileClose) sidebarMobileClose.style.display = 'none';
    }
    updateSidebarText();
    updateUpgradeSection();
    updateLogo();
  }
  window.addEventListener('resize', handleResize);
  // Initial state
  handleResize();
  updateSidebarText();
  updateUpgradeSection();
  updateLogo();
});
</script>
<!-- Main content -->
<div class="flex-1 flex flex-col min-h-screen">
  <!-- Header -->
  <header class="bg-white shadow-sm sticky top-0 z-10">
    <div class="flex items-center justify-between px-6 py-4">
      <div class="flex items-center">
        <!-- Hamburger menu for mobile/desktop collapse (move here, match Dashboard-home.html) -->
        <button id="sidebar-toggle" class="p-2 rounded-full hover:bg-gray-100 mr-4">
          <i class="ri-menu-line"></i>
        </button>
        <span class="hidden lg:block font-semibold text-xl text-primary">Settings</span>
      </div>
      <div class="flex items-center space-x-4">
        <div class="flex items-center bg-gray-100 rounded-full px-3 py-1">
          <div class="w-5 h-5 flex items-center justify-center mr-2 text-primary">
            <i class="ri-coin-line"></i>
          </div>
          <span class="text-sm font-medium">1,000 credits</span>
        </div>
        <a href="Dashboard-pay.html">
          <button class="bg-primary text-white py-2 px-4 rounded-button whitespace-nowrap hover:bg-blue-600 transition-colors">
            <span>Upgrade</span>
          </button>
        </a>
        <div class="dropdown">
          <button class="flex items-center space-x-2 focus:outline-none">
            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
              <img src="" alt="User avatar" class="w-full h-full object-cover">
            </div>
            <i class="ri-arrow-down-s-line text-gray-500"></i>
          </button>
          <div class="dropdown-content right-0 mt-2">
            <a href="#">Profile</a>
            <a href="#">Settings</a>
            <a href="logout.php">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>
  <!-- Main content scrollable area -->
<main class="flex-1 overflow-y-auto p-6">
<div class="max-w-5xl mx-auto">
<h1 class="text-2xl font-bold text-gray-900 mb-6">Settings</h1>
<!-- Profile Image Section -->
<section class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
<div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
<h2 class="text-lg font-medium text-gray-900">Profile Image</h2>
<button class="flex items-center text-gray-500" id="toggle-profile">
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Profile Image Section collapse/expand
  const toggleProfile = document.getElementById('toggle-profile');
  const profileContent = document.getElementById('profile-content');
  const profileIcon = document.getElementById('profile-icon');
  if (toggleProfile && profileContent && profileIcon) {
    toggleProfile.addEventListener('click', function() {
      if (profileContent.classList.contains('hidden')) {
        profileContent.classList.remove('hidden');
        profileIcon.classList.remove('ri-arrow-down-s-line');
        profileIcon.classList.add('ri-arrow-up-s-line');
      } else {
        profileContent.classList.add('hidden');
        profileIcon.classList.remove('ri-arrow-up-s-line');
        profileIcon.classList.add('ri-arrow-down-s-line');
      }
    });
  }
});
</script>
<div class="w-5 h-5 flex items-center justify-center">
<i class="ri-arrow-up-s-line" id="profile-icon"></i>
</div>
</button>
</div>
<div class="p-6" id="profile-content">
<div class="flex flex-col md:flex-row items-center gap-8">
<div class="w-40 h-40 rounded-full bg-gray-100 overflow-hidden flex items-center justify-center relative">
  <img src="" alt="Current profile" class="w-full h-full object-cover" id="profile-preview">
</div>
<div class="flex-1">
<div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition-colors cursor-pointer" id="dropzone">
<div class="w-12 h-12 mx-auto mb-4 flex items-center justify-center text-gray-400">
<i class="ri-upload-cloud-line ri-2x"></i>
</div>
<p class="text-sm text-gray-600 mb-2">Drag and drop your image here, or</p>
<button type="button" class="px-4 py-2 bg-primary text-white rounded-button text-sm font-medium hover:bg-blue-600 transition-colors whitespace-nowrap" id="choose-file-btn">Choose File</button>
<input type="file" id="profile-upload" class="hidden" accept=".jpg,.jpeg,.png,.webp">
</div>
<div class="mt-4 flex justify-end">
<button class="px-4 py-2 bg-primary text-white rounded-button text-sm font-medium hover:bg-blue-600 transition-colors whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed" id="save-profile" disabled>Save Changes</button>
</div>
</div>
</div>
</div>
</section>
<!-- Account Information Section -->
<section class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
<div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
<h2 class="text-lg font-medium text-gray-900">Account Information</h2>
<button class="flex items-center text-gray-500" id="toggle-account">
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Account Information Section collapse/expand
  const toggleAccount = document.getElementById('toggle-account');
  const accountContent = document.getElementById('account-content');
  const accountIcon = document.getElementById('account-icon');
  if (toggleAccount && accountContent && accountIcon) {
    toggleAccount.addEventListener('click', function() {
      if (accountContent.classList.contains('hidden')) {
        accountContent.classList.remove('hidden');
        accountIcon.classList.remove('ri-arrow-down-s-line');
        accountIcon.classList.add('ri-arrow-up-s-line');
      } else {
        accountContent.classList.add('hidden');
        accountIcon.classList.remove('ri-arrow-up-s-line');
        accountIcon.classList.add('ri-arrow-down-s-line');
      }
    });
  }
});
</script>
<div class="w-5 h-5 flex items-center justify-center">
<i class="ri-arrow-up-s-line" id="account-icon"></i>
</div>
</button>
</div>
<div class="p-6" id="account-content">
<form id="account-form">
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
<div>
<label for="full-name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
<input type="text" id="full-name" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition" value="Jonathan Wilson">
</div>
<div>
<label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
<input type="email" id="email" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition" value="jonathan.wilson@example.com">
</div>
<div>
<label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number (optional)</label>
<input type="tel" id="phone" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition" value="+1 (555) 123-4567">
</div>
</div>
<div class="border-t border-gray-100 pt-6 mt-6">
<h3 class="text-md font-medium text-gray-900 mb-4">Change Password</h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div>
<label for="current-password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
<input type="password" id="current-password" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
</div>
<div></div>
<div>
<label for="new-password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
<input type="password" id="new-password" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
<div class="mt-1 h-1 w-full bg-gray-200 rounded-full overflow-hidden">
<div class="h-full bg-gray-400 rounded-full" style="width: 0%" id="password-strength"></div>
</div>
</div>
<div>
<label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
<input type="password" id="confirm-password" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
</div>
</div>
</div>
<div class="border-t border-gray-100 pt-6 mt-6">
<h3 class="text-md font-medium text-gray-900 mb-4">Notifications</h3>
<div class="flex items-center justify-between py-2">
<div>
<p class="text-sm font-medium text-gray-700">Email Notifications</p>
<p class="text-xs text-gray-500">Receive emails about your account activity</p>
</div>
<label class="custom-switch">
<input type="checkbox" checked>
<span class="switch-slider"></span>
</label>
</div>
<div class="flex items-center justify-between py-2">
<div>
<p class="text-sm font-medium text-gray-700">Marketing Emails</p>
<p class="text-xs text-gray-500">Receive emails about new features and offers</p>
</div>
<label class="custom-switch">
<input type="checkbox">
<span class="switch-slider"></span>
</label>
</div>
</div>
<div class="flex justify-end space-x-3 mt-6">
<button type="button" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-button text-sm font-medium hover:bg-gray-50 transition-colors whitespace-nowrap">Cancel</button>
<button type="submit" class="px-4 py-2 bg-primary text-white rounded-button text-sm font-medium hover:bg-blue-600 transition-colors whitespace-nowrap">Save Changes</button>
</div>
</form>
</div>
</section>
<!-- Security Section -->
<section class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
<div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
<h2 class="text-lg font-medium text-gray-900">Security & Access</h2>
<button class="flex items-center text-gray-500" id="toggle-security">
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Security & Access Section collapse/expand
  const toggleSecurity = document.getElementById('toggle-security');
  const securityContent = document.getElementById('security-content');
  const securityIcon = document.getElementById('security-icon');
  if (toggleSecurity && securityContent && securityIcon) {
    toggleSecurity.addEventListener('click', function() {
      if (securityContent.classList.contains('hidden')) {
        securityContent.classList.remove('hidden');
        securityIcon.classList.remove('ri-arrow-down-s-line');
        securityIcon.classList.add('ri-arrow-up-s-line');
      } else {
        securityContent.classList.add('hidden');
        securityIcon.classList.remove('ri-arrow-up-s-line');
        securityIcon.classList.add('ri-arrow-down-s-line');
      }
    });
  }
});
</script>
<div class="w-5 h-5 flex items-center justify-center">
<i class="ri-arrow-down-s-line" id="security-icon"></i>
</div>
</button>
</div>
<div class="p-6 hidden" id="security-content">
  <!-- 2FA SECTION -->
<div class="mb-6">
<h3 class="text-md font-medium text-gray-900 mb-4">Two-Factor Authentication</h3>
<div class="flex items-center justify-between py-2 border-b border-gray-100">
<div>
<p class="text-sm font-medium text-gray-700">Two-Factor Authentication</p>
<p class="text-xs text-gray-500">Add an extra layer of security to your account</p>
</div>
<label class="custom-switch">
<input type="checkbox" id="2fa-toggle">
<span class="switch-slider"></span>
</label>
</div>
<div class="py-4 hidden" id="2fa-setup">
<div class="bg-gray-50 p-4 rounded-lg mb-4">
<h4 class="text-sm font-medium text-gray-900 mb-2">Setup Instructions</h4>
<ol class="text-xs text-gray-600 space-y-2 pl-4 list-decimal">
<li>Download an authenticator app like Google Authenticator or Authy</li>
<li>Scan the QR code below with the app</li>
<li>Enter the 6-digit code from the app to verify</li>
</ol>
</div>
<div class="flex flex-col md:flex-row md:items-center gap-6 mb-4">
<div class="w-40 h-40 bg-white border border-gray-200 rounded-lg flex items-center justify-center p-2">
<img src="https://readdy.ai/api/search-image?query=QR%20code%20on%20white%20background%2C%20simple%20black%20and%20white%20QR%20code%20pattern%2C%20clear%20and%20scannable&width=150&height=150&seq=3&orientation=squarish" alt="2FA QR Code" class="w-full h-full object-contain">
</div>
<div class="flex-1">
<p class="text-sm text-gray-600 mb-4">After scanning the QR code, enter the 6-digit verification code from your authenticator app:</p>
<div class="flex space-x-2 mb-4">
<input type="text" maxlength="6" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition" placeholder="000000">
</div>
<button class="px-4 py-2 bg-primary text-white rounded-button text-sm font-medium hover:bg-blue-600 transition-colors whitespace-nowrap">Verify & Enable</button>
</div>
</div>
<div class="border-t border-gray-100 pt-4">
<h4 class="text-sm font-medium text-gray-900 mb-2">Backup Codes</h4>
<p class="text-xs text-gray-600 mb-3">Save these backup codes in a secure place. You can use them to sign in if you lose access to your authenticator app.</p>
<div class="grid grid-cols-2 md:grid-cols-5 gap-2 mb-3">
<div class="bg-gray-50 p-2 rounded text-xs font-mono text-center">ABCD-1234</div>
<div class="bg-gray-50 p-2 rounded text-xs font-mono text-center">EFGH-5678</div>
<div class="bg-gray-50 p-2 rounded text-xs font-mono text-center">IJKL-9012</div>
<div class="bg-gray-50 p-2 rounded text-xs font-mono text-center">MNOP-3456</div>
<div class="bg-gray-50 p-2 rounded text-xs font-mono text-center">QRST-7890</div>
</div>
<button class="text-sm text-primary hover:text-blue-700 font-medium">Download Codes</button>
</div>
</div>
</div>
<div>
  <h3 class="text-md font-medium text-gray-900 mb-4">Active Sessions</h3>
  <div class="space-y-4">
    <?php foreach ($sessions as $session): ?>
    <div class="border border-gray-200 rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between session-card" data-session="<?= htmlspecialchars($session['session_id']) ?>">
      <div class="flex items-center">
        <div class="w-10 h-10 flex items-center justify-center mr-3 text-gray-500 bg-gray-100 rounded-full">
          <i class="ri-tablet-line"></i>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-900">
            <?= ($session['session_id'] === session_id()) ? 'Current Device' : 'Other Device' ?>
          </p>
          <p class="text-xs text-gray-500">
            <?= htmlspecialchars($session['user_agent']) ?>
          </p>
          <p class="text-xs text-gray-500">
            <?= htmlspecialchars($session['city']) ?>, <?= htmlspecialchars($session['country']) ?>
          </p>
          <p class="text-xs text-gray-500">
            IP: <?= htmlspecialchars($session['ip_address']) ?> • Last active: <?= htmlspecialchars($session['last_active']) ?>
          </p>
        </div>
      </div>
      <div class="mt-3 md:mt-0">
        <?php if ($session['session_id'] !== session_id()): ?>
        <button type="button"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-button text-xs font-medium hover:bg-gray-50 transition-colors whitespace-nowrap"
                onclick="signOutSession('<?= htmlspecialchars($session['session_id']) ?>', this)">
          Sign Out
        </button>
        <?php else: ?>
        <span class="px-4 py-2 border border-gray-300 text-gray-500 rounded-button text-xs font-medium">Active</span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-4">
    <button type="button"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-button text-sm font-medium hover:bg-gray-50 transition-colors whitespace-nowrap"
            onclick="signOutAll(this)">
      Sign Out From All Devices
    </button>
  </div>
</div>

<script>
function signOutSession(sessionId, btn) {
  btn.disabled = true;
  fetch('signout_session.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'session_id=' + encodeURIComponent(sessionId)
  })
  .then(res => res.text())
  .then(() => {
    // Remove the session card
    const card = btn.closest('.session-card');
    if (card) card.remove();
  })
  .catch(() => {
    alert('Failed to sign out session.');
    btn.disabled = false;
  });
}

function signOutAll(btn) {
  btn.disabled = true;
  fetch('signout_all.php', {
    method: 'POST'
  })
  .then(res => res.text())
  .then(() => {
    // Redirect or clear all sessions visually
    location.href = '<?= BASE_URL ?>signup.html?form=login&status=all_signed_out';
  })
  .catch(() => {
    alert('Failed to sign out from all devices.');
    btn.disabled = false;
  });
}
</script>

</div>
</section>
<!-- Danger Zone Section -->
<section class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
<div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
<h2 class="text-lg font-medium text-gray-900">Danger Zone</h2>
<button class="flex items-center text-gray-500" id="toggle-danger">
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Danger Zone Section collapse/expand
  const toggleDanger = document.getElementById('toggle-danger');
  const dangerContent = document.getElementById('danger-content');
  const dangerIcon = document.getElementById('danger-icon');
  if (toggleDanger && dangerContent && dangerIcon) {
    toggleDanger.addEventListener('click', function() {
      if (dangerContent.classList.contains('hidden')) {
        dangerContent.classList.remove('hidden');
        dangerIcon.classList.remove('ri-arrow-down-s-line');
        dangerIcon.classList.add('ri-arrow-up-s-line');
      } else {
        dangerContent.classList.add('hidden');
        dangerIcon.classList.remove('ri-arrow-up-s-line');
        dangerIcon.classList.add('ri-arrow-down-s-line');
      }
    });
  }
});
</script>
<div class="w-5 h-5 flex items-center justify-center">
<i class="ri-arrow-down-s-line" id="danger-icon"></i>
</div>
</button>
</div>
<div class="p-6 hidden" id="danger-content">
<div class="border border-red-200 rounded-lg p-6 bg-red-50">
<div class="flex items-start mb-4">
<div class="w-10 h-10 flex items-center justify-center mr-3 text-red-500">
<i class="ri-alert-line ri-lg"></i>
</div>
<div>
<h3 class="text-lg font-medium text-center mb-1">Delete Account</h3>
<p class="text-sm text-gray-600 text-center">Once you delete your account, there is no going back. This action is permanent and cannot be undone.</p>
</div>
</div>
<button class="px-4 py-2 bg-red-600 text-white rounded-button text-sm font-medium hover:bg-red-700 transition-colors whitespace-nowrap" id="delete-account-btn">Delete Account</button>
</div>
</div>
</section>


<!-- Delete Account Modal -->
<div class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" id="delete-modal">
<div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
<div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
<h3 class="text-lg font-medium text-gray-900">Delete Account</h3>
<button class="text-gray-500 hover:text-gray-700" id="close-delete-modal">
<div class="w-5 h-5 flex items-center justify-center">
<i class="ri-close-line"></i>
</div>
</button>
</div>
<div class="p-6">
<div class="mb-4">
<div class="w-12 h-12 mx-auto mb-4 flex items-center justify-center text-red-500 bg-red-100 rounded-full">
<i class="ri-delete-bin-line ri-xl"></i>
</div>
<h4 class="text-lg font-medium text-center mb-2">Are you absolutely sure?</h4>
<p class="text-sm text-gray-600 text-center">This action cannot be undone. This will permanently delete your account and remove all your data from our servers.</p>
</div>
<div class="mb-4">
<label for="delete-confirm" class="block text-sm font-medium text-gray-700 mb-1">Please type "delete my account" to confirm</label>
<input type="text" id="delete-confirm" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition" placeholder="delete my account">
</div>
<div class="mb-4">
<label for="delete-password" class="block text-sm font-medium text-gray-700 mb-1">Enter your password</label>
<input type="password" id="delete-password" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
</div>
<div class="flex justify-end space-x-3">
<button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-button text-sm font-medium hover:bg-gray-50 transition-colors whitespace-nowrap" id="cancel-delete-modal">Cancel</button>
<button class="px-4 py-2 bg-red-600 text-white rounded-button text-sm font-medium hover:bg-red-700 transition-colors whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed" id="confirm-delete-btn" disabled>Delete Account</button>
</div>
</div>
</div>
</div>


<!-- Add Payment Modal (hidden by default) -->
<div class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" id="payment-modal">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
      <h3 class="text-lg font-medium text-gray-900">Add Crypto Wallet</h3>
      <button class="text-gray-500 hover:text-gray-700" id="close-payment-modal">
        <div class="w-5 h-5 flex items-center justify-center">
          <i class="ri-close-line"></i>
        </div>
      </button>
    </div>
    <div class="p-6">
      <form id="payment-form">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="crypto-currency" class="block text-sm font-medium text-gray-700 mb-1">Cryptocurrency</label>
            <select id="crypto-currency" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition bg-white" required>
              <option value="">Select Cryptocurrency</option>
              <option value="BTC">Bitcoin (BTC)</option>
              <option value="ETH">Ethereum (ETH)</option>
              <option value="USDT">Tether (USDT)</option>
            </select>
          </div>
          <div>
            <label for="blockchain-network" class="block text-sm font-medium text-gray-700 mb-1">Blockchain Network</label>
            <select id="blockchain-network" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition bg-white" required>
              <option value="">Select Network</option>
              <option value="bitcoin">Bitcoin</option>
              <option value="ethereum">Ethereum</option>
              <option value="bnb">BNB Chain</option>
              <option value="polygon">Polygon</option>
            </select>
          </div>
          <div class="md:col-span-2">
            <label for="wallet-address" class="block text-sm font-medium text-gray-700 mb-1">Wallet Address</label>
            <input type="text" id="wallet-address" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition" placeholder="Paste your wallet address" required>
            <p class="text-xs text-red-500 mt-1 hidden" id="wallet-error"></p>
          </div>
          <div>
            <label for="wallet-label" class="block text-sm font-medium text-gray-700 mb-1">Label / Nickname <span class="text-gray-400 text-xs">(optional)</span></label>
            <input type="text" id="wallet-label" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition" placeholder="e.g. My Main Wallet">
          </div>
          <div>
            <label for="wallet-type" class="block text-sm font-medium text-gray-700 mb-1">Wallet Type</label>
            <select id="wallet-type" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition bg-white" required>
              <option value="">Select Type</option>
              <option value="personal">Personal</option>
              <option value="business">Business</option>
            </select>
          </div>
          <div class="md:col-span-2">
            <label for="billing-frequency" class="block text-sm font-medium text-gray-700 mb-1">Billing Frequency</label>
            <select id="billing-frequency" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary outline-none transition bg-white" required>
              <option value="">Select Frequency</option>
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="annually">Annually</option>
            </select>
          </div>
        </div>
        <div class="mt-4 mb-2">
          <div class="flex items-center cursor-pointer select-none" id="terms-toggle">
            <div class="custom-checkbox" id="terms-checkbox"></div>
            <label for="terms-checkbox" class="ml-2 text-sm text-gray-700 select-none cursor-pointer">
              I agree to the <a href="#" class="text-primary underline">Terms &amp; Conditions</a>
            </label>
          </div>
        </div>
        <div class="flex justify-end space-x-3 mt-4"></div>
          <button type="button" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-button text-sm font-medium hover:bg-gray-50 transition-colors whitespace-nowrap" id="cancel-payment-modal">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-primary text-white rounded-button text-sm font-medium hover:bg-blue-600 transition-colors whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed" id="save-wallet-btn" disabled>Save Wallet</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
  // Fetch user settings data
  fetch('../home/update_settings.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const userData = data.data.user;
        
        // Update both profile images
        const profilePreview = document.getElementById('profile-preview');
        const navbarProfile = document.querySelector('.dropdown img');
        
        function updateProfileImage(element) {
          if (element) {
            if (userData.profile_picture) {
              // Add error handling for the image
              element.onerror = function() {
                // If image fails to load, use the fallback avatar with initials
                this.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(userData.full_name)}&background=1E3A8A&color=fff&length=1&size=400`;
              };
              element.src = userData.profile_picture;
            } else {
              // Use initials avatar if no profile picture
              element.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(userData.full_name)}&background=1E3A8A&color=fff&length=1&size=400`;
            }
          }
        }

        // Update both profile images
        updateProfileImage(profilePreview);
        updateProfileImage(navbarProfile);

        // Update account information form
        const fullNameInput = document.getElementById('full-name');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');

        if (fullNameInput) fullNameInput.value = userData.full_name || '';
        if (emailInput) emailInput.value = userData.email || '';
        if (phoneInput) phoneInput.value = userData.phone || '';

        // Update credits display
        const creditsDisplay = document.querySelector('.text-sm.font-medium');
        if (creditsDisplay) {
          creditsDisplay.textContent = `${data.data.credits} credits`;
        }

        // Update active sessions
       /* const sessionsContainer = document.querySelector('.space-y-4');
        if (sessionsContainer && data.data.sessions) {
          const currentSession = data.data.sessions[0]; // Most recent session
          if (currentSession) {
            const sessionElement = sessionsContainer.querySelector('.bg-blue-50');
            if (sessionElement) {
              const deviceInfo = sessionElement.querySelector('.text-xs.text-gray-500');
              if (deviceInfo) {
                deviceInfo.textContent = `${currentSession.device} • ${currentSession.browser} • ${currentSession.location}`;
              }
              const ipInfo = deviceInfo.nextElementSibling;
              if (ipInfo) {
                ipInfo.textContent = `IP: ${currentSession.ip_address} • Last active: ${formatLastActive(currentSession.last_activity)}`;
              }
            }
          }
        }*/

        // Update subscription info if available
        /*if (data.data.subscription) {
          const subscription = data.data.subscription;
          const planName = document.querySelector('.text-xs.text-gray-500');
          if (planName) {
            planName.textContent = `of ${subscription.plan_name}`;
          }
        }*/
      } else {
        console.error('Failed to fetch user data:', data.message);
      }
    })
    .catch(error => {
      console.error('Error fetching user data:', error);
    });

  // Helper function to format last active time
  function formatLastActive(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return `${Math.floor(diff/60000)} minutes ago`;
    if (diff < 86400000) return `${Math.floor(diff/3600000)} hours ago`;
    return `${Math.floor(diff/86400000)} days ago`;
  }

  // Profile image upload handling
  const profileUpload = document.getElementById('profile-upload');
  const chooseFileBtn = document.getElementById('choose-file-btn');
  const dropzone = document.getElementById('dropzone');
  const saveProfileBtn = document.getElementById('save-profile');

  if (profileUpload && chooseFileBtn && dropzone && saveProfileBtn) {
    // Handle choose file button click
    chooseFileBtn.addEventListener('click', function() {
      profileUpload.click();
    });

    // Handle file selection
    profileUpload.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        handleProfileFile(this.files[0]);
      }
    });

    // Handle drag and drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
      dropzone.addEventListener(eventName, () => dropzone.classList.add('border-primary'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, () => dropzone.classList.remove('border-primary'), false);
    });

    dropzone.addEventListener('drop', function(e) {
      const dt = e.dataTransfer;
      if (dt && dt.files && dt.files[0]) {
        handleProfileFile(dt.files[0]);
      }
    });

    function handleProfileFile(file) {
      const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
      if (!validTypes.includes(file.type)) {
        showToast('Please select a valid image file (JPG, PNG, or WEBP)', 'error');
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        showToast('File size exceeds 5MB limit', 'error');
        return;
      }

      const reader = new FileReader();
      reader.onload = function(e) {
        const profilePreview = document.getElementById('profile-preview');
        if (profilePreview) {
        profilePreview.src = e.target.result;
        }
        saveProfileBtn.disabled = false;
      };
      reader.readAsDataURL(file);
    }

    // Save profile button
    saveProfileBtn.addEventListener('click', function() {
      const profilePreview = document.getElementById('profile-preview');
      const navbarProfile = document.querySelector('.dropdown img');
      if (!profilePreview) return;

      saveProfileBtn.disabled = true;
      const originalText = saveProfileBtn.innerHTML;
      saveProfileBtn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2 inline-block"></div> Saving...';

      // Create form data
      const formData = new FormData();
      formData.append('profile_picture', profilePreview.src);

      // Send to server
      fetch('../home/update_profile.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Profile picture updated successfully', 'success');
          // Update navbar profile picture as well
          if (navbarProfile) {
            navbarProfile.src = profilePreview.src;
          }
        } else {
          showToast(data.message || 'Failed to update profile picture', 'error');
          // If update fails, revert to initials
          const initialsUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(userData.full_name)}&background=1E3A8A&color=fff&length=1&size=400`;
          profilePreview.src = initialsUrl;
          if (navbarProfile) {
            navbarProfile.src = initialsUrl;
          }
        }
      })
      .catch(error => {
        showToast('Error updating profile picture', 'error');
        console.error('Error:', error);
        // If error occurs, revert to initials
        const initialsUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(userData.full_name)}&background=1E3A8A&color=fff&length=1&size=400`;
        profilePreview.src = initialsUrl;
        if (navbarProfile) {
          navbarProfile.src = initialsUrl;
        }
      })
      .finally(() => {
        saveProfileBtn.innerHTML = originalText;
        saveProfileBtn.disabled = true;
      });
    });
  }

  // Toast notification function
  function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${
      type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } shadow-lg z-50`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
      toast.remove();
    }, 3000);
  }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Account Information Section functionality
  const accountForm = document.getElementById('account-form');
  const fullNameInput = document.getElementById('full-name');
  const emailInput = document.getElementById('email');
  const phoneInput = document.getElementById('phone');
  const currentPasswordInput = document.getElementById('current-password');
  const newPasswordInput = document.getElementById('new-password');
  const confirmPasswordInput = document.getElementById('confirm-password');
  const passwordStrengthBar = document.getElementById('password-strength');

  // Password strength check
  if (newPasswordInput && passwordStrengthBar) {
    newPasswordInput.addEventListener('input', function() {
      const val = newPasswordInput.value;
      let strength = 0;
      if (val.length >= 8) strength += 1;
      if (/[A-Z]/.test(val)) strength += 1;
      if (/[0-9]/.test(val)) strength += 1;
      if (/[^A-Za-z0-9]/.test(val)) strength += 1;
      let percent = [0, 25, 50, 75, 100][strength];
      passwordStrengthBar.style.width = percent + '%';
      passwordStrengthBar.className = 'h-full rounded-full ' +
        (percent < 50 ? 'bg-gray-400' : percent < 75 ? 'bg-yellow-400' : 'bg-green-500');
    });
  }

  // Form validation and submission
  if (accountForm) {
    accountForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Basic validation
      let errors = [];
      if (!fullNameInput.value.trim()) errors.push('Full Name is required.');
      if (!emailInput.value.trim() || !/^[^@]+@[^@]+\.[^@]+$/.test(emailInput.value)) errors.push('Valid Email is required.');
      
      if (newPasswordInput.value || confirmPasswordInput.value) {
        if (newPasswordInput.value.length < 8) errors.push('New password must be at least 8 characters.');
        if (newPasswordInput.value !== confirmPasswordInput.value) errors.push('Passwords do not match.');
      }

      if (errors.length > 0) {
        showToast(errors.join('\n'), 'error');
        return;
      }

      // Create form data
      const formData = new FormData();
      formData.append('full_name', fullNameInput.value.trim());
      formData.append('email', emailInput.value.trim());
      formData.append('phone', phoneInput.value.trim());
      
      if (currentPasswordInput.value) {
        formData.append('current_password', currentPasswordInput.value);
      }
      if (newPasswordInput.value) {
        formData.append('new_password', newPasswordInput.value);
      }

      // Show loading state
      const submitBtn = accountForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2 inline-block"></div> Saving...';

      // Send update request
      fetch('../home/update_user_details.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Account information updated successfully!', 'success');
          // Reset password fields
          currentPasswordInput.value = '';
          newPasswordInput.value = '';
          confirmPasswordInput.value = '';
          passwordStrengthBar.style.width = '0%';
          
          // Update profile picture if it was changed
          if (data.data.profile_picture) {
            const profilePreview = document.getElementById('profile-preview');
            if (profilePreview) {
              profilePreview.src = data.data.profile_picture;
            }
          }
        } else {
          showToast(data.message || 'Failed to update account information', 'error');
        }
      })
      .catch(error => {
        showToast('Error updating account information', 'error');
        console.error('Error:', error);
      })
      .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      });
    });
  }

  // Modal for Account Info Section
  // Reuse the profile modal if present, else create a new one
  let accountModal = document.getElementById('account-success-modal');
  if (!accountModal) {
    accountModal = document.createElement('div');
    accountModal.id = 'account-success-modal';
    accountModal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden';
    accountModal.innerHTML = `
      <div class="bg-white rounded-lg shadow-xl max-w-xs w-full p-6 text-center">
        <div class="w-12 h-12 mx-auto mb-4 flex items-center justify-center text-green-500 bg-green-100 rounded-full">
          <i class="ri-checkbox-circle-line ri-2x"></i>
        </div>
        <h4 class="text-lg font-medium mb-2">Success</h4>
        <p class="text-sm text-gray-600 mb-4"></p>
        <button id="close-account-success-modal" class="px-4 py-2 bg-primary text-white rounded-button text-sm font-medium hover:bg-blue-600 transition-colors">OK</button>
      </div>
    `;
    document.body.appendChild(accountModal);
  }
  function showAccountModal(message, success) {
    const icon = accountModal.querySelector('i');
    const title = accountModal.querySelector('h4');
    const desc = accountModal.querySelector('p');
    if (success) {
      icon.className = 'ri-checkbox-circle-line ri-2x';
      icon.parentElement.className = 'w-12 h-12 mx-auto mb-4 flex items-center justify-center text-green-500 bg-green-100 rounded-full';
      title.textContent = 'Success';
      desc.textContent = message;
    } else {
      icon.className = 'ri-close-circle-line ri-2x';
      icon.parentElement.className = 'w-12 h-12 mx-auto mb-4 flex items-center justify-center text-red-500 bg-red-100 rounded-full';
      title.textContent = 'Error';
      desc.textContent = message;
    }
    accountModal.classList.remove('hidden');
  }
  accountModal.addEventListener('click', function(e) {
    if (e.target.id === 'close-account-success-modal' || e.target === accountModal) {
      accountModal.classList.add('hidden');
    }
  });
});
</script>
</body>
</html>