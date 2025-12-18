<?php
// auth.php - Authentication helper functions

session_start();

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require authentication - redirect to login if not logged in
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser($pdo) {
    $userId = getCurrentUserId();
    if (!$userId) return null;
    
    $stmt = $pdo->prepare("SELECT id, username, email, fullname, avatar, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Login user
 */
function loginUser($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT id, username, email, password, fullname, avatar FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['avatar'] = $user['avatar'];
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        return true;
    }
    
    return false;
}

/**
 * Register new user
 */
function registerUser($pdo, $username, $email, $password, $fullName = null) {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Email already exists'];
    }
    
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Username already exists'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, fullname) VALUES (?, ?, ?, ?)");
    
    try {
        $stmt->execute([$username, $email, $hashedPassword, $fullName]);
        return ['success' => true, 'user_id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Registration failed: ' . $e->getMessage()];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy session
    session_destroy();
    
    // Redirect to login
    header('Location: login.php');
    exit();
}

/**
 * Get user initials for avatar placeholder
 */
function getUserInitials($fullName, $username) {
    if ($fullName) {
        $parts = explode(' ', trim($fullName));
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
        }
        return strtoupper(substr($fullName, 0, 2));
    }
    return strtoupper(substr($username, 0, 2));
}

/**
 * Validate password strength
 */
function validatePassword($password) {
    if (strlen($password) < 8) {
        return ['valid' => false, 'error' => 'Password must be at least 8 characters'];
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'error' => 'Password must contain at least one uppercase letter'];
    }
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'error' => 'Password must contain at least one lowercase letter'];
    }
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'error' => 'Password must contain at least one number'];
    }
    return ['valid' => true];
}

/**
 * Sanitize input
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}