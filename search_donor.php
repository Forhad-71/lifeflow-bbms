<?php
// search_donor.php - LifeFlow Search Donors
require "includes/auth.php";
require_admin();
require "config.php";

$results = [];
$searchPerformed = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['blood_type']) || isset($_GET['search_value']))) {
    $searchPerformed = true;
    
    $bloodType = $_GET['blood_type'] ?? '';
    $searchBy = $_GET['search_by'] ?? 'name';
    $searchValue = trim($_GET['search_value'] ?? '');
    
    $sql = "SELECT * FROM donor WHERE 1=1";
    $params = [];
    $types = "";
    
    if ($bloodType) {
        $sql .= " AND blood_group = ?";
        $params[] = $bloodType;
        $types .= "s";
    }
    
    if ($searchValue) {
        switch ($searchBy) {
            case 'mobile':
                $sql .= " AND mobile_no LIKE ?";
                break;
            case 'email':
                $sql .= " AND email LIKE ?";
                break;
            case 'city':
                $sql .= " AND city LIKE ?";
                break;
            default: // name
                $sql .= " AND full_name LIKE ?";
        }
        $params[] = "%$searchValue%";
        $types .= "s";
    }
    
    $sql .= " ORDER BY full_name ASC LIMIT 50";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $results[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$pageTitle = "Search Donors - LifeFlow";
include 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-header" id="pageHeader">
            <h1 class="page-title"><i class="fas fa-search" style="color: var(--primary);"></i> Search Donors</h1>
            <p class="page-subtitle">Find donors by name, mobile, email or city</p>
        </div>
        
        <!-- Search Form -->
        <div class="card" id="searchForm" style="margin-bottom: 30px;">
            <form method="GET" action="">
                <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                    <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                        <label><i class="fas fa-filter"></i> Search By</label>
                        <select name="search_by" id="searchBy" onchange="updatePlaceholder()">
                            <option value="name" <?php echo ($_GET['search_by'] ?? '') === 'name' ? 'selected' : ''; ?>>
                                👤 Name
                            </option>
                            <option value="mobile" <?php echo ($_GET['search_by'] ?? '') === 'mobile' ? 'selected' : ''; ?>>
                                📱 Mobile
                            </option>
                            <option value="email" <?php echo ($_GET['search_by'] ?? '') === 'email' ? 'selected' : ''; ?>>
                                ✉️ Email
                            </option>
                            <option value="city" <?php echo ($_GET['search_by'] ?? '') === 'city' ? 'selected' : ''; ?>>
                                🏙️ City
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="flex: 2; min-width: 250px; margin-bottom: 0;">
                        <label><i class="fas fa-search"></i> Search Value</label>
                        <input type="text" name="search_value" id="searchInput" placeholder="Search by name..." value="<?php echo htmlspecialchars($_GET['search_value'] ?? ''); ?>">
                    </div>
                    
                    <!-- Hidden field for blood type from quick filter -->
                    <input type="hidden" name="blood_type" id="bloodTypeHidden" value="<?php echo htmlspecialchars($_GET['blood_type'] ?? ''); ?>">
                    
                    <button type="submit" class="btn btn--primary btn--large">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Quick Blood Type Filters -->
        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 25px;" id="quickFilters">
            <span style="color: var(--text-muted); display: flex; align-items: center;">Quick filter:</span>
            <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type): ?>
            <a href="?blood_type=<?php echo urlencode($type); ?>" class="btn btn--small <?php echo ($_GET['blood_type'] ?? '') === $type ? 'btn--primary' : 'btn--glass'; ?>">
                <?php echo $type; ?>
            </a>
            <?php endforeach; ?>
            <a href="?" class="btn btn--small btn--glass">All</a>
        </div>
        
        <!-- Results -->
        <div id="searchResults">
            <?php if ($searchPerformed): ?>
                <?php if (empty($results)): ?>
                <div class="card" style="text-align: center; padding: 60px;">
                    <i class="fas fa-user-slash" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 20px;"></i>
                    <h3>No Donors Found</h3>
                    <p style="color: var(--text-muted);">Try adjusting your search criteria</p>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card__header">
                        <h3 class="card__title">
                            <i class="fas fa-users" style="color: var(--primary);"></i>
                            Found <?php echo count($results); ?> donor<?php echo count($results) !== 1 ? 's' : ''; ?>
                        </h3>
                    </div>
                    
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Blood Group</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>City</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $donor): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 35px; height: 35px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.8rem;">
                                                <?php echo strtoupper(substr($donor['full_name'], 0, 2)); ?>
                                            </div>
                                            <?php echo htmlspecialchars($donor['full_name']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge--danger" style="font-weight: 700;">
                                            <?php echo htmlspecialchars($donor['blood_group']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="tel:<?php echo htmlspecialchars($donor['mobile_no']); ?>" style="color: var(--primary);">
                                            <?php echo htmlspecialchars($donor['mobile_no']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($donor['email'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($donor['city'] ?? '-'); ?></td>
                                    <td>
                                        <a href="tel:<?php echo htmlspecialchars($donor['mobile_no']); ?>" class="btn btn--success btn--small">
                                            <i class="fas fa-phone"></i> Call
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            <?php else: ?>
            <div class="card" style="text-align: center; padding: 60px;">
                <i class="fas fa-search" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 20px;"></i>
                <h3>Search for Donors</h3>
                <p style="color: var(--text-muted);">Use the form above to search by name, mobile, email or city</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function playEntranceAnimations() {
    gsap.from('#pageHeader', { y: 30, opacity: 0, duration: 0.6 });
    gsap.from('#searchForm', { y: 20, opacity: 0, duration: 0.5, delay: 0.2 });
    gsap.from('#quickFilters', { y: 20, opacity: 0, duration: 0.4, delay: 0.3 });
    gsap.from('#searchResults', { y: 30, opacity: 0, duration: 0.6, delay: 0.4 });
}

// Update placeholder based on search type
function updatePlaceholder() {
    const searchBy = document.getElementById('searchBy').value;
    const input = document.getElementById('searchInput');
    
    const placeholders = {
        'name': 'Search by name...',
        'mobile': 'Search by mobile number...',
        'email': 'Search by email address...',
        'city': 'Search by city...'
    };
    
    input.placeholder = placeholders[searchBy] || 'Search...';
}

// Run on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePlaceholder();
});
</script>
