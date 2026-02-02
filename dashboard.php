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

        /* Play Section Specifics */
        .play-filters {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
            scrollbar-width: none;
            /* Hide scrollbar */
        }

        .play-filters::-webkit-scrollbar {
            display: none;
        }

        .filter-pill {
            padding: 8px 16px;
            border-radius: 50px;
            background: #1a1a1a;
            border: 1px solid var(--glass-border);
            color: var(--text-white);
            font-size: 0.85rem;
            white-space: nowrap;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .filter-pill:hover,
        .filter-pill.active {
            background: var(--primary-green);
            color: #000;
            font-weight: 600;
            border-color: var(--primary-green);
        }

        .filter-badge {
            background: #000;
            color: var(--primary-green);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        .filter-pill.active .filter-badge {
            background: rgba(0, 0, 0, 0.2);
            color: #000;
        }

        .game-card {
            background: #121212;
            border-radius: 16px;
            padding: 1.2rem;
            border: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: 0.3s;
            position: relative;
        }

        .game-card:hover {
            border-color: var(--primary-green);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .game-type {
            color: var(--text-gray);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .game-organizer {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0.5rem 0;
        }

        .org-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            font-size: 0.9rem;
        }

        .org-info h4 {
            font-size: 0.95rem;
            margin-bottom: 2px;
        }

        .org-info p {
            font-size: 0.75rem;
            color: var(--text-gray);
        }

        .game-time {
            font-weight: 700;
            font-size: 0.9rem;
            color: #fff;
        }

        .game-location {
            display: flex;
            align-items: start;
            gap: 8px;
            color: var(--text-gray);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .game-location i {
            margin-top: 3px;
        }

        .game-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .level-pill {
            background: rgba(255, 255, 255, 0.05);
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            color: var(--text-gray);
        }

        .join-btn {
            padding: 6px 16px;
            border-radius: 6px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            cursor: pointer;
            border: none;
        }

        .btn-booked {
            background: var(--primary-green);
            color: #000;
        }

        .btn-join {
            background: transparent;
            border: 1px solid var(--primary-green);
            color: var(--primary-green);
        }

        .btn-join:hover {
            background: rgba(57, 255, 20, 0.1);
        }

        /* Custom Dropdown */
        .filter-dropdown-container {
            position: relative;
            display: inline-block;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 110%;
            left: 0;
            background: #1a1a1a;
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 8px;
            min-width: 200px;
            z-index: 100;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            max-height: 300px;
            overflow-y: auto;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            padding: 10px 14px;
            color: var(--text-gray);
            cursor: pointer;
            border-radius: 8px;
            transition: 0.2s;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dropdown-item:hover {
            background: rgba(57, 255, 20, 0.1);
            color: var(--primary-green);
        }

        .dropdown-item.active {
            background: var(--primary-green);
            color: #000;
            font-weight: 600;
        }

        /* Scrollbar for dropdown */
        .dropdown-menu::-webkit-scrollbar {
            width: 6px;
        }

        .dropdown-menu::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 3px;
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
                <a href="#" class="nav-link active" onclick="showSection('overview'); return false;">
                    <i class="fa-solid fa-house"></i>
                    <span>Overview</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('play'); return false;">
                    <i class="fa-solid fa-play"></i>
                    <span>Play</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('coaching'); return false;">
                    <i class="fa-solid fa-user-graduate"></i>
                    <span>Coaching</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="2.php" class="nav-link">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Book Venue</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('mygames'); return false;">
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

        <div id="overview" class="dashboard-section">
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
                    <a href="#" style="color: var(--primary-green); text-decoration: none; font-size: 0.9rem;">View
                        All</a>
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
        </div>

        <!-- PLAY SECTION -->
        <div id="play" class="dashboard-section" style="display: none;">

            <!-- Filter Bar -->
            <div class="play-filters">
                <button class="filter-pill" onclick="toggleSort()">
                    <i class="fa-solid fa-clock-rotate-left"></i> <span id="sort-label">Latest</span>
                </button>
                <button class="filter-pill" onclick="resetFilters()">
                    <i class="fa-solid fa-sliders"></i> Reset
                </button>
                <div class="filter-dropdown-container">
                    <button class="filter-pill active" id="btn-sport" onclick="toggleSportDropdown(event)">
                        <i class="fa-solid fa-whistle"></i> <span id="sport-label">All Sports</span>
                        <span class="filter-badge" id="sport-badge">3</span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.7rem; margin-left: 5px;"></i>
                    </button>
                    <div id="sport-dropdown" class="dropdown-menu">
                        <div class="dropdown-item active" onclick="selectSport('All')">All Sports</div>
                        <div class="dropdown-item" onclick="selectSport('Badminton')">Badminton</div>
                        <div class="dropdown-item" onclick="selectSport('Football')">Football</div>
                        <div class="dropdown-item" onclick="selectSport('Cricket')">Cricket</div>
                        <div class="dropdown-item" onclick="selectSport('Basketball')">Basketball</div>
                        <div class="dropdown-item" onclick="selectSport('Tennis')">Tennis</div>
                        <div class="dropdown-item" onclick="selectSport('Table Tennis')">Table Tennis</div>
                        <div class="dropdown-item" onclick="selectSport('Swimming')">Swimming</div>
                        <div class="dropdown-item" onclick="selectSport('Volleyball')">Volleyball</div>
                    </div>
                </div>
                <div style="position: relative;">
                    <button class="filter-pill" id="btn-date" onclick="openDatePicker()">
                        <i class="fa-regular fa-calendar"></i> <span id="date-label">Any Date</span>
                    </button>
                    <input type="date" id="date-filter-input"
                        style="opacity: 0; position: absolute; top: 100%; left: 0; width: 1px; height: 1px; pointer-events: none;"
                        onchange="handleDateChange(this)">
                </div>
                <button class="filter-pill" id="btn-type" onclick="cycleTypeFilter()">
                    <i class="fa-solid fa-money-bill"></i> <span id="type-label">All Types</span>
                </button>
            </div>

            <div id="games-container" class="stats-grid"
                style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));">
                <!-- Games will be injected here via JS -->
            </div>
        </div>

        <!-- MY GAMES SECTION -->
        <div id="mygames" class="dashboard-section" style="display: none;">
            <div class="section-header">
                <h3>My Games</h3>
                <button onclick="openHostModal()"
                    style="padding: 10px 20px; background: var(--primary-green); color: #000; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-plus"></i> Host Game
                </button>
            </div>

            <div id="my-games-list" class="stats-grid"
                style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));">
                <!-- User's hosted or joined games will appear here -->
                <div
                    style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--text-gray); border: 1px dashed var(--glass-border); border-radius: 16px;">
                    <i class="fa-solid fa-gamepad" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>You haven't hosted or joined any games yet.</p>
                </div>
            </div>
        </div>

        <!-- HOST GAME MODAL -->
        <div id="hostGameModal"
            style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
            <div class="section-card"
                style="width: 100%; max-width: 600px; position: relative; max-height: 90vh; overflow-y: auto;">
                <button onclick="document.getElementById('hostGameModal').style.display='none'"
                    style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;"><i
                        class="fa-solid fa-times"></i></button>
                <div class="section-header">
                    <h3>Host a New Game</h3>
                </div>
                <form id="hostGameForm" onsubmit="submitHostGame(event)">
                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($userName); ?>">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; color: var(--text-gray); margin-bottom: 5px;">Sport</label>
                            <select name="sport" required
                                style="width: 100%; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
                                <option value="">Select...</option>
                                <option value="Badminton">Badminton</option>
                                <option value="Football">Football</option>
                                <option value="Cricket">Cricket</option>
                                <option value="Tennis">Tennis</option>
                                <option value="Basketball">Basketball</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; color: var(--text-gray); margin-bottom: 5px;">Game
                                Type</label>
                            <input type="text" name="type" placeholder="e.g. Doubles, 5v5" required
                                style="width: 100%; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; color: var(--text-gray); margin-bottom: 5px;">Venue /
                            Location</label>
                        <input type="text" name="venue" required placeholder="Where are you playing?"
                            style="width: 100%; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; color: var(--text-gray); margin-bottom: 5px;">Date</label>
                            <input type="date" name="date" id="hostGameDate" required
                                style="width: 100%; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="display: block; color: var(--text-gray); margin-bottom: 5px;">Time
                                Slot</label>
                            <div style="display: flex; gap: 5px; align-items: center;">
                                <select name="startTime" id="hostStartTime" required
                                    style="background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px; width: 100%;">
                                </select>
                                <span>to</span>
                                <select name="endTime" id="hostEndTime" required
                                    style="background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px; width: 100%;">
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; color: var(--text-gray); margin-bottom: 5px;">Skill
                                Level</label>
                            <select name="skill"
                                style="width: 100%; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
                                <option value="Open For All">Open For All</option>
                                <option value="Beginner">Beginner</option>
                                <option value="Intermediate">Intermediate</option>
                                <option value="Advanced">Advanced</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; color: var(--text-gray); margin-bottom: 5px;">Max
                                Players</label>
                            <input type="number" name="maxPlayers" min="2" value="4"
                                style="width: 100%; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; color: var(--text-gray); margin-bottom: 5px;">Entry Fee (Per
                            Person)</label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <select id="feeType" onchange="toggleFeeInput()"
                                style="width: 100px; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
                                <option value="Free">Free</option>
                                <option value="Paid">Paid</option>
                            </select>
                            <input type="number" name="price" id="priceInput" placeholder="Amount (₹)"
                                style="display: none; flex-grow: 1; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
                        </div>
                    </div>

                    <button type="submit"
                        style="width: 100%; padding: 14px; background: var(--primary-green); color: black; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">Create
                        Game</button>
                    <div id="hostMsg" style="margin-top: 10px; text-align: center; font-size: 0.9rem;"></div>
                </form>
            </div>
        </div>


        <!-- PAYMENT MODAL -->
        <div id="paymentModal"
            style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
            <div class="section-card" style="width: 100%; max-width: 400px; position: relative;">
                <button onclick="document.getElementById('paymentModal').style.display='none'"
                    style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: #aaa; cursor: pointer; font-size: 1.2rem;">
                    <i class="fa-solid fa-times"></i>
                </button>
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <div style="width: 60px; height: 60px; background: rgba(0, 255, 149, 0.1); color: var(--primary-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 10px;">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <h3 style="margin: 0; font-size: 1.4rem;">Complete Payment</h3>
                    <p style="color: var(--text-gray); font-size: 0.9rem; margin-top: 5px;">Secure your slot in the game</p>
                </div>

                <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: var(--text-gray);">Entry Fee</span>
                        <span id="payAmount" style="font-weight: 700; color: #fff;">₹0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-gray);">Convenience Fee</span>
                        <span style="font-weight: 700; color: var(--primary-green);">₹0</span>
                    </div>
                    <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.05); margin: 12px 0;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-weight: 700; color: #fff;">Grand Total</span>
                        <span id="payTotal" style="font-weight: 700; color: var(--primary-green); font-size: 1.2rem;">₹0</span>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; color: var(--text-gray); margin-bottom: 10px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Payment Method</label>
                    <div style="display: grid; gap: 10px;">
                        <div style="background: rgba(255,255,255,0.05); border: 1px solid var(--primary-green); padding: 12px; border-radius: 8px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                            <input type="radio" name="payType" checked style="accent-color: var(--primary-green);">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                            <span>UPI (PhonePe, GPay, Paytm)</span>
                        </div>
                        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); padding: 12px; border-radius: 8px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                            <input type="radio" name="payType" style="accent-color: var(--primary-green);">
                            <i class="fa-solid fa-credit-card"></i>
                            <span>Credit / Debit Card</span>
                        </div>
                    </div>
                </div>

                <button id="payConfirmBtn" onclick="completePayment()"
                    style="width: 100%; padding: 14px; background: var(--primary-green); color: black; border: none; border-radius: 8px; font-weight: 800; cursor: pointer; text-transform: uppercase; letter-spacing: 1px;">
                    Pay & Join Now
                </button>
                <div id="payMsg" style="margin-top: 10px; text-align: center; font-size: 0.9rem;"></div>
            </div>
        </div>

        <div id="coaching" class="dashboard-section" style="display: none;">

            <div class="stats-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                <?php
                // Trainers display logic removed as per request
                ?>

                <!-- Book a Trainer Card -->
                <div class='stat-card'
                    style='display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px; border-style: dashed; border-color: var(--text-gray); cursor: pointer;'
                    onclick="window.location.href='book_trainer.php'">
                    <div class='user-avatar'
                        style='width: 80px; height: 80px; font-size: 1.5rem; margin-bottom: 0.5rem; background: transparent; border: 2px dashed var(--text-gray); color: var(--text-gray);'>
                        <i class="fa-solid fa-dumbbell"></i>
                    </div>
                    <h4 style='font-size: 1.2rem; color: var(--text-gray);'>Book a Trainer</h4>
                    <p style='color: var(--text-gray); font-size: 0.85rem;'>Find a professional coach.</p>
                    <button
                        style='width: 100%; margin-top: 1rem; padding: 12px; background: transparent; color: var(--primary-green); border: 1px solid var(--primary-green); border-radius: 8px; font-weight: 700; cursor: pointer;'>Browse
                        Trainers</button>
                </div>

                <!-- NEW: Join as Trainer Card or Status -->
                <?php
                $appStatus = null;
                if (isset($pdo) && $pdo) {
                    try {
                        $stmt = $pdo->prepare("SELECT status FROM TRAINER_APPLICATIONS WHERE user_email = ? ORDER BY created_at DESC LIMIT 1");
                        $stmt->execute([$email]);
                        $appStatus = $stmt->fetchColumn();
                    } catch (Exception $e) {
                    }
                }


                // Unified Card Logic for Visual Consistency
                $cardBorderColor = 'var(--text-gray)';
                $iconColor = 'var(--text-gray)';
                $iconClass = 'fa-plus';
                $btnText = 'Apply Now';
                $btnDisabled = false;
                $statusMsg = 'Share your expertise and earn.';
                $clickAction = "openTrainerModal()";
                $btnStyle = "background: transparent; color: var(--primary-green); border: 1px solid var(--primary-green);";

                if ($appStatus === 'Pending') {
                    $cardBorderColor = 'var(--warning)';
                    $iconColor = 'var(--warning)';
                    $iconClass = 'fa-clock';
                    $btnText = 'Under Review';
                    $btnDisabled = true;
                    $statusMsg = '<span style="color: var(--warning);">Application Waiting for Approval</span>';
                    $clickAction = "";
                    $btnStyle = "background: rgba(255, 170, 0, 0.1); color: var(--warning); border: 1px solid var(--warning); cursor: not-allowed;";
                } elseif ($appStatus === 'Rejected') {
                    $cardBorderColor = 'var(--danger)';
                    $iconColor = 'var(--danger)';
                    $statusMsg = '<span style="color: var(--danger); font-weight: bold;">Previous application rejected. Try again.</span>';
                }

                // Show if NOT trainer OR if they have a pending/rejected app (so they can see status/retry)
                // Show card always for now as per user request
                if (true):
                    ?>

                    <div class='stat-card'
                        style='display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px; border-style: dashed; border-color: <?php echo $cardBorderColor; ?>; cursor: pointer;'
                        onclick="<?php echo $clickAction; ?>">
                        <div class='user-avatar'
                            style='width: 80px; height: 80px; font-size: 1.5rem; margin-bottom: 0.5rem; background: transparent; border: 2px dashed <?php echo $iconColor; ?>; color: <?php echo $iconColor; ?>;'>
                            <i class="fa-solid <?php echo $iconClass; ?>"></i>
                        </div>
                        <h4 style='font-size: 1.2rem; color: var(--text-gray);'>Become a Trainer</h4>
                        <p style='color: var(--text-gray); font-size: 0.85rem;'><?php echo $statusMsg; ?></p>
                        <button <?php echo $btnDisabled ? 'disabled' : ''; ?>
                            style='width: 100%; margin-top: 1rem; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; <?php echo $btnStyle; ?>'>
                            <?php echo $btnText; ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TRAINER APPLICATION MODAL -->
        <div id="trainerModal"
            style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
            <div class="section-card" style="width: 100%; max-width: 500px; position: relative;">
                <button onclick="document.getElementById('trainerModal').style.display='none'"
                    style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;"><i
                        class="fa-solid fa-times"></i></button>
                <div class="section-header">
                    <h3>Trainer Application</h3>
                </div>
                <form id="trainerForm" onsubmit="submitTrainerApplication(event)">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; color: var(--text-gray); margin-bottom: 5px;">Full Name</label>
                        <input type="text" name="full_name" required
                            style="width: 100%; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;"
                            value="<?php echo htmlspecialchars($userName); ?>">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; color: var(--text-gray); margin-bottom: 5px;">Specialization
                            (Sport)</label>
                        <select name="specialization" required
                            style="width: 100%; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
                            <option value="">Select Sport...</option>
                            <option value="Football">Football</option>
                            <option value="Cricket">Cricket</option>
                            <option value="Basketball">Basketball</option>
                            <option value="Tennis">Tennis</option>
                            <option value="Badminton">Badminton</option>
                            <option value="Fitness/Gym">Fitness/Gym</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; color: var(--text-gray); margin-bottom: 5px;">Years of
                            Experience</label>
                        <input type="number" name="experience" min="0" required
                            style="width: 100%; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; color: var(--text-gray); margin-bottom: 5px;">Upload
                            Certificate/Credentials</label>
                        <input type="file" name="certificate" required accept=".pdf,.img,.png,.jpg,.jpeg"
                            style="width: 100%; background: #0a0a0a; border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 8px;">
                        <p style="font-size: 0.8rem; color: var(--text-gray); margin-top: 5px;">Max size: 5MB. Formats:
                            PDF, JPG, PNG.</p>
                    </div>
                    <button type="submit"
                        style="width: 100%; padding: 14px; background: var(--primary-green); color: black; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">Submit
                        Application</button>
                    <div id="trainerMsg" style="margin-top: 10px; text-align: center; font-size: 0.9rem;"></div>
                </form>
            </div>
        </div>

        <script>
            function openTrainerModal() {
                document.getElementById('trainerModal').style.display = 'flex';
            }

            function submitTrainerApplication(e) {
                e.preventDefault();
                const form = document.getElementById('trainerForm');
                const formData = new FormData(form);
                const msg = document.getElementById('trainerMsg');

                msg.innerHTML = '<span style="color: var(--text-gray);">Uploading and submitting...</span>';

                fetch('submit_trainer_application.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            msg.innerHTML = '<span style="color: var(--primary-green);">' + data.message + '</span>';
                            setTimeout(() => {
                                document.getElementById('trainerModal').style.display = 'none';
                                form.reset();
                                msg.innerHTML = '';
                            }, 2000);
                        } else {
                            msg.innerHTML = '<span style="color:red;">' + data.message + '</span>';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        msg.innerHTML = '<span style="color:red;">An error occurred. Try again.</span>';
                    });
            }


            // --- PLAY SECTION LOGIC ---
            let games = []; // Will be populated from DB
            const currentUserName = "<?php echo $userName; ?>";

            let joinedGames = [];

            async function fetchGames() {
                try {
                    const response = await fetch('get_games.php');
                    const data = await response.json();
                    if (data.success) {
                        joinedGames = (data.joinedIds || []).map(id => parseInt(id));
                        games = data.games.map(g => {
                            const gid = parseInt(g.id);
                            const isHostedByMe = (g.host_name === currentUserName);
                            const hasJoined = joinedGames.includes(gid);
                            return {
                                id: gid,
                                sport: g.sport,
                                type: g.game_type + ' • ' + g.skill_level,
                                organizer: {
                                    name: g.host_name,
                                    initial: g.host_name ? g.host_name.charAt(0) : '?',
                                    going: 1,
                                    karma: 100
                                },
                                timeDisplay: `${g.game_date}, ${g.start_time} - ${g.end_time}`,
                                timestamp: new Date(`${g.game_date}T${g.start_time}`).getTime(),
                                location: g.venue,
                                distance: '',
                                level: g.skill_level,
                                status: isHostedByMe ? 'My Game' : (hasJoined ? 'Joined' : 'Join'),
                                isPaid: g.price !== 'Free',
                                priceDisplay: g.price === 'Free' ? 'Free' : '₹' + g.price,
                                maxPlayers: g.max_players
                            };
                        });
                        renderGames();
                        renderMyGames();
                    }
                } catch (error) {
                    console.error('Error fetching games:', error);
                }
            }

            let filters = {
                sport: 'All',
                date: 'All',
                paid: 'All',
                sort: 'desc'
            };

            function renderGames() {
                const container = document.getElementById('games-container');
                if (!container) return;

                // Mock "Current Date" as Feb 2, 2026 for consistency with data
                const now = new Date('2026-02-02T00:00:00').getTime();

                let filtered = games.filter(g => {
                    const gameDate = g.timestamp;

                    if (filters.sport !== 'All' && g.sport !== filters.sport) return false;

                    if (filters.paid !== 'All') {
                        const isPaid = (filters.paid === 'Paid');
                        if (g.isPaid !== isPaid) return false;
                    }

                    // Date Filter
                    if (filters.date !== 'All') {
                        if (filters.date.match(/^\d{4}-\d{2}-\d{2}$/)) {
                            const gameDateObj = new Date(g.timestamp);
                            const year = gameDateObj.getFullYear();
                            const month = String(gameDateObj.getMonth() + 1).padStart(2, '0');
                            const day = String(gameDateObj.getDate()).padStart(2, '0');
                            const gameDateStr = `${year}-${month}-${day}`;
                            if (gameDateStr !== filters.date) return false;
                        }
                        else if (filters.date === 'This Week') {
                            if (gameDate < now || gameDate > now + 604800000) return false;
                        } else if (filters.date === 'Weekend') {
                            const d = new Date(gameDate);
                            const day = d.getDay();
                            if (day !== 0 && day !== 6) return false;
                        }
                    }
                    return true;
                });

                // Sort
                filtered.sort((a, b) => {
                    return filters.sort === 'asc' ? a.timestamp - b.timestamp : b.timestamp - a.timestamp;
                });

                if (filtered.length === 0) {
                    container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 2rem; color: var(--text-gray);">No games found matching your filters.</div>';
                } else {
                    container.innerHTML = filtered.map(g => `
                        <div class="game-card">
                            <div class="game-type">${g.sport} • ${g.type}</div>
                            <div class="game-organizer">
                                <div class="org-avatar" style="background: #e6e6e6; color: #333; display: flex; align-items: center; justify-content: center; font-weight: bold;">${g.organizer.initial}</div>
                                <div class="org-info">
                                    <h4>${g.organizer.name} <span style="font-size: 0.7rem; background: #333; padding: 2px 6px; border-radius: 4px; margin-left: 5px; color: #fff;">${g.organizer.going} Going</span></h4>
                                    <p>${g.organizer.karma} Karma</p>
                                </div>
                            </div>
                            <div class="game-time">${g.timeDisplay}</div>
                            <div class="game-location">
                                <i class="fa-solid fa-location-dot"></i>
                                <span>${g.location} ${g.distance}</span>
                            </div>
                            <div class="game-footer">
                                <span class="level-pill">${g.level}</span>
                                <div style="display:flex; align-items:center; gap: 10px;">
                                    ${g.isPaid ? `<span style="font-size: 0.8rem; color: var(--primary-green); font-weight: bold;">${g.priceDisplay}</span>` : '<span style="font-size: 0.8rem; color: #aaa;">Free</span>'}
                                    <button 
                                        class="join-btn ${g.status === 'Join' ? 'btn-join' : 'btn-booked'}"
                                        onclick="joinGame(this, ${g.id})"
                                        ${g.status !== 'Join' ? 'disabled' : ''}
                                    >
                                        ${g.status}
                                    </button>
                                    ${g.status === 'My Game' ? `
                                        <button onclick="deleteGame(${g.id})" style="background: none; border: none; color: #ff4444; cursor: pointer; font-size: 1.1rem; padding-left: 5px;" title="Delete Game">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `).join('');
                }

                // Update Badge
                const badge = document.getElementById('sport-badge');
                if (badge) badge.innerText = filtered.length;
            }

            function renderMyGames() {
                const container = document.getElementById('my-games-list');
                if (!container) return;

                const myGames = games.filter(g => g.organizer.name === currentUserName);

                if (myGames.length === 0) {
                    container.innerHTML = `
                        <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--text-gray); border: 1px dashed var(--glass-border); border-radius: 16px;">
                            <i class="fa-solid fa-gamepad" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>You haven't hosted any games yet.</p>
                        </div>
                    `;
                } else {
                    container.innerHTML = myGames.map(g => `
                        <div class="game-card" style="border-left: 4px solid var(--primary-green);">
                            <div class="game-type">${g.sport} • ${g.type}</div>
                            <div class="game-time">${g.timeDisplay}</div>
                            <div class="game-location">
                                <i class="fa-solid fa-location-dot"></i>
                                <span>${g.location}</span>
                            </div>
                            <div class="game-footer">
                                <span class="level-pill">${g.level}</span>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <span style="font-size: 0.8rem; font-weight: bold; color: var(--primary-green);">HOSTING</span>
                                    <button onclick="deleteGame(${g.id})" style="background: none; border: none; color: #ff4444; cursor: pointer; font-size: 1.1rem; padding: 5px;" title="Delete Game">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('');
                }
            }

            // Dropdown Logic
            function toggleSportDropdown(e) {
                e.stopPropagation();
                const btn = e.currentTarget;
                const dropdown = document.getElementById('sport-dropdown');

                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                    dropdown.style.display = 'none'; // Ensure display is off
                    return;
                }

                // Show and Position
                dropdown.style.display = 'block'; // Must be visible to measure? No, just for fixed pos.
                dropdown.classList.add('show');

                // Use Fixed Positioning to escape scrollable containers
                const rect = btn.getBoundingClientRect();
                dropdown.style.position = 'fixed';
                dropdown.style.top = (rect.bottom + 8) + 'px';
                dropdown.style.left = rect.left + 'px';
                dropdown.style.width = '200px';
                dropdown.style.zIndex = '9999';
            }

            function selectSport(sport) {
                filters.sport = sport;
                document.getElementById('sport-label').innerText = sport === 'All' ? 'All Sports' : sport;

                // Update active class in dropdown
                document.querySelectorAll('#sport-dropdown .dropdown-item').forEach(item => {
                    if (item.innerText === (sport === 'All' ? 'All Sports' : sport)) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });

                renderGames();
            }

            // Close dropdown when clicking outside
            window.addEventListener('click', function (e) {
                const dropdown = document.getElementById('sport-dropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    // If click is NOT on the dropdown and NOT on the button...
                    if (!e.target.closest('#sport-dropdown') && !e.target.closest('#btn-sport')) {
                        dropdown.classList.remove('show');
                        dropdown.style.display = 'none';
                    }
                }
            });

            // Close on scroll (optional but good for fixed elements)
            window.addEventListener('scroll', function () {
                const dropdown = document.getElementById('sport-dropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                    dropdown.style.display = 'none';
                }
            }, true); // Capture phase to catch all scrolls

            function toggleSort() {
                filters.sort = filters.sort === 'asc' ? 'desc' : 'asc';
                document.getElementById('sort-label').innerText = filters.sort === 'asc' ? 'Earliest' : 'Latest';
                renderGames();
            }

            function cycleTypeFilter() {
                const types = ['All', 'Paid', 'Free']; // mapped to isPaid true/false
                const idx = types.indexOf(filters.paid);
                filters.paid = types[(idx + 1) % types.length];
                document.getElementById('type-label').innerText = filters.paid === 'All' ? 'All Types' : filters.paid;
                renderGames();
            }

            function openDatePicker() {
                const input = document.getElementById('date-filter-input');
                if ('showPicker' in HTMLInputElement.prototype) {
                    input.showPicker();
                } else {
                    input.click();
                }
            }

            function handleDateChange(input) {
                if (input.value) {
                    filters.date = input.value; // YYYY-MM-DD
                    document.getElementById('date-label').innerText = input.value;
                } else {
                    filters.date = 'All';
                    document.getElementById('date-label').innerText = 'Any Date';
                }
                renderGames();
            }

            function resetFilters() {
                filters = { sport: 'All', date: 'All', paid: 'All', sort: 'desc' };
                // Reset Select UI
                selectSport('All');
                document.getElementById('type-label').innerText = 'All Types';
                document.getElementById('date-label').innerText = 'Any Date';
                document.getElementById('sort-label').innerText = 'Latest';
                const dateInput = document.getElementById('date-filter-input');
                if (dateInput) dateInput.value = '';
                // render called inside selectSport
            }

            // Initial Render
            setTimeout(fetchGames, 100);

            // --- HOST GAME LOGIC ---
            function populateTimeDropdowns() {
                const startSelect = document.getElementById('hostStartTime');
                const endSelect = document.getElementById('hostEndTime');
                if (!startSelect || !endSelect) return;

                startSelect.innerHTML = '';
                endSelect.innerHTML = '';

                for (let h = 0; h < 24; h++) {
                    for (let m = 0; m < 60; m += 30) {
                        const h24 = h.toString().padStart(2, '0');
                        const m24 = m.toString().padStart(2, '0');
                        const time24 = `${h24}:${m24}`;

                        const period = h >= 12 ? 'PM' : 'AM';
                        const h12 = h % 12 === 0 ? 12 : h % 12;
                        const time12 = `${h12}:${m24} ${period}`;

                        startSelect.innerHTML += `<option value="${time24}">${time12}</option>`;
                        endSelect.innerHTML += `<option value="${time24}">${time12}</option>`;
                    }
                }
            }

            function openHostModal() {
                const modal = document.getElementById('hostGameModal');
                const dateInput = document.getElementById('hostGameDate');
                if (dateInput) {
                    const today = new Date().toISOString().split('T')[0];
                    dateInput.setAttribute('min', today);
                }
                populateTimeDropdowns();
                modal.style.display = 'flex';
            }

            function toggleFeeInput() {
                const feeType = document.getElementById('feeType').value;
                const priceInput = document.getElementById('priceInput');
                if (feeType === 'Paid') {
                    priceInput.style.display = 'block';
                    priceInput.setAttribute('required', 'true');
                } else {
                    priceInput.style.display = 'none';
                    priceInput.removeAttribute('required');
                }
            }

            async function submitHostGame(e) {
                e.preventDefault();
                const form = document.getElementById('hostGameForm');
                const msg = document.getElementById('hostMsg');
                const submitBtn = form.querySelector('button[type="submit"]');

                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                // --- Client-side Validation ---
                const selectedDate = data.date;
                const startTime = data.startTime;
                const endTime = data.endTime;

                const now = new Date();
                const todayStr = now.toISOString().split('T')[0];
                const currentTimeStr = now.toTimeString().substring(0, 5);

                if (selectedDate < todayStr) {
                    msg.style.color = '#ff4444';
                    msg.innerText = 'Error: Date cannot be in the past.';
                    return;
                }

                if (selectedDate === todayStr && startTime <= currentTimeStr) {
                    msg.style.color = '#ff4444';
                    msg.innerText = 'Error: Start time must be in the future for today.';
                    return;
                }

                if (endTime <= startTime) {
                    msg.style.color = '#ff4444';
                    msg.innerText = 'Error: End time must be after start time.';
                    return;
                }

                // Adjust price if free
                if (document.getElementById('feeType').value === 'Free') {
                    data.price = 'Free';
                }

                submitBtn.disabled = true;
                submitBtn.innerText = 'Creating...';
                msg.innerText = '';

                try {
                    const response = await fetch('create_game.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.success) {
                        msg.style.color = 'var(--primary-green)';
                        msg.innerText = 'Game Created Successfully!';
                        setTimeout(() => {
                            document.getElementById('hostGameModal').style.display = 'none';
                            form.reset();
                            submitBtn.disabled = false;
                            submitBtn.innerText = 'Create Game';
                            msg.innerText = '';
                            fetchGames(); // Refresh data for both sections
                        }, 1500);
                    } else {
                        throw new Error(result.message);
                    }
                } catch (err) {
                    msg.style.color = '#ff4444';
                    msg.innerText = err.message || 'Error creating game';
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Create Game';
                }
            }

            async function joinGame(btn, gameId) {
                // Find game info
                const game = games.find(g => g.id === gameId);
                if (!game) return;

                if (game.isPaid) {
                    // Logic: Paid games require payment first
                    openPaymentModal(game, btn);
                    return;
                }

                // If FREE, join directly
                await processJoin(btn, gameId);
            }

            let pendingJoinData = null;

            function openPaymentModal(game, btn) {
                const modal = document.getElementById('paymentModal');
                document.getElementById('payAmount').innerText = game.priceDisplay;
                document.getElementById('payTotal').innerText = game.priceDisplay;
                document.getElementById('payMsg').innerText = '';
                
                pendingJoinData = { btn, gameId: game.id };
                modal.style.display = 'flex';
            }

            async function completePayment() {
                if (!pendingJoinData) return;
                
                const payBtn = document.getElementById('payConfirmBtn');
                const msg = document.getElementById('payMsg');
                
                payBtn.disabled = true;
                payBtn.innerText = 'Processing...';
                
                // Simulate payment gateway delay
                setTimeout(async () => {
                    msg.style.color = 'var(--primary-green)';
                    msg.innerText = 'Payment Successful! Joining game...';
                    
                    await processJoin(pendingJoinData.btn, pendingJoinData.gameId);
                    
                    setTimeout(() => {
                        document.getElementById('paymentModal').style.display = 'none';
                        payBtn.disabled = false;
                        payBtn.innerText = 'Pay & Join Now';
                        pendingJoinData = null;
                    }, 1000);
                }, 1500);
            }

            async function processJoin(btn, gameId) {
                console.log("Joining game:", gameId);
                const originalText = btn.innerText;

                btn.disabled = true;
                btn.innerText = 'Joining...';

                try {
                    const response = await fetch('join_game.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ gameId: gameId })
                    });
                    const result = await response.json();

                    if (result.success) {
                        btn.innerText = 'Joined';
                        btn.classList.remove('btn-join');
                        btn.classList.add('btn-booked');
                        fetchGames(); // Refresh lists
                    } else {
                        alert(result.message);
                        btn.disabled = false;
                        btn.innerText = originalText;
                    }
                } catch (error) {
                    console.error('Error joining game:', error);
                    alert('An error occurred. Please try again.');
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            }
            window.joinGame = joinGame;
            window.completePayment = completePayment;

            async function deleteGame(gameId) {
                if (!confirm("Are you sure you want to delete this game? This will also remove all joined participants.")) return;

                try {
                    const response = await fetch('delete_game.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ gameId: gameId })
                    });
                    const result = await response.json();

                    if (result.success) {
                        fetchGames(); // Refresh both My Games and Play sections
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    console.error('Error deleting game:', error);
                    alert('An error occurred while deleting the game.');
                }
            }
            window.deleteGame = deleteGame;

            function showSection(sectionId) {
                // Hide all sections
                document.querySelectorAll('.dashboard-section').forEach(sec => {
                    sec.style.display = 'none';
                });
                // Show selected
                const activeSec = document.getElementById(sectionId);
                if (activeSec) activeSec.style.display = 'block';

                // Specific renders
                if (sectionId === 'play') renderGames();
                if (sectionId === 'mygames') renderMyGames();

                // Update Nav State
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('onclick') && link.getAttribute('onclick').includes(sectionId)) {
                        link.classList.add('active');
                    }
                });
            }
        </script>
    </main>

</body>

</html>