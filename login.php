<?php
// Include database first
require_once 'config/database.php';

// Check if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        
        header("Location: dashboard.php?message=Welcome back!");
        exit;
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EWU Lost & Found</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div class="modern-form" style="max-width: 500px;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;">
                <i class="fas fa-search"></i>
            </div>
            <h1>Welcome Back</h1>
            <p style="color: var(--gray);">Sign in to your account</p>
        </div>
        
        <?php if($error): ?>
            <div class="message message-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group-modern">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <input type="email" id="email" name="email" class="form-input-modern" 
                       placeholder="student@std.ewu.bd" required>
            </div>
            
            <div class="form-group-modern">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input type="password" id="password" name="password" class="form-input-modern" 
                       placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="btn-modern btn-primary-modern" style="width: 100%;">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
            <p style="color: var(--gray);">Don't have an account?</p>
            <a href="register.php" class="btn-modern" style="width: 100%; margin-top: 1rem; background: var(--light); color: var(--dark);">
                <i class="fas fa-user-plus"></i> Create New Account
            </a>
        </div>
        
        <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
            <p style="margin-bottom: 0.5rem; font-weight: 600;">Demo Accounts:</p>
            <p style="margin: 0.3rem 0;"><strong>Student:</strong> student1@std.ewu.bd / password123</p>
            <p style="margin: 0.3rem 0;"><strong>Admin:</strong> admin@ewu.edu.bd / password123</p>
        </div>
    </div>
</body>
</html>