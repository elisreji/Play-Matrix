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
    header("Location: dashboard.php");
    exit;
}

if ($currentUser) {
    $userName = $currentUser['full_name'] ?? $currentUser['name'] ?? 'Trainer';
    if (!isset($currentUser['role']) || $currentUser['role'] !== 'Trainer') {
        header("Location: dashboard.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}

// Fetch Requests
$requests = [];
if ($pdo) {
    $stmt = $pdo->prepare("SELECT tr.*, u.full_name as user_name FROM TRAINER_REQUESTS tr LEFT JOIN USERS u ON tr.user_email = u.email WHERE tr.trainer_email = ? ORDER BY tr.created_at DESC");
    $stmt->execute([$email]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests - PlayMatrix</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #050505;
            --bg-card: #121212;
            --primary-green: #39ff14;
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
            text-decoration: none;
            color: white;
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
            margin-bottom: 0.5rem;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(57, 255, 20, 0.1);
            color: var(--primary-green);
        }

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

        .card {
            background: var(--bg-card);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            padding: 1rem;
            color: var(--text-gray);
            font-weight: 600;
            border-bottom: 1px solid var(--glass-border);
        }

        td {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: rgba(255, 170, 0, 0.1);
            color: #ffaa00;
        }

        .status-accepted {
            background: rgba(57, 255, 20, 0.1);
            color: var(--primary-green);
        }

        .status-rejected {
            background: rgba(255, 68, 68, 0.1);
            color: #ff4444;
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
            margin-left: 5px;
        }

        .btn-accept {
            background: var(--primary-green);
            color: #000;
        }

        .btn-reject {
            background: #ff4444;
            color: #fff;
        }

        .btn-action:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-gray);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.3;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }

            .brand span,
            .nav-link span {
                display: none;
            }

            .main-content {
                margin-left: 80px;
            }
        }

        /* Toast Styling */
        #toastContainer {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: #1a1a1a;
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            border-left: 4px solid var(--primary-green);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
            animation: slideIn 0.3s ease-out forwards;
            min-width: 300px;
            transition: opacity 0.5s;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast.error {
            border-left-color: #ff4444;
        }

        tr.row-fade-out {
            transition: 0.5s;
            opacity: 0.3;
            filter: blur(2px);
            pointer-events: none;
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <a href="trainer_dashboard.php" class="brand">
            <div class="brand-icon"><i class="fa-solid fa-graduation-cap"></i></div>
            <span>TRAINER ZONE</span>
        </a>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="trainer_dashboard.php" class="nav-link">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Overview</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_dashboard.php#profile" class="nav-link">
                    <i class="fa-solid fa-id-card"></i>
                    <span>My Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_dashboard.php#programs" class="nav-link">
                    <i class="fa-solid fa-dumbbell"></i>
                    <span>Programs</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_dashboard.php#schedule" class="nav-link">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Schedule</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_dashboard.php#bookings" class="nav-link">
                    <i class="fa-solid fa-check-double"></i>
                    <span>Bookings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_requests.php" class="nav-link active">
                    <i class="fa-solid fa-envelope-open-text"></i>
                    <span>Requests</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_dashboard.php#students" class="nav-link">
                    <i class="fa-solid fa-users"></i>
                    <span>Students</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_dashboard.php#resources" class="nav-link">
                    <i class="fa-solid fa-file-arrow-up"></i>
                    <span>Resources</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_dashboard.php#earnings" class="nav-link">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Earnings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="trainer_dashboard.php#health" class="nav-link">
                    <i class="fa-solid fa-heart-pulse"></i>
                    <span>Health Profile</span>
                </a>
            </li>
        </ul>

        <div style="margin-top: auto; border-top: 1px solid var(--glass-border); padding-top: 1rem;">
            <a href="dashboard.php" class="nav-link">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Player View</span>
            </a>
            <a href="logout.php" class="nav-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header>
            <div class="welcome-text">
                <h1>Trainer Requests</h1>
                <p>Manage people who want to train with you.</p>
            </div>
        </header>

        <div class="card">
            <?php if (empty($requests)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <h3>No Requests Found</h3>
                    <p>When students want to book you, they'll show up here.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Date Received</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td><strong>
                                        <?php echo htmlspecialchars($req['user_name'] ?: 'New Student'); ?>
                                    </strong></td>
                                <td>
                                    <?php echo htmlspecialchars($req['user_email']); ?>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($req['created_at'])); ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($req['status']); ?>">
                                        <?php echo $req['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($req['status'] === 'Pending'): ?>
                                        <button onclick="processRequest(event, <?php echo $req['id']; ?>, 'Accepted')"
                                            class="btn-action btn-accept">Accept</button>
                                        <button onclick="processRequest(event, <?php echo $req['id']; ?>, 'Rejected')"
                                            class="btn-action btn-reject">Reject</button>
                                    <?php else: ?>
                                        <span style="color: var(--text-gray); font-size: 0.9rem;">Processed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <div id="toastContainer"></div>

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

        async function processRequest(event, requestId, action) {
            const row = event.target.closest('tr');
            const buttons = row.querySelectorAll('button');

            // Visual feedback - disable buttons
            buttons.forEach(b => b.disabled = true);

            try {
                const response = await fetch('handle_trainer_request.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ requestId: requestId, action: action })
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message);

                    // Visual update of the row without reload
                    row.classList.add('row-fade-out');
                    const statusTd = row.cells[3];
                    const actionTd = row.cells[4];

                    statusTd.innerHTML = `<span class="status-badge status-${action.toLowerCase()}">${action}</span>`;
                    actionTd.innerHTML = `<span style="color: var(--text-gray); font-size: 0.9rem; opacity: 0.6;">Processed</span>`;

                    setTimeout(() => {
                        row.classList.remove('row-fade-out');
                    }, 1000);
                } else {
                    showToast(result.message, true);
                    buttons.forEach(b => b.disabled = false);
                }
            } catch (err) {
                console.error(err);
                showToast('Connection error. Please try again.', true);
                buttons.forEach(b => b.disabled = false);
            }
        }
    </script>
</body>

</html>