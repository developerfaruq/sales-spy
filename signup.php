<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Authentication</title>
  <script src="https://cdn.tailwindcss.com/3.4.16"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#1E3A8A',
            secondary: '#F2E49C'
          },
          borderRadius: {
            'none': '0px',
            'sm': '4px',
            DEFAULT: '8px',
            'md': '12px',
            'lg': '16px',
            'xl': '20px',
            '2xl': '24px',
            '3xl': '32px',
            'full': '9999px',
            'button': '8px'
          }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #F9FBF4 0%, #F5F7F0 100%);
      min-height: 100vh;
      overflow-x: hidden;
    }
    .image-transition {
      transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
      transform-origin: center;
    }
    .image-transition.fade-out {
      opacity: 0;
      transform: scale(1.1);
    }
    .image-transition.fade-in {
      opacity: 1;
      transform: scale(1);
    }

    .popup-alert {
  position: fixed;
  top: 20px;
  
  z-index: 9999;
  background-color: #eed7c5ff;
  color: black;
  padding: 15px 20px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  font-weight: 500;
  animation: fadeOut 0.5s ease-in-out forwards;
  animation-delay: 5s; /* will fade after 5 seconds */
}

@keyframes fadeOut {
  to {
    opacity: 0;
    transform: translateY(-10px);
    pointer-events: none;
  }
}


    .glass-effect {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
    }
    .input-group {
      position: relative;
    }
    .input-group input {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s;
      background-color: rgba(255, 255, 255, 0.5);
    }
    .input-group input:focus {
      border-color: #09906F;
      box-shadow: 0 0 0 2px rgba(9, 144, 111, 0.2);
      outline: none;
    }
    .input-group label {
      position: absolute;
      left: 1rem;
      top: 0.75rem;
      font-size: 1rem;
      color: #6b7280;
      pointer-events: none;
      transition: all 0.3s ease;
    }
    .input-group input:focus + label,
    .input-group input:not(:placeholder-shown) + label {
      top: -0.5rem;
      left: 0.75rem;
      font-size: 0.75rem;
      padding: 0 0.25rem;
      background-color: white;
      color: #09906F;
    }
    .social-btn {
      transition: all 0.3s;
    }
    .social-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .toggle-forms {
      overflow: hidden;
      position: relative;
    }
    .forms-wrapper {
      display: flex;
      transition: transform 0.5s ease-in-out;
      width: 200%;
    }
    .signin-form, .signup-form {
      width: 50%;
      transition: opacity 0.3s ease-in-out;
    }
    .hidden-form {
      opacity: 0;
      pointer-events: none;
    }
    .visible-form {
      opacity: 1;
      pointer-events: auto;
    }
    .custom-checkbox {
      appearance: none;
      width: 1.25rem;
      height: 1.25rem;
      border: 1px solid #d1d5db;
      border-radius: 4px;
      background-color: rgba(255, 255, 255, 0.5);
      cursor: pointer;
      position: relative;
    }
    .custom-checkbox:checked {
      background-color: #09906F;
      border-color: #09906F;
    }
    .custom-checkbox:checked::after {
      content: '';
      position: absolute;
      left: 0.4rem;
      top: 0.2rem;
      width: 0.4rem;
      height: 0.7rem;
      border: solid white;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg);
    }
    #toast {
      transition: opacity 0.5s, transform 0.5s;
      z-index: 9999;
    }
    #toast .glass-effect {
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(8px);
      border-radius: 16px;
      border-width: 1.5px;
    }
    @media (max-width: 640px) {
      .glass-effect {
        width: 90%;
        padding: 1.5rem;
      }
    }
  </style>
</head>
<body class="flex flex-col items-center justify-center p-4 md:p-8">
  <!-- error handling modal -->
<?php if (isset($_GET['status']) && $_GET['status'] === 'account_unlocked'): ?>
  <div id="BackAlert" class="popup-alert">
    Account unlocked please login again.
  </div>
<?php endif; ?>
 <?php if (isset($_GET['status']) && $_GET['status'] === 'ip_blocked'): ?>
  <div id="BackAlert" class="popup-alert">
    🚫 Only <strong>1 accounts</strong> are allowed per user. Further registrations are blocked for security reasons, please contact support for help.
  </div>
<?php endif; ?>
<?php if (isset($_GET['status']) && $_GET['status'] === 'duplicate_email_or_phone'): ?>
  <div id="BackAlert" class="popup-alert">
    Duplicate email or phone number.
  </div>
