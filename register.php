<?php 
include "config.php";

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])){
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPass = $_POST['confirm_password'];
    $checkStmt = $pdo->prepare("SELECT * FROM users WHERE email = ? ");
    $checkStmt->execute([$email]);
    $user = $checkStmt->fetch();
    if ($user){
        $message = "Account already exists with this email !";
    }else{
        if($password !== $confirmPass){
            $message = "Password NOT matched ";
            exit;
        }elseif(isset($username,$fullname,$email,$password) && !empty($username) && !empty($fullname) && !empty($email) && !empty($password)){
            $hashedPass = password_hash($password ,PASSWORD_DEFAULT);
        }else{
            echo "Fill all the cases with your informations please !";
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO users (username,fullname,email,password) VALUES(? , ? , ? , ?)");
        $success = $stmt->execute([$username,$fullname,$email,$hashedPass]);
        if($success){
            echo "User registered successfully ✅";
            header("location:login.php");
        }
    }}
    

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
          fontFamily: {
            display: ['Manrope', 'sans-serif']
          }
        }
      }
    }
  </script>

  <style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    :root {
      --card-bg: #ffffff;
      --bg: #f6f8f7;
      --accent-light: #2563eb;
    }
    .dark {
      --card-bg: #0f1720;
      --bg: #0b1210;
      --accent-dark-from: #7c3aed;
      --accent-dark-to: #ec4899;
    }
  </style>
</head>

<body class="font-display bg-[color:var(--bg)] min-h-screen flex items-center justify-center p-4">

  <div class="w-full max-w-md">

    <!-- Logo -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-[color:var(--accent-light)] to-indigo-600 mb-4">
        <span class="material-symbols-outlined text-white text-3xl">
          account_balance_wallet
        </span>
      </div>
      <h1 class="text-3xl font-bold text-gray-900">Create Account</h1>
      <p class="text-gray-600 mt-2">Join Smart Wallet to manage your finances</p>
    </div>

    <!-- Register Form -->
    <div class="bg-[color:var(--card-bg)] rounded-2xl p-8 shadow-lg border border-gray-100">

      <form method="POST" action="register.php">
        <div class="space-y-4">
            <?php if (!empty($message)): ?>
                <div class="mb-4 text-red-600 font-medium">
                    <?php echo $message ?>;
                </div>
            <?php endif;?>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
            <input type="text" name="username" required
              class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="johndoe">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
            <input type="text" name="fullname"
              class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="John Doe">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
            <input type="email" name="email" required
              class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="john@example.com">
          </div>
            

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
            <input type="password" name="password" required
              class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="••••••••">
            <p class="mt-1 text-xs text-gray-500">
              Min 8 characters, uppercase, lowercase, and number
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
            <input type="password" name="confirm_password" required
              class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="••••••••">
          </div>

          <button type="submit" name="submit"
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
        <p class="text-sm text-gray-600">
          Already have an account?
          <a href="login.php" class="text-blue-600 font-semibold hover:underline">
            Login here
          </a>
        </p>
      </div>

    </div>

    <p class="text-center text-sm text-gray-500 mt-6">
      © 2025 Smart Wallet. All rights reserved.
    </p>

  </div>

</body>
</html>
