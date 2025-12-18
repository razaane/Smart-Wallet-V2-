<?php 
session_start();
include "config.php"; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';


$message = "";

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])){
    $email_input = $_POST['email'];
    $pass_input = $_POST['password'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt ->execute([$email_input]);
$user = $stmt->fetch();
if(!$user){
    $message = "User not found!";
}else{
    if(password_verify($pass_input, $user['password'])){
        $otp = rand(100000,999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_email']=$email_input;
        $_SESSION['otp_time'] = time();

        $mail = new PHPMailer(true);

        try{                    //Enable verbose debug output
          $mail->isSMTP();                                            //Send using SMTP
          $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
          $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
          $mail->Username   = 'wakhidirazane@gmail.com';                     //SMTP username
          $mail->Password   = 'yipniysyyojnqhiv';                               //SMTP password
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
          $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

          //Recipients
          $mail->setFrom('wakhidirazane@gmail.com', 'Smart Wallet');
          $mail->addAddress($email_input);

          //Content
          $mail->isHTML(true);                                 
          
                $mail->Subject = 'Your OTP Code';
                $mail->Body    = "<b>Your OTP is: $otp</b>";
                $mail->AltBody = "Your OTP is: $otp";

          $mail->send();
          header("Location: otp.php");
          exit;

        }catch(Exception $e){
          $message = "Failed to send OTP email";
        }
    }else{
        $message = "Wrong Password !";
    }
}
}

?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Login — Your Smart Wallet</title>
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
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Smart Wallet</h1>
      <p class="text-gray-600 dark:text-gray-400 mt-2">Welcome back! Please login to continue</p>
    </div>

    <!-- Login Form -->
    <div class="bg-[color:var(--card-bg)] rounded-2xl p-8 shadow-lg border border-gray-100 dark:border-gray-800">
      <form method="POST" action="login.php">
        <div class="space-y-4">
            <?php if (!empty($message)): ?>
                <div class="mb-4 text-red-600 font-medium">
                    <?php echo $message ?>;
                </div>
            <?php endif;?>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
            <input type="email" name="email" required 
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="your@email.com">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
            <input type="password" name="password" required 
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="••••••••">
          </div>

          <button type="submit" name ="login"
                  class="w-full py-3 rounded-xl text-white font-semibold hover:opacity-90 transition" 
                  style="background: linear-gradient(90deg, #2563eb, #4f46e5);">
            <span class="flex items-center justify-center gap-2">
              <span class="material-symbols-outlined">login</span>
              Login
            </span>
          </button>
        </div>
      </form>

      <div class="mt-6 text-center">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Don't have an account? 
          <a href="register.php" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">Register here</a>
        </p>
      </div>
    </div>

    <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
      © 2025 Smart Wallet. All rights reserved.
    </p>
  </div>

</body>
</html>
