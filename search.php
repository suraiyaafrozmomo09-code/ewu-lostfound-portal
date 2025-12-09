<?php
require_once 'config/database.php';
?>
<?php include 'includes/header.php'; ?>

<div style="max-width: 1200px; margin: 2rem auto; padding: 0 2rem;">
    <div class="modern-form">
        <h2 style="margin-bottom: 1.5rem; color: var(--dark); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-search"></i> Search Items
        </h2>
        
        <form method="GET" action="" class="search-input-group">
            <input type="text" name="query" class="search-input" 
                   placeholder="Search by item name, description, or location..." 
                   value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
            <select name="type" class="search-input" style="width: 150px;">
                <option value="">All Items</option>
                <option value="lost" <?php echo (isset($_GET['type']) && $_GET['type'] == 'lost') ? 'selected' : ''; ?>>Lost Items</option>
                <option value="found" <?php echo (isset($_GET['type']) && $_GET['type'] == 'found') ? 'selected' : ''; ?>>Found Items</option>
            </select>
            <button type="submit" class="btn-modern btn-primary-modern">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>
    
    <?php if(isset($_GET['query']) || isset($_GET['type'])): ?>
        <div style="margin-top: 3rem;">
            <h3 style="margin-bottom: 1.5rem; color: var(--dark); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search"></i> Search Results
                <?php if(isset($_GET['query']) && !empty($_GET['query'])): ?>
                    <span style="font-size: 1rem; color: var(--gray); font-weight: normal;">
                        for "<?php echo htmlspecialchars($_GET['query']); ?>"
                    </span>
                <?php endif; ?>
            </h3>
            
            <?php
            $search_query = isset($_GET['query']) ? "%" . $_GET['query'] . "%" : "%";
            $type = isset($_GET['type']) ? $_GET['type'] : '';
            
            // If no type specified, show both
            $show_lost = ($type == '' || $type == 'lost');
            $show_found = ($type == '' || $type == 'found');
            
            $total_results = 0;
            
            // Search lost items
            if($show_lost) {
                $stmt = $conn->prepare("SELECT li.*, c.name as category FROM lost_items li 
                                      LEFT JOIN categories c ON li.category_id = c.id 
                                      WHERE (li.item_name LIKE ? OR li.description LIKE ? OR li.lost_location LIKE ?) 
                                      AND li.status='lost' 
                                      ORDER BY li.lost_date DESC");
                $stmt->execute([$search_query, $search_query, $search_query]);
                
                if($stmt->rowCount() > 0) {
                    echo '<div style="margin: 2rem 0;">';
                    echo '<h4 style="margin: 2rem 0 1rem; color: var(--warning); display: flex; align-items: center; gap: 8px;">';
                    echo '<i class="fas fa-exclamation-triangle"></i> Lost Items';
                    echo '<span style="font-size: 0.9rem; background: var(--warning); color: #996e00; padding: 0.2rem 0.6rem; border-radius: 20px; margin-left: 10px;">';
                    echo $stmt->rowCount() . ' found';
                    echo '</span>';
                    echo '</h4>';
                    
                    while($item = $stmt->fetch()) {
                        $total_results++;
                        echo '<div class="item-card-modern">';
                        echo '<div class="item-badge badge-lost">Lost</div>';
                        echo '<h4>' . htmlspecialchars($item['item_name']) . '</h4>';
                        echo '<p><strong>Category:</strong> ' . htmlspecialchars($item['category']) . '</p>';
                        echo '<p><strong>Lost:</strong> ' . date('M d, Y', strtotime($item['lost_date'])) . ' at ' . htmlspecialchars($item['lost_location']) . '</p>';
                        echo '<p style="margin: 0.5rem 0;">' . htmlspecialchars($item['description']) . '</p>';
                        echo '<a href="view_item.php?type=lost&id=' . $item['id'] . '" class="btn-modern" style="padding: 0.5rem 1rem;">';
                        echo '<i class="fas fa-eye"></i> View Details';
                        echo '</a>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
            }
            
            // Search found items
            if($show_found) {
                $stmt = $conn->prepare("SELECT fi.*, c.name as category FROM found_items fi 
                                      LEFT JOIN categories c ON fi.category_id = c.id 
                                      WHERE (fi.item_name LIKE ? OR fi.description LIKE ? OR fi.found_location LIKE ?) 
                                      AND fi.status='unclaimed' 
                                      ORDER BY fi.found_date DESC");
                $stmt->execute([$search_query, $search_query, $search_query]);
                
                if($stmt->rowCount() > 0) {
                    echo '<div style="margin: 2rem 0;">';
                    echo '<h4 style="margin: 2rem 0 1rem; color: var(--success); display: flex; align-items: center; gap: 8px;">';
                    echo '<i class="fas fa-hands-helping"></i> Found Items';
                    echo '<span style="font-size: 0.9rem; background: var(--success); color: white; padding: 0.2rem 0.6rem; border-radius: 20px; margin-left: 10px;">';
                    echo $stmt->rowCount() . ' found';
                    echo '</span>';
                    echo '</h4>';
                    
                    while($item = $stmt->fetch()) {
                        $total_results++;
                        echo '<div class="item-card-modern">';
                        echo '<div class="item-badge badge-found">Found</div>';
                        echo '<h4>' . htmlspecialchars($item['item_name']) . '</h4>';
                        echo '<p><strong>Category:</strong> ' . htmlspecialchars($item['category']) . '</p>';
                        echo '<p><strong>Found:</strong> ' . date('M d, Y', strtotime($item['found_date'])) . ' at ' . htmlspecialchars($item['found_location']) . '</p>';
                        echo '<p style="margin: 0.5rem 0;">' . htmlspecialchars($item['description']) . '</p>';
                        echo '<a href="view_item.php?type=found&id=' . $item['id'] . '" class="btn-modern" style="padding: 0.5rem 1rem; background: var(--success); color: white;">';
                        echo '<i class="fas fa-hand-paper"></i> Claim This Item';
                        echo '</a>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
            }
            
            if($total_results == 0) {
                echo '<div class="glass-card" style="text-align: center; padding: 3rem;">';
                echo '<div style="font-size: 4rem; color: var(--gray); margin-bottom: 1rem;">';
                echo '<i class="fas fa-search"></i>';
                echo '</div>';
                echo '<h3>No items found</h3>';
                echo '<p style="color: var(--gray);">Try different search terms or browse all items</p>';
                echo '<a href="search.php" class="btn-modern btn-primary-modern" style="margin-top: 1rem;">';
                echo '<i class="fas fa-redo"></i> Clear Search';
                echo '</a>';
                echo '</div>';
            } else {
                echo '<div class="glass-card" style="margin-top: 2rem; text-align: center; background: #f8f9fa;">';
                echo '<p><strong>' . $total_results . '</strong> items found</p>';
                echo '</div>';
            }
            ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>