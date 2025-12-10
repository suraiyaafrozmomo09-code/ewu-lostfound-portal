<?php
// Start session at the very beginning
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EWU Lost & Found Portal</title>
    <link rel="stylesheet" href="/ewu-lostfound/assets/css/style.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Hero Header -->
    <div class="hero-header">
        <div class="nav-container">
            <a href="/ewu-lostfound/index.php" class="logo">
                <i class="fas fa-search"></i> EWULost&Found
            </a>
            
            <div class="nav-links">
                <a href="/ewu-lostfound/index.php" class="nav-link">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="/ewu-lostfound/search.php" class="nav-link">
                    <i class="fas fa-search"></i> Search
                </a>
                <a href="/ewu-lostfound/report_lost.php" class="nav-link">
                    <i class="fas fa-exclamation-triangle"></i> Report Lost
                </a>
                <a href="/ewu-lostfound/report_found.php" class="nav-link">
                    <i class="fas fa-hands-helping"></i> Report Found
                </a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="/ewu-lostfound/dashboard.php" class="nav-link">
                        <i class="fas fa-user"></i> Dashboard
                    </a>
                    <a href="/ewu-lostfound/logout.php" class="nav-link" style="background: var(--accent);">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="/ewu-lostfound/login.php" class="nav-link" style="background: var(--accent);">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="/ewu-lostfound/register.php" class="nav-link">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <h1 class="fade-in">Find What's Lost, Return What's Found</h1>
        <p class="fade-in" style="animation-delay: 0.2s;">East West University's platform to reunite lost items with their owners</p>
        
        <div class="search-hero fade-in" style="animation-delay: 0.4s;">
            <form action="/ewu-lostfound/search.php" method="GET" class="search-input-group">
                <input type="text" name="query" class="search-input" placeholder="Search for items (iPhone, ID Card, Books...)" required>
                <select name="type" class="search-input" style="width: 150px;">
                    <option value="">All Items</option>
                    <option value="lost">Lost Items</option>
                    <option value="found">Found Items</option>
                </select>
                <button type="submit" class="btn-modern btn-primary-modern">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>
    </div>
    
    <!-- Messages -->
    <?php if(isset($_GET['message'])): ?>
        <div class="message message-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['error'])): ?>
        <div class="message message-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>