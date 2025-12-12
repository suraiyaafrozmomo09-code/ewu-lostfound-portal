<?php
// Database configuration for Port 3306
$host = 'localhost';
$dbname = 'ewu_lostfound';  // Changed to match your database name
$username = 'root';
$password = '';
$port = 3306; // Changed to port 3306

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Connect to MySQL on port 3306
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    
    // Set PDO attributes
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Test connection with a simple query
    $conn->query("SELECT 1");
    
    // Optional: Check if admin user exists
    checkAdminUser($conn);
    
} catch(PDOException $e) {
    // More detailed error message
    $error_msg = "❌ Database Connection Failed!\n";
    $error_msg .= "Error: " . $e->getMessage() . "\n";
    $error_msg .= "Trying to connect to: mysql:host=$host;port=$port;dbname=$dbname\n";
    $error_msg .= "Username: $username\n";
    
    // Try alternative connection without port
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Connected without specifying port (using default 3306)";
    } catch(PDOException $e2) {
        die($error_msg . "\nAlternative connection also failed: " . $e2->getMessage());
    }
}

// Function to ensure admin user exists
function checkAdminUser($conn) {
    try {
        // Check if users table exists
        $tableExists = $conn->query("SHOW TABLES LIKE 'users'")->rowCount() > 0;
        
        if ($tableExists) {
            // Check if admin user exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute(['admin@ewu.edu.bd']);
            
            if($stmt->rowCount() == 0) {
                // Create admin user
                $admin_password = "Admin@2024";
                $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                
                $insert = $conn->prepare("INSERT INTO users (email, password, name, phone, role) VALUES (?, ?, ?, ?, ?)");
                $insert->execute([
                    'admin@ewu.edu.bd',
                    $hashed_password,
                    'System Administrator',
                    '01710000000',
                    'admin'
                ]);
                
                // Uncomment for debugging:
                // echo "✅ Admin user created: admin@ewu.edu.bd / $admin_password<br>";
            } else {
                // Uncomment for debugging:
                // echo "✅ Admin user already exists<br>";
            }
        }
    } catch(PDOException $e) {
        // Table might not exist yet, that's okay
        // echo "Note: " . $e->getMessage() . "<br>";
    }
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Optional: Display connection status (for debugging)
// echo "✅ Connected to database '$dbname' on port $port";
?>
