<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['user'];
$users = json_decode(file_get_contents('users.json'), true);
$currentUser = null;
foreach ($users as $u) {
    if ($u['email'] === $email) {
        $currentUser = $u;
        break;
    }
}
$userName = $currentUser['name'] ?? 'Player One';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PlayMatrix</title>
    <!-- Fonts -
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #050505;
            --bg-card: #121212;
            --primary-green: #39ff14;
            --primary-green-dim: #2cb812;
            --text-white: #ffffff;
            --text-gray: #a1a1a1;
            --glass-border: rgba(255, 255, 255, 0.08);
            --sidebar-width: 260px;
            --header-height: 80px;
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
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background: #0a0a0a;
            border-right: 1px solid var(--glass-border);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            padding: 2rem 1.5rem;
            z-index: 1000;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 3rem;
        }

        .brand-icon {
            background: var(--primary-green);
            color: var(--bg-dark);
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 1.2rem;
            box-shadow: 0 0 15px rgba(57, 255, 20, 0.3);
        }

        .brand span {
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .nav-menu {
            list-style: none;
            flex-grow: 1;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 15px;
            color: var(--text-gray);
            text-decoration: none;
            border-radius: 10px;
            transition: 0.3s;
            font-weight: 500;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(57, 255, 20, 0.1);
            color: var(--primary-green);
        }

        .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .logout-section {
            border-top: 1px solid var(--glass-border);
            padding-top: 1.5rem;
        }

        /* Main Content Styling */
        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 2rem 3rem;
            min-height: 100vh;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .welcome-text h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .welcome-text p {
            color: var(--text-gray);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #121212;
            padding: 8px 15px;
            border-radius: 50px;
            border: 1px solid var(--glass-border);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #39ff14, #00d2ff);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #000;
        }

        /* Grid Layout for Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: #121212;
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            position: relative;
            overflow: hidden;
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-green);
        }

        .stat-icon {
            font-size: 1.5rem;
            color: var(--primary-green);
            margin-bottom: 1.5rem;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 900;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-gray);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Recent Activity / Booking Section */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-card {
            background: #121212;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            padding: 2rem;
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.2rem 0;
            border-bottom: 1px solid #1a1a1a;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .activity-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: rgba(57, 255, 20, 0.1);
            color: var(--primary-green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .activity-details h4 {
            font-size: 1rem;
            margin-bottom: 2px;
        }

        .activity-details p {
            font-size: 0.85rem;
            color: var(--text-gray);
        }

        .activity-status {
            padding: 6px 15px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-completed {
            background: rgba(57, 255, 20, 0.1);
            color: var(--primary-green);
        }

        .status-pending {
            background: rgba(255, 184, 0, 0.1);
            color: #ffb800;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 2rem 1rem;
            }

            .brand span,
            .nav-link span {
                display: none;
            }

            .main-content {
                margin-left: 80px;
                padding: 2rem;
            }
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon"><i class="fa-solid fa-rocket"></i></div>
            <span>PLAYMATRIX</span>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="#" class="nav-link active">
                    <i class="fa-solid fa-house"></i>
                    <span>Overview</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="2.php" class="nav-link">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Book Venue</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-gamepad"></i>
                    <span>My Games</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-award"></i>
                    <span>Achievements</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-gears"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>

        <div class="logout-section">
            <a href="login.php" class="nav-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header>
            <div class="welcome-text">
                <h1>Game On! 🚀</h1>
                <p>Ready to dominate the matrix today?</p>
            </div>
            <div class="user-profile">
                <span>
                    <?php echo htmlspecialchars($userName); ?>
                </span>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
            </div>
        </header>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-bolt"></i></div>
                <div class="stat-value">2,450</div>
                <div class="stat-label">Matrix Points</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-trophy"></i></div>
                <div class="stat-value">12</div>
                <div class="stat-label">Trophies Won</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-check-double"></i></div>
                <div class="stat-value">04</div>
                <div class="stat-label">Active Bookings</div>
            </div>
        </section>

        <section class="activity-section">
            <div class="section-header">
                <h3>Recent Activity</h3>
                <a href="#" style="color: var(--primary-green); text-decoration: none; font-size: 0.9rem;">View All</a>
            </div>

            <div class="section-card">
                <ul class="activity-list">
                    <li class="activity-item">
                        <div class="activity-info">
                            <div class="activity-icon"><i class="fa-solid fa-futbol"></i></div>
                            <div class="activity-details">
                                <h4>Football Arena Booking</h4>
                                <p>Today at 6:00 PM • Main Complex</p>
                            </div>
                        </div>
                        <span class="activity-status status-completed">Confirmed</span>
                    </li>
                    <li class="activity-item">
                        <div class="activity-info">
                            <div class="activity-icon"><i class="fa-solid fa-coins"></i></div>
                            <div class="activity-details">
                                <h4>Coins Earned</h4>
                                <p>Won a match in Matrix Arena</p>
                            </div>
                        </div>
                        <span class="activity-status">+500 PTS</span>
                    </li>
                    <li class="activity-item">
                        <div class="activity-info">
                            <div class="activity-icon"><i class="fa-solid fa-basketball"></i></div>
                            <div class="activity-details">
                                <h4>Basketball Practice</h4>
                                <p>Tomorrow at 8:00 AM • Court 2</p>
                            </div>
                        </div>
                        <span class="activity-status status-pending">Pending</span>
                    </li>
                </ul>
            </div>
        </section>
    </main>

</body>

</html>