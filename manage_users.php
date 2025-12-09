<?php
require_once 'config/database.php';

// Check if user is admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php?error=Admin access required");
    exit;
}

// Handle user actions
if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);
    
    if($action == 'delete') {
        // Don't delete admin users
        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if($user['role'] != 'admin') {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $message = "User deleted successfully";
        } else {
            $error = "Cannot delete admin users";
        }
    } elseif($action == 'toggle_admin') {
        $stmt = $conn->prepare("UPDATE users SET role = IF(role='admin', 'student', 'admin') WHERE id = ?");
        $stmt->execute([$id]);
        $message = "User role updated";
    }
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>
<?php include 'includes/header.php'; ?>

<div style="max-width: 1200px; margin: 2rem auto; padding: 0 2rem;">
    <div class="glass-card" style="background: linear-gradient(135deg, #7209b7, #560bad); color: white;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-users-cog"></i> Manage Users
                </h1>
                <p>Manage all system users</p>
            </div>
            <div style="font-size: 2rem;">
                <i class="fas fa-user-shield"></i>
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
    
    <!-- Search and Actions -->
    <div class="glass-card" style="margin-top: 2rem;">
        <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem;">
            <form method="GET" style="flex: 1;">
                <div class="search-input-group" style="margin: 0;">
                    <input type="text" name="search" class="search-input" 
                           placeholder="Search users by name or email..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
            <a href="admin_dashboard.php" class="btn-modern" style="background: var(--gray); color: white;">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        
        <!-- Users Table -->
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Build query based on search
                    if($search) {
                        $sql = "SELECT * FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY created_at DESC";
                        $stmt = $conn->prepare($sql);
                        $search_term = "%$search%";
                        $stmt->execute([$search_term, $search_term]);
                    } else {
                        $sql = "SELECT * FROM users ORDER BY created_at DESC";
                        $stmt = $conn->query($sql);
                    }
                    
                    if($stmt->rowCount() > 0) {
                        while($user = $stmt->fetch()) {
                            echo '<tr>';
                            echo '<td>' . $user['id'] . '</td>';
                            echo '<td><strong>' . htmlspecialchars($user['name']) . '</strong></td>';
                            echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                            echo '<td>' . htmlspecialchars($user['phone']) . '</td>';
                            
                            // Role badge
                            $role_color = ($user['role'] == 'admin') ? 'var(--primary)' : 'var(--success)';
                            echo '<td><span style="background: ' . $role_color . '; color: white; padding: 0.3rem 0.6rem; border-radius: 20px; font-size: 0.8rem;">' . ucfirst($user['role']) . '</span></td>';
                            
                            echo '<td>' . date('M d, Y', strtotime($user['created_at'])) . '</td>';
                            echo '<td>';
                            echo '<div style="display: flex; gap: 0.5rem;">';
                            
                            // Don't allow deleting current user or admin users
                            if($user['id'] != $_SESSION['user_id']) {
                                if($user['role'] != 'admin') {
                                    echo '<a href="?action=delete&id=' . $user['id'] . '" class="btn-modern" style="padding: 0.3rem 0.6rem; background: var(--danger); color: white; font-size: 0.8rem;" onclick="return confirm(\'Delete this user?\')">';
                                    echo '<i class="fas fa-trash"></i>';
                                    echo '</a>';
                                    
                                    echo '<a href="?action=toggle_admin&id=' . $user['id'] . '" class="btn-modern" style="padding: 0.3rem 0.6rem; background: var(--primary); color: white; font-size: 0.8rem;">';
                                    echo '<i class="fas fa-shield-alt"></i> Make Admin';
                                    echo '</a>';
                                } else {
                                    echo '<a href="?action=toggle_admin&id=' . $user['id'] . '" class="btn-modern" style="padding: 0.3rem 0.6rem; background: var(--warning); color: #996e00; font-size: 0.8rem;">';
                                    echo '<i class="fas fa-user"></i> Remove Admin';
                                    echo '</a>';
                                }
                            } else {
                                echo '<span style="color: var(--gray); font-size: 0.8rem;">Current User</span>';
                            }
                            
                            echo '</div>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: var(--gray);">No users found</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Stats -->
        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #eee;">
            <?php
            $total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $total_admins = $conn->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
            $total_students = $conn->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
            ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary);"><?php echo $total_users; ?></div>
                    <div style="color: var(--gray); font-size: 0.9rem;">Total Users</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary);"><?php echo $total_admins; ?></div>
                    <div style="color: var(--gray); font-size: 0.9rem;">Admins</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--success);"><?php echo $total_students; ?></div>
                    <div style="color: var(--gray); font-size: 0.9rem;">Students</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>