<?php
require_once 'config/database.php';

// Check required parameters
if(!isset($_GET['type']) || !isset($_GET['id'])) {
    header("Location: index.php?error=Invalid request");
    exit;
}

$type = $_GET['type'];
$id = intval($_GET['id']);

if($type == 'lost') {
    $stmt = $conn->prepare("SELECT li.*, c.name as category_name, u.name as user_name, u.phone as user_phone 
                           FROM lost_items li 
                           LEFT JOIN categories c ON li.category_id = c.id 
                           LEFT JOIN users u ON li.user_id = u.id 
                           WHERE li.id = ?");
} else {
    $stmt = $conn->prepare("SELECT fi.*, c.name as category_name, u.name as user_name, u.phone as user_phone 
                           FROM found_items fi 
                           LEFT JOIN categories c ON fi.category_id = c.id 
                           LEFT JOIN users u ON fi.user_id = u.id 
                           WHERE fi.id = ?");
}

$stmt->execute([$id]);
$item = $stmt->fetch();

if(!$item) {
    header("Location: index.php?error=Item not found");
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<div style="max-width: 800px; margin: 2rem auto; padding: 0 2rem;">
    <div class="glass-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h1 style="color: var(--dark);">
                <?php echo htmlspecialchars($item['item_name']); ?>
            </h1>
            <div class="item-badge <?php echo ($type == 'lost') ? 'badge-lost' : 'badge-found'; ?>">
                <?php echo ($type == 'lost') ? 'Lost' : 'Found'; ?>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div>
                <h3 style="color: var(--gray); margin-bottom: 0.5rem; font-size: 1rem;">
                    <i class="fas fa-info-circle"></i> ITEM DETAILS
                </h3>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category_name']); ?></p>
                    <p><strong>Reported by:</strong> <?php echo htmlspecialchars($item['user_name']); ?></p>
                    <?php if($type == 'lost'): ?>
                        <p><strong>Lost Date:</strong> <?php echo date('F j, Y', strtotime($item['lost_date'])); ?></p>
                        <p><strong>Lost Location:</strong> <?php echo htmlspecialchars($item['lost_location']); ?></p>
                        <p><strong>Status:</strong> <span style="color: var(--warning); font-weight: 600;">Still Missing</span></p>
                    <?php else: ?>
                        <p><strong>Found Date:</strong> <?php echo date('F j, Y', strtotime($item['found_date'])); ?></p>
                        <p><strong>Found Location:</strong> <?php echo htmlspecialchars($item['found_location']); ?></p>
                        <p><strong>Status:</strong> 
                            <span style="color: <?php echo ($item['status'] == 'claimed') ? 'var(--primary)' : 'var(--success)'; ?>; font-weight: 600;">
                                <?php echo ($item['status'] == 'claimed') ? 'Claimed' : 'Available for Claim'; ?>
                            </span>
                        </p>
                    <?php endif; ?>
                    <p><strong>Reported on:</strong> <?php echo date('F j, Y', strtotime($item['created_at'])); ?></p>
                </div>
                
                <?php if($type == 'found' && $item['status'] == 'unclaimed'): ?>
                <div style="margin-top: 1.5rem;">
                    <h3 style="color: var(--gray); margin-bottom: 0.5rem; font-size: 1rem;">
                        <i class="fas fa-hand-paper"></i> CLAIM THIS ITEM
                    </h3>
                    <div style="background: #e7f7f3; padding: 1rem; border-radius: 8px;">
                        <p style="color: var(--success); margin-bottom: 1rem;">
                            <i class="fas fa-info-circle"></i> If this is your item, please login to claim it.
                        </p>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="claim_item.php?type=found&id=<?php echo $id; ?>" class="btn-modern" style="background: var(--success); color: white;">
                                <i class="fas fa-hand-paper"></i> Claim This Item
                            </a>
                        <?php else: ?>
                            <a href="login.php?redirect=view_item.php?type=found&id=<?php echo $id; ?>" class="btn-modern" style="background: var(--success); color: white;">
                                <i class="fas fa-sign-in-alt"></i> Login to Claim
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div>
                <h3 style="color: var(--gray); margin-bottom: 0.5rem; font-size: 1rem;">
                    <i class="fas fa-align-left"></i> DESCRIPTION
                </h3>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; white-space: pre-wrap; min-height: 200px;">
                    <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                </div>
                
                <div style="margin-top: 1.5rem;">
                    <h3 style="color: var(--gray); margin-bottom: 0.5rem; font-size: 1rem;">
                        <i class="fas fa-user-circle"></i> CONTACT INFORMATION
                    </h3>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                        <p><strong>Reporter:</strong> <?php echo htmlspecialchars($item['user_name']); ?></p>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($item['user_phone']); ?></p>
                        <?php else: ?>
                            <p><strong>Phone:</strong> <span style="color: var(--gray);">Login to view contact details</span></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
            <a href="search.php" class="btn-modern" style="background: var(--gray); color: white;">
                <i class="fas fa-arrow-left"></i> Back to Search
            </a>
            
            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id']): ?>
                <button class="btn-modern" style="background: var(--primary); color: white;">
                    <i class="fas fa-edit"></i> Edit Item
                </button>
                <button class="btn-modern" style="background: var(--danger); color: white;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            <?php endif; ?>
            
            <a href="index.php" class="btn-modern" style="background: var(--light); color: var(--dark);">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>