<?php
session_start();
require_once 'config/database.php';

// Check required parameters
if(!isset($_GET['type']) || !isset($_GET['id'])) {
    header("Location: index.php?error=Invalid request");
    exit;
}

$type = $_GET['type'];
$id = intval($_GET['id']);

if($type == 'lost') {
    $stmt = $conn->prepare("SELECT li.*, c.name as category_name, u.name as user_name, u.phone as user_phone, 
                           u.email as user_email
                           FROM lost_items li 
                           LEFT JOIN categories c ON li.category_id = c.id 
                           LEFT JOIN users u ON li.user_id = u.id 
                           WHERE li.id = ?");
} else {
    $stmt = $conn->prepare("SELECT fi.*, c.name as category_name, u.name as user_name, u.phone as user_phone,
                           u.email as user_email, 
                           uc.name as claimed_by_name, uc.email as claimed_by_email
                           FROM found_items fi 
                           LEFT JOIN categories c ON fi.category_id = c.id 
                           LEFT JOIN users u ON fi.user_id = u.id 
                           LEFT JOIN users uc ON fi.claimed_by = uc.id
                           WHERE fi.id = ?");
}

$stmt->execute([$id]);
$item = $stmt->fetch();

if(!$item) {
    header("Location: index.php?error=Item not found");
    exit;
}

// Handle claim submission if form was posted
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['claim_item'])) {
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php?redirect=view_item.php?type=" . $type . "&id=" . $id);
        exit;
    }
    
    // Update the item as claimed (WITHOUT claimed_at)
    try {
        $update_stmt = $conn->prepare("UPDATE found_items SET status = 'claimed', claimed_by = ? WHERE id = ? AND status = 'unclaimed'");
        $update_stmt->execute([$_SESSION['user_id'], $id]);
        
        if($update_stmt->rowCount() > 0) {
            // Refresh item data
            $stmt->execute([$id]);
            $item = $stmt->fetch();
            
            $success_message = "✅ Item claimed successfully! The owner has been notified.";
        } else {
            $error_message = "Item could not be claimed. It may have already been claimed.";
        }
    } catch(PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}
?>

<?php include 'includes/header.php'; ?>

