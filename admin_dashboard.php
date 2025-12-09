<?php
require_once 'config/database.php';

// Check if user is admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php?error=Admin access required");
    exit;
}

// Get comprehensive statistics
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_students = $conn->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$total_admins = $conn->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();

$total_lost = $conn->query("SELECT COUNT(*) FROM lost_items")->fetchColumn();
$lost_active = $conn->query("SELECT COUNT(*) FROM lost_items WHERE status='lost'")->fetchColumn();
$lost_found = $conn->query("SELECT COUNT(*) FROM lost_items WHERE status='found'")->fetchColumn();

$total_found = $conn->query("SELECT COUNT(*) FROM found_items")->fetchColumn();
$found_unclaimed = $conn->query("SELECT COUNT(*) FROM found_items WHERE status='unclaimed'")->fetchColumn();
$found_claimed = $conn->query("SELECT COUNT(*) FROM found_items WHERE status='claimed'")->fetchColumn();

// Get recent activities
$recent_lost = $conn->query("SELECT li.*, u.name as user_name FROM lost_items li LEFT JOIN users u ON li.user_id = u.id ORDER BY li.created_at DESC LIMIT 5")->fetchAll();
$recent_found = $conn->query("SELECT fi.*, u.name as user_name FROM found_items fi LEFT JOIN users u ON fi.user_id = u.id ORDER BY fi.created_at DESC LIMIT 5")->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<div style="max-width: 1200px; margin: 2rem auto; padding: 0 2rem;">
    <!-- Admin Header -->
    <div class="glass-card" style="background: linear-gradient(135deg, #7209b7, #560bad); color: white;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-crown"></i> Admin Dashboard
                </h1>
                <p>System Administration Panel</p>
            </div>
            <div style="font-size: 2rem;">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin: 2rem 0;">
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 2.5rem; color: var(--primary);"><?php echo $total_users; ?></div>
                    <div style="color: var(--gray);">Total Users</div>
                </div>
                <div style="font-size: 2rem; color: var(--primary);">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div style="margin-top: 1rem; font-size: 0.9rem;">
                <span style="color: var(--success);"><?php echo $total_students; ?> Students</span> • 
                <span style="color: var(--primary);"><?php echo $total_admins; ?> Admins</span>
            </div>
        </div>
        
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 2.5rem; color: var(--warning);"><?php echo $total_lost; ?></div>
                    <div style="color: var(--gray);">Lost Items</div>
                </div>
                <div style="font-size: 2rem; color: var(--warning);">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div style="margin-top: 1rem; font-size: 0.9rem;">
                <span style="color: var(--warning);"><?php echo $lost_active; ?> Active</span> • 
                <span style="color: var(--success);"><?php echo $lost_found; ?> Found</span>
            </div>
        </div>
        
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 2.5rem; color: var(--success);"><?php echo $total_found; ?></div>
                    <div style="color: var(--gray);">Found Items</div>
                </div>
                <div style="font-size: 2rem; color: var(--success);">
                    <i class="fas fa-hands-helping"></i>
                </div>
            </div>
            <div style="margin-top: 1rem; font-size: 0.9rem;">
                <span style="color: var(--success);"><?php echo $found_unclaimed; ?> Unclaimed</span> • 
                <span style="color: var(--primary);"><?php echo $found_claimed; ?> Claimed</span>
            </div>
        </div>
    </div>
    
    <!-- Recent Activities -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 2rem; margin-top: 2rem;">
        <!-- Recent Lost Items -->
        <div class="glass-card">
            <h3 style="margin-bottom: 1rem; color: var(--dark); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-clock"></i> Recent Lost Items
            </h3>
            
            <?php if(count($recent_lost) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>User</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_lost as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($item['item_name'], 0, 20)) . '...'; ?></td>
                            <td><?php echo htmlspecialchars($item['user_name']); ?></td>
                            <td><?php echo date('M d', strtotime($item['created_at'])); ?></td>
                            <td>
                                <span class="item-badge badge-lost" style="display: inline-block; position: static; font-size: 0.8rem;">
                                    <?php echo $item['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: var(--gray); text-align: center; padding: 2rem;">No recent lost items.</p>
            <?php endif; ?>
        </div>
        
        <!-- Recent Found Items -->
        <div class="glass-card">
            <h3 style="margin-bottom: 1rem; color: var(--dark); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-clock"></i> Recent Found Items
            </h3>
            
            <?php if(count($recent_found) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>User</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_found as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($item['item_name'], 0, 20)) . '...'; ?></td>
                            <td><?php echo htmlspecialchars($item['user_name']); ?></td>
                            <td><?php echo date('M d', strtotime($item['created_at'])); ?></td>
                            <td>
                                <span class="item-badge <?php echo ($item['status'] == 'claimed') ? 'badge-claimed' : 'badge-found'; ?>" 
                                      style="display: inline-block; position: static; font-size: 0.8rem;">
                                    <?php echo $item['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: var(--gray); text-align: center; padding: 2rem;">No recent found items.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Admin Actions -->
     
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
    <a href="manage_users.php" class="btn-modern" style="background: var(--primary); color: white; flex-direction: column; text-align: center;">
        <i class="fas fa-users-cog" style="font-size: 1.5rem;"></i>
        <span>Manage Users</span>
    </a>
    
    <a href="manage_items.php" class="btn-modern" style="background: var(--success); color: white; flex-direction: column; text-align: center;">
        <i class="fas fa-boxes" style="font-size: 1.5rem;"></i>
        <span>Manage Items</span>
    </a>
    
    <a href="manage_categories.php" class="btn-modern" style="background: var(--warning); color: #996e00; flex-direction: column; text-align: center;">
        <i class="fas fa-tags" style="font-size: 1.5rem;"></i>
        <span>Categories</span>
    </a>
    
    <a href="reports.php" class="btn-modern" style="background: #f72585; color: white; flex-direction: column; text-align: center;">
        <i class="fas fa-chart-bar" style="font-size: 1.5rem;"></i>
        <span>Reports</span>
    </a>
</div>
    
    <!-- Back to User Dashboard -->
    <div style="text-align: center; margin-top: 2rem;">
        <a href="dashboard.php" class="btn-modern" style="background: var(--gray); color: white;">
            <i class="fas fa-arrow-left"></i> Back to User Dashboard
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>