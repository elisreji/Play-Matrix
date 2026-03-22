<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'db_connect.php';

$email = $_SESSION['user'];
$userName = 'Trainer';

// Fetch user details including role to verify access
if ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM USERS WHERE email = ?");
    $stmt->execute([$email]);
    $currentUser = $stmt->fetch();
} else {
    // Fallback to JSON - though trainer features rely on DB
    $users = json_decode(file_get_contents('data/users.json'), true);
    foreach ($users as $u) {
        if ($u['email'] === $email) {
            $currentUser = $u;
            break;
        }
    }
}

if ($currentUser) {
    $userName = $currentUser['full_name'] ?? $currentUser['name'] ?? 'Trainer';
    if (!isset($currentUser['role']) || $currentUser['role'] !== 'Trainer') {
        if (isset($currentUser['role']) && $currentUser['role'] === 'Admin') {
            header("Location: admin.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    }
}

// Fetch Trainer Profile Data (if exists)
$trainerProfile = [];
$programs = [];
$schedule = [];
$bookings = [];
$students = [];


if ($pdo) {
    // Profile
    $stmt = $pdo->prepare("SELECT * FROM TRAINER_PROFILES WHERE user_email = ?");
    $stmt->execute([$email]);
    $trainerProfile = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // Programs
    $stmt = $pdo->prepare("SELECT * FROM COACHING_PROGRAMS WHERE trainer_email = ? AND status = 'Published'");
    $stmt->execute([$email]);
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Schedule
    $stmt = $pdo->prepare("SELECT * FROM TRAINER_SCHEDULE WHERE trainer_email = ?");
    $stmt->execute([$email]);
    $tempSchedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tempSchedule as $s) {
        $schedule[$s['day_of_week']] = $s;
    }

    // Bookings
    $stmt = $pdo->prepare("SELECT b.*, u.full_name as student_name, p.title as program_title 
                           FROM TRAINER_BOOKINGS b 
                           LEFT JOIN USERS u ON b.user_email = u.email 
                           LEFT JOIN COACHING_PROGRAMS p ON b.program_id = p.id
                           WHERE b.trainer_email = ? ORDER BY b.session_date DESC");
    $stmt->execute([$email]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Students (Distinct users from bookings or requests)
    $stmt = $pdo->prepare("SELECT DISTINCT u.* FROM USERS u 
                           INNER JOIN TRAINER_BOOKINGS b ON u.email = b.user_email 
                           WHERE b.trainer_email = ?
                           UNION
                           SELECT DISTINCT u.* FROM USERS u 
                           INNER JOIN TRAINER_REQUESTS r ON u.email = r.user_email 
                           WHERE r.trainer_email = ? AND r.status = 'Accepted'");
    $stmt->execute([$email, $email]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);



    // Resources
    $stmt = $pdo->prepare("SELECT * FROM TRAINER_RESOURCES WHERE trainer_email = ? ORDER BY created_at DESC");
    $stmt->execute([$email]);
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Dashboard - PlayMatrix</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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
            overflow-y: auto;
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
            cursor: pointer;
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
            width: calc(100% - var(--sidebar-width));
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

        /* Sections */
        .dashboard-section {
            display: none;
            animation: fadeIn 0.5s ease-out;
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

        /* Forms & Cards */
        .card {
            background: var(--bg-card);
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .btn-primary {
            background: var(--primary-green);
            color: #000;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-primary:hover {
            box-shadow: 0 0 15px rgba(57, 255, 20, 0.4);
        }

        .btn-outline {
            border: 1px solid var(--glass-border);
            background: transparent;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: #fff;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-gray);
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            background: #0a0a0a;
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: #fff;
            font-family: 'Outfit', sans-serif;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-green);
        }

        #toastContainer {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            background: #121212;
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            animation: toastSlideIn 0.3s ease-out;
            transition: 0.5s;
        }

        .toast.error {
            border-color: #ff4444;
        }

        @keyframes toastSlideIn {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon"><i class="fa-solid fa-graduation-cap"></i></div>
            <span>TRAINER ZONE</span>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <div class="nav-link active" onclick="showSection('overview', this)">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Overview</span>
                </div>
            </li>
            <li class="nav-item">
                <div class="nav-link" onclick="showSection('profile', this)">
                    <i class="fa-solid fa-id-card"></i>
                    <span>My Profile</span>
                </div>
            </li>

            <li class="nav-item">
                <a href="trainer_venues.php" class="nav-link">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Venues</span>
                </a>
            </li>
            <li class="nav-item">
                <div class="nav-link" onclick="showSection('schedule', this)">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Schedule</span>
                </div>
            </li>
            <li class="nav-item">
                <div class="nav-link" onclick="showSection('bookings', this)">
                    <i class="fa-solid fa-check-double"></i>
                    <span>Bookings</span>
                </div>
            </li>
            <li class="nav-item">
                <div class="nav-link" onclick="showSection('requests', this)">
                    <i class="fa-solid fa-envelope-open-text"></i>
                    <span>Requests</span>
                    <?php 
                        $pendingCount = 0;
                        if (isset($pdo) && $pdo) {
                            $stmtP = $pdo->prepare("SELECT COUNT(*) FROM TRAINER_REQUESTS WHERE trainer_email = ? AND status = 'Pending'");
                            $stmtP->execute([$email]);
                            $pendingCount = $stmtP->fetchColumn();
                        }
                        if ($pendingCount > 0): 
                    ?>
                        <span class="unread-badge" style="background: #ff4b2b; color: white; font-size: 0.7rem; padding: 2px 6px; border-radius: 10px; font-weight: 700;"><?php echo $pendingCount; ?></span>
                    <?php endif; ?>
                </div>
            </li>
            <li class="nav-item">
                <div class="nav-link" onclick="showSection('students', this)">
                    <i class="fa-solid fa-users"></i>
                    <span>Students</span>
                </div>
            </li>
            <li class="nav-item">
                <div class="nav-link" onclick="showSection('resources', this)">
                    <i class="fa-solid fa-file-arrow-up"></i>
                    <span>Resources</span>
                </div>
            </li>
            <li class="nav-item">
                <div class="nav-link" onclick="showSection('events', this)">
                    <i class="fa-solid fa-calendar-star"></i>
                    <span>Events</span>
                </div>
            </li>

            <li class="nav-item">
                <div class="nav-link" onclick="showSection('health', this)">
                    <i class="fa-solid fa-heart-pulse"></i>
                    <span>Health Profile</span>
                </div>
            </li>
        </ul>

        <div class="logout-section">
            <a href="logout.php" class="nav-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header>
            <div class="welcome-text">
                <h1>Hello, Coach <?php echo htmlspecialchars($userName); ?>! 👋</h1>
                <p>Manage your fitness empire directly from the matrix.</p>
            </div>
            <div class="user-profile">
                <span><?php echo htmlspecialchars($userName); ?></span>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
            </div>
        </header>

        <!-- 1. OVERVIEW SECTION -->
        <div id="overview" class="dashboard-section active">
            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="stat-value">
                        <?php echo count(array_filter($bookings, function ($b) {
                            return $b['session_date'] == date('Y-m-d');
                        })); ?>
                    </div>
                    <div class="stat-label">Sessions Today</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-user-group"></i></div>
                    <div class="stat-value"><?php echo count($students); ?></div>
                    <div class="stat-label">Active Students</div>
                </div>
                <div class="stat-card" style="cursor: pointer;" onclick="showSection('requests', document.querySelector('.nav-link[onclick*=\'requests\']'))">
                    <div class="stat-icon"><i class="fa-solid fa-envelope-open-text" style="color: #ffaa00;"></i></div>
                    <div class="stat-value"><?php echo $pendingCount; ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>
            </section>

            <div class="card">
                <h3>Welcome to your Command Center</h3>
                <p style="color: var(--text-gray); margin-top: 10px;">Use the sidebar to manage your profile, create new
                    coaching events, set your availability, and track your students' progress.</p>
            </div>
        </div>

        <!-- 2. PROFILE SECTION -->
        <div id="profile" class="dashboard-section">
            <div class="section-header">
                <h2>Manage Trainer Profile</h2>
                <div style="display: flex; gap: 10px;">
                    <a href="trainer_profile.php?email=<?php echo urlencode($email); ?>" target="_blank"
                        class="btn-outline"
                        style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-eye"></i> View Public Profile
                    </a>
                    <button class="btn-primary" onclick="saveProfile()">Save Changes</button>
                </div>
            </div>
            <div class="card">
                <form id="profileForm">
                    <div class="form-group">
                        <label>Bio / About Me</label>
                        <textarea rows="4" name="bio" required minlength="10" maxlength="1000"
                            placeholder="Tell students about yourself..."><?php echo htmlspecialchars($trainerProfile['bio'] ?? ''); ?></textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Specializations (comma separated)</label>
                            <input type="text" name="specializations" required
                                value="<?php echo htmlspecialchars($trainerProfile['specializations'] ?? ''); ?>"
                                placeholder="e.g. Yoga, HIIT, Weight Loss">
                        </div>
                        <div class="form-group">
                            <label>Years of Experience</label>
                            <input type="number" name="experience" required min="0" max="60"
                                value="<?php echo htmlspecialchars($trainerProfile['years_experience'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Certifications (comma separated urls or names)</label>
                        <input type="text" name="certifications" required
                            value="<?php echo htmlspecialchars($trainerProfile['certifications'] ?? ''); ?>"
                            placeholder="e.g. ACE Certified, NASM">
                    </div>
                    <div class="form-group">
                        <label>Upload Certification Document</label>
                        <input type="file" name="certification_doc" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                            style="padding: 10px; background: #0a0a0a; border: 1px solid var(--glass-border); border-radius: 8px; color: #fff; width: 100%;">
                        <?php if (!empty($trainerProfile['certification_doc'])): ?>
                            <small style="display: block; margin-top: 5px; color: var(--text-gray);">
                                Current: <a href="<?php echo htmlspecialchars($trainerProfile['certification_doc']); ?>"
                                    target="_blank" style="color: var(--primary-green); text-decoration: none;">View
                                    Document</a>
                            </small>
                        <?php endif; ?>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Base Hourly Rate (₹)</label>
                            <input type="number" name="hourly_rate" required min="0"
                                value="<?php echo htmlspecialchars($trainerProfile['hourly_rate'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Profile Photo</label>
                            <input type="file" name="profile_photo" accept="image/*"
                                style="padding: 10px; background: #0a0a0a; border: 1px solid var(--glass-border); border-radius: 8px; color: #fff; width: 100%;">
                            <?php if (!empty($trainerProfile['profile_photo'])): ?>
                                <small style="display: block; margin-top: 5px; color: var(--text-gray);">
                                    Current: <a href="<?php echo htmlspecialchars($trainerProfile['profile_photo']); ?>"
                                        target="_blank" style="color: var(--primary-green); text-decoration: none;">View
                                        Photo</a>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. EVENTS SECTION -->
        <div id="events" class="dashboard-section">
            <div class="section-header">
                <h2>My Events</h2>
                <button class="btn-primary" onclick="openEventModal()"><i class="fa-solid fa-plus"></i> New Event</button>
            </div>
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
                <?php if (empty($programs)): ?>
                    <p style="color: var(--text-gray); grid-column: 1/-1; text-align:center; padding: 3rem 0;">
                        <i class="fa-solid fa-calendar-plus" style="font-size: 2.5rem; display:block; margin-bottom:15px; opacity:0.3;"></i>
                        No events created yet. Click "New Event" to get started!
                    </p>
                <?php else: ?>
                    <?php foreach ($programs as $prog): ?>
                        <div class="stat-card" style="padding: 1.5rem; position: relative; display:flex; flex-direction:column; gap:10px;">
                            <button onclick="deleteEvent(<?php echo $prog['id']; ?>)"
                                style="position: absolute; top: 15px; right: 15px; background: transparent; border: none; color: #ff4444; cursor: pointer; font-size: 1.1rem;"
                                title="Delete Event">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <span style="background: rgba(57,255,20,0.1); color: var(--primary-green); padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; width:fit-content;"><?php echo htmlspecialchars($prog['type']); ?></span>
                            <h3 style="margin: 5px 0; font-size:1.1rem;"><?php echo htmlspecialchars($prog['title']); ?></h3>
                            <div style="font-size: 0.85rem; color: var(--text-gray); display: flex; flex-direction: column; gap: 6px;">
                                <div><i class="fa-solid fa-location-dot" style="color: var(--primary-green); width:16px;"></i> <?php echo htmlspecialchars($prog['location'] ?? 'Online'); ?></div>
                                <?php if (!empty($prog['event_date'])): ?>
                                    <div><i class="fa-solid fa-calendar" style="color: var(--primary-green); width:16px;"></i> <?php echo date('D, M d Y', strtotime($prog['event_date'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($prog['event_time'])): ?>
                                    <div><i class="fa-solid fa-clock" style="color: var(--primary-green); width:16px;"></i>
                                        <?php echo date('h:i A', strtotime($prog['event_time'])); ?>
                                        <?php if (!empty($prog['event_end_time'])): ?> – <?php echo date('h:i A', strtotime($prog['event_end_time'])); ?><?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; border-top: 1px solid var(--glass-border); padding-top: 10px;">
                                <span style="font-weight: 800; font-size: 1.2rem; color: var(--primary-green);">₹<?php echo number_format($prog['price'], 2); ?></span>
                                <span style="font-size: 0.75rem; color: var(--text-gray); background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px;">per person</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="stat-card" onclick="openEventModal()"
                    style="border-style: dashed; display: flex; align-items: center; justify-content: center; flex-direction: column; cursor: pointer; opacity: 0.6; padding: 2rem; gap: 10px; min-height: 200px;">
                    <i class="fa-solid fa-plus" style="font-size: 2rem; color: var(--primary-green);"></i>
                    <p style="color: var(--text-gray);">Create New Event</p>
                </div>
            </div>
        </div>

        <!-- 4. SCHEDULE SECTION -->
        <div id="schedule" class="dashboard-section">
            <div class="section-header">
                <h2>Manage Schedule & Availability</h2>
            </div>
            <div class="card">
                <p style="margin-bottom: 20px;">Set your weekly recurring availability.</p>
                <form id="scheduleForm">
                    <div style="display: grid; gap: 15px;">
                        <?php
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        foreach ($days as $day):
                            $sched = $schedule[$day] ?? ['start_time' => '09:00', 'end_time' => '17:00', 'active' => false];
                            $isActive = isset($schedule[$day]);
                            ?>
                            <div class="schedule-row"
                                style="display: flex; align-items: center; gap: 20px; background: #0a0a0a; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border);">
                                <div style="width: 100px; font-weight: 600;"><?php echo $day; ?></div>
                                <input type="hidden" name="day[]" value="<?php echo $day; ?>">
                                <input type="time" name="start[]" value="<?php echo $sched['start_time']; ?>"
                                    style="width: auto;">
                                <span>to</span>
                                <input type="time" name="end[]" value="<?php echo $sched['end_time']; ?>"
                                    style="width: auto;">
                                <label
                                    style="display: inline-flex; align-items: center; gap: 5px; cursor: pointer; margin-bottom: 0;">
                                    <input type="checkbox" name="active[]" value="1" style="width: auto;" <?php echo $isActive ? 'checked' : ''; ?>> Active
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="updateSchedule()" class="btn-primary"
                        style="margin-top: 20px;">Update Schedule</button>
                </form>
            </div>
        </div>

        <!-- 5. BOOKINGS SECTION -->
        <div id="bookings" class="dashboard-section">
            <div class="section-header">
                <h2>Session Requests & Bookings</h2>
            </div>
            <div class="card">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                            <th style="padding: 10px;">Student</th>
                            <th style="padding: 10px;">Event/Type</th>
                            <th style="padding: 10px;">Date & Time</th>
                            <th style="padding: 10px;">Status</th>
                            <th style="padding: 10px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="5" style="padding: 20px; text-align: center; color: var(--text-gray);">No
                                    bookings found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $b): ?>
                                <tr style="border-bottom: 1px solid #1a1a1a;">
                                    <td style="padding: 15px 10px;">
                                        <?php echo htmlspecialchars($b['student_name'] ?? $b['user_email']); ?>
                                    </td>
                                    <td style="padding: 15px 10px;">
                                        <?php echo htmlspecialchars($b['program_title'] ?? 'Session'); ?>
                                    </td>
                                    <td style="padding: 15px 10px;">
                                        <?php echo date('M d, H:i', strtotime($b['session_date'] . ' ' . $b['session_time'])); ?>
                                    </td>
                                    <td style="padding: 15px 10px;">
                                        <span
                                            style="color: <?php
                                            echo match ($b['status']) {
                                                'Pending' => '#ffaa00',
                                                'Approved' => 'var(--primary-green)',
                                                'Completed' => '#00d2ff',
                                                'Rejected', 'Cancelled' => '#ff4444',
                                                default => 'var(--text-gray)'
                                            };
                                            ?>; background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">
                                            <?php echo $b['status']; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px 10px; text-align: right;">
                                        <?php if ($b['status'] === 'Pending'): ?>
                                            <button onclick="updateBooking(<?php echo $b['id']; ?>, 'Approved')"
                                                style="background: var(--primary-green); border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-right: 5px;"><i
                                                    class="fa-solid fa-check"></i></button>
                                            <button onclick="updateBooking(<?php echo $b['id']; ?>, 'Rejected')"
                                                style="background: #ff4444; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;"><i
                                                    class="fa-solid fa-xmark"></i></button>
                                        <?php elseif ($b['status'] === 'Approved'): ?>
                                            <button onclick="updateBooking(<?php echo $b['id']; ?>, 'Completed')"
                                                class="btn-primary" style="padding: 5px 10px; font-size: 0.8rem;">Complete</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="requests" class="dashboard-section">
            <div class="section-header">
                <h2>Incoming Requests</h2>
                <p style="color: var(--text-gray);">Manage student inquiries and connection requests.</p>
            </div>
            <div class="card">
                <div class="table-container" style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--glass-border);">
                                <th style="padding: 15px; color: var(--text-gray);">Student Email</th>
                                <th style="padding: 15px; color: var(--text-gray);">Date</th>
                                <th style="padding: 15px; color: var(--text-gray);">Status</th>
                                <th style="padding: 15px; color: var(--text-gray); text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($pdo) {
                                $stmt = $pdo->prepare("SELECT * FROM TRAINER_REQUESTS WHERE trainer_email = ? ORDER BY created_at DESC");
                                $stmt->execute([$email]);
                                $requests = $stmt->fetchAll();

                                if ($requests) {
                                    foreach ($requests as $req) {
                                        $statusColor = 'var(--text-gray)';
                                        if ($req['status'] === 'Accepted')
                                            $statusColor = 'var(--primary-green)';
                                        if ($req['status'] === 'Rejected')
                                            $statusColor = '#ff4444';

                                        echo "<tr style='border-bottom: 1px solid rgba(255,255,255,0.02);' id='req-row-" . $req['id'] . "'>";
                                        echo "<td style='padding: 15px;'>" . htmlspecialchars($req['user_email']) . "</td>";
                                        echo "<td style='padding: 15px;'>" . date('M d, Y', strtotime($req['created_at'])) . "</td>";
                                        echo "<td style='padding: 15px;'><span class='status-pill' style='padding: 4px 10px; border-radius: 50px; font-size: 0.8rem; background: rgba(255,255,255,0.05); color: $statusColor;'>" . htmlspecialchars($req['status']) . "</span></td>";
                                        echo "<td style='padding: 15px; text-align: right;'>";
                                        if ($req['status'] === 'Pending') {
                                            echo "<button onclick=\"processRequest(event, " . $req['id'] . ", 'Accepted')\" class='btn-primary' style='padding: 5px 12px; font-size: 0.8rem; margin-right: 5px; background: var(--primary-green); color: black;'>Accept</button>";
                                            echo "<button onclick=\"processRequest(event, " . $req['id'] . ", 'Rejected')\" class='btn-primary' style='padding: 5px 12px; font-size: 0.8rem; background: #ff4444; color: white;'>Reject</button>";
                                        } else {
                                            echo "<span style='color: var(--text-gray); font-size: 0.8rem; opacity: 0.6;'>Processed</span>";
                                        }
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' style='padding: 30px; text-align: center; color: var(--text-gray);'>No incoming requests yet.</td></tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="students" class="dashboard-section">
            <div class="section-header">
                <h2>My Athletes</h2>
            </div>
            <div class="stats-grid">
                <?php if (empty($students)): ?>
                    <p style="color: var(--text-gray);">No students assigned yet.</p>
                <?php else: ?>
                    <?php foreach ($students as $stu): ?>
                        <div class="stat-card" style="display: flex; align-items: center; gap: 15px; padding: 1.5rem;">
                            <div
                                style="width: 50px; height: 50px; background: linear-gradient(45deg, #39ff14, #00d2ff); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: black; font-weight: 700;">
                                <?php echo strtoupper(substr($stu['full_name'] ?? $stu['name'] ?? 'U', 0, 1)); ?>
                            </div>
                            <div>
                                <h4 style="margin-bottom: 3px;">
                                    <?php echo htmlspecialchars($stu['full_name'] ?? $stu['name'] ?? 'User'); ?>
                                </h4>
                                <p style="font-size: 0.8rem; color: var(--text-gray);">
                                    <?php echo htmlspecialchars($stu['email']); ?>
                                </p>
                                <div style="margin-top: 5px;">
                                    <button
                                        onclick="alert('Viewing progress for <?php echo addslashes($stu['full_name'] ?? $stu['name'] ?? 'User'); ?>')"
                                        style="font-size: 0.8rem; padding: 4px 8px; border: 1px solid var(--glass-border); background: transparent; color: white; border-radius: 4px; cursor: pointer;">View
                                        Progress</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="resources" class="dashboard-section">
            <div class="section-header">
                <h2>Resources (Plans & Validations)</h2>
                <button class="btn-primary" onclick="triggerUpload()"><i class="fa-solid fa-upload"></i> Upload
                    Resource</button>
                <input type="file" id="resourceInput" style="display: none;" onchange="uploadResource(this)">
            </div>
            <div class="card">
                <p>Upload workout routines, diet plans, and video guides here to assign to your students.</p>

                <div style="margin-top: 2rem;">
                    <?php if (empty($resources)): ?>
                        <div
                            style="border: 2px dashed var(--glass-border); padding: 3rem; text-align: center; border-radius: 10px;">
                            <i class="fa-solid fa-cloud-arrow-up"
                                style="font-size: 3rem; color: var(--text-gray); margin-bottom: 1rem;"></i>
                            <p>No resources uploaded yet.</p>
                        </div>
                    <?php else: ?>
                        <div
                            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                            <?php foreach ($resources as $res): ?>
                                <div class="stat-card" style="padding: 1rem; display: flex; align-items: center; gap: 15px;">
                                    <i class="fa-solid fa-file-pdf" style="font-size: 1.5rem; color: #ff4444;"></i>
                                    <div style="flex-grow: 1;">
                                        <h4 style="font-size: 0.9rem;"><?php echo htmlspecialchars($res['title']); ?></h4>
                                        <p style="font-size: 0.7rem; color: var(--text-gray);"><?php echo $res['type']; ?></p>
                                    </div>
                                    <a href="<?php echo $res['file_path']; ?>" target="_blank"
                                        style="color: var(--primary-green);"><i class="fa-solid fa-download"></i></a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="health" class="dashboard-section">
            <div class="section-header">
                <h2>Health Profile</h2>
                <button class="btn-primary" onclick="updateHealthProfile()">Update Vitals</button>
            </div>
            <div class="card">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div style="background: #0a0a0a; padding: 15px; border-radius: 10px;">
                        <div style="color: var(--text-gray); font-size: 0.9rem;">Height</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">
                            <?php echo htmlspecialchars($trainerProfile['height'] ?? '---'); ?> cm
                        </div>
                    </div>
                    <div style="background: #0a0a0a; padding: 15px; border-radius: 10px;">
                        <div style="color: var(--text-gray); font-size: 0.9rem;">Weight</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">
                            <?php echo htmlspecialchars($trainerProfile['weight'] ?? '---'); ?> kg
                        </div>
                    </div>
                    <div style="background: #0a0a0a; padding: 15px; border-radius: 10px;">
                        <div style="color: var(--text-gray); font-size: 0.9rem;">BMI</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">
                            <?php
                            if (!empty($trainerProfile['height']) && !empty($trainerProfile['weight'])) {
                                $h = $trainerProfile['height'] / 100;
                                $w = $trainerProfile['weight'];
                                echo number_format($w / ($h * $h), 1);
                            } else {
                                echo '---';
                            }
                            ?>
                        </div>
                    </div>
                    <div style="background: #0a0a0a; padding: 15px; border-radius: 10px;">
                        <div style="color: var(--text-gray); font-size: 0.9rem;">Blood Group</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">
                            <?php echo htmlspecialchars($trainerProfile['blood_group'] ?? '---'); ?>
                        </div>
                    </div>
                </div>
                <div style="margin-top: 2rem;">
                    <h4>Medical Conditions / Allergies</h4>
                    <p style="color: var(--text-gray);">
                        <?php echo !empty($trainerProfile['medical_conditions']) ? htmlspecialchars($trainerProfile['medical_conditions']) : 'None listed.'; ?>
                    </p>
                </div>
            </div>
        </div>

    </main>

    <div id="toastContainer"></div>

    <div id="eventModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.92); backdrop-filter: blur(12px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
        <div class="card" style="width: 100%; max-width: 800px; max-height: 90vh; overflow-y: auto; border: 1px solid rgba(57, 255, 20, 0.3); border-radius: 24px; background: #0a0a0a; position: relative; animation: modalSlideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1); padding: 0;">
            
            <div style="position: sticky; top: 0; background: #0a0a0a; padding: 25px 35px; border-bottom: 1px solid var(--glass-border); z-index: 10; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="font-size: 1.8rem; font-weight: 800; letter-spacing: -0.5px;">Create Tournament</h2>
                    <p style="color: var(--text-gray); font-size: 0.9rem;">Host a competition and manage participants.</p>
                </div>
                <button onclick="closeEventModal()" style="background: rgba(255,255,255,0.05); border: none; color: #fff; width: 40px; height: 40px; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s;"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form id="eventForm" style="padding: 35px;">
                <input type="hidden" name="is_tournament" value="1">
                
                <div style="margin-bottom: 30px;">
                    <h4 style="color: var(--primary-green); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-info-circle"></i> Basic Information
                    </h4>
                    <div class="form-group">
                        <label>Tournament / Event Name</label>
                        <input type="text" name="title" required placeholder="e.g. Matrix Summer Cricket Cup 2026">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Sport Type</label>
                            <select name="type">
                                <option value="Cricket">Cricket</option>
                                <option value="Football">Football</option>
                                <option value="Badminton">Badminton</option>
                                <option value="Tennis">Tennis</option>
                                <option value="Basketball">Basketball</option>
                                <option value="Swimming">Swimming</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Venue / Location</label>
                            <input type="text" name="location" required placeholder="e.g. Golden Turf, MG Road">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tournament Description</label>
                        <textarea name="description" rows="3" placeholder="Explain the rules, eligibility and details..."></textarea>
                    </div>
                </div>

                <div style="margin-bottom: 30px;">
                    <h4 style="color: var(--primary-green); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-calendar-days"></i> Schedule Details
                    </h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" id="start_date" required>
                        </div>
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" id="end_date" required>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Registration Deadline</label>
                            <input type="date" name="registration_deadline" id="reg_deadline" required>
                        </div>
                        <div class="form-group">
                            <label>Match Time (Starting From)</label>
                            <input type="time" name="event_time" id="event_time" required>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 30px;">
                    <h4 style="color: var(--primary-green); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-coins"></i> Fees & Participants
                    </h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Entry Fee (₹)</label>
                            <input type="number" name="price" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label>Max Teams / Participants</label>
                            <input type="number" name="max_participants" placeholder="16" value="16">
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 30px;">
                    <h4 style="color: var(--primary-green); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-trophy"></i> Prizes & Format
                    </h4>
                    <div class="form-group">
                        <label>Tournament Format</label>
                        <select name="tournament_format">
                            <option value="Knockout">Knockout</option>
                            <option value="League">League</option>
                            <option value="Round Robin">Round Robin</option>
                            <option value="Double Elimination">Double Elimination</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Prize Details</label>
                        <textarea name="prize_details" rows="2" placeholder="e.g. ₹5000 + Trophy for Winner"></textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Contact Information</label>
                            <input type="text" name="contact_info" placeholder="+91 98765 43210">
                        </div>
                        <div class="form-group">
                            <label>Tournament Banner / Image</label>
                            <input type="file" name="banner" accept="image/*" style="padding: 10px;">
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px;">
                    <button type="button" onclick="submitTournamentForm(this)" class="btn-primary" style="height: 55px; font-size: 1.1rem; font-weight: 800; box-shadow: 0 0 20px rgba(57, 255, 20, 0.3);">
                        <i class="fa-solid fa-plus-circle"></i> Create Tournament
                    </button>
                    <button type="button" onclick="closeEventModal()" class="btn-outline" style="height: 55px; font-size: 1rem;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showToast(message, isError = false) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast' + (isError ? ' error' : '');
            toast.innerHTML = `
                <i class="fa-solid ${isError ? 'fa-circle-exclamation' : 'fa-circle-check'}" style="color: ${isError ? '#ff4444' : 'var(--primary-green)'}"></i>
                <span>${message}</span>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }

        function showSection(sectionId, element) {
            document.querySelectorAll('.dashboard-section').forEach(sec => sec.style.display = 'none');
            const target = document.getElementById(sectionId);
            if (target) target.style.display = 'block';
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            if (element) element.classList.add('active');
        }

        async function saveProfile() {
            const form = document.getElementById('profileForm');
            if (!form.checkValidity()) { form.reportValidity(); return; }
            const formData = new FormData(form);
            try {
                const response = await fetch('api/update_trainer_profile.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) { showToast(result.message); setTimeout(() => location.reload(), 1500); }
                else { showToast(result.message, true); }
            } catch (err) { showToast('Connection error', true); }
        }

        async function updateSchedule() {
            const form = document.getElementById('scheduleForm');
            const rows = form.querySelectorAll('.schedule-row');
            const schedule = [];
            rows.forEach(row => {
                schedule.push({
                    day: row.querySelector('input[name="day[]"]').value,
                    start: row.querySelector('input[name="start[]"]').value,
                    end: row.querySelector('input[name="end[]"]').value,
                    active: row.querySelector('input[name="active[]"]').checked
                });
            });
            try {
                const response = await fetch('api/handle_schedule.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ schedule })
                });
                const result = await response.json();
                alert(result.message);
            } catch (err) { console.error(err); }
        }

        function openEventModal() {
            if (!document.getElementById('start_date')._flatpickr) { flatpickr("#start_date", { minDate: "today", dateFormat: "Y-m-d" }); }
            if (!document.getElementById('end_date')._flatpickr) { flatpickr("#end_date", { minDate: "today", dateFormat: "Y-m-d" }); }
            if (!document.getElementById('reg_deadline')._flatpickr) { flatpickr("#reg_deadline", { minDate: "today", dateFormat: "Y-m-d" }); }
            if (!document.getElementById('event_time')._flatpickr) {
                flatpickr("#event_time", {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    altInput: true,
                    altFormat: "h:i K",
                    time_24hr: false
                });
            }
            document.getElementById('eventModal').style.display = 'flex';
        }

        function closeEventModal() { document.getElementById('eventModal').style.display = 'none'; }

        async function submitTournamentForm(btn) {
            const form = document.getElementById('eventForm');
            const data = new FormData(form);
            data.append('action', 'create');

            if (!form.title.value || !form.start_date.value || !form.end_date.value || !form.location.value) {
                 showToast("Please fill all required fields.", true);
                 return;
            }

            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Processing...';
            
            try {
                const response = await fetch('api/handle_programs.php', { method: 'POST', body: data });
                const result = await response.json();
                if (result.success) { showToast("Tournament Created Successfully!"); setTimeout(() => location.reload(), 1500); }
                else { showToast(result.message, true); btn.disabled = false; btn.innerHTML = originalContent; }
            } catch (err) { showToast("System error occurred.", true); btn.disabled = false; btn.innerHTML = originalContent; }
        }

        async function updateBooking(bookingId, status) {
            if (!confirm(`Mark this booking as ${status}?`)) return;
            try {
                const response = await fetch('api/update_booking_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ bookingId, status })
                });
                const result = await response.json();
                if (result.success) { alert(result.message); location.reload(); }
                else { alert(result.message); }
            } catch (err) { console.error(err); }
        }

        async function processRequest(event, requestId, action) {
            const row = document.getElementById('req-row-' + requestId);
            const buttons = row.querySelectorAll('button');
            buttons.forEach(b => b.disabled = true);
            try {
                const response = await fetch('api/handle_trainer_request.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ requestId: requestId, action: action })
                });
                const result = await response.json();
                if (result.success) {
                    showToast(result.message);
                    row.cells[2].innerHTML = `<span class='status-pill' style='padding: 4px 10px; border-radius: 50px; font-size: 0.8rem; background: rgba(255,255,255,0.05); color: ${action === 'Accepted' ? 'var(--primary-green)' : '#ff4444'};'>${action}</span>`;
                    row.cells[3].innerHTML = `<span style='color: var(--text-gray); font-size: 0.8rem; opacity: 0.6;'>Processed</span>`;
                } else { showToast(result.message, true); buttons.forEach(b => b.disabled = false); }
            } catch (err) { showToast('Connection error. Please try again.', true); buttons.forEach(b => b.disabled = false); }
        }

        function triggerUpload() { document.getElementById('resourceInput').click(); }

        async function uploadResource(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            const title = prompt("Enter Resource Title:", file.name);
            if (!title) return;
            const formData = new FormData();
            formData.append('resource', file);
            formData.append('title', title);
            formData.append('type', 'Workout Plan');
            try {
                const response = await fetch('api/upload_resource.php', { method: 'POST', body: formData });
                const result = await response.json();
                alert(result.message);
                if (result.success) location.reload();
            } catch (err) { console.error(err); }
        }

        async function updateHealthProfile() {
            const height = prompt("Enter Height (cm):", "<?php echo $trainerProfile['height'] ?? ''; ?>");
            const weight = prompt("Enter Height (kg):", "<?php echo $trainerProfile['weight'] ?? ''; ?>");
            const blood = prompt("Enter Blood Group:", "<?php echo $trainerProfile['blood_group'] ?? ''; ?>");
            const conditions = prompt("Enter Medical Conditions/Allergies:", "<?php echo $trainerProfile['medical_conditions'] ?? ''; ?>");
            if (height === null) return;
            try {
                const response = await fetch('api/update_health_profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ height, weight, blood_group: blood, medical_conditions: conditions })
                });
                const result = await response.json();
                alert(result.message);
                if (result.success) location.reload();
            } catch (err) { console.error(err); }
        }

        async function deleteEvent(id) {
            if (!confirm('Are you sure you want to delete this event?')) return;
            try {
                const res = await fetch('api/delete_event.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const result = await res.json();
                if (result.success) { alert('Event removed successfully.'); location.reload(); }
                else { alert('Error: ' + result.message); }
            } catch (err) { alert('An error occurred. Please try again.'); }
        }

        window.onload = function () {
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                const navLink = document.querySelector(`.nav-link[onclick*="'${hash}'"]`);
                showSection(hash, navLink);
            }
        };
    </script>
</body>

</html>