<?php
echo "<h1>Database Connection Test</h1>";

// Try different connection methods
$connections = [
    ["mysql:host=localhost;port=3306;dbname=ewu_lostfound", "Port 3306"],
    ["mysql:host=127.0.0.1;port=3306;dbname=ewu_lostfound", "127.0.0.1 Port 3306"],
    ["mysql:host=localhost;dbname=ewu_lostfound", "Default port (no port specified)"],
];

foreach($connections as $connection) {
    list($dsn, $description) = $connection;
    
    echo "<h3>Testing: {$description}</h3>";
    
    try {
        $conn = new PDO($dsn, 'root', '');
        echo "✅ <strong style='color: green;'>SUCCESS!</strong> Connected via {$description}<br>";
        
        // Check if we can query
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll();
        
        echo "✅ Found " . count($tables) . " tables in database<br>";
        
        if(count($tables) > 0) {
            echo "Tables: ";
            foreach($tables as $table) {
                echo $table[0] . ", ";
            }
            echo "<br>";
        }
        
        // Check for users table specifically
        $stmt = $conn->query("SHOW TABLES LIKE 'users'");
        if($stmt->rowCount() > 0) {
            echo "✅ 'users' table exists<br>";
            
            // Check for admin user
            $stmt = $conn->query("SELECT * FROM users WHERE email = 'admin@ewu.edu.bd'");
            if($stmt->rowCount() > 0) {
                echo "✅ Admin user exists<br>";
            } else {
                echo "⚠️ Admin user not found<br>";
            }
        }
        
    } catch(PDOException $e) {
        echo "❌ <strong style='color: red;'>FAILED:</strong> " . $e->getMessage() . "<br>";
    }
    echo "<hr>";
}

// Test with session
echo "<h3>Session Test</h3>";
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}
echo "Session ID: " . session_id() . "<br>";
echo "Session status: " . session_status() . "<br>";

echo "<hr>";
echo "<h3>Quick Actions:</h3>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
echo "<p><a href='force_login.php'>Force Login (Admin Bypass)</a></p>";
echo '<form action="login.php" method="post" style="margin-top: 20px;">';
echo '<input type="hidden" name="email" value="admin@ewu.edu.bd">';
echo '<input type="hidden" name="password" value="Admin@2024">';
echo '<button type="submit">Test Admin Login</button>';
echo '</form>';
?>
