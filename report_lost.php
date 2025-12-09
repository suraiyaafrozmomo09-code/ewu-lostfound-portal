<?php
// Include database first
require_once 'config/database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first");
    exit;
}

$success = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_name = trim($_POST['item_name']);
    $description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $lost_date = $_POST['lost_date'];
    $lost_location = trim($_POST['lost_location']);
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO lost_items (item_name, description, category_id, lost_date, lost_location, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$item_name, $description, $category_id, $lost_date, $lost_location, $user_id]);
        
        if($stmt->rowCount() > 0) {
            $success = "Lost item reported successfully!";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<?php include 'includes/header.php'; ?>

<div style="max-width: 600px; margin: 2rem auto;">
    <div class="modern-form">
        <h2 style="margin-bottom: 1.5rem; color: var(--dark); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-exclamation-triangle"></i> Report Lost Item
        </h2>
        
        <?php if(isset($error)): ?>
            <div class="message message-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="message message-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group-modern">
                <label for="item_name">
                    <i class="fas fa-tag"></i> Item Name
                </label>
                <input type="text" id="item_name" name="item_name" class="form-input-modern" 
                       placeholder="e.g., iPhone, Wallet, Text Book" required>
            </div>
            
            <div class="form-group-modern">
                <label for="category_id">
                    <i class="fas fa-list"></i> Category
                </label>
                <select id="category_id" name="category_id" class="form-input-modern" required>
                    <option value="">Select Category</option>
                    <?php
                    $stmt = $conn->query("SELECT * FROM categories ORDER BY name");
                    while($cat = $stmt->fetch()) {
                        echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group-modern">
                <label for="description">
                    <i class="fas fa-align-left"></i> Description
                </label>
                <textarea id="description" name="description" class="form-input-modern" 
                          rows="4" placeholder="Describe your item (color, brand, special marks, serial number if any)" required></textarea>
            </div>
            
            <div class="form-group-modern">
                <label for="lost_date">
                    <i class="fas fa-calendar"></i> When did you lose it?
                </label>
                <input type="date" id="lost_date" name="lost_date" class="form-input-modern" 
                       value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group-modern">
                <label for="lost_location">
                    <i class="fas fa-map-marker-alt"></i> Where did you lose it?
                </label>
                <input type="text" id="lost_location" name="lost_location" class="form-input-modern" 
                       placeholder="e.g., Library Ground Floor, CSE Building Room 501, Cafeteria" required>
                <small style="color: var(--gray); font-size: 0.85rem;">Be specific about the location</small>
            </div>
            
            <button type="submit" class="btn-modern btn-primary-modern" style="width: 100%;">
                <i class="fas fa-paper-plane"></i> Report Lost Item
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="index.php" class="btn-modern" style="background: var(--gray); color: white;">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>