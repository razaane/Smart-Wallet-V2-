<?php
include("config.php");
session_start();

// Redirect to login if no OTP session exists
if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email'])) {
    header("Location: login.php");
    exit;
}

$message = "";

// Handle OTP form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $otp_input = trim($_POST['otp']);

    // Optional: expire OTP after 5 minutes
    if (time() - $_SESSION['otp_time'] > 300) {
        $message = "OTP expired. Please login again.";
        session_destroy();
    } else {
      $session_otp = (string)$_SESSION['otp'];

        if ($otp_input == $session_otp) {
            // OTP correct → mark user as logged in
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = true ;
            $_SESSION['user_email'] = $_SESSION['otp_email'];

            // Clear OTP session
            unset($_SESSION['otp']);
            unset($_SESSION['otp_email']);
            unset($_SESSION['otp_time']);

            header("Location: index.php");
            exit;
        } else {
            $message = "Incorrect OTP. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>OTP Verification — Your Smart Wallet</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: { extend: { fontFamily: { display: ['Manrope', 'sans-serif'] } } }
    }
  </script>

  <style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    :root { --card-bg: #ffffff; --bg: #f6f8f7; --accent-light: #2563eb; }
    .dark { --card-bg: #0f1720; --bg: #0b1210; --accent-dark-from: #7c3aed; --accent-dark-to: #ec4899; }
    .otp-input { 
      width: 100%; 
      height: 4rem; 
      text-align: center; 
      font-size: 2rem; 
      font-weight: 600;
      letter-spacing: 1rem;
      padding-left: 1rem;
    }
    .otp-input::-webkit-outer-spin-button, .otp-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .otp-input[type=number] { -moz-appearance: textfield; }
  </style>
</head>
<body class="font-display bg-[color:var(--bg)] dark:bg-[color:var(--bg)] min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
  <!-- Logo/Brand -->
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-[color:var(--accent-light)] to-indigo-600 mb-4">
      <span class="material-symbols-outlined text-white text-3xl">verified_user</span>
    </div>
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Verify Your Account</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-2">Enter the 6-digit code sent to your email</p>
  </div>

  <!-- OTP Form -->
  <div class="bg-[color:var(--card-bg)] rounded-2xl p-8 shadow-lg border border-gray-100 dark:border-gray-800">
    <form method="POST" action="" id="otpForm">
      <?php if (!empty($message)): ?>
        <div class="mb-4 text-red-600 font-medium text-center">
            <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>
      
      <!-- Single OTP Input Field -->
      <div class="mb-6">
        <input 
          type="text" 
          name="otp" 
          id="otp" 
          maxlength="6" 
          pattern="[0-9]{6}"
          inputmode="numeric"
          class="otp-input rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
          placeholder="000000"
          required
          autocomplete="off">
      </div>

      <button type="submit" name="verify_otp" class="w-full py-3 rounded-xl text-white font-semibold hover:opacity-90 transition" style="background: linear-gradient(90deg, #2563eb, #4f46e5);">
        <span class="flex items-center justify-center gap-2">
          <span class="material-symbols-outlined">check_circle</span>
          Verify Code
        </span>
      </button>
    </form>

    <div class="mt-6 text-center">
      <p class="text-sm text-gray-600 dark:text-gray-400">
        Didn't receive the code? 
        <a href="login.php" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">Resend</a>
      </p>
    </div>
  </div>

  <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
    © 2025 Smart Wallet. All rights reserved.
  </p>
</div>

<script>
  const otpInput = document.getElementById('otp');
  
  // Auto-focus on load
  otpInput.focus();
  
  // Only allow numbers
  otpInput.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    
    // Limit to 6 digits
    if (this.value.length > 6) {
      this.value = this.value.slice(0, 6);
    }
  });
  
  // Prevent non-numeric keys
  otpInput.addEventListener('keypress', function(e) {
    if (e.key < '0' || e.key > '9') {
      e.preventDefault();
    }
  });
</script>

</body>
</html>