<?php endif; ?>
<?php if (isset($_GET['status']) && $_GET['status'] === 'database_error'): ?>
  <div id="BackAlert" class="popup-alert">
    we are currently experiencing some issues and our team are actively working on it , for more enquiry contact support.
  </div>
<?php endif; ?>
<?php if (isset($_GET['status']) && $_GET['status'] === 'invalid_email'): ?>
  <div id="BackAlert" class="popup-alert">
    Invalid email.
  </div>
<?php endif; ?>
<?php if (isset($_GET['status']) && $_GET['status'] === 'account_disabled'): ?>
  <div id="BackAlert" class="popup-alert">
    Account disabled, please contact support.
  </div>
<?php endif; ?>
<?php if (isset($_GET['status']) && $_GET['status'] === 'account_locked_pword'): ?>
  <div id="BackAlert" class="popup-alert">
    Account locked, please try again after 5 minutes.
  </div>
<?php endif; ?>
<?php if (isset($_GET['status']) && $_GET['status'] === 'invalid_credentials'): ?>
  <div id="BackAlert" class="popup-alert">
    Invalid password your account will be locked after 5 failed attempt.
  </div>
<?php endif; ?>
<?php if (isset($_GET['status']) && $_GET['status'] === 'user_not_found'): ?>
  <div id="BackAlert" class="popup-alert">
    invalid email, please try another email.
  </div>
<?php endif; ?>
<?php if (isset($_GET['status']) && $_GET['status'] === 'session_expired'): ?>
  <div id="BackAlert" class="popup-alert">
    session expired, please login.
  </div>
<?php endif; ?>

<?php if (isset($_GET['status']) && $_GET['status'] === 'all_signed_out'): ?>
  <div id="BackAlert" class="popup-alert">
    All session signed out.
  </div>
<?php endif; ?>

<?php if (isset($_GET['status']) && $_GET['status'] === 'session_revoked'): ?>
  <div id="BackAlert" class="popup-alert">
    you have been logged out.
  </div>
<?php endif; ?>
<script>
  setTimeout(() => {
    const alert = document.getElementById('BackAlert');
    if (alert) {
      alert.remove();
    }
  }, 6000); // remove after 6 seconds (matches fade delay + buffer)
