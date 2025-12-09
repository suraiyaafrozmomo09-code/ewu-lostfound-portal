<?php
// Create demo users with correct password
require_once 'config/database.php';

echo "<h2>Creating Demo Users</h2>";

// Delete existing demo users
$stmt = $conn->prepare("DELETE FROM users WHERE email IN (?, ?, ?)");
$stmt->execute(['student1@std.ewu.bd', 'student2@std.ewu.bd', 'admin@ewu.edu.bd']);
echo "<p>Deleted existing demo users</p>";

// Create password hash for "password123"
$password = "password123";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "<p>Password hash for 'password123': " . $hashed_password . "</p>";

// Insert new demo users
$users = [
    ['student1@std.ewu.bd', $hashed_password, 'John Doe', '01710000001', 'student'],
    ['student2@std.ewu.bd', $hashed_password, 'Jane Smith', '01710000002', 'student'],
    ['admin@ewu.edu.bd', $hashed_password, 'System Admin', '01710000000', 'admin']
];

foreach($users as $user) {
    $stmt = $conn->prepare("INSERT INTO users (email, password, name, phone, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute($user);
    echo "<p>Created user: " . $user[0] . "</p>";
}

echo "<h3>Demo Users Created Successfully!</h3>";
echo "<p>Email: student1@std.ewu.bd</p>";
echo "<p>Password: password123</p>";
echo "<p>Admin Email: admin@ewu.edu.bd</p>";
echo "<p>Admin Password: password123</p>";

echo "<p><a href='login.php'>Go to Login Page</a></p>";
?>