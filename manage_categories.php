<?php
require_once 'config/database.php';

// Check if user is admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php?error=Admin access required");
    exit;
}

// Handle category actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['add_category'])) {
        $name = trim($_POST['name']);
        if(!empty($name)) {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            $message = "Category added successfully";
        }
    } elseif(isset($_POST['update_category'])) {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        if(!empty($name)) {
            $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            $message = "Category updated successfully";
        }
    }
}

// Handle delete
if(isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    
    // Check if category is being used
    $stmt = $conn->prepare("SELECT COUNT(*) FROM lost_items WHERE category_id = ?");
    $stmt->execute([$id]);
    $lost_count = $stmt->fetchColumn();
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM found_items WHERE category_id = ?");
    $stmt->execute([$id]);
    $found_count = $stmt->fetchColumn();
    
    if($lost_count == 0 && $found_count == 0) {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Category deleted successfully";
    } else {
        $error = "Cannot delete category - it is being used by " . ($lost_count + $found_count) . " items";
    }
}
?>
<?php include 'includes/header.php'; ?>

<div style="max-width: 1200px; margin: 2rem auto; padding: 0 2rem;">
    <div class="glass-card" style="background: linear-gradient(135deg, #7209b7, #560bad); color: white;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-tags"></i> Manage Categories
                </h1>
                <p>Manage item categories</p>
            </div>
            <div style="font-size: 2rem;">
                <i class="fas fa-folder-open"></i>
            </div>
        </div>
    </div>
    
    <!-- Messages -->
    <?php if(isset($message)): ?>
        <div class="message message-success">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
        <div class="message message-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <!-- Add Category Form -->
    <div class="glass-card" style="margin-top: 2rem;">
        <h3 style="margin-bottom: 1.5rem; color: var(--dark); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-plus-circle"></i> Add New Category
        </h3>
        
        <form method="POST" action="">
            <div style="display: flex; gap: 1rem;">
                <input type="text" name="name" class="form-input-modern" placeholder="Enter category name" required>
                <button type="submit" name="add_category" class="btn-modern btn-primary-modern">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </div>
        </form>
    </div>
    
    <!-- Categories List -->
    <div class="glass-card" style="margin-top: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="color: var(--dark); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-list"></i> All Categories
            </h3>
            <a href="admin_dashboard.php" class="btn-modern" style="background: var(--gray); color: white;">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
            <?php
            $stmt = $conn->query("SELECT c.*, 
                                 (SELECT COUNT(*) FROM lost_items WHERE category_id = c.id) as lost_count,
                                 (SELECT COUNT(*) FROM found_items WHERE category_id = c.id) as found_count
                                 FROM categories c ORDER BY name");
            
            if($stmt->rowCount() > 0) {
                while($category = $stmt->fetch()) {
                    $total_items = $category['lost_count'] + $category['found_count'];
                    
                    echo '<div class="item-card-modern" style="margin: 0;">';
                    echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">';
                    echo '<h4 style="margin: 0;">' . htmlspecialchars($category['name']) . '</h4>';
                    echo '<span style="background: var(--primary); color: white; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.8rem;">';
                    echo $total_items . ' items';
                    echo '</span>';
                    echo '</div>';
                    
                    echo '<div style="font-size: 0.9rem; color: var(--gray); margin-bottom: 1rem;">';
                    echo '<div>Lost: ' . $category['lost_count'] . ' items</div>';
                    echo '<div>Found: ' . $category['found_count'] . ' items</div>';
                    echo '</div>';
                    
                    echo '<div style="display: flex; gap: 0.5rem;">';
                    echo '<button onclick="editCategory(' . $category['id'] . ', \'' . htmlspecialchars($category['name']) . '\')" class="btn-modern" style="padding: 0.5rem 1rem; background: var(--warning); color: #996e00; font-size: 0.8rem;">';
                    echo '<i class="fas fa-edit"></i> Edit';
                    echo '</button>';
                    
                    if($total_items == 0) {
                        echo '<a href="?delete_id=' . $category['id'] . '" class="btn-modern" style="padding: 0.5rem 1rem; background: var(--danger); color: white; font-size: 0.8rem;" onclick="return confirm(\'Delete this category?\')">';
                        echo '<i class="fas fa-trash"></i> Delete';
                        echo '</a>';
                    } else {
                        echo '<button class="btn-modern" style="padding: 0.5rem 1rem; background: var(--gray); color: white; font-size: 0.8rem; cursor: not-allowed;" title="Cannot delete - category in use">';
                        echo '<i class="fas fa-trash"></i> In Use';
                        echo '</button>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div style="text-align: center; padding: 2rem; color: var(--gray); grid-column: 1 / -1;">';
                echo '<i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 1rem;"></i>';
                echo '<p>No categories found. Add your first category.</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modern-form" style="max-width: 500px;">
        <h3 style="margin-bottom: 1.5rem; color: var(--dark);">
            <i class="fas fa-edit"></i> Edit Category
        </h3>
        <form id="editForm" method="POST" action="">
            <input type="hidden" id="editId" name="id">
            <div class="form-group-modern">
                <label for="editName">Category Name</label>
                <input type="text" id="editName" name="name" class="form-input-modern" required>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" name="update_category" class="btn-modern btn-primary-modern" style="flex: 1;">
                    <i class="fas fa-save"></i> Update
                </button>
                <button type="button" onclick="closeModal()" class="btn-modern" style="background: var(--gray); color: white;">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(id, name) {
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if(e.target === this) {
        closeModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>