<div style="max-width: 800px; margin: 2rem auto; padding: 0 2rem;">
    <div class="glass-card">
        <!-- Success/Error Messages -->
    <?php if(isset($success_message)): ?>
        <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #28a745;">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
    <?php elseif(isset($error_message)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #dc3545;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
    <?php elseif(isset($_GET['message'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #28a745;">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php elseif(isset($_GET['error'])): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #dc3545;">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>
    
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
                        <p><strong>Status:</strong> 
                            <span style="color: <?php echo ($item['status'] == 'found') ? 'var(--success)' : 'var(--warning)'; ?>; font-weight: 600;">
                                <?php echo ($item['status'] == 'found') ? 'Found/Recovered' : 'Still Missing'; ?>
                            </span>
                        </p>
                    <?php else: ?>
                        <p><strong>Found Date:</strong> <?php echo date('F j, Y', strtotime($item['found_date'])); ?></p>
                        <p><strong>Found Location:</strong> <?php echo htmlspecialchars($item['found_location']); ?></p>
                        <p><strong>Status:</strong> 
                            <?php if($item['status'] == 'claimed'): ?>
                                <span style="color: var(--primary); font-weight: 600;">
                                    <i class="fas fa-check-circle"></i> Claimed
                                </span>
                                <?php if($item['claimed_by_name']): ?>
                                    <br><small>By: <?php echo htmlspecialchars($item['claimed_by_name']); ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: var(--success); font-weight: 600;">
                                    <i class="fas fa-clock"></i> Available for Claim
                                </span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <p><strong>Reported on:</strong> <?php echo date('F j, Y g:i A', strtotime($item['created_at'])); ?></p>
                </div>
                
                <!-- CLAIM BUTTON SECTION -->
                <?php if($type == 'found' && $item['status'] == 'unclaimed'): ?>
                <div style="margin-top: 1.5rem;">
                    <h3 style="color: var(--gray); margin-bottom: 0.5rem; font-size: 1rem;">
                        <i class="fas fa-hand-paper"></i> CLAIM THIS ITEM
                    </h3>
                    <div style="background: #e7f7f3; padding: 1.5rem; border-radius: 8px; border: 2px dashed #28a745;">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <?php if($_SESSION['user_id'] != $item['user_id']): ?>
                                <p style="color: var(--success); margin-bottom: 1rem;">
                                    <i class="fas fa-info-circle"></i> Is this your item? Click below to claim it.
                                </p>
                                <form method="POST" onsubmit="return confirm('Are you sure this is your item? You will need to provide proof of ownership.');">
                                    <input type="hidden" name="claim_item" value="1">
                                    <button type="submit" class="btn-modern" style="background: var(--success); color: white; width: 100%; padding: 1rem; font-size: 1.1rem;">
                                        <i class="fas fa-hand-paper"></i> CLAIM THIS ITEM NOW
                                    </button>
                                </form>
                                <p style="color: var(--gray); font-size: 0.9rem; margin-top: 0.5rem;">
                                    <i class="fas fa-shield-alt"></i> Your claim will be verified by the finder.
                                </p>
                            <?php else: ?>
                                <p style="color: var(--warning);">
                                    <i class="fas fa-exclamation-triangle"></i> You reported this item. Wait for someone to claim it.
                                </p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p style="color: var(--success); margin-bottom: 1rem;">
                                <i class="fas fa-info-circle"></i> If this is your item, please login to claim it.
                            </p>
                            <a href="login.php?redirect=<?php echo urlencode('view_item.php?type=found&id=' . $id); ?>" 
                               class="btn-modern" style="background: var(--success); color: white; width: 100%; padding: 1rem; font-size: 1.1rem;">
                                <i class="fas fa-sign-in-alt"></i> LOGIN TO CLAIM
                            </a>
                            <p style="color: var(--gray); font-size: 0.9rem; margin-top: 0.5rem;">
                                <i class="fas fa-user-plus"></i> Don't have an account? <a href="register.php" style="color: var(--primary);">Register here</a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <!-- END CLAIM BUTTON SECTION -->
            </div>
            
            <div>
                <h3 style="color: var(--gray); margin-bottom: 0.5rem; font-size: 1rem;">
                    <i class="fas fa-align-left"></i> DESCRIPTION
                </h3>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; white-space: pre-wrap; min-height: 200px; line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                </div>
                
                <div style="margin-top: 1.5rem;">
                    <h3 style="color: var(--gray); margin-bottom: 0.5rem; font-size: 1rem;">
                        <i class="fas fa-user-circle"></i> CONTACT INFORMATION
                    </h3>
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                        <p><strong>Reporter:</strong> <?php echo htmlspecialchars($item['user_name']); ?></p>
                        
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <?php if($_SESSION['user_id'] == $item['user_id'] || $_SESSION['role'] == 'admin' || ($type == 'found' && $item['status'] == 'claimed' && $_SESSION['user_id'] == $item['claimed_by'])): ?>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($item['user_email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($item['user_phone']); ?></p>
                                <?php if($type == 'found' && $item['status'] == 'claimed' && $item['claimed_by_name']): ?>
                                    <p><strong>Claimed by:</strong> <?php echo htmlspecialchars($item['claimed_by_name']); ?></p>
                                    <p><strong>Claimant Email:</strong> <?php echo htmlspecialchars($item['claimed_by_email']); ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p><strong>Contact:</strong> <span style="color: var(--gray);">Only visible to item owner and admin</span></p>
                                <p><small>If this is your item, claim it to get contact details.</small></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p><strong>Contact Details:</strong> <span style="color: var(--gray);">Login to view contact information</span></p>
                            <p><small>For privacy, contact details are only shown to verified users.</small></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee; flex-wrap: wrap;">
            <a href="search.php" class="btn-modern" style="background: var(--gray); color: white;">
                <i class="fas fa-arrow-left"></i> Back to Search
            </a>
            
            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id']): ?>
                <a href="edit_item.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>" class="btn-modern" style="background: var(--primary); color: white;">
                    <i class="fas fa-edit"></i> Edit Item
                </a>
                <a href="delete_item.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>" 
                   class="btn-modern" 
                   style="background: var(--danger); color: white;"
                   onclick="return confirm('Are you sure you want to delete this item?');">
                    <i class="fas fa-trash"></i> Delete
                </a>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin_item.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>" class="btn-modern" style="background: var(--warning); color: black;">
                    <i class="fas fa-cog"></i> Admin Actions
                </a>
            <?php endif; ?>
            
            <a href="index.php" class="btn-modern" style="background: var(--light); color: var(--dark);">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
