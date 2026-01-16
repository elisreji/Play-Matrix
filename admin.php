<?php
session_start();
require_once 'db_connect.php';

// Access Control
$adminEmails = ["elisreji2028@mca.ajce.in", "junaelsamathew2028@mca.ajce.in"];
if (!isset($_SESSION['user']) || !in_array($_SESSION['user'], $adminEmails)) {
    header("Location: login.php");
    exit;
}
$currentUser = $_SESSION['user'];

// Handle Lockdown Toggle
$settingsFile = 'settings.json';
$settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : ['lockdown_mode' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_lockdown') {
    $settings['lockdown_mode'] = !($settings['lockdown_mode']);
    file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
    $successMessage = $settings['lockdown_mode'] ? "Platform LOCKED DOWN!" : "Platform RESTORED!";
}

// Handle Plan Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_plan') {
    $planId = $_POST['plan_id'];
    $planName = $_POST['plan_name'];
    $planPrice = $_POST['plan_price'];
    $planBadge = $_POST['plan_badge'];
    $planFeatures = explode("\n", trim($_POST['plan_features']));
    $planFeatures = array_map('trim', $planFeatures);

    $plans = json_decode(file_get_contents('plans.json'), true);
    foreach ($plans as &$plan) {
        if ($plan['id'] === $planId) {
            $plan['name'] = $planName;
            $plan['price'] = $planPrice;
            $plan['badge'] = $planBadge;
            $plan['features'] = $planFeatures;
            break;
        }
    }
    file_put_contents('plans.json', json_encode($plans, JSON_PRETTY_PRINT));
    $successMessage = "Plan updated successfully!";
}

// Handle User Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $userEmail = $_POST['email'] ?? '';

    if (in_array($action, ['update_user', 'delete_user', 'toggle_block']) && !empty($userEmail)) {
        $users = json_decode(file_get_contents('users.json'), true);
        $updated = false;

        // 1. Sync Database
        if ($pdo) {
            try {
                if ($action === 'delete_user' && $userEmail !== $currentUser) {
                    // Completely delete user - they can register again as new member
                    $stmt = $pdo->prepare("DELETE FROM USERS WHERE email = ?");
                    $stmt->execute([$userEmail]);
                    $updated = true;
                } elseif ($action === 'toggle_block' && $userEmail !== $currentUser) {
                    // Toggle block status - blocked users cannot login or register
                    $stmt = $pdo->prepare("UPDATE USERS SET is_blocked = NOT is_blocked WHERE email = ?");
                    $stmt->execute([$userEmail]);
                    $updated = true;
                } elseif ($action === 'update_user') {
                    $stmt = $pdo->prepare("UPDATE USERS SET full_name = ?, role = ? WHERE email = ?");
                    $stmt->execute([$_POST['name'], $_POST['role'], $userEmail]);
                    $updated = true;
                }
            } catch (Exception $e) {
            }
        }

        // 2. Sync JSON
        foreach ($users as $key => &$user) {
            if ($user['email'] === $userEmail) {
                if ($action === 'delete_user' && $userEmail !== $currentUser) {
                    // Completely remove from JSON - allows re-registration
                    unset($users[$key]);
                    $users = array_values($users);
                    $successMessage = "User deleted successfully! They can now register as a new member.";
                    $updated = true;
                } elseif ($action === 'toggle_block' && $userEmail !== $currentUser) {
                    // Toggle block - prevents login and registration
                    $user['is_blocked'] = !(isset($user['is_blocked']) && $user['is_blocked']);
                    $successMessage = $user['is_blocked'] ? "User blocked! They cannot login or register." : "User unblocked! They can now access the platform.";
                    $updated = true;
                } elseif ($action === 'update_user') {
                    $user['name'] = $_POST['name'] ?? $user['name'];
                    $user['role'] = $_POST['role'] ?? $user['role'];
                    $successMessage = "User profile updated!";
                    $updated = true;
                }
                break;
            }
        }

        if ($updated) {
            file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
        }
    }
}

// Handle Ads/Promotions Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $adsFile = 'ads.json';
    $ads = file_exists($adsFile) ? json_decode(file_get_contents($adsFile), true) : [];
    $updatedAds = false;

    if ($action === 'add_ad' || $action === 'update_ad') {
        $adId = $_POST['ad_id'] ?? uniqid('ad_');
        $adTitle = $_POST['title'] ?? '';
        $adStatus = $_POST['status'] ?? 'Active';
        $adBanner = $_POST['banner'] ?? ''; // Placeholder for now

        $adData = [
            'id' => $adId,
            'title' => $adTitle,
            'status' => $adStatus,
            'banner' => $adBanner
        ];

        if ($action === 'add_ad') {
            $ads[] = $adData;
        } else {
            foreach ($ads as &$ad) {
                if ($ad['id'] === $adId) {
                    $ad = $adData;
                    break;
                }
            }
        }
        $updatedAds = true;
        $successMessage = $action === 'add_ad' ? "New promotion added!" : "Promotion updated!";
    } elseif ($action === 'delete_ad') {
        $adId = $_POST['ad_id'];
        foreach ($ads as $key => $ad) {
            if ($ad['id'] === $adId) {
                unset($ads[$key]);
                $ads = array_values($ads);
                $updatedAds = true;
                $successMessage = "Promotion deleted!";
                break;
            }
        }
    }

    if ($updatedAds) {
        file_put_contents($adsFile, json_encode($ads, JSON_PRETTY_PRINT));
    }
}

// Handle Dispute Resolutions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resolve_dispute') {
    $disputeId = $_POST['dispute_id'] ?? '';
    $disputesFile = 'disputes.json';

    if (file_exists($disputesFile)) {
        $disputes = json_decode(file_get_contents($disputesFile), true);
        $updated = false;
        foreach ($disputes as &$d) {
            if ($d['id'] === $disputeId) {
                $d['status'] = 'Resolved';
                $updated = true;
                break;
            }
        }
        if ($updated) {
            file_put_contents($disputesFile, json_encode($disputes, JSON_PRETTY_PRINT));
            $successMessage = "Dispute #$disputeId marked as Resolved!";
        }
    }
}

// Handle Tournament/Event Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['approve_tournament', 'reject_tournament'])) {
    $tournamentId = $_POST['tournament_id'] ?? '';
    $action = $_POST['action'];
    $status = ($action === 'approve_tournament') ? 'Approved' : 'Rejected';
    $updated = false;

    // 1. Sync Database
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("UPDATE TOURNAMENTS SET admin_approval = ? WHERE tournament_id = ?");
            $stmt->execute([$status, $tournamentId]);
            $updated = true;
        } catch (Exception $e) {
        }
    }

    // 2. Sync JSON
    $tournamentsFile = 'tournaments.json';
    if (file_exists($tournamentsFile)) {
        $tournaments = json_decode(file_get_contents($tournamentsFile), true);
        foreach ($tournaments as &$t) {
            if ($t['id'] == $tournamentId) {
                $t['status'] = $status;
                $updated = true;
                break;
            }
        }
        if ($updated) {
            file_put_contents($tournamentsFile, json_encode($tournaments, JSON_PRETTY_PRINT));
        }
    }

    if ($updated) {
        $successMessage = ($action === 'approve_tournament') ? "Tournament/Event approved!" : "Tournament/Event rejected!";
    }
}


