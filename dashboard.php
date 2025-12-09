<?php
require_once 'config/database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Get user's stats - CORRECTED VERSION
$stmt = $conn->prepare("SELECT COUNT(*) FROM lost_items WHERE user_id = ?");
$stmt->execute([$user_id]);
$lost_items_count = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM found_items WHERE user_id = ?");
$stmt->execute([$user_id]);
$found_items_count = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM found_items WHERE claimed_by = ?");
$stmt->execute([$user_id]);
$claimed_items_count = $stmt->fetchColumn();
?>
<?php include 'includes/header.php'; ?>

<div style="max-width: 1200px; margin: 2rem auto; padding: 0 2rem;">
    <div class="glass-card" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
        <h1 style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
    </div>
    
    <!-- User Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin: 2rem 0;">
        <div class="glass-card">
            <div style="font-size: 2.5rem; color: var(--warning); margin-bottom: 0.5rem;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div style="font-size: 2rem; font-weight: bold;"><?php echo $lost_items_count; ?></div>
            <div style="color: var(--gray);">Your Lost Items</div>
        </div>
        
        <div class="glass-card">
            <div style="font-size: 2.5rem; color: var(--success); margin-bottom: 0.5rem;">
                <i class="fas fa-hands-helping"></i>
            </div>
            <div style="font-size: 2rem; font-weight: bold;"><?php echo $found_items_count; ?></div>
            <div style="color: var(--gray);">Your Found Items</div>
        </div>
        
        <div class="glass-card">
            <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 0.5rem;">
                <i class="fas fa-heart"></i>
            </div>
            <div style="font-size: 2rem; font-weight: bold;"><?php echo $claimed_items_count; ?></div>
            <div style="color: var(--gray);">Items Claimed</div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem; margin-top: 2rem;">
        <!-- Your Lost Items -->
        <div class="glass-card">
            <h3 style="margin-bottom: 1rem; color: var(--dark); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-exclamation-triangle"></i> Your Lost Items
                <span style="font-size: 0.9rem; background: var(--warning); color: #996e00; padding: 0.2rem 0.6rem; border-radius: 20px;">
                    <?php echo $lost_items_count; ?>
                </span>
            </h3>
            
            <?php
            $stmt = $conn->prepare("SELECT li.*, c.name as category FROM lost_items li 
                                  LEFT JOIN categories c ON li.category_id = c.id 
                                  WHERE li.user_id = ? 
                                  ORDER BY li.lost_date DESC LIMIT 5");
            $stmt->execute([$user_id]);
            
            if($stmt->rowCount() > 0) {
                while($item = $stmt->fetch()) {
                    echo '<div class="item-card-modern">';
                    echo '<div class="item-badge badge-lost">Lost</div>';
                    echo '<h4>' . htmlspecialchars($item['item_name']) . '</h4>';
                    echo '<p><strong>Category:</strong> ' . htmlspecialchars($item['category']) . '</p>';
                    echo '<p><strong>Lost:</strong> ' . date('M d, Y', strtotime($item['lost_date'])) . '</p>';
                    echo '<p><strong>Location:</strong> ' . htmlspecialchars($item['lost_location']) . '</p>';
                    echo '<p style="font-size: 0.9rem; color: var(--gray);">' . substr(htmlspecialchars($item['description']), 0, 80) . '...</p>';
                    echo '<a href="view_item.php?type=lost&id=' . $item['id'] . '" class="btn-modern" style="padding: 0.5rem 1rem;">';
                    echo '<i class="fas fa-eye"></i> View Details';
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                echo '<p style="color: var(--gray); text-align: center; padding: 2rem;">You haven\'t reported any lost items yet.</p>';
            }
            ?>
            
            <div style="margin-top: 1rem; text-align: center;">
                <a href="report_lost.php" class="btn-modern btn-primary-modern">
                    <i class="fas fa-plus"></i> Report New Lost Item
                </a>
            </div>
        </div>
        
        <!-- Your Found Items -->
        <div class="glass-card">
            <h3 style="margin-bottom: 1rem; color: var(--dark); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-hands-helping"></i> Your Found Items
                <span style="font-size: 0.9rem; background: var(--success); color: white; padding: 0.2rem 0.6rem; border-radius: 20px;">
                    <?php echo $found_items_count; ?>
                </span>
            </h3>
            
            <?php
            $stmt = $conn->prepare("SELECT fi.*, c.name as category FROM found_items fi 
                                  LEFT JOIN categories c ON fi.category_id = c.id 
                                  WHERE fi.user_id = ? 
                                  ORDER BY fi.found_date DESC LIMIT 5");
            $stmt->execute([$user_id]);
            
            if($stmt->rowCount() > 0) {
                while($item = $stmt->fetch()) {
                    echo '<div class="item-card-modern">';
                    $badge_class = ($item['status'] == 'claimed') ? 'badge-claimed' : 'badge-found';
                    $badge_text = ($item['status'] == 'claimed') ? 'Claimed' : 'Found';
                    echo '<div class="item-badge ' . $badge_class . '">' . $badge_text . '</div>';
                    echo '<h4>' . htmlspecialchars($item['item_name']) . '</h4>';
                    echo '<p><strong>Category:</strong> ' . htmlspecialchars($item['category']) . '</p>';
                    echo '<p><strong>Found:</strong> ' . date('M d, Y', strtotime($item['found_date'])) . '</p>';
                    echo '<p><strong>Location:</strong> ' . htmlspecialchars($item['found_location']) . '</p>';
                    echo '<p style="font-size: 0.9rem; color: var(--gray);">' . substr(htmlspecialchars($item['description']), 0, 80) . '...</p>';
                    echo '<a href="view_item.php?type=found&id=' . $item['id'] . '" class="btn-modern" style="padding: 0.5rem 1rem;">';
                    echo '<i class="fas fa-eye"></i> View Details';
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                echo '<p style="color: var(--gray); text-align: center; padding: 2rem;">You haven\'t reported any found items yet.</p>';
            }
            ?>
            
            <div style="margin-top: 1rem; text-align: center;">
                <a href="report_found.php" class="btn-modern" style="background: var(--success); color: white;">
                    <i class="fas fa-plus"></i> Report New Found Item
                </a>
            </div>
        </div>
    </div>
    
    <?php if($_SESSION['role'] == 'admin'): ?>
    <div class="glass-card" style="margin-top: 2rem; background: linear-gradient(135deg, #7209b7, #560bad); color: white;">
        <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-crown"></i> Admin Panel
        </h3>
        <p>You have administrative privileges to manage the system.</p>
        <div style="margin-top: 1rem; display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="admin_dashboard.php" class="btn-modern" style="background: white; color: #7209b7;">
                <i class="fas fa-cog"></i> Go to Admin Dashboard
            </a>
            <a href="manage_users.php" class="btn-modern" style="background: rgba(255,255,255,0.2); color: white;">
                <i class="fas fa-users-cog"></i> Manage Users
            </a>
            <a href="manage_items.php" class="btn-modern" style="background: rgba(255,255,255,0.2); color: white;">
                <i class="fas fa-boxes"></i> Manage All Items
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>