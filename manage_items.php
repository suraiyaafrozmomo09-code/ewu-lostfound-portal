<?php
require_once 'config/database.php';

// Check if user is admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php?error=Admin access required");
    exit;
}

// Handle item actions
if(isset($_GET['action']) && isset($_GET['type']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $type = $_GET['type'];
    $id = intval($_GET['id']);
    
    if($action == 'delete') {
        if($type == 'lost') {
            $stmt = $conn->prepare("DELETE FROM lost_items WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            $stmt = $conn->prepare("DELETE FROM found_items WHERE id = ?");
            $stmt->execute([$id]);
        }
        $message = "Item deleted successfully";
    } elseif($action == 'mark_found') {
        $stmt = $conn->prepare("UPDATE lost_items SET status = 'found' WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Item marked as found";
    } elseif($action == 'mark_claimed') {
        $stmt = $conn->prepare("UPDATE found_items SET status = 'claimed' WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Item marked as claimed";
    }
}

// Current tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';
?>
<?php include 'includes/header.php'; ?>

<div style="max-width: 1200px; margin: 2rem auto; padding: 0 2rem;">
    <div class="glass-card" style="background: linear-gradient(135deg, #7209b7, #560bad); color: white;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-boxes"></i> Manage Items
                </h1>
                <p>Manage all lost and found items</p>
            </div>
            <div style="font-size: 2rem;">
                <i class="fas fa-box-open"></i>
            </div>
        </div>
    </div>
    
    <!-- Messages -->
    <?php if(isset($message)): ?>
        <div class="message message-success">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <!-- Tabs -->
    <div class="glass-card" style="margin-top: 2rem;">
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">
            <a href="?tab=all" class="btn-modern <?php echo $tab == 'all' ? 'btn-primary-modern' : ''; ?>" style="text-decoration: none;">
                <i class="fas fa-list"></i> All Items
            </a>
            <a href="?tab=lost" class="btn-modern <?php echo $tab == 'lost' ? 'btn-primary-modern' : ''; ?>" style="text-decoration: none;">
                <i class="fas fa-exclamation-triangle"></i> Lost Items
            </a>
            <a href="?tab=found" class="btn-modern <?php echo $tab == 'found' ? 'btn-primary-modern' : ''; ?>" style="text-decoration: none;">
                <i class="fas fa-hands-helping"></i> Found Items
            </a>
            <a href="admin_dashboard.php" class="btn-modern" style="background: var(--gray); color: white; margin-left: auto;">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        
        <!-- Items Table -->
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item Name</th>
                        <th>Type</th>
                        <th>User</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Build query based on tab
                    if($tab == 'lost') {
                        $sql = "SELECT li.*, u.name as user_name FROM lost_items li 
                                LEFT JOIN users u ON li.user_id = u.id 
                                ORDER BY li.created_at DESC";
                        $stmt = $conn->query($sql);
                        
                        while($item = $stmt->fetch()) {
                            echo '<tr>';
                            echo '<td>L-' . $item['id'] . '</td>';
                            echo '<td><strong>' . htmlspecialchars($item['item_name']) . '</strong></td>';
                            echo '<td><span class="item-badge badge-lost" style="position: static; display: inline-block;">Lost</span></td>';
                            echo '<td>' . htmlspecialchars($item['user_name']) . '</td>';
                            echo '<td>' . date('M d, Y', strtotime($item['lost_date'])) . '</td>';
                            echo '<td>' . htmlspecialchars($item['lost_location']) . '</td>';
                            
                            $status_color = ($item['status'] == 'lost') ? 'var(--warning)' : 'var(--success)';
                            echo '<td><span style="color: ' . $status_color . '; font-weight: 600;">' . ucfirst($item['status']) . '</span></td>';
                            
                            echo '<td>';
                            echo '<div style="display: flex; gap: 0.5rem;">';
                            echo '<a href="view_item.php?type=lost&id=' . $item['id'] . '" class="btn-modern" style="padding: 0.3rem 0.6rem; background: var(--primary); color: white; font-size: 0.8rem;">';
                            echo '<i class="fas fa-eye"></i>';
                            echo '</a>';
                            
                            if($item['status'] == 'lost') {
                                echo '<a href="?action=mark_found&type=lost&id=' . $item['id'] . '" class="btn-modern" style="padding: 0.3rem 0.6rem; background: var(--success); color: white; font-size: 0.8rem;">';
                                echo '<i class="fas fa-check"></i> Mark Found';
                                echo '</a>';
                            }
                            
                            echo '<a href="?action=delete&type=lost&id=' . $item['id'] . '" class="btn-modern" style="padding: 0.3rem 0.6rem; background: var(--danger); color: white; font-size: 0.8rem;" onclick="return confirm(\'Delete this item?\')">';
                            echo '<i class="fas fa-trash"></i>';
                            echo '</a>';
                            echo '</div>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        
                    } elseif($tab == 'found') {
                        $sql = "SELECT fi.*, u.name as user_name FROM found_items fi 
                                LEFT JOIN users u ON fi.user_id = u.id 
                                ORDER BY fi.created_at DESC";
                        $stmt = $conn->query($sql);
                        
                        while($item = $stmt->fetch()) {
                            echo '<tr>';
                            echo '<td>F-' . $item['id'] . '</td>';
                            echo '<td><strong>' . htmlspecialchars($item['item_name']) . '</strong></td>';
                            echo '<td><span class="item-badge badge-found" style="position: static; display: inline-block;">Found</span></td>';
                            echo '<td>' . htmlspecialchars($item['user_name']) . '</td>';
                            echo '<td>' . date('M d, Y', strtotime($item['found_date'])) . '</td>';
                            echo '<td>' . htmlspecialchars($item['found_location']) . '</td>';
                            
                            $status_color = ($item['status'] == 'unclaimed') ? 'var(--success)' : 'var(--primary)';
                            echo '<td><span style="color: ' . $status_color . '; font-weight: 600;">' . ucfirst($item['status']) . '</span></td>';
                            
                            echo '<td>';
                            echo '<div style="display: flex; gap: 0.5rem;">';
                            echo '<a href="view_item.php?type=found&id=' . $item['id'] . '" class="btn-modern" style="padding: 0.3rem 0.6rem; background: var(--primary); color: white; font-size: 0.8rem;">';
                            echo '<i class="fas fa-eye"></i>';
                            echo '</a>';
                            
                            if($item['status'] == 'unclaimed') {
                                echo '<a href="?action=mark_claimed&type=found&id=' . $item['id'] . '" class="btn-modern" style="padding: 0.3rem 0.6rem; background: var(--primary); color: white; font-size: 0.8rem;">';
                                echo '<i class="fas fa-hand-paper"></i> Mark Claimed';
                                echo '</a>';
                            }
                            
                            echo '<a href="?action=delete&type=found&id=' . $item['id'] . '" class="btn-modern" style="padding: 0.3rem 0.6rem; background: var(--danger); color: white; font-size: 0.8rem;" onclick="return confirm(\'Delete this item?\')">';
                            echo '<i class="fas fa-trash"></i>';
                            echo '</a>';
                            echo '</div>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        
                    } else {
                        // Show all items (both lost and found)
                        echo '<tr><td colspan="8" style="text-align: center; padding: 2rem; color: var(--gray);">';
                        echo 'Select a tab to view items';
                        echo '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Stats -->
        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #eee;">
            <?php
            $total_lost = $conn->query("SELECT COUNT(*) FROM lost_items")->fetchColumn();
            $lost_active = $conn->query("SELECT COUNT(*) FROM lost_items WHERE status='lost'")->fetchColumn();
            $total_found = $conn->query("SELECT COUNT(*) FROM found_items")->fetchColumn();
            $found_unclaimed = $conn->query("SELECT COUNT(*) FROM found_items WHERE status='unclaimed'")->fetchColumn();
            ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--warning);"><?php echo $total_lost; ?></div>
                    <div style="color: var(--gray); font-size: 0.9rem;">Total Lost</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--warning);"><?php echo $lost_active; ?></div>
                    <div style="color: var(--gray); font-size: 0.9rem;">Still Missing</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--success);"><?php echo $total_found; ?></div>
                    <div style="color: var(--gray); font-size: 0.9rem;">Total Found</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--success);"><?php echo $found_unclaimed; ?></div>
                    <div style="color: var(--gray); font-size: 0.9rem;">Unclaimed</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>