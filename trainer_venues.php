<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'db_connect.php';

$email = $_SESSION['user'];

// Verify Trainer Role
$isTrainer = false;
if ($pdo) {
    $stmt = $pdo->prepare("SELECT role FROM USERS WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && $user['role'] === 'Trainer') {
        $isTrainer = true;
    }
}
if (!$isTrainer) {
    header("Location: dashboard.php");
    exit;
}

// Handle Form Submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_venue') {
    $venue_name = trim($_POST['venue_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $about_venue = trim($_POST['about_venue'] ?? '');
    $timing = trim($_POST['timing'] ?? '');
    $sports_available = trim($_POST['sports_available'] ?? '');

    // File uploads
    $upload_dir = 'uploads/venues/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $pic1 = '';
    $pic2 = '';
    $pic3 = '';

    if (isset($_FILES['pic1']) && $_FILES['pic1']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['pic1']['name'], PATHINFO_EXTENSION);
        $pic1 = $upload_dir . uniqid('venue_') . '.' . $ext;
        move_uploaded_file($_FILES['pic1']['tmp_name'], $pic1);
    }
    if (isset($_FILES['pic2']) && $_FILES['pic2']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['pic2']['name'], PATHINFO_EXTENSION);
        $pic2 = $upload_dir . uniqid('venue_') . '.' . $ext;
        move_uploaded_file($_FILES['pic2']['tmp_name'], $pic2);
    }
    if (isset($_FILES['pic3']) && $_FILES['pic3']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['pic3']['name'], PATHINFO_EXTENSION);
        $pic3 = $upload_dir . uniqid('venue_') . '.' . $ext;
        move_uploaded_file($_FILES['pic3']['tmp_name'], $pic3);
    }

    if (!empty($venue_name) && !empty($location) && $pdo) {
        $stmt = $pdo->prepare("INSERT INTO TRAINER_VENUES (trainer_email, venue_name, location, about_venue, timing, sports_available, pic1, pic2, pic3, approval_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Approved')");
        if ($stmt->execute([$email, $venue_name, $location, $about_venue, $timing, $sports_available, $pic1, $pic2, $pic3])) {
            $message = '<div class="alert alert-success">Venue added successfully!</div>';
        }
        else {
            $message = '<div class="alert alert-danger">Error adding venue.</div>';
        }
    }
    else {
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_venue') {
    $venue_id = $_POST['venue_id'] ?? null;
    if ($venue_id && $pdo) {
        // Fetch pics to delete them from server
        $stmtImg = $pdo->prepare("SELECT pic1, pic2, pic3 FROM TRAINER_VENUES WHERE id = ? AND trainer_email = ?");
        $stmtImg->execute([$venue_id, $email]);
        $imgs = $stmtImg->fetch();
        if ($imgs) {
            foreach (['pic1', 'pic2', 'pic3'] as $p) {
                if (!empty($imgs[$p]) && file_exists($imgs[$p])) {
                    @unlink($imgs[$p]);
                }
            }
        }

        $stmt = $pdo->prepare("DELETE FROM TRAINER_VENUES WHERE id = ? AND trainer_email = ?");
        if ($stmt->execute([$venue_id, $email])) {
            $message = '<div class="alert alert-success">Venue deleted successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error deleting venue.</div>';
        }
    }
}

