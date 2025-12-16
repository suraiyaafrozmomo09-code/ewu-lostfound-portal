<?php
session_start();
include 'config/database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login to claim items");
    exit;
}

// Check if form was submitted
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
    $item_type = $_POST['item_type'];
    $user_id = $_SESSION['user_id'];
    
    try {
        if($item_type == 'found') {
            // Update found_items table
            $stmt = $conn->prepare("UPDATE found_items SET status = 'claimed', claimed_by = ? WHERE id = ? AND status = 'unclaimed'");
            $stmt->execute([$user_id, $item_id]);
            
            if($stmt->rowCount() > 0) {
                // Success - redirect back to item with success message
                header("Location: view_item.php?type=found&id=" . $item_id . "&message=Item claimed successfully!");
                exit;
            } else {
                // Item already claimed or doesn't exist
                header("Location: view_item.php?type=found&id=" . $item_id . "&error=Item already claimed or not found");
                exit;
            }
        } else {
            // For lost items (if needed later)
            header("Location: index.php?error=Cannot claim lost items");
            exit;
        }
        
    } catch(PDOException $e) {
        header("Location: view_item.php?type=" . $item_type . "&id=" . $item_id . "&error=Database error: " . urlencode($e->getMessage()));
        exit;
    }
} else {
    // No form data
    header("Location: index.php");
    exit;
}
?>