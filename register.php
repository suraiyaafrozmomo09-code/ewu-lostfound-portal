<?php
// Include database first
require_once 'config/database.php';

// Check if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        
        if($checkStmt->rowCount() > 0) {
            $error = "Email already registered!";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $password]);
            
            if($stmt->rowCount() > 0) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    } catch(PDOException $e) {
        $error = "Registration error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EWU Lost & Found</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div class="modern-form" style="max-width: 500px;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1>Create Account</h1>
            <p style="color: var(--gray);">Join EWU Lost & Found community</p>
        </div>
        
        <?php if($error): ?>
            <div class="message message-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="message message-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <br><br>
                <a href="login.php" class="btn-modern" style="padding: 0.5rem 1rem;">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
            </div>
        <?php endif; ?>
        
        <?php if(!$success): ?>
        <form method="POST" action="">
            <div class="form-group-modern">
                <label for="name">
                    <i class="fas fa-user"></i> Full Name
                </label>
                <input type="text" id="name" name="name" class="form-input-modern" 
                       placeholder="Enter your full name" required>
            </div>
            
            <div class="form-group-modern">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <input type="email" id="email" name="email" class="form-input-modern" 
                       placeholder="student@std.ewu.bd" required>
                <small style="color: var(--gray); font-size: 0.85rem;">Use your EWU student email</small>
            </div>
            
            <div class="form-group-modern">
                <label for="phone">
                    <i class="fas fa-phone"></i> Phone Number
                </label>
                <input type="text" id="phone" name="phone" class="form-input-modern" 
                       placeholder="017XXXXXXXX" required>
            </div>
            
            <div class="form-group-modern">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input type="password" id="password" name="password" class="form-input-modern" 
                       placeholder="Create a password" required minlength="6">
                <small style="color: var(--gray); font-size: 0.85rem;">Minimum 6 characters</small>
            </div>
            
            <button type="submit" class="btn-modern btn-primary-modern" style="width: 100%;">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
            <p style="color: var(--gray);">Already have an account?</p>
            <a href="login.php" class="btn-modern" style="width: 100%; margin-top: 1rem; background: var(--light); color: var(--dark);">
                <i class="fas fa-sign-in-alt"></i> Login to Account
            </a>
        </div>
    </div>
</body>
</html>