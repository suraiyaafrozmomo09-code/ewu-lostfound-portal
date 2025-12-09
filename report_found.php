<?php
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
    $found_date = $_POST['found_date'];
    $found_location = trim($_POST['found_location']);
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO found_items (item_name, description, category_id, found_date, found_location, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$item_name, $description, $category_id, $found_date, $found_location, $user_id]);
        
        if($stmt->rowCount() > 0) {
            $success = "Found item reported successfully! Thank you for your honesty.";
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
            <i class="fas fa-hands-helping"></i> Report Found Item
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
                    <i class="fas fa-tag"></i> What did you find?
                </label>
                <input type="text" id="item_name" name="item_name" class="form-input-modern" 
                       placeholder="Describe the item (e.g., Black Wallet, iPhone, ID Card)" required>
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
                    <i class="fas fa-align-left"></i> Description Details
                </label>
                <textarea id="description" name="description" class="form-input-modern" 
                          rows="4" placeholder="Provide details about the item (color, brand, condition, any identifiable marks)" required></textarea>
            </div>
            
            <div class="form-group-modern">
                <label for="found_date">
                    <i class="fas fa-calendar"></i> When did you find it?
                </label>
                <input type="date" id="found_date" name="found_date" class="form-input-modern" 
                       value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group-modern">
                <label for="found_location">
                    <i class="fas fa-map-marker-alt"></i> Where did you find it?
                </label>
                <input type="text" id="found_location" name="found_location" class="form-input-modern" 
                       placeholder="e.g., Library Entrance, CSE Building 3rd Floor, Cafeteria Table 5" required>
                <small style="color: var(--gray); font-size: 0.85rem;">Be specific to help the owner find it</small>
            </div>
            
            <div style="background: #f0f7ff; padding: 1rem; border-radius: 8px; margin: 1.5rem 0;">
                <p style="color: var(--primary); margin: 0;">
                    <i class="fas fa-info-circle"></i> <strong>Thank you for your honesty!</strong> Your report helps reunite lost items with their owners.
                </p>
            </div>
            
            <button type="submit" class="btn-modern" style="width: 100%; background: var(--success); color: white;">
                <i class="fas fa-paper-plane"></i> Report Found Item
            </button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>