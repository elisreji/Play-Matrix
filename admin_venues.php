<?php
session_start();
require_once 'db_connect.php';

// Check admin access
$adminEmails = ['admin@gmail.com', 'elisr@gmail.com', 'paulinsabu@gmail.com'];
if (!isset($_SESSION['user']) || !in_array($_SESSION['user'], $adminEmails)) {
    header("Location: login.php");
    exit;
}

$message = "";

// Handle Venue Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $venueId = $_POST['venue_id'];

    if ($action === 'approve_venue') {
        $stmt = $pdo->prepare("UPDATE TRAINER_VENUES SET approval_status = 'Approved' WHERE id = ?");
        if ($stmt->execute([$venueId])) {
            $message = "Venue approved successfully.";
        }
    } elseif ($action === 'reject_venue') {
        $stmt = $pdo->prepare("UPDATE TRAINER_VENUES SET approval_status = 'Rejected' WHERE id = ?");
        if ($stmt->execute([$venueId])) {
            $message = "Venue rejected.";
        }
    }
}

// Fetch Pending Venues
$pendingVenues = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM TRAINER_VENUES WHERE approval_status = 'Pending' ORDER BY created_at ASC");
        $stmt->execute();
        $pendingVenues = $stmt->fetchAll();
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Venue Approvals - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #050505;
            --bg-sidebar: #0a0a0a;
            --primary-green: #39ff14;
            --text-gray: #a1a1a1;
            --glass-border: rgba(255, 255, 255, 0.08);
            --danger: #ff4444;
        }
        body { background: var(--bg-dark); color: white; font-family: 'Outfit', sans-serif; margin: 0; display: flex; }
        .sidebar { width: 280px; background: var(--bg-sidebar); border-right: 1px solid var(--glass-border); height: 100vh; position: fixed; padding: 20px; overflow-y: auto; }
        .main { margin-left: 280px; padding: 40px; flex: 1; }
        .card { background: #121212; border-radius: 16px; border: 1px solid var(--glass-border); padding: 20px; margin-top: 20px; }
        .venues-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .venue-card { background: #0a0a0a; border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; }
        .action-btn { background: none; border: 1px solid var(--glass-border); color: white; padding: 10px 20px; border-radius: 8px; cursor: pointer; transition: 0.3s; flex: 1; font-weight: 600; }
        .action-btn.approve { background: var(--primary-green); color: black; border: none; }
        .action-btn.approve:hover { opacity: 0.85; }
        .action-btn.reject { border-color: var(--danger); color: var(--danger); }
        .action-btn.reject:hover { background: rgba(255, 68, 68, 0.1); }
        .badge-pending { background: rgba(255, 193, 7, 0.1); color: #ffc107; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
        .nav-link { display: flex; align-items: center; gap: 15px; padding: 12px; color: var(--text-gray); text-decoration: none; border-radius: 10px; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { background: rgba(57, 255, 20, 0.1); color: var(--primary-green); }
        .venue-img { width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 15px; }
        .venue-meta { color: var(--text-gray); font-size: 0.85rem; margin: 5px 0; }
        .venue-meta i { color: var(--primary-green); width: 16px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2 style="color: var(--primary-green); margin-bottom: 30px;">PLAYMATRIX</h2>
        <a href="admin.php" class="nav-link"><i class="fa-solid fa-chart-pie"></i> Overview</a>
        <a href="admin_users.php" class="nav-link"><i class="fa-solid fa-user-group"></i> Users</a>
        <a href="admin_trainer_requests.php" class="nav-link"><i class="fa-solid fa-address-card"></i> Trainer Approvals</a>
        <a href="admin_tournaments.php" class="nav-link"><i class="fa-solid fa-circle-check"></i> Tournament Approvals</a>
        <a href="admin_venues.php" class="nav-link active"><i class="fa-solid fa-map-location-dot"></i> Venue Approvals</a>
        <a href="admin.php#refunds" class="nav-link"><i class="fa-solid fa-money-bill-transfer"></i> Refund Requests</a>
        <a href="logout.php" class="nav-link" style="margin-top: 50px;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main">
        <h1>Pending Venue Approvals</h1>

        <?php if ($message): ?>
            <div style="background: rgba(57, 255, 20, 0.1); color: var(--primary-green); padding: 15px; border-radius: 8px; margin: 20px 0;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <?php if (empty($pendingVenues)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-gray);">
                    <i class="fa-solid fa-map-location-dot" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.2;"></i>
                    <p>No venues awaiting approval at the moment.</p>
                </div>
            <?php else: ?>
                <div class="venues-grid">
                    <?php foreach ($pendingVenues as $venue): ?>
                        <div class="venue-card">
                            <?php if (!empty($venue['pic1'])): ?>
                                <img src="<?php echo htmlspecialchars($venue['pic1']); ?>" class="venue-img" alt="Venue">
                            <?php endif; ?>

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <h3><?php echo htmlspecialchars($venue['venue_name']); ?></h3>
                                <span class="badge-pending">PENDING</span>
                            </div>

                            <p class="venue-meta"><i class="fa-solid fa-user-tie"></i> <?php echo htmlspecialchars($venue['trainer_email']); ?></p>
                            <p class="venue-meta"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($venue['location']); ?></p>
                            <p class="venue-meta"><i class="fa-solid fa-futbol"></i> <?php echo htmlspecialchars($venue['sports_available']); ?></p>
                            <?php if (!empty($venue['capacity'])): ?>
                                <p class="venue-meta"><i class="fa-solid fa-users"></i> Capacity: <?php echo htmlspecialchars($venue['capacity']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($venue['price_per_hour'])): ?>
                                <p class="venue-meta"><i class="fa-solid fa-indian-rupee-sign"></i> ₹<?php echo number_format($venue['price_per_hour'], 2); ?>/hr</p>
                            <?php endif; ?>

                            <div style="display: flex; gap: 10px; margin-top: auto; padding-top: 15px;">
                                <form method="POST" style="display: contents;">
                                    <input type="hidden" name="venue_id" value="<?php echo $venue['id']; ?>">
                                    <button name="action" value="approve_venue" class="action-btn approve">
                                        <i class="fa-solid fa-check"></i> Approve
                                    </button>
                                    <button name="action" value="reject_venue" class="action-btn reject">
                                        <i class="fa-solid fa-xmark"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
