<?php
require_once('config.php');
require_once('auth.php');

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $fullName = sanitizeInput($_POST['fullname'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $passwordCheck = validatePassword($password);
        if (!$passwordCheck['valid']) {
            $error = $passwordCheck['error'];
        } else {
            $result = registerUser($pdo, $username, $email, $password, $fullName);
            if ($result['success']) {
                $success = 'Registration successful! Redirecting to login...';
                header('refresh:2;url=login.php');
            } else {
                $error = $result['error'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Register — Your Smart Wallet</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: { display: ['Manrope', 'sans-serif'] }
        }
      }
    }
  </script>

  <style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    :root { --card-bg: #ffffff; --bg: #f6f8f7; --accent-light: #2563eb; }
    .dark { --card-bg: #0f1720; --bg: #0b1210; --accent-dark-from: #7c3aed; --accent-dark-to: #ec4899; }
  </style>
</head>
<body class="font-display bg-[color:var(--bg)] dark:bg-[color:var(--bg)] min-h-screen flex items-center justify-center p-4">

  <div class="w-full max-w-md">
    <!-- Logo/Brand -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-[color:var(--accent-light)] to-indigo-600 mb-4">
        <span class="material-symbols-outlined text-white text-3xl">account_balance_wallet</span>
      </div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Account</h1>
      <p class="text-gray-600 dark:text-gray-400 mt-2">Join Smart Wallet to manage your finances</p>
    </div>

    <!-- Register Form -->
    <div class="bg-[color:var(--card-bg)] rounded-2xl p-8 shadow-lg border border-gray-100 dark:border-gray-800">
      <?php if ($error): ?>
        <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-red-600 dark:text-red-400">error</span>
            <p class="text-sm text-red-600 dark:text-red-400"><?= htmlspecialchars($error) ?></p>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="mb-6 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/30">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-green-600 dark:text-green-400">check_circle</span>
            <p class="text-sm text-green-600 dark:text-green-400"><?= htmlspecialchars($success) ?></p>
          </div>
        </div>
      <?php endif; ?>

      <form method="POST" action="register.php">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username *</label>
            <input type="text" name="username" required 
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="johndoe"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name</label>
            <input type="text" name="fullname" 
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="John Doe"
                   value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email *</label>
            <input type="email" name="email" required 
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="john@example.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password *</label>
            <input type="password" name="password" required 
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="••••••••">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Min 8 characters, uppercase, lowercase, and number</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password *</label>
            <input type="password" name="confirm_password" required 
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="••••••••">
          </div>

          <button type="submit" 
                  class="w-full py-3 rounded-xl text-white font-semibold hover:opacity-90 transition" 
                  style="background: linear-gradient(90deg, #2563eb, #4f46e5);">
            <span class="flex items-center justify-center gap-2">
              <span class="material-symbols-outlined">person_add</span>
              Create Account
            </span>
          </button>
        </div>
      </form>

      <div class="mt-6 text-center">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Already have an account? 
          <a href="login.php" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">Login here</a>
        </p>
      </div>
    </div>

    <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
      © 2025 Smart Wallet. All rights reserved.
    </p>
  </div>

</body>
</html>