// Fetch Existing Venues (Trainer's own venues)
$venues = [];
if ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM TRAINER_VENUES WHERE trainer_email = ? ORDER BY created_at DESC");
    $stmt->execute([$email]);
    $venues = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Venues - PlayMatrix</title>
    <!-- Fonts -->
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
            --text-white: #ffffff;
            --text-gray: #a1a1a1;
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: \'Outfit\', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-white);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .btn-back {
            color: var(--text-gray);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .btn-back:hover {
            color: var(--primary-green);
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
            display: inline-block;
        }

        .btn-primary:hover {
            box-shadow: 0 0 15px rgba(57, 255, 20, 0.4);
        }

        .card {
            background: var(--bg-card);
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-gray);
        }

        input[type="text"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 12px;
            background: #0a0a0a;
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: #fff;
            font-family: \'Outfit\', sans-serif;
        }

        input[type="file"] {
            padding: 9px;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-green);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: rgba(57, 255, 20, 0.1);
            color: var(--primary-green);
            border: 1px solid var(--primary-green);
        }

        .alert-danger {
            background: rgba(255, 68, 68, 0.1);
            color: #ff4444;
            border: 1px solid #ff4444;
        }

        .venues-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .venue-card {
            background: #1a1a1a;
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            overflow: hidden;
        }

        .venue-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #0a0a0a;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-gray);
        }

        .venue-info {
            padding: 1.5rem;
        }

        .venue-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--primary-green);
        }

        .venue-detail {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 8px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .venue-detail i {
            margin-top: 3px;
            color: var(--primary-green);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div>
                <a href="trainer_dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back to
                    Dashboard</a>
                <h1 style="margin-top: 15px;">Manage Venues</h1>
            </div>
        </div>

        <?php echo $message; ?>

        <div class="card">
            <h2 style="margin-bottom: 20px;">Add New Venue</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_venue">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Venue Name *</label>
                        <input type="text" name="venue_name" required placeholder="e.g. Matrix Arena">
                    </div>
                    
                    <div class="form-group">
                        <label>Location *</label>
                        <input type="text" name="location" required placeholder="e.g. 123 Matrix Street, City">
                    </div>
                </div>

                <div class="form-group">
                    <label>About Venue</label>
                    <textarea name="about_venue" rows="3" placeholder="Description of the facilities..."></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Timing</label>
                        <input type="text" name="timing" placeholder="e.g. 06:00 AM - 10:00 PM">
                    </div>

                    <div class="form-group">
                        <label>Sports Available</label>
                        <input type="text" name="sports_available" placeholder="e.g. Football, Tennis, Basketball">
                    </div>
                </div>

                <h3 style="margin: 15px 0 10px; font-size: 1.1rem;">Venue Photos (Up to 3)</h3>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                    <div class="form-group">
                        <label>Photo 1</label>
                        <input type="file" name="pic1" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Photo 2</label>
                        <input type="file" name="pic2" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Photo 3</label>
                        <input type="file" name="pic3" accept="image/*">
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="margin-top: 10px;"><i class="fa-solid fa-plus"></i>
                    Save Venue</button>
            </form>
        </div>

        <h2 style="margin-bottom: 20px;">Your Venues</h2>

        <?php if (empty($venues)): ?>
            <div class="card" style="text-align: center; color: var(--text-gray);">
                <i class="fa-solid fa-building" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>You haven't added any venues yet.</p>
            </div>
        <?php
else: ?>
            <div class="venues-grid">
                <?php foreach ($venues as $venue): ?>
                    <div class="venue-card">
                        <?php if (!empty($venue['pic1'])): ?>
                            <img src="<?php echo htmlspecialchars($venue['pic1']); ?>" alt="Venue Image" class="venue-image">
                        <?php
        else: ?>
                            <div class="venue-image">
                                <i class="fa-solid fa-image" style="font-size: 2rem; opacity: 0.5;"></i>
                            </div>
                        <?php
        endif; ?>

                        <div class="venue-info">
                            <h3 class="venue-title">
                                <?php echo htmlspecialchars($venue['venue_name']); ?>
                            </h3>

                            <?php if (!empty($venue['location'])): ?>
                                <div class="venue-detail">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span>
                                        <?php echo htmlspecialchars($venue['location']); ?>
                                    </span>
                                </div>
                            <?php
        endif; ?>

                            <?php if (!empty($venue['timing'])): ?>
                                <div class="venue-detail">
                                    <i class="fa-solid fa-clock"></i>
                                    <span>
                                        <?php echo htmlspecialchars($venue['timing']); ?>
                                    </span>
                                </div>
                            <?php
        endif; ?>

                            <?php if (!empty($venue['sports_available'])): ?>
                                <div class="venue-detail">
                                    <i class="fa-solid fa-futbol"></i>
                                    <span>
                                        <?php echo htmlspecialchars($venue['sports_available']); ?>
                                    </span>
                                </div>
                            <?php
        endif; ?>

                            <?php if (!empty($venue['about_venue'])): ?>
                                <div class="venue-detail"
                                    style="margin-top: 15px; border-top: 1px solid var(--glass-border); padding-top: 15px;">
                                    <?php echo nl2br(htmlspecialchars($venue['about_venue'])); ?>
                                </div>
                            <?php
        endif; ?>

                            <?php
        $pics = 0;
        if (!empty($venue['pic1']))
            $pics++;
        if (!empty($venue['pic2']))
            $pics++;
        if (!empty($venue['pic3']))
            $pics++;
?>
                            <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--glass-border); padding-top: 15px;">
                                <div style="font-size: 0.8rem; color: var(--text-gray);">
                                    <?php echo $pics; ?> Photo(s) Attached
                                </div>
                                <form method="POST" action="" onsubmit="return confirm('WARNING: Are you sure you want to completely delete this venue? This action cannot be undone.');" style="margin: 0;">
                                    <input type="hidden" name="action" value="delete_venue">
                                    <input type="hidden" name="venue_id" value="<?php echo $venue['id']; ?>">
                                    <button type="submit" style="background: rgba(255, 68, 68, 0.1); color: #ff4444; border: 1px solid rgba(255, 68, 68, 0.3); padding: 8px 15px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.85rem; transition: all 0.3s;" onmouseover="this.style.background='rgba(255, 68, 68, 0.2)'; this.style.borderColor='#ff4444';" onmouseout="this.style.background='rgba(255, 68, 68, 0.1)'; this.style.borderColor='rgba(255, 68, 68, 0.3)';">
                                        <i class="fa-solid fa-trash-can" style="pointer-events: none;"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php
    endforeach; ?>
            </div>
        <?php endif; ?>


    </div>
</body>

</html>

</html>