<?php
// Include database connection at the beginning
require_once 'config/database.php';
?>
<?php include 'includes/header.php'; ?>

<div style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <!-- Stats Section -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin: 2rem 0;">
        <?php
        $lost_count = $conn->query("SELECT COUNT(*) FROM lost_items WHERE status='lost'")->fetchColumn();
$found_count = $conn->query("SELECT COUNT(*) FROM found_items WHERE status='unclaimed'")->fetchColumn();
$users_count = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$claimed_count = $conn->query("SELECT COUNT(*) FROM found_items WHERE status='claimed'")->fetchColumn();
        ?>
        
        <div class="glass-card" style="border-left: 4px solid var(--warning);">
            <div style="font-size: 2.5rem; color: var(--warning); margin-bottom: 0.5rem;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div style="font-size: 2rem; font-weight: bold;"><?php echo $lost_count; ?></div>
            <div style="color: var(--gray);">Lost Items</div>
        </div>
        
        <div class="glass-card" style="border-left: 4px solid var(--success);">
            <div style="font-size: 2.5rem; color: var(--success); margin-bottom: 0.5rem;">
                <i class="fas fa-hands-helping"></i>
            </div>
            <div style="font-size: 2rem; font-weight: bold;"><?php echo $found_count; ?></div>
            <div style="color: var(--gray);">Found Items</div>
        </div>
        
        <div class="glass-card" style="border-left: 4px solid var(--primary);">
            <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 0.5rem;">
                <i class="fas fa-users"></i>
            </div>
            <div style="font-size: 2rem; font-weight: bold;"><?php echo $users_count; ?></div>
            <div style="color: var(--gray);">Active Users</div>
        </div>
        
        <div class="glass-card" style="border-left: 4px solid #f72585;">
            <div style="font-size: 2.5rem; color: #f72585; margin-bottom: 0.5rem;">
                <i class="fas fa-heart"></i>
            </div>
            <div style="font-size: 2rem; font-weight: bold;"><?php echo $claimed_count; ?></div>
            <div style="color: var(--gray);">Items Claimed</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="glass-card">
        <h2 style="margin-bottom: 1.5rem; color: var(--dark); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-bolt"></i> Quick Actions
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <a href="report_lost.php" class="btn-modern btn-primary-modern" style="justify-content: flex-start; text-align: left;">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Report Lost Item</strong>
                    <small style="display: block; opacity: 0.8;">Can't find something?</small>
                </div>
            </a>
            
            <a href="report_found.php" class="btn-modern" style="justify-content: flex-start; text-align: left; background: var(--success); color: white;">
                <i class="fas fa-hands-helping"></i>
                <div>
                    <strong>Report Found Item</strong>
                    <small style="display: block; opacity: 0.8;">Found someone's item?</small>
                </div>
            </a>
            
            <a href="search.php" class="btn-modern" style="justify-content: flex-start; text-align: left; background: var(--gray); color: white;">
                <i class="fas fa-search"></i>
                <div>
                    <strong>Search Items</strong>
                    <small style="display: block; opacity: 0.8;">Browse all items</small>
                </div>
            </a>
            
            <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'register.php'; ?>" 
               class="btn-modern" style="justify-content: flex-start; text-align: left; background: var(--warning); color: #996e00;">
                <i class="fas fa-user"></i>
                <div>
                    <strong><?php echo isset($_SESSION['user_id']) ? 'My Dashboard' : 'Register Now'; ?></strong>
                    <small style="display: block; opacity: 0.8;">Track your items</small>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Recent Items -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem; margin-top: 2rem;">
        <!-- Lost Items -->
        <div class="glass-card">
            <h3 style="margin-bottom: 1rem; color: var(--dark); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-exclamation-circle"></i> Recently Lost
            </h3>
            
            <?php
            $stmt = $conn->query("SELECT li.*, c.name as category FROM lost_items li 
                                  LEFT JOIN categories c ON li.category_id = c.id 
                                  WHERE li.status='lost' 
                                  ORDER BY li.lost_date DESC LIMIT 5");
            
            if($stmt->rowCount() > 0) {
                while($item = $stmt->fetch()) {
                    echo '<div class="item-card-modern">';
                    echo '<div class="item-badge badge-lost">Lost</div>';
                    echo '<h4>' . htmlspecialchars($item['item_name']) . '</h4>';
                    echo '<p style="color: var(--gray); margin: 0.5rem 0; font-size: 0.9rem;">';
                    echo '<i class="fas fa-calendar"></i> ' . date('M d, Y', strtotime($item['lost_date']));
                    echo ' • <i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($item['lost_location']);
                    echo ' • <i class="fas fa-tag"></i> ' . htmlspecialchars($item['category']);
                    echo '</p>';
                    echo '<p style="font-size: 0.95rem;">' . substr(htmlspecialchars($item['description']), 0, 100) . '...</p>';
                    echo '<div style="margin-top: 1rem;">';
                    echo '<a href="view_item.php?type=lost&id=' . $item['id'] . '" class="btn-modern" style="padding: 0.5rem 1rem;">';
                    echo '<i class="fas fa-eye"></i> View Details';
                    echo '</a>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p style="color: var(--gray); text-align: center; padding: 2rem;">No lost items reported recently.</p>';
            }
            ?>
            
            <div style="text-align: center; margin-top: 1rem;">
                <a href="search.php?type=lost" class="btn-modern" style="background: var(--warning); color: #996e00;">
                    <i class="fas fa-list"></i> View All Lost Items
                </a>
            </div>
        </div>
        
        <!-- Found Items -->
        <div class="glass-card">
            <h3 style="margin-bottom: 1rem; color: var(--dark); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-hands-helping"></i> Recently Found
            </h3>
            
            <?php
            $stmt = $conn->query("SELECT fi.*, c.name as category FROM found_items fi 
                                  LEFT JOIN categories c ON fi.category_id = c.id 
                                  WHERE fi.status='unclaimed' 
                                  ORDER BY fi.found_date DESC LIMIT 5");
            
            if($stmt->rowCount() > 0) {
                while($item = $stmt->fetch()) {
                    echo '<div class="item-card-modern">';
                    echo '<div class="item-badge badge-found">Found</div>';
                    echo '<h4>' . htmlspecialchars($item['item_name']) . '</h4>';
                    echo '<p style="color: var(--gray); margin: 0.5rem 0; font-size: 0.9rem;">';
                    echo '<i class="fas fa-calendar"></i> ' . date('M d, Y', strtotime($item['found_date']));
                    echo ' • <i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($item['found_location']);
                    echo ' • <i class="fas fa-tag"></i> ' . htmlspecialchars($item['category']);
                    echo '</p>';
                    echo '<p style="font-size: 0.95rem;">' . substr(htmlspecialchars($item['description']), 0, 100) . '...</p>';
                    echo '<div style="margin-top: 1rem;">';
                    echo '<a href="view_item.php?type=found&id=' . $item['id'] . '" class="btn-modern" style="padding: 0.5rem 1rem; background: var(--success); color: white;">';
                    echo '<i class="fas fa-hand-paper"></i> Claim This Item';
                    echo '</a>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p style="color: var(--gray); text-align: center; padding: 2rem;">All found items have been claimed!</p>';
            }
            ?>
            
            <div style="text-align: center; margin-top: 1rem;">
                <a href="search.php?type=found" class="btn-modern" style="background: var(--success); color: white;">
                    <i class="fas fa-list"></i> View All Found Items
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>