// Fetch some initial data if DB exists
$usersCount = 0;
$tournamentsPending = 0;
$totalBookings = 0;
$revenue = 0;

if ($pdo) {
    try {
        $usersCount = $pdo->query("SELECT COUNT(*) FROM USERS")->fetchColumn();
        $tournamentsPending = $pdo->query("SELECT COUNT(*) FROM TOURNAMENTS WHERE admin_approval = 'Pending'")->fetchColumn();
    } catch (Exception $e) {
    }
}

// Check tournaments.json for pending count if DB count is 0
if ($tournamentsPending == 0 && file_exists('tournaments.json')) {
    $tempTournaments = json_decode(file_get_contents('tournaments.json'), true);
    foreach ($tempTournaments as $tt) {
        if (($tt['status'] ?? 'Pending') === 'Pending')
            $tournamentsPending++;
    }
}

// Check users.json for usersCount if DB count is 0
if ($usersCount == 0 && file_exists('users.json')) {
    $tempUsers = json_decode(file_get_contents('users.json'), true);
    $usersCount = is_array($tempUsers) ? count($tempUsers) : 0;
}

$blockedUsers = [];
if (file_exists('users.json')) {
    $tempUsers = json_decode(file_get_contents('users.json'), true);
    foreach ($tempUsers as $bu) {
        if (isset($bu['is_blocked']) && $bu['is_blocked'])
            $blockedUsers[] = $bu;
    }
}
$settings['blacklist_count'] = count($blockedUsers);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PlayMatrix</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #050505;
            --bg-card: #0c0c0c;
            --bg-sidebar: #0a0a0a;
            --primary-green: #39ff14;
            --primary-green-glow: rgba(57, 255, 20, 0.4);
            --text-white: #ffffff;
            --text-gray: #a1a1a1;
            --glass-border: rgba(255, 255, 255, 0.08);
            --danger: #ff4444;
            --warning: #ffaa00;
            --info: #00aaff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-white);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background-color: var(--bg-sidebar);
            border-right: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            padding: 2rem 1.5rem;
            position: fixed;
            height: 100vh;
            z-index: 100;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 3rem;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-green);
            color: var(--bg-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 1.2rem;
            box-shadow: 0 0 20px var(--primary-green-glow);
        }

        .brand-name {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .nav-list {
            list-style: none;
            flex-grow: 1;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.8rem 1rem;
            color: var(--text-gray);
            text-decoration: none;
            border-radius: 12px;
            transition: 0.3s;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .nav-link:hover {
            color: var(--primary-green);
            background: rgba(57, 255, 20, 0.05);
        }

        .nav-link.active {
            color: var(--bg-dark);
            background: var(--primary-green);
            box-shadow: 0 0 20px rgba(57, 255, 20, 0.2);
        }

        .nav-link.active i {
            color: var(--bg-dark);
        }

        .logout-section {
            padding-top: 2rem;
            border-top: 1px solid var(--glass-border);
        }

        /* Main Content Styles */
        .main-content {
            flex-grow: 1;
            margin-left: 280px;
            padding: 2rem 3rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(45deg, #1a1a1a, #333);
            border: 1px solid var(--glass-border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--primary-green);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.05), transparent);
            transition: 0.5s;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #444;
        }

        @keyframes sweep {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        @keyframes pulse {
            from {
                transform: scale(1);
                opacity: 0.8;
            }

            to {
                transform: scale(1.1);
                opacity: 1;
            }
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 10px;
            position: relative;
            overflow: hidden;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .stat-card:hover {
            border-color: var(--primary-green);
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 10px 30px rgba(57, 255, 20, 0.1);
        }

        .mgmt-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.8rem;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .mgmt-card:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: var(--primary-green);
            transform: scale(1.01);
        }

        .ad-banner-img:hover {
            transform: scale(1.1);
        }

        .stat-label {
            color: var(--text-gray);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 800;
        }

        .stat-trend {
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .trend-up {
            color: var(--primary-green);
        }

        .trend-down {
            color: var(--danger);
        }

        /* Dashboard Sections */
        .dashboard-section {
            display: none;
            animation: fadeIn 0.5s ease-out forwards;
        }

        .dashboard-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th {
            text-align: left;
            padding: 1rem;
            color: var(--text-gray);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            border-bottom: 1px solid var(--glass-border);
        }

        td {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid var(--glass-border);
            font-size: 0.95rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-pending {
            background: rgba(255, 170, 0, 0.1);
            color: var(--warning);
            border: 1px solid var(--warning);
        }

        .badge-approved {
            background: rgba(57, 255, 20, 0.1);
            color: var(--primary-green);
            border: 1px solid var(--primary-green);
        }

        .badge-rejected {
            background: rgba(255, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .badge-blocked {
            background: rgba(255, 68, 68, 0.3);
            color: #ffffff;
            border: 1px solid var(--danger);
        }

        .action-btn {
            background: transparent;
            border: 1px solid var(--glass-border);
            color: var(--text-white);
            padding: 8px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
            margin-right: 5px;
        }

        .action-btn:hover {
            background: var(--primary-green);
            color: var(--bg-dark);
            border-color: var(--primary-green);
        }

        .action-btn.delete:hover {
            background: var(--danger);
            color: var(--text-white);
            border-color: var(--danger);
        }

        /* Grid Layouts for Management */
        .mgmt-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
                padding: 2rem 1rem;
            }

            .brand-name,
            .nav-text {
                display: none;
            }

            .main-content {
                margin-left: 80px;
                padding: 2rem;
            }

            .sidebar:hover {
                width: 280px;
            }

            .sidebar:hover .brand-name,
            .sidebar:hover .nav-text {
                display: block;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-dark);
        }

        ::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="brand">
            <div class="brand-icon">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <span class="brand-name">PLAY MATRIX</span>
        </div>

        <nav class="nav-list">
            <div class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('overview', event)">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span class="nav-text">Overview</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('users', event)">
                    <i class="fa-solid fa-user-group"></i>
                    <span class="nav-text">Platform Users</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('trainers', event)">
                    <i class="fa-solid fa-user-tie"></i>
                    <span class="nav-text">Official Trainers</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('approvals', event)">
                    <i class="fa-solid fa-circle-check"></i>
                    <span class="nav-text">Tournament Approvals</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('subscriptions', event)">
                    <i class="fa-solid fa-gem"></i>
                    <span class="nav-text">Subscriptions</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('bookings', event)">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span class="nav-text">Bookings & Payments</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('security', event)">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span class="nav-text">Platform Security</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('disputes', event)">
                    <i class="fa-solid fa-handshake-angle"></i>
                    <span class="nav-text">Disputes & Refunds</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('ads', event)">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span class="nav-text">Ads & Promotions</span>
                </a>
            </div>
        </nav>

        <div class="logout-section">
            <a href="logout.php" class="nav-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <header>
            <div>
                <h1>Admin Command Center</h1>
                <p style="color: var(--text-gray); margin-top: 5px;">Welcome back, Master Administrator</p>
            </div>
            <div class="user-info">
                <div style="text-align: right;">
                    <div style="font-weight: 600;">
                        <?php echo $currentUser === 'elisreji2028@mca.ajce.in' ? 'Elis Reji' : 'Juna Elsa Mathew'; ?>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-gray);">
                        <?php echo $currentUser; ?>
                    </div>
                </div>
                <div class="user-avatar"><?php echo $currentUser === 'elisreji2028@mca.ajce.in' ? 'ER' : 'JM'; ?></div>
            </div>
        </header>

        <?php if ($settings['lockdown_mode']): ?>
            <div
                style="background: rgba(255, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 1rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; overflow: hidden; position: relative;">
                <div style="display: flex; align-items: center; gap: 10px; z-index: 1;">
                    <i class="fa-solid fa-triangle-exclamation"
                        style="font-size: 1.5rem; animation: pulse 1s infinite alternate;"></i>
                    <div>
                        <strong style="text-transform: uppercase;">System Lockdown Active</strong>
                        <p style="font-size: 0.85rem; opacity: 0.8;">User access is restricted and new bookings are paused.
                        </p>
                    </div>
                </div>
                <form method="POST" style="margin: 0; z-index: 1;">
                    <input type="hidden" name="action" value="toggle_lockdown">
                    <button type="submit"
                        style="background: var(--danger); color: white; border: none; padding: 8px 15px; border-radius: 8px; font-weight: 600; cursor: pointer;">Disable
                        Lockdown</button>
                </form>
                <div
                    style="position: absolute; top: 0; right: 0; bottom: 0; left: 0; background: linear-gradient(90deg, transparent 0%, rgba(255,68,68,0.05) 50%, transparent 100%); animation: sweep 3s infinite linear;">
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($successMessage)): ?>
            <div
                style="background: rgba(57, 255, 20, 0.1); border: 1px solid var(--primary-green); color: var(--primary-green); padding: 1rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; gap: 10px; animation: fadeIn 0.5s ease-out;">
                <i class="fa-solid fa-circle-check"></i>
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <!-- STATS OVERVIEW -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total Users</span>
                <span class="stat-value">
                    <?php echo $usersCount; ?>
                </span>
                <span class="stat-trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> +12% this month</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Pending Tournament Approvals</span>
                <span class="stat-value">
                    <?php echo $tournamentsPending; ?>
                </span>
                <span class="stat-trend" style="color: var(--warning);"><i class="fa-solid fa-clock"></i> 4 new
                    today</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Active Bookings</span>
                <span class="stat-value">142</span>
                <span class="stat-trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> +5.4%</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Monthly Revenue</span>
                <span class="stat-value">₹14,250</span>
                <span class="stat-trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> +21%</span>
            </div>
        </div>

        <!-- SECTION: OVERVIEW -->
        <div id="overview" class="dashboard-section">
            <div class="card" style="position: relative; overflow: hidden; border-color: rgba(57, 255, 20, 0.2);">
                <div
                    style="position: absolute; top: -100px; right: -100px; width: 200px; height: 200px; background: var(--primary-green); filter: blur(100px); opacity: 0.1;">
                </div>
                <div class="section-header">
                    <h2 class="section-title"><i class="fa-solid fa-wave-square"
                            style="color: var(--primary-green); margin-right: 10px;"></i> Platform Pulse</h2>
                    <span class="badge badge-approved" style="animation: pulse 2s infinite;">System Stable</span>
                </div>
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-top: 1rem;">
                    <div
                        style="background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 15px; border: 1px solid var(--glass-border);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="font-size: 0.8rem; color: var(--text-gray);">CPU Load</span>
                            <span style="font-size: 0.8rem; color: var(--primary-green);">24%</span>
                        </div>
                        <div style="height: 6px; background: #222; border-radius: 10px; overflow: hidden;">
                            <div
                                style="width: 24%; height: 100%; background: var(--primary-green); box-shadow: 0 0 10px var(--primary-green);">
                            </div>
                        </div>
                    </div>
                    <div
                        style="background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 15px; border: 1px solid var(--glass-border);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="font-size: 0.8rem; color: var(--text-gray);">DB Health</span>
                            <span style="font-size: 0.8rem; color: var(--primary-green);">Optimal</span>
                        </div>
                        <div style="height: 6px; background: #222; border-radius: 10px; overflow: hidden;">
                            <div
                                style="width: 98%; height: 100%; background: var(--primary-green); box-shadow: 0 0 10px var(--primary-green);">
                            </div>
                        </div>
                    </div>
                    <div
                        style="background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 15px; border: 1px solid var(--glass-border);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="font-size: 0.8rem; color: var(--text-gray);">Server Uptime</span>
                            <span style="font-size: 0.8rem; color: var(--primary-green);">14 Days</span>
                        </div>
                        <div style="height: 6px; background: #222; border-radius: 10px; overflow: hidden;">
                            <div
                                style="width: 100%; height: 100%; background: var(--primary-green); box-shadow: 0 0 10px var(--primary-green);">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                <div class="card" style="margin-bottom: 0;">
                    <h2 class="section-title" style="margin-bottom: 1.5rem;">Recent Security Alerts</h2>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div
                            style="display: flex; align-items: center; gap: 15px; padding: 1rem; background: rgba(255,68,68,0.05); border-left: 4px solid var(--danger); border-radius: 8px;">
                            <i class="fa-solid fa-triangle-exclamation" style="color: var(--danger);"></i>
                            <div>
                                <div style="font-weight: 600;">Multiple failed login attempts</div>
                                <div style="font-size: 0.85rem; color: var(--text-gray);">IP: 192.168.1.104 • User:
                                    unknown</div>
                            </div>
                            <div style="margin-left: auto; font-size: 0.8rem; color: var(--text-gray);">2 mins ago</div>
                        </div>
                        <div
                            style="display: flex; align-items: center; gap: 15px; padding: 1rem; background: rgba(57,255,20,0.05); border-left: 4px solid var(--primary-green); border-radius: 8px;">
                            <i class="fa-solid fa-shield-check" style="color: var(--primary-green);"></i>
                            <div>
                                <div style="font-weight: 600;">System firewall updated</div>
                                <div style="font-size: 0.85rem; color: var(--text-gray);">Server: US-EAST-1 • Version:
                                    4.2.0</div>
                            </div>
                            <div style="margin-left: auto; font-size: 0.8rem; color: var(--text-gray);">1 hour ago</div>
                        </div>
                    </div>
                </div>
                <div class="card" style="margin-bottom: 0;">
                    <h2 class="section-title" style="margin-bottom: 1.5rem;">Quick Actions</h2>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <button
                            style="padding: 1.5rem 10px; background: rgba(57,255,20,0.1); border: 1px solid var(--primary-green); color: var(--primary-green); border-radius: 12px; cursor: pointer;">
                            <i class="fa-solid fa-plus-circle" style="display: block; margin-bottom: 5px;"></i> Create
                            Ad
                        </button>
                        <button
                            style="padding: 1.5rem 10px; background: rgba(0,170,255,0.1); border: 1px solid var(--info); color: var(--info); border-radius: 12px; cursor: pointer;">
                            <i class="fa-solid fa-file-export" style="display: block; margin-bottom: 5px;"></i> Reports
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION: USERS -->
        <div id="users" class="dashboard-section">
            <div class="card">
                <div class="section-header">
                    <h2 class="section-title">Platform Users</h2>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" placeholder="Search users..."
                            style="background: var(--bg-dark); border: 1px solid var(--glass-border); color: white; padding: 8px 15px; border-radius: 10px;">
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $displayUsers = [];
                            if ($pdo) {
                                try {
                                    $stmt = $pdo->query("SELECT * FROM USERS WHERE role = 'User'");
                                    $usersFromDb = $stmt->fetchAll();
                                    foreach ($usersFromDb as $udb) {
                                        $displayUsers[] = [
                                            'email' => $udb['email'],
                                            'name' => $udb['full_name'],
                                            'is_verified' => (bool) $udb['is_verified'],
                                            'is_blocked' => (bool) $udb['is_blocked'],
                                            'role' => $udb['role']
                                        ];
                                    }
                                } catch (Exception $e) {
                                }
                            }

                            if (empty($displayUsers) && file_exists('users.json')) {
                                $usersList = json_decode(file_get_contents('users.json'), true);
                                foreach ($usersList as $u) {
                                    if (($u['role'] ?? 'User') === 'User')
                                        $displayUsers[] = $u;
                                }
                            }

                            foreach ($displayUsers as $u) {
                                $isVerified = isset($u['is_verified']) && $u['is_verified'];
                                $isBlocked = isset($u['is_blocked']) && $u['is_blocked'];
                                $initials = strtoupper(substr($u['name'] ?? 'U', 0, 2));
                                $statusBadge = ($isBlocked) ? "<span class='badge badge-blocked'>Blocked</span>" :
                                    (($isVerified) ? "<span class='badge badge-approved'>Verified</span>" : "<span class='badge badge-pending'>Pending</span>");
                                $isSelf = ($u['email'] === $currentUser);

                                echo "<tr>
                                    <td>
                                        <div style='display: flex; align-items: center; gap: 10px;'>
                                            <div style='width: 32px; height: 32px; background: #222; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: var(--primary-green); border: 1px solid var(--glass-border);'>$initials</div>
                                            <span style='font-weight: 500;'>{$u['name']}</span>
                                        </div>
                                    </td>
                                    <td>{$u['email']}</td>
                                    <td>$statusBadge</td>
                                    <td>
                                        <button class='action-btn' title='Edit' onclick='openUserEditModal(" . json_encode($u) . ")'><i class='fa-solid fa-pen-to-square'></i></button>
                                        " . (!$isSelf ? "
                                        <button class='action-btn' title='" . ($isBlocked ? 'Unblock' : 'Block') . "' onclick='manageUser(\"toggle_block\", \"{$u['email']}\", \"User\")' style='" . ($isBlocked ? 'color: var(--primary-green);' : '') . "'><i class='fa-solid " . ($isBlocked ? 'fa-unlock' : 'fa-ban') . "'></i></button>
                                        <button class='action-btn delete' title='Delete' onclick='manageUser(\"delete_user\", \"{$u['email']}\", \"User\")'><i class='fa-solid fa-trash'></i></button>
                                        " : "<span style='font-size: 0.8rem; color: var(--text-gray); padding-left: 10px;'>You</span>") . "
                                    </td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECTION: TRAINERS -->
        <div id="trainers" class="dashboard-section">
            <div class="card">
                <div class="section-header">
                    <h2 class="section-title">Official Trainers</h2>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" placeholder="Search trainers..."
                            style="background: var(--bg-dark); border: 1px solid var(--glass-border); color: white; padding: 8px 15px; border-radius: 10px;">
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $displayTrainers = [];
                            if ($pdo) {
                                try {
                                    $stmt = $pdo->query("SELECT * FROM USERS WHERE role = 'Trainer'");
                                    $trainersFromDb = $stmt->fetchAll();
                                    foreach ($trainersFromDb as $tdb) {
                                        $displayTrainers[] = [
                                            'email' => $tdb['email'],
                                            'name' => $tdb['full_name'],
                                            'is_verified' => (bool) $tdb['is_verified'],
                                            'is_blocked' => (bool) $tdb['is_blocked'],
                                            'role' => $tdb['role']
                                        ];
                                    }
                                } catch (Exception $e) {
                                }
                            }

                            if (empty($displayTrainers) && file_exists('users.json')) {
                                foreach ($usersList as $u) {
                                    if (($u['role'] ?? '') === 'Trainer')
                                        $displayTrainers[] = $u;
                                }
                            }

                            foreach ($displayTrainers as $u) {
                                $isVerified = isset($u['is_verified']) && $u['is_verified'];
                                $isBlocked = isset($u['is_blocked']) && $u['is_blocked'];
                                $initials = strtoupper(substr($u['name'] ?? 'T', 0, 2));
                                $statusBadge = ($isBlocked) ? "<span class='badge badge-blocked'>Blocked</span>" :
                                    (($isVerified) ? "<span class='badge badge-approved'>Verified</span>" : "<span class='badge badge-pending'>Pending</span>");

                                echo "<tr>
                                    <td>
                                        <div style='display: flex; align-items: center; gap: 10px;'>
                                            <div style='width: 32px; height: 32px; background: #222; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: var(--primary-green); border: 1px solid var(--glass-border);'>$initials</div>
                                            <span style='font-weight: 500;'>{$u['name']}</span>
                                        </div>
                                    </td>
                                    <td>{$u['email']}</td>
                                    <td>$statusBadge</td>
                                    <td>
                                        <button class='action-btn' title='Edit' onclick='openUserEditModal(" . json_encode($u) . ")'><i class='fa-solid fa-pen-to-square'></i></button>
                                        <button class='action-btn' title='" . ($isBlocked ? 'Unblock' : 'Block') . "' onclick='manageUser(\"toggle_block\", \"{$u['email']}\", \"Trainer\")' style='" . ($isBlocked ? 'color: var(--primary-green);' : '') . "'><i class='fa-solid " . ($isBlocked ? 'fa-unlock' : 'fa-ban') . "'></i></button>
                                        <button class='action-btn delete' title='Delete' onclick='manageUser(\"delete_user\", \"{$u['email']}\", \"Trainer\")'><i class='fa-solid fa-trash'></i></button>
                                    </td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECTION: APPROVALS -->
        <div id="approvals" class="dashboard-section">
            <div class="card">
                <div class="section-header">
                    <h2 class="section-title">Tournament & Event Approvals</h2>
                </div>
                <div class="mgmt-grid">
                    <?php
                    $allTournaments = [];
                    if ($pdo) {
                        try {
                            $stmt = $pdo->query("SELECT t.*, v.venue_name FROM TOURNAMENTS t LEFT JOIN VENUES v ON t.venue_id = v.venue_id WHERE t.admin_approval = 'Pending'");
                            $allTournaments = $stmt->fetchAll();
                        } catch (Exception $e) {
                        }
                    }

                    if (empty($allTournaments) && file_exists('tournaments.json')) {
                        $jsonTournaments = json_decode(file_get_contents('tournaments.json'), true);
                        foreach ($jsonTournaments as $jt) {
                            if (($jt['status'] ?? 'Pending') === 'Pending')
                                $allTournaments[] = $jt;
                        }
                    }

                    if (empty($allTournaments)): ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-gray);">
                            <i class="fa-solid fa-calendar-check"
                                style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.2;"></i>
                            <p>No pending tournament or event requests.</p>
                        </div>
                    <?php else:
                        foreach ($allTournaments as $t):
                            $tId = $t['tournament_id'] ?? $t['id'];
                            $tName = $t['tournament_name'] ?? $t['name'];
                            $tVenue = $t['venue_name'] ?? $t['venue'] ?? 'Unknown Venue';
                            $tDate = $t['start_date'] ?? $t['date'] ?? 'N/A';
                            $tType = $t['type'] ?? 'Tournament';
                            $tOrganizer = $t['organizer'] ?? 'Official';
                            ?>
                            <div class="mgmt-card" style="border-left: 4px solid var(--warning);">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                    <div>
                                        <span class="badge badge-pending"
                                            style="margin-bottom: 8px; display: inline-block;"><?php echo $tType; ?></span>
                                        <h4 style="font-size: 1.1rem;"><?php echo $tName; ?></h4>
                                        <p style="font-size: 0.85rem; color: var(--text-gray); margin-top: 5px;">
                                            <i class="fa-solid fa-location-dot"></i> <?php echo $tVenue; ?>
                                        </p>
                                        <p style="font-size: 0.85rem; color: var(--text-gray);">
                                            <i class="fa-solid fa-user-tie"></i> Organizer: <?php echo $tOrganizer; ?>
                                        </p>
                                        <p style="font-size: 0.85rem; color: var(--text-gray);">
                                            <i class="fa-solid fa-calendar-day"></i> Date: <?php echo $tDate; ?>
                                        </p>
                                        <?php if (isset($t['description'])): ?>
                                            <p
                                                style="font-size: 0.8rem; color: var(--text-gray); margin-top: 10px; font-style: italic; line-height: 1.4;">
                                                "<?php echo $t['description']; ?>"
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge badge-pending">WAITING</span>
                                </div>
                                <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                                    <button class="action-btn"
                                        style="flex: 1; background: var(--primary-green); color: var(--bg-dark); border: none; font-weight: 700;"
                                        onclick="manageTournament('approve_tournament', '<?php echo $tId; ?>')">Approve</button>
                                    <button class="action-btn delete"
                                        style="flex: 1; border-color: var(--danger); color: var(--danger);"
                                        onclick="manageTournament('reject_tournament', '<?php echo $tId; ?>')">Reject</button>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <!-- Hidden Tournament Action Form -->
        <form id="tournamentActionForm" method="POST" style="display:none;">
            <input type="hidden" name="action" id="t_action">
            <input type="hidden" name="tournament_id" id="t_id">
        </form>

        <!-- SECTION: SUBSCRIPTIONS -->
        <div id="subscriptions" class="dashboard-section">
            <div class="card">
                <div class="section-header">
                    <h2 class="section-title">Subscription Plans</h2>
                    <button class="btn-primary" style="padding: 8px 20px; font-size: 0.9rem; width: auto; margin: 0;"><i
                            class="fa-solid fa-plus"></i> New Plan</button>
                </div>
                <div class="mgmt-grid">
                    <?php
                    $plans = json_decode(file_get_contents('plans.json'), true);
                    foreach ($plans as $plan):
                        ?>
                        <div class="mgmt-card"
                            style="<?php echo ($plan['id'] === 'Gold') ? 'border-color: var(--primary-green);' : ''; ?>">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                <span style="font-size: 1.2rem;"><?php echo $plan['badge']; ?></span>
                                <span
                                    style="font-weight: 800; color: <?php echo ($plan['id'] === 'Gold') ? 'var(--primary-green)' : 'var(--text-gray)'; ?>;"><?php echo explode(' ', $plan['name'])[0]; ?></span>
                            </div>
                            <div style="font-size: 1.5rem; font-weight: 800; margin-bottom: 1rem;">
                                ₹<?php echo $plan['price']; ?><span
                                    style="font-size: 0.8rem; color: var(--text-gray);">/mo</span></div>
                            <ul
                                style="list-style: none; font-size: 0.85rem; color: var(--text-gray); margin-bottom: 1.5rem; height: 100px; overflow-y: auto;">
                                <?php foreach ($plan['features'] as $feature): ?>
                                    <li style="margin-bottom: 8px;"><i class="fa-solid fa-check"
                                            style="color: var(--primary-green); margin-right: 8px;"></i> <?php echo $feature; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <button class="action-btn" style="width: 100%;"
                                onclick='openEditModal(<?php echo json_encode($plan); ?>)'>Modify Plan</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Plan Editor Modal -->
        <div id="planModal"
            style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
            <div class="card"
                style="width: 100%; max-width: 500px; position: relative; animation: slideUp 0.4s ease-out forwards;">
                <button onclick="closeModal()"
                    style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;"><i
                        class="fa-solid fa-times"></i></button>
                <h2 class="section-title">Edit Subscription Plan</h2>
                <form method="POST" style="margin-top: 1.5rem;">
                    <input type="hidden" name="action" value="update_plan">
                    <input type="hidden" name="plan_id" id="edit_plan_id">

                    <div style="margin-bottom: 1rem;">
                        <label
                            style="display: block; color: var(--text-gray); font-size: 0.85rem; margin-bottom: 5px;">Plan
                            Name</label>
                        <input type="text" name="plan_name" id="edit_plan_name"
                            style="width: 100%; background: var(--bg-dark); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 8px;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label
                                style="display: block; color: var(--text-gray); font-size: 0.85rem; margin-bottom: 5px;">Price
                                (₹)</label>
                            <input type="text" name="plan_price" id="edit_plan_price"
                                style="width: 100%; background: var(--bg-dark); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 8px;">
                        </div>
                        <div>
                            <label
                                style="display: block; color: var(--text-gray); font-size: 0.85rem; margin-bottom: 5px;">Badge
                                (Emoji)</label>
                            <input type="text" name="plan_badge" id="edit_plan_badge"
                                style="width: 100%; background: var(--bg-dark); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 8px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label
                            style="display: block; color: var(--text-gray); font-size: 0.85rem; margin-bottom: 5px;">Features
                            (One per line)</label>
                        <textarea name="plan_features" id="edit_plan_features" rows="5"
                            style="width: 100%; background: var(--bg-dark); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 8px; font-family: inherit; resize: vertical;"></textarea>
                    </div>

                    <button type="submit" class="btn-primary" style="margin: 0;">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- User Editor Modal -->
        <div id="userModal"
            style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
            <div class="card"
                style="width: 100%; max-width: 450px; position: relative; animation: slideUp 0.4s ease-out forwards;">
                <button onclick="closeUserModal()"
                    style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;"><i
                        class="fa-solid fa-times"></i></button>
                <h2 class="section-title">Edit User Profile</h2>
                <form method="POST" style="margin-top: 1.5rem;">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="email" id="edit_user_email">

                    <div style="margin-bottom: 1rem;">
                        <label
                            style="display: block; color: var(--text-gray); font-size: 0.85rem; margin-bottom: 5px;">Full
                            Name</label>
                        <input type="text" name="name" id="edit_user_name"
                            style="width: 100%; background: var(--bg-dark); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 8px;">
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label
                            style="display: block; color: var(--text-gray); font-size: 0.85rem; margin-bottom: 5px;">Role</label>
                        <select name="role" id="edit_user_role"
                            style="width: 100%; background: var(--bg-dark); border: 1px solid var(--glass-border); color: white; padding: 10px; border-radius: 8px;">
                            <option value="User">User</option>
                            <option value="Trainer">Trainer</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary" style="margin: 0;">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Hidden Global Form for Actions -->
        <form id="globalActionForm" method="POST" style="display:none;">
            <input type="hidden" name="action" id="global_action">
            <input type="hidden" name="email" id="global_email">
            <input type="hidden" name="role" id="global_role">
        </form>
    </div>
    </div>

    <!-- OTHER SECTIONS PLACEHOLDERS (To fulfill requirements) -->
    <div id="bookings" class="dashboard-section">
        <div class="card">
            <h2 class="section-title">Bookings & Payments Monitor</h2>
            <p style="color: var(--text-gray); margin: 1rem 0;">Real-time stream of all financial transactions and
                booking logs.</p>
            <div
                style="padding: 4rem; text-align: center; color: var(--text-gray); background: rgba(0,0,0,0.2); border-radius: 20px; border: 1px dashed var(--glass-border);">
                <i class="fa-solid fa-receipt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <p>Financial Ledger - No new transactions in the last hour.</p>
            </div>
        </div>
    </div>

    <div id="security" class="dashboard-section">
        <div class="card" style="position: relative; overflow: hidden;">
            <div
                style="position: absolute; top: 0; right: 0; width: 300px; height: 300px; background: var(--primary-green-glow); filter: blur(100px); opacity: 0.05; pointer-events: none;">
            </div>
            <h2 class="section-title">Platform Security & Controls</h2>
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem;">
                <div class="mgmt-card" style="background: rgba(255,68,68,0.02); border-color: rgba(255,68,68,0.1);">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 1.5rem;">
                        <div
                            style="width: 50px; height: 50px; background: rgba(255,68,68,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--danger); font-size: 1.5rem;">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 1.2rem;">Blacklisted Entities</h3>
                            <p style="font-size: 0.85rem; color: var(--text-gray);">
                                <?php echo $settings['blacklist_count']; ?> currently restricted access.
                            </p>
                        </div>
                    </div>
                    <button class="action-btn"
                        style="width: 100%; border-color: var(--danger); color: var(--danger); background: rgba(255,68,68,0.05);"
                        onclick="openBlacklistModal()">
                        <i class="fa-solid fa-list-ul" style="margin-right: 8px;"></i> View Blacklist
                    </button>
                </div>

                <div class="mgmt-card"
                    style="<?php echo $settings['lockdown_mode'] ? 'border-color: var(--warning); background: rgba(255,170,0,0.02);' : 'border-color: rgba(255,255,255,0.05);'; ?>">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 1.5rem;">
                        <div
                            style="width: 50px; height: 50px; background: <?php echo $settings['lockdown_mode'] ? 'rgba(255,170,0,0.1)' : 'rgba(255,255,255,0.05)'; ?>; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: <?php echo $settings['lockdown_mode'] ? 'var(--warning)' : 'white'; ?>; font-size: 1.5rem;">
                            <i
                                class="fa-solid <?php echo $settings['lockdown_mode'] ? 'fa-lock' : 'fa-lock-open'; ?>"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 1.2rem;">Emergency Lockdown</h3>
                            <p style="font-size: 0.85rem; color: var(--text-gray);">Restrict all front-end access
                                instantly.</p>
                        </div>
                    </div>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="toggle_lockdown">
                        <button type="submit" class="action-btn"
                            style="width: 100%; border-color: var(--warning); color: var(--warning); background: rgba(255,170,0,0.05);">
                            <i class="fa-solid <?php echo $settings['lockdown_mode'] ? 'fa-power-off' : 'fa-hand'; ?>"
                                style="margin-right: 8px;"></i>
                            <?php echo $settings['lockdown_mode'] ? 'Disable Lockdown' : 'Enable Lockdown'; ?>
                        </button>
                    </form>
                </div>

                <div class="mgmt-card">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 1.5rem;">
                        <div
                            style="width: 50px; height: 50px; background: rgba(0,170,255,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--info); font-size: 1.5rem;">
                            <i class="fa-solid fa-file-shield"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 1.2rem;">Audit Logs</h3>
                            <p style="font-size: 0.85rem; color: var(--text-gray);">System-wide admin action history.
                            </p>
                        </div>
                    </div>
                    <button class="action-btn"
                        style="width: 100%; border-color: var(--info); color: var(--info); background: rgba(0,170,255,0.05);">
                        <i class="fa-solid fa-magnifying-glass-chart" style="margin-right: 8px;"></i> Inspect Logs
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Blacklist Modal -->
    <div id="blacklistModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(15px);">
        <div class="card"
            style="width: 100%; max-width: 600px; max-height: 80vh; overflow-y: auto; border-color: var(--danger);">
            <div class="section-header">
                <h2 class="section-title"><i class="fa-solid fa-ban"
                        style="color: var(--danger); margin-right: 10px;"></i> Restricted Entities</h2>
                <button onclick="closeBlacklistModal()"
                    style="background: none; border: none; color: var(--text-gray); cursor: pointer; font-size: 1.5rem;"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="table-container" style="margin-top: 1rem;">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($blockedUsers)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-gray); padding: 2rem;">No users
                                    currently blacklisted.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($blockedUsers as $bu): ?>
                                <tr>
                                    <td><?php echo $bu['name']; ?></td>
                                    <td><?php echo $bu['email']; ?></td>
                                    <td>
                                        <button class="action-btn"
                                            style="color: var(--primary-green); border-color: var(--primary-green);"
                                            onclick="manageUser('toggle_block', '<?php echo $bu['email']; ?>', '<?php echo $bu['role']; ?>')">Restore
                                            Access</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="disputes" class="dashboard-section">
        <div class="card">
            <h2 class="section-title">Disputes & User Complaints</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Reported By</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $disputesList = file_exists('disputes.json') ? json_decode(file_get_contents('disputes.json'), true) : [];
                        $hasPending = false;
                        foreach ($disputesList as $d):
                            if ($d['status'] !== 'Pending')
                                continue;
                            $hasPending = true;
                            $priorityColor = ($d['priority'] === 'High') ? 'rgba(255,170,0,0.5)' :
                                (($d['priority'] === 'Medium') ? 'rgba(0,170,255,0.5)' : 'rgba(57,255,20,0.5)');
                            ?>
                            <tr>
                                <td>#<?php echo $d['id']; ?></td>
                                <td><?php echo $d['reported_by']; ?></td>
                                <td><?php echo $d['category']; ?></td>
                                <td><span class="badge"
                                        style="background: <?php echo $priorityColor; ?>; color: white;"><?php echo $d['priority']; ?></span>
                                </td>
                                <td><span class="badge badge-pending"><?php echo $d['status']; ?></span></td>
                                <td><button class="action-btn"
                                        onclick="resolveDispute('<?php echo $d['id']; ?>')">Resolve</button></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$hasPending): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-gray); padding: 2rem;">No
                                    pending disputes. Good job!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Hidden Dispute Action Form -->
    <form id="disputeForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="resolve_dispute">
        <input type="hidden" name="dispute_id" id="resolve_dispute_id">
    </form>

    <div id="ads" class="dashboard-section">
        <div class="card" style="position: relative; overflow: hidden;">
            <div
                style="position: absolute; top: 0; left: 0; width: 300px; height: 300px; background: rgba(57, 255, 20, 0.05); filter: blur(100px); pointer-events: none;">
            </div>
            <div class="section-header">
                <h2 class="section-title"><i class="fa-solid fa-bullhorn"
                        style="color: var(--primary-green); margin-right: 12px;"></i> Promotions & Announcements</h2>
                <button class="btn-primary"
                    style="padding: 10px 24px; font-size: 0.9rem; width: auto; margin: 0; box-shadow: 0 0 20px rgba(57, 255, 20, 0.2);"
                    onclick="openAdModal()"><i class="fa-solid fa-plus-circle" style="margin-right: 8px;"></i> Create
                    Promotion</button>
            </div>

            <div class="mgmt-grid" style="margin-top: 2rem;">
                <?php
                $adsList = file_exists('ads.json') ? json_decode(file_get_contents('ads.json'), true) : [];
                if (empty($adsList)): ?>
                    <div
                        style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem; color: var(--text-gray); background: rgba(255,255,255,0.01); border-radius: 20px; border: 1px dashed var(--glass-border);">
                        <i class="fa-solid fa-clapperboard"
                            style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.1;"></i>
                        <p style="font-size: 1.1rem;">No promotions active at the moment.</p>
                        <p style="font-size: 0.85rem; opacity: 0.6; margin-top: 8px;">Create an announcement to reach your
                            players.</p>
                    </div>
                <?php else:
                    foreach ($adsList as $ad):
                        $isActive = ($ad['status'] === 'Active');
                        ?>
                        <div class="mgmt-card"
                            style="border-radius: 24px; transition: 0.4s; <?php echo $isActive ? 'border-color: rgba(57, 255, 20, 0.2);' : ''; ?>">
                            <div
                                style="position: relative; height: 160px; background: #0a0a0a; margin-bottom: 1.5rem; border-radius: 16px; overflow: hidden; border: 1px solid var(--glass-border);">
                                <?php if (!empty($ad['banner'])): ?>
                                    <img src="<?php echo $ad['banner']; ?>"
                                        style="width: 100%; height: 100%; object-fit: cover; transition: 0.6s;"
                                        class="ad-banner-img">
                                <?php else: ?>
                                    <div
                                        style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: linear-gradient(135deg, #111 0%, #222 100%);">
                                        <i class="fa-solid fa-megaphone"
                                            style="font-size: 2.5rem; color: #333; margin-bottom: 10px;"></i>
                                        <span
                                            style="color: var(--text-gray); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px;">No
                                            Asset Provided</span>
                                    </div>
                                <?php endif; ?>
                                <div style="position: absolute; top: 12px; right: 12px;">
                                    <span class="badge"
                                        style="background: <?php echo $isActive ? 'rgba(57, 255, 20, 0.9)' : 'rgba(255, 68, 68, 0.9)'; ?>; color: #000; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border: none;">
                                        <?php echo $ad['status']; ?>
                                    </span>
                                </div>
                            </div>

                            <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; font-weight: 700;"><?php echo $ad['title']; ?>
                            </h3>
                            <p
                                style="font-size: 0.85rem; color: var(--text-gray); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;">
                                <span
                                    style="width: 8px; height: 8px; border-radius: 50%; background: <?php echo $isActive ? 'var(--primary-green)' : 'var(--danger)'; ?>; display: inline-block; <?php echo $isActive ? 'animation: pulse 2s infinite;' : ''; ?>"></span>
                                Live Campaign: <?php echo $isActive ? 'Broadcasting' : 'Paused'; ?>
                            </p>

                            <div style="display: flex; gap: 12px;">
                                <button class="action-btn"
                                    style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; font-weight: 600;"
                                    onclick='openAdModal(<?php echo json_encode($ad); ?>)'>
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                <button class="action-btn delete"
                                    style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; font-weight: 600;"
                                    onclick='deleteAd("<?php echo $ad['id']; ?>")'>
                                    <i class="fa-solid fa-trash-can"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <!-- Ad Editor Modal -->
    <div id="adModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(20px);">
        <div class="card"
            style="width: 100%; max-width: 500px; position: relative; animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; border-color: var(--primary-green);">
            <button onclick="closeAdModal()"
                style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.05); border: none; color: white; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s;">
                <i class="fa-solid fa-times"></i>
            </button>
            <h2 class="section-title" id="adModalTitle" style="display: flex; align-items: center; gap: 12px;">
                <i class="fa-solid fa-bullhorn" style="color: var(--primary-green);"></i> Create Promotion
            </h2>
            <p style="color: var(--text-gray); font-size: 0.85rem; margin-top: 5px;">Configure the announcement details
                for the platform.</p>

            <form method="POST" style="margin-top: 2rem;">
                <input type="hidden" name="action" id="ad_action" value="add_ad">
                <input type="hidden" name="ad_id" id="ad_id_input">

                <div style="margin-bottom: 1.2rem;">
                    <label
                        style="display: block; color: var(--text-white); font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">Campaign
                        Title</label>
                    <input type="text" name="title" id="ad_title" required placeholder="e.g. New Year Special 20% Off"
                        style="width: 100%; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px 15px; border-radius: 12px; focus: border-color: var(--primary-green);">
                </div>

                <div style="margin-bottom: 1.2rem;">
                    <label
                        style="display: block; color: var(--text-white); font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">Asset
                        URL (Banner)</label>
                    <input type="url" name="banner" id="ad_banner"
                        placeholder="https://source.unsplash.com/random/1200x600?sports"
                        style="width: 100%; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px 15px; border-radius: 12px;">
                    <p style="font-size: 0.75rem; color: var(--text-gray); margin-top: 6px;">Recommended Aspect Ratio:
                        2:1</p>
                </div>

                <div style="margin-bottom: 2rem;">
                    <label
                        style="display: block; color: var(--text-white); font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">Campaign
                        Status</label>
                    <select name="status" id="ad_status"
                        style="width: 100%; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px 15px; border-radius: 12px; cursor: pointer;">
                        <option value="Active">Active (Broadcasting Now)</option>
                        <option value="Inactive">Inactive (Draft/Paused)</option>
                    </select>
                </div>

                <div style="display: flex; gap: 15px;">
                    <button type="button" class="action-btn"
                        style="flex: 1; padding: 14px; margin: 0; border: 1px solid var(--glass-border);"
                        onclick="closeAdModal()">Cancel</button>
                    <button type="submit" class="btn-primary"
                        style="flex: 1; margin: 0; padding: 14px; background: var(--primary-green); color: black;">
                        <i class="fa-solid fa-paper-plane" style="margin-right: 8px;"></i> Save & Publish
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Ad Action Form -->
    <form id="adActionForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete_ad">
        <input type="hidden" name="ad_id" id="delete_ad_id">
    </form>

    </div>

    <script>
        function showSection(sectionId, event) {
            if (event) event.preventDefault();

            // Hide all sections
            document.querySelectorAll('.dashboard-section').forEach(section => {
                section.classList.remove('active');
            });

            // Deactivate all nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });

            // Show the selected section
            document.getElementById(sectionId).classList.add('active');

            // Activate the corresponding nav link
            if (event) {
                event.currentTarget.classList.add('active');
            } else {
                // Find link by sectionId
                document.querySelectorAll('.nav-link').forEach(link => {
                    const onclick = link.getAttribute('onclick');
                    if (onclick && onclick.includes(sectionId)) {
                        link.classList.add('active');
                    }
                });
            }
        }

        function openEditModal(plan) {
            document.getElementById('edit_plan_id').value = plan.id;
            document.getElementById('edit_plan_name').value = plan.name;
            document.getElementById('edit_plan_price').value = plan.price;
            document.getElementById('edit_plan_badge').value = plan.badge;
            document.getElementById('edit_plan_features').value = plan.features.join('\n');

            document.getElementById('planModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('planModal').style.display = 'none';
        }

        function openUserEditModal(user) {
            document.getElementById('edit_user_email').value = user.email;
            document.getElementById('edit_user_name').value = user.name;
            document.getElementById('edit_user_role').value = user.role || 'User';
            document.getElementById('userModal').style.display = 'flex';
        }

        function closeUserModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        function manageUser(action, email, role) {
            let confirmMsg = "";
            if (action === 'delete_user') confirmMsg = "Are you sure you want to permanently delete this user?";
            if (action === 'toggle_block') confirmMsg = "Are you sure you want to change this user's access status?";

            if (confirmMsg && !confirm(confirmMsg)) return;

            document.getElementById('global_action').value = action;
            document.getElementById('global_email').value = email;
            document.getElementById('global_role').value = role;
            document.getElementById('globalActionForm').submit();
        }

        function openAdModal(ad = null) {
            if (ad) {
                document.getElementById('adModalTitle').innerText = "Edit Promotion";
                document.getElementById('ad_action').value = "update_ad";
                document.getElementById('ad_id_input').value = ad.id;
                document.getElementById('ad_title').value = ad.title;
                document.getElementById('ad_banner').value = ad.banner || "";
                document.getElementById('ad_status').value = ad.status;
            } else {
                document.getElementById('adModalTitle').innerText = "Create Promotion";
                document.getElementById('ad_action').value = "add_ad";
                document.getElementById('ad_id_input').value = "";
                document.getElementById('ad_title').value = "";
                document.getElementById('ad_banner').value = "";
                document.getElementById('ad_status').value = "Active";
            }
            document.getElementById('adModal').style.display = 'flex';
        }

        function closeAdModal() {
            document.getElementById('adModal').style.display = 'none';
        }

        function deleteAd(id) {
            if (confirm("Are you sure you want to delete this promotion?")) {
                document.getElementById('delete_ad_id').value = id;
                document.getElementById('adActionForm').submit();
            }
        }

        function resolveDispute(id) {
            if (confirm("Mark ticket #" + id + " as resolved?")) {
                document.getElementById('resolve_dispute_id').value = id;
                document.getElementById('disputeForm').submit();
            }
        }

        function manageTournament(action, id) {
            let confirmMsg = (action === 'approve_tournament') ? "Approve this tournament/event?" : "Reject this tournament/event?";
            if (confirm(confirmMsg)) {
                document.getElementById('t_action').value = action;
                document.getElementById('t_id').value = id;
                document.getElementById('tournamentActionForm').submit();
            }
        }

        function openBlacklistModal() {
            document.getElementById('blacklistModal').style.display = 'flex';
        }

        function closeBlacklistModal() {
            document.getElementById('blacklistModal').style.display = 'none';
        }

        // Initial call to show overview or the relevant section
        window.onload = () => {
            <?php
            $action = $_POST['action'] ?? '';
            $role = $_POST['role'] ?? 'User';
            if ($action === 'update_plan'): ?>
                showSection('subscriptions');
            <?php elseif (in_array($action, ['update_user', 'delete_user', 'toggle_block'])): ?>
                showSection('<?php echo ($role === 'Trainer' ? 'trainers' : 'users'); ?>');
            <?php elseif (in_array($action, ['add_ad', 'update_ad', 'delete_ad'])): ?>
                showSection('ads');
            <?php elseif ($action === 'resolve_dispute'): ?>
                showSection('disputes');
            <?php else: ?>
                showSection('overview');
            <?php endif; ?>
        };
    </script>
</body>

</html>