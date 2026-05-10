<?php
// view_requests.php - LifeFlow Blood Requests
require "includes/auth.php";
require_admin();
require "config.php";

// Get all requests
$requests = [];
$result = mysqli_query($conn, "SELECT * FROM request ORDER BY date DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $requests[] = $row;
}

$totalRequests = count($requests);

$pageTitle = "Blood Requests - LifeFlow";
include 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-header" id="pageHeader">
            <h1 class="page-title"><i class="fas fa-hand-holding-medical" style="color: var(--primary);"></i> Blood Requests</h1>
            <p class="page-subtitle">Total <?php echo $totalRequests; ?> blood requests received</p>
        </div>
        
        <!-- Requests List -->
        <div id="requestsList">
            <?php if (empty($requests)): ?>
            <div class="card" style="text-align: center; padding: 60px;">
                <i class="fas fa-inbox" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 20px;"></i>
                <h3 style="margin-bottom: 10px;">No Requests Found</h3>
                <p style="color: var(--text-muted);">There are no blood requests yet.</p>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient Name</th>
                                <th>Blood Group</th>
                                <th>Units</th>
                                <th>Mobile</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                            <tr id="request-<?php echo $request['request_id']; ?>">
                                <td>#<?php echo $request['request_id']; ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                            <?php echo htmlspecialchars($request['blood_group']); ?>
                                        </div>
                                        <?php echo htmlspecialchars($request['name']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge--danger" style="font-weight: 700;">
                                        <?php echo htmlspecialchars($request['blood_group']); ?>
                                    </span>
                                </td>
                                <td><?php echo $request['units_needed']; ?> units</td>
                                <td>
                                    <a href="tel:<?php echo htmlspecialchars($request['mobile']); ?>" style="color: var(--primary);">
                                        <?php echo htmlspecialchars($request['mobile']); ?>
                                    </a>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($request['date'])); ?></td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <button onclick="approveRequest(<?php echo $request['request_id']; ?>, '<?php echo $request['blood_group']; ?>', <?php echo $request['units_needed']; ?>)" 
                                                class="btn btn--success btn--small" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button onclick="declineRequest(<?php echo $request['request_id']; ?>)" 
                                                class="btn btn--danger btn--small" title="Decline">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function playEntranceAnimations() {
    gsap.from('#pageHeader', { y: 30, opacity: 0, duration: 0.6 });
    gsap.from('#requestsList', { y: 30, opacity: 0, duration: 0.6, delay: 0.2 });
}

// Approve Request
function approveRequest(requestId, bloodGroup, units) {
    if (!confirm('Approve this request? This will decrease ' + units + ' units of ' + bloodGroup + ' from stock.')) {
        return;
    }
    
    fetch('api/approve_request.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ request_id: requestId, blood_group: bloodGroup, units: units })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Toast.show('Request approved! Stock updated.', 'success');
            // Remove row with animation
            const row = document.getElementById('request-' + requestId);
            gsap.to(row, { opacity: 0, x: 100, duration: 0.3, onComplete: () => row.remove() });
        } else {
            Toast.show(data.message || 'Failed to approve', 'error');
        }
    })
    .catch(err => {
        Toast.show('Error approving request', 'error');
    });
}

// Decline Request
function declineRequest(requestId) {
    if (!confirm('Are you sure you want to decline this request?')) {
        return;
    }
    
    fetch('api/decline_request.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ request_id: requestId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Toast.show('Request declined', 'success');
            // Remove row with animation
            const row = document.getElementById('request-' + requestId);
            gsap.to(row, { opacity: 0, x: -100, duration: 0.3, onComplete: () => row.remove() });
        } else {
            Toast.show(data.message || 'Failed to decline', 'error');
        }
    })
    .catch(err => {
        Toast.show('Error declining request', 'error');
    });
}
</script>
