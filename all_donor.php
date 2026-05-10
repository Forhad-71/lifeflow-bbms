<?php
// all_donor.php - LifeFlow All Donors List
require "includes/auth.php";
require_admin();
require "config.php";

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get total count
$totalResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM donor");
$total = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($total / $perPage);

// Get donors with pagination
$donors = [];
$result = mysqli_query($conn, "SELECT * FROM donor ORDER BY donor_id DESC LIMIT $perPage OFFSET $offset");
while ($row = mysqli_fetch_assoc($result)) {
    $donors[] = $row;
}

$pageTitle = "All Donors - LifeFlow";
include 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-header" id="pageHeader" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1 class="page-title"><i class="fas fa-users" style="color: var(--primary);"></i> All Donors</h1>
                <p class="page-subtitle">Showing <?php echo count($donors); ?> of <?php echo $total; ?> registered donors</p>
            </div>
            <div style="display: flex; gap: 15px;">
                <a href="search_donor.php" class="btn btn--glass">
                    <i class="fas fa-search"></i> Search
                </a>
                <a href="add_donor.php" class="btn btn--primary">
                    <i class="fas fa-plus"></i> Add Donor
                </a>
            </div>
        </div>
        
        <div class="card" id="donorTable">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Blood Group</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>City</th>
                            <th>Gender</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($donors)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                                No donors found. <a href="add_donor.php" style="color: var(--primary);">Add one now</a>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($donors as $donor): ?>
                        <tr>
                            <td>#<?php echo $donor['donor_id']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 35px; height: 35px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.8rem;">
                                        <?php echo strtoupper(substr($donor['full_name'], 0, 2)); ?>
                                    </div>
                                    <?php echo htmlspecialchars($donor['full_name']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge--danger" style="font-weight: 700; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($donor['blood_group']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($donor['mobile_no']); ?></td>
                            <td><?php echo htmlspecialchars($donor['email'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($donor['city'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($donor['gender'] ?? '-'); ?></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="update_donor.php?id=<?php echo $donor['donor_id']; ?>" class="btn btn--icon btn--glass" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_donor.php?id=<?php echo $donor['donor_id']; ?>" class="btn btn--icon btn--danger" title="Delete" onclick="return confirm('Delete this donor?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
            <!-- Pagination -->
            <div style="display: flex; justify-content: center; gap: 10px; margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="btn btn--glass btn--small">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="btn <?php echo $i === $page ? 'btn--primary' : 'btn--glass'; ?> btn--small">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="btn btn--glass btn--small">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function playEntranceAnimations() {
    gsap.from('#pageHeader', { y: 30, opacity: 0, duration: 0.6 });
    gsap.from('#donorTable', { y: 50, opacity: 0, duration: 0.8, delay: 0.2 });
}
</script>