</script>


  <div id="toast" class="fixed left-1/2 top-8 z-50 transform -translate-x-1/2 opacity-0 pointer-events-none transition-all duration-500 min-w-[260px] max-w-xs"></div>
  <div class="w-full max-w-4xl">
    <a href="index.html" class="inline-flex items-center text-gray-600 hover:text-primary transition-colors mb-6">
      <i class="ri-arrow-left-line mr-2"></i>
      <span>Back to Home</span>
    </a>
    <div class="flex flex-col md:flex-row w-full">
      <div class="w-full md:w-1/2 p-6 md:p-12 flex flex-col justify-center">
        <h1 class="text-3xl md:text-4xl font-bold mb-2 text-gray-800">Welcome Back</h1>
        <p class="text-gray-600 mb-8">Welcome to <span class="font-['Pacifico'] text-primary">Sales - Spy</span> – Seamless, secure, and smart access to your digital tools.</p>
        <div class="toggle-forms">
          <div class="forms-wrapper" id="formsWrapper">
            <!-- Sign In Form -->
            <form action="auth/login/" method="post" class="signin-form visible-form px-1" id="signinForm">
              <div class="space-y-4">
                <div class="input-group">
                  <input type="email" id="signin-email" name="email" placeholder=" " class="border-none">
                  <label for="signin-email">Email Address</label>
                </div>
                <div class="input-group">
                  <input type="password" id="signin-password" name="password" placeholder=" " class="border-none">
                  <label for="signin-password">Password</label>
                </div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="custom-checkbox mr-2">
                    <label for="remember" class="text-sm text-gray-600">Remember me</label>
                  </div>
                  <a href="javascript:void(0)" id="forgotPasswordLink" class="text-sm text-primary hover:underline">Forgot Password?</a>
                </div>
                <button type="submit" class="w-full bg-primary text-white py-3 !rounded-button font-medium hover:bg-opacity-90 transition-all whitespace-nowrap">Sign In</button>
                <div class="text-center">
                  <p class="text-gray-600">Don't have an account?
                    <a href="javascript:void(0)" id="showSignUp" class="text-primary hover:underline">Sign Up</a>
                  </p>
                </div>
              </div>
            </form>
            <!-- Sign Up Form -->
             

            <form action="auth/signup/" method="post" class="signup-form hidden-form px-1" id="signupForm">
              <div class="space-y-4">
                <div class="input-group">
                  <input type="text" id="fullname" name="fullname" placeholder=" " class="border-none">
                  <label for="fullname">Full Name</label>
                </div>
                <div class="input-group">
                  <input type="email" id="signup-email" name="email" placeholder=" " class="border-none">
                  <label for="signup-email">Email Address</label>
                </div>
                <div class="input-group">
                  <input type="tel" id="phone" name="phone" placeholder=" " class="border-none">
                  <label for="phone">Phone Number</label>
                </div>
                <div class="input-group">
                  <input type="password" id="signup-password" name="password" placeholder=" " class="border-none">
                  <label for="signup-password">Password</label>
                </div>
                <div class="input-group">
                  <input type="password" id="confirm-password" name="confirm-password" placeholder=" " class="border-none">
                  <label for="confirm-password">Confirm Password</label>
                </div>
                <div class="flex items-center">
                  <input type="checkbox" id="terms" name="terms" class="custom-checkbox mr-2">
                  <label for="terms" class="text-sm text-gray-600">I agree to the <a href="javascript:void(0)" class="text-primary hover:underline">Terms of Service</a> and <a href="javascript:void(0)" class="text-primary hover:underline">Privacy Policy</a></label>
                </div>
                <button type="submit" class="w-full bg-primary text-white py-3 !rounded-button font-medium hover:bg-opacity-90 transition-all whitespace-nowrap">Create Account</button>
                <div class="text-center">
                  <p class="text-gray-600">Already have an account?
                    <a href="javascript:void(0)" id="showSignIn" class="text-primary hover:underline">Sign In</a>
                  </p>
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="mt-6">
          <div class="flex items-center justify-center">
            <div class="flex-grow h-px bg-gray-300"></div>
            <span class="mx-4 text-sm text-gray-500">or continue with</span>
            <div class="flex-grow h-px bg-gray-300"></div>
          </div>
          <div class="grid grid-cols-4 gap-3 mt-4">
            <button class="social-btn flex items-center justify-center p-2 border border-gray-300 !rounded-button bg-white hover:bg-gray-50" aria-label="Sign in with Google">
              <div class="w-6 h-6 flex items-center justify-center">
                <i class="ri-google-fill text-[#DB4437] ri-lg"></i>
              </div>
            </button>
            <button class="social-btn flex items-center justify-center p-2 border border-gray-300 !rounded-button bg-white hover:bg-gray-50" aria-label="Sign in with LinkedIn">
              <div class="w-6 h-6 flex items-center justify-center">
                <i class="ri-linkedin-fill text-[#0077B5] ri-lg"></i>
              </div>
            </button>
            <button class="social-btn flex items-center justify-center p-2 border border-gray-300 !rounded-button bg-white hover:bg-gray-50" aria-label="Sign in with Facebook">
              <div class="w-6 h-6 flex items-center justify-center">
                <i class="ri-facebook-fill text-[#1877F2] ri-lg"></i>
              </div>
            </button>
            <button class="social-btn flex items-center justify-center p-2 border border-gray-300 !rounded-button bg-white hover:bg-gray-50" aria-label="Sign in with Apple">
              <div class="w-6 h-6 flex items-center justify-center">
                <i class="ri-apple-fill text-[#000000] ri-lg"></i>
              </div>
            </button>
          </div>
        </div>
      </div>
      <div class="hidden md:flex md:w-1/2 glass-effect rounded-xl overflow-hidden relative">
        <div class="w-full h-full absolute image-transition fade-in" id="signinImage" style="background-image: url('https://readdy.ai/api/search-image?query=A%20modern%20abstract%20digital%20illustration%20featuring%20deep%20navy%20blue%20as%20the%20dominant%20color%2C%20with%20subtle%20geometric%20patterns%20and%20flowing%20lines.%20The%20design%20incorporates%20lighter%20blue%20accents%20and%20white%20highlights%20creating%20a%20sense%20of%20depth%20and%20movement.%20The%20composition%20should%20be%20elegant%20and%20professional%2C%20with%20a%20subtle%20gradient%20effect%20that%20adds%20sophistication.%20The%20overall%20style%20should%20be%20minimalist%20yet%20engaging%2C%20perfect%20for%20a%20modern%20login%20interface.&width=600&height=800&seq=auth-bg-2&orientation=portrait'); background-size: cover; background-position: center;"></div>
        <div class="w-full h-full absolute image-transition fade-out" id="signupImage" style="background-image: url('https://readdy.ai/api/search-image?query=A%20contemporary%20abstract%20composition%20dominated%20by%20deep%20navy%20blue%20tones%2C%20featuring%20dynamic%20curved%20forms%20and%20crystalline%20structures.%20The%20design%20should%20incorporate%20subtle%20white%20and%20light%20blue%20highlights%20that%20create%20a%20sense%20of%20dimension%20and%20energy.%20Geometric%20patterns%20should%20flow%20organically%20across%20the%20composition%2C%20suggesting%20both%20stability%20and%20innovation.%20The%20style%20should%20be%20sophisticated%20and%20modern%2C%20ideal%20for%20a%20professional%20signup%20interface.&width=600&height=800&seq=auth-bg-3&orientation=portrait'); background-size: cover; background-position: center;"></div>
      </div>
    </div>
    <div class="mt-8 text-center text-gray-500 text-sm">
      <p>© 2025 <span class="font-['Pacifico'] text-primary">Sales - Spy</span>. All rights reserved.</p>
    </div>
  </div>
  <!-- Forgot Password Modal -->
  <div id="forgotPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4 relative">
      <div id="modalContent">
        <div id="resetForm" class="space-y-4">
          <h2 class="text-2xl font-semibold text-gray-800 mb-4">Reset Your Password</h2>
          <p class="text-gray-600 mb-4">Enter your email address and we'll send you instructions to reset your password.</p>
          <div class="input-group">
            <input type="email" id="reset-email" placeholder=" " class="border-none">
            <label for="reset-email">Email Address</label>
          </div>
          <div class="flex gap-3 mt-6">
            <button id="sendResetLink" class="flex-1 bg-primary text-white py-2 !rounded-button font-medium hover:bg-opacity-90 transition-all whitespace-nowrap">Send Reset Link</button>
            <button id="cancelReset" class="flex-1 border border-gray-300 text-gray-700 py-2 !rounded-button font-medium hover:bg-gray-50 transition-all whitespace-nowrap">Cancel</button>
          </div>
        </div>
        <div id="confirmationMessage" class="hidden text-center py-4">
          <i class="ri-mail-send-line text-primary ri-3x mb-4"></i>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Check Your Email</h3>
          <p class="text-gray-600 mb-4">We've sent password reset instructions to your email address.</p>
          <button id="closeConfirmation" class="w-full bg-primary text-white py-2 !rounded-button font-medium hover:bg-opacity-90 transition-all whitespace-nowrap">Close</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const showSignUp = document.getElementById('showSignUp');
      const showSignIn = document.getElementById('showSignIn');
      const signinForm = document.getElementById('signinForm');
      const signupForm = document.getElementById('signupForm');
      const formsWrapper = document.getElementById('formsWrapper');
      const signinImage = document.getElementById('signinImage');
      const signupImage = document.getElementById('signupImage');
      const toast = document.getElementById('toast');
      const forgotPasswordLink = document.getElementById('forgotPasswordLink');
      const forgotPasswordModal = document.getElementById('forgotPasswordModal');
      const resetForm = document.getElementById('resetForm');
      const confirmationMessage = document.getElementById('confirmationMessage');
      const sendResetLink = document.getElementById('sendResetLink');
      const cancelReset = document.getElementById('cancelReset');
      const closeConfirmation = document.getElementById('closeConfirmation');
      const resetEmail = document.getElementById('reset-email');

      // Form toggle function
      function showForm(formType) {
        if (formType === 'signup') {
          signinForm.classList.remove('visible-form');
          signinForm.classList.add('hidden-form');
          signupForm.classList.remove('hidden-form');
          signupForm.classList.add('visible-form');
          formsWrapper.style.transform = 'translateX(-50%)';
          signinImage.classList.remove('fade-in');
          signinImage.classList.add('fade-out');
          signupImage.classList.remove('fade-out');
          signupImage.classList.add('fade-in');
        } else {
          signupForm.classList.remove('visible-form');
          signupForm.classList.add('hidden-form');
          signinForm.classList.remove('hidden-form');
          signinForm.classList.add('visible-form');
          formsWrapper.style.transform = 'translateX(0)';
          signinImage.classList.remove('fade-out');
          signinImage.classList.add('fade-in');
          signupImage.classList.remove('fade-in');
          signupImage.classList.add('fade-out');
        }
      }

      // Check query parameter on load
      const params = new URLSearchParams(window.location.search);
      const formParam = params.get('form');
      if (formParam === 'signup') {
        showForm('signup');
      } else {
        showForm('login');
      }

      // Show toast notification for success (from query param)
      const status = params.get('status');
      if (status === 'login_success') {
        showToast('Signed in successfully!', 'success');
      } else if (status === 'signup_success') {
        showToast('Account created successfully!', 'success');
      }

      // Toast notification function
      function showToast(message, type = 'success') {
        toast.innerHTML = `
          <div class="flex items-center px-5 py-3 rounded-xl shadow-lg glass-effect border ${type === 'success' ? 'border-green-400 bg-green-50' : 'border-red-400 bg-red-50'}">
            <i class="ri-${type === 'success' ? 'checkbox-circle-fill text-green-600' : 'close-circle-fill text-red-600'} ri-xl mr-3"></i>
            <span class="text-base font-medium text-gray-800">${message}</span>
          </div>
        `;
        toast.classList.remove('opacity-0', 'pointer-events-none');
        toast.classList.add('opacity-100');
        setTimeout(() => {
          toast.classList.remove('opacity-100');
          toast.classList.add('opacity-0', 'pointer-events-none');
        }, 2500);
      }

      // Toggle form links
      showSignUp.addEventListener('click', function(e) {
        e.preventDefault();
        showForm('signup');
      });
      showSignIn.addEventListener('click', function(e) {
        e.preventDefault();
        showForm('login');
      });

      // Input validation for label animation
      const inputs = document.querySelectorAll('input');
      inputs.forEach(input => {
        input.addEventListener('blur', function() {
          if (this.value.trim() !== '') {
            this.classList.add('not-empty');
          } else {
            this.classList.remove('not-empty');
          }
        });
      });

      // Sign In Form Validation
      signinForm.addEventListener('submit', function(e) {
        const email = document.getElementById('signin-email').value.trim();
        const password = document.getElementById('signin-password').value.trim();
        if (!email || !password) {
          e.preventDefault();
          showToast('Please enter both email and password.', 'error');
          return;
        }
        if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
          e.preventDefault();
          showToast('Please enter a valid email address.', 'error');
          return;
        }
      });

      // Sign Up Form Validation
      signupForm.addEventListener('submit', function(e) {
        const fullname = document.getElementById('fullname').value.trim();
        const email = document.getElementById('signup-email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const password = document.getElementById('signup-password').value.trim();
        const confirmPassword = document.getElementById('confirm-password').value.trim();
        const terms = document.getElementById('terms').checked;
        if (!fullname || !email || !phone || !password || !confirmPassword) {
          e.preventDefault();
          showToast('Please fill in all fields.', 'error');
          return;
        }
        if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
          e.preventDefault();
          showToast('Please enter a valid email address.', 'error');
          return;
        }
        if (password.length < 6) {
          e.preventDefault();
          showToast('Password must be at least 6 characters.', 'error');
          return;
        }
        if (password !== confirmPassword) {
          e.preventDefault();
          showToast('Passwords do not match.', 'error');
          return;
        }
        if (!terms) {
          e.preventDefault();
          showToast('You must agree to the Terms and Privacy Policy.', 'error');
          return;
        }
      });

      // Forgot Password Modal Logic
      forgotPasswordLink.addEventListener('click', function(e) {
        e.preventDefault();
        forgotPasswordModal.classList.remove('hidden');
        forgotPasswordModal.classList.add('flex');
        resetForm.classList.remove('hidden');
        confirmationMessage.classList.add('hidden');
        resetEmail.value = '';
      });
      function closeModal() {
        forgotPasswordModal.classList.add('hidden');
        forgotPasswordModal.classList.remove('flex');
      }
      cancelReset.addEventListener('click', closeModal);
      closeConfirmation.addEventListener('click', closeModal);
      forgotPasswordModal.addEventListener('click', function(e) {
        if (e.target === forgotPasswordModal) {
          closeModal();
        }
      });
      sendResetLink.addEventListener('click', function () {
  if (resetEmail.value.trim() === '') {
    resetEmail.focus();
    return;
  }

  // Send the email to backend
  fetch('auth/reset/send_reset.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: 'email=' + encodeURIComponent(resetEmail.value.trim())
  })
    .then(res => res.text())
    .then(response => {
      confirmationMessage.querySelector('p').innerHTML = response;
      resetForm.classList.add('hidden');
      confirmationMessage.classList.remove('hidden');
    });
});
});
  </script>
</body>
</html>