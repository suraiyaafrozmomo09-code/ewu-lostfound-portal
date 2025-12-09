<?php
require_once 'config/database.php';

// Check if user is admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php?error=Admin access required");
    exit;
}

// Get statistics for reports
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_lost = $conn->query("SELECT COUNT(*) FROM lost_items")->fetchColumn();
$total_found = $conn->query("SELECT COUNT(*) FROM found_items")->fetchColumn();

// Monthly stats
$monthly_stats = [];
for($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    
    // Lost items this month
    $stmt = $conn->prepare("SELECT COUNT(*) FROM lost_items WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stmt->execute([$month]);
    $lost_count = $stmt->fetchColumn();
    
    // Found items this month
    $stmt = $conn->prepare("SELECT COUNT(*) FROM found_items WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stmt->execute([$month]);
    $found_count = $stmt->fetchColumn();
    
    $monthly_stats[$month_name] = [
        'lost' => $lost_count,
        'found' => $found_count,
        'total' => $lost_count + $found_count
    ];
}

// Category distribution
$categories = $conn->query("SELECT c.name, 
                           (SELECT COUNT(*) FROM lost_items WHERE category_id = c.id) as lost_count,
                           (SELECT COUNT(*) FROM found_items WHERE category_id = c.id) as found_count
                           FROM categories c ORDER BY name")->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<div style="max-width: 1200px; margin: 2rem auto; padding: 0 2rem;">
    <div class="glass-card" style="background: linear-gradient(135deg, #7209b7, #560bad); color: white;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-chart-bar"></i> Reports & Analytics
                </h1>
                <p>System statistics and analytics</p>
            </div>
            <div style="font-size: 2rem;">
                <i class="fas fa-chart-pie"></i>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin: 2rem 0;">
        <div class="glass-card">
            <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 0.5rem;">
                <i class="fas fa-users"></i>
            </div>
            <div style="font-size: 2rem; font-weight: bold;"><?php echo $total_users; ?></div>
            <div style="color: var(--gray);">Total Users</div>
        </div>
        
        <div class="glass-card">
            <div style="font-size: 2.5rem; color: var(--warning); margin-bottom: 0.5rem;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div style="font-size: 2rem; font-weight: bold;"><?php echo $total_lost; ?></div>
            <div style="color: var(--gray);">Lost Items</div>
        </div>
        
        <div class="glass-card">
            <div style="font-size: 2.5rem; color: var(--success); margin-bottom: 0.5rem;">
                <i class="fas fa-hands-helping"></i>
            </div>
            <div style="font-size: 2rem; font-weight: bold;"><?php echo $total_found; ?></div>
            <div style="color: var(--gray);">Found Items</div>
        </div>
        
        <div class="glass-card">
            <div style="font-size: 2.5rem; color: #f72585; margin-bottom: 0.5rem;">
                <i class="fas fa-chart-line"></i>
            </div>
            <div style="font-size: 2rem; font-weight: bold;"><?php echo $total_lost + $total_found; ?></div>
            <div style="color: var(--gray);">Total Items</div>
        </div>
    </div>
    
    <!-- Monthly Activity Chart -->
    <div class="glass-card" style="margin-top: 2rem;">
        <h3 style="margin-bottom: 1.5rem; color: var(--dark); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-chart-line"></i> Monthly Activity (Last 6 Months)
        </h3>
        
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Lost Items</th>
                        <th>Found Items</th>
                        <th>Total Items</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($monthly_stats as $month => $stats): ?>
                    <tr>
                        <td><strong><?php echo $month; ?></strong></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="color: var(--warning);"><?php echo $stats['lost']; ?></span>
                                <div style="width: 100px; height: 8px; background: #f0f0f0; border-radius: 4px; overflow: hidden;">
                                    <div style="width: <?php echo ($stats['total'] > 0) ? ($stats['lost'] / $stats['total'] * 100) : 0; ?>%; height: 100%; background: var(--warning);"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="color: var(--success);"><?php echo $stats['found']; ?></span>
                                <div style="width: 100px; height: 8px; background: #f0f0f0; border-radius: 4px; overflow: hidden;">
                                    <div style="width: <?php echo ($stats['total'] > 0) ? ($stats['found'] / $stats['total'] * 100) : 0; ?>%; height: 100%; background: var(--success);"></div>
                                </div>
                            </div>
                        </td>
                        <td><strong><?php echo $stats['total']; ?></strong></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 120px; height: 8px; background: #f0f0f0; border-radius: 4px; overflow: hidden;">
                                    <div style="width: <?php echo ($stats['total'] > 0) ? 100 : 0; ?>%; height: 100%; background: linear-gradient(90deg, var(--warning), var(--success));"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Category Distribution -->
    <div class="glass-card" style="margin-top: 2rem;">
        <h3 style="margin-bottom: 1.5rem; color: var(--dark); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-chart-pie"></i> Category Distribution
        </h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <!-- Category Table -->
            <div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Lost</th>
                            <th>Found</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_all_items = 0;
                        foreach($categories as $cat): 
                            $total = $cat['lost_count'] + $cat['found_count'];
                            $total_all_items += $total;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                            <td style="color: var(--warning);"><?php echo $cat['lost_count']; ?></td>
                            <td style="color: var(--success);"><?php echo $cat['found_count']; ?></td>
                            <td><strong><?php echo $total; ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Category Stats -->
            <div>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                    <h4 style="margin-bottom: 1rem; color: var(--gray);">Category Insights</h4>
                    
                    <?php if(count($categories) > 0): 
                        // Find most popular category
                        $most_popular = null;
                        foreach($categories as $cat) {
                            $total = $cat['lost_count'] + $cat['found_count'];
                            if($most_popular === null || $total > $most_popular['total']) {
                                $most_popular = [
                                    'name' => $cat['name'],
                                    'total' => $total,
                                    'lost' => $cat['lost_count'],
                                    'found' => $cat['found_count']
                                ];
                            }
                        }
                        
                        // Find least popular category
                        $least_popular = null;
                        foreach($categories as $cat) {
                            $total = $cat['lost_count'] + $cat['found_count'];
                            if($least_popular === null || $total < $least_popular['total']) {
                                $least_popular = [
                                    'name' => $cat['name'],
                                    'total' => $total
                                ];
                            }
                        }
                    ?>
                    
                    <div style="margin-bottom: 1rem;">
                        <div style="font-size: 0.9rem; color: var(--gray);">Most Popular Category</div>
                        <div style="font-size: 1.2rem; font-weight: bold; color: var(--primary);">
                            <?php echo htmlspecialchars($most_popular['name']); ?>
                        </div>
                        <div style="font-size: 0.9rem; color: var(--gray);">
                            <?php echo $most_popular['total']; ?> items 
                            (<?php echo $most_popular['lost']; ?> lost, <?php echo $most_popular['found']; ?> found)
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <div style="font-size: 0.9rem; color: var(--gray);">Total Items in System</div>
                        <div style="font-size: 1.2rem; font-weight: bold; color: var(--primary);">
                            <?php echo $total_all_items; ?> items
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <div style="font-size: 0.9rem; color: var(--gray);">Number of Categories</div>
                        <div style="font-size: 1.2rem; font-weight: bold; color: var(--primary);">
                            <?php echo count($categories); ?> categories
                        </div>
                    </div>
                    
                    <?php else: ?>
                    <div style="text-align: center; padding: 2rem; color: var(--gray);">
                        <i class="fas fa-chart-pie" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <p>No category data available</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Export Options -->
    <div class="glass-card" style="margin-top: 2rem;">
        <h3 style="margin-bottom: 1.5rem; color: var(--dark); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-file-export"></i> Export Data
        </h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="export_data.php?type=users" class="btn-modern" style="background: var(--primary); color: white; flex-direction: column; text-align: center;">
                <i class="fas fa-users" style="font-size: 1.5rem;"></i>
                <span>Export Users</span>
            </a>
            
            <a href="export_data.php?type=lost" class="btn-modern" style="background: var(--warning); color: #996e00; flex-direction: column; text-align: center;">
                <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem;"></i>
                <span>Export Lost Items</span>
            </a>
            
            <a href="export_data.php?type=found" class="btn-modern" style="background: var(--success); color: white; flex-direction: column; text-align: center;">
                <i class="fas fa-hands-helping" style="font-size: 1.5rem;"></i>
                <span>Export Found Items</span>
            </a>
            
            <a href="export_data.php?type=all" class="btn-modern" style="background: #f72585; color: white; flex-direction: column; text-align: center;">
                <i class="fas fa-file-excel" style="font-size: 1.5rem;"></i>
                <span>Export All Data</span>
            </a>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="admin_dashboard.php" class="btn-modern" style="background: var(--gray); color: white;">
                <i class="fas fa-arrow-left"></i> Back to Admin Dashboard
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>