<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'db_connect.php';

$trainerVenues = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM TRAINER_VENUES WHERE approval_status = 'Approved' ORDER BY created_at DESC");
        $stmt->execute();
        $trainerVenues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venues - PlayMatrix</title>
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
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-white);
            min-height: 100vh;
            padding: 2rem;
            position: relative;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .btn-back {
            color: var(--text-gray);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
            font-weight: 500;
        }

        .btn-back:hover {
            color: var(--primary-green);
        }

        .venues-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }

        .venue-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: 0.4s;
            position: relative;
        }

        .venue-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-green);
            box-shadow: 0 10px 30px rgba(57, 255, 20, 0.1);
        }

        .venue-image-carousel {
            height: 200px;
            background: #111;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--glass-border);
        }

        .venue-image-carousel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.5s;
        }

        .venue-card:hover .venue-image-carousel img {
            transform: scale(1.05);
        }

        .venue-info {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .venue-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary-green);
        }

        .venue-detail {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
            color: var(--text-gray);
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .venue-detail i {
            color: rgba(255,255,255,0.4);
            margin-top: 3px;
            width: 16px;
            text-align: center;
        }

        .about-text {
            border-top: 1px solid var(--glass-border);
            padding-top: 1rem;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.7);
        }

        .empty-state {
            text-align: center;
            padding: 4rem;
            color: var(--text-gray);
            background: rgba(255,255,255,0.02);
            border: 1px dashed var(--glass-border);
            border-radius: 20px;
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <a href="dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
                <h1 style="margin-top: 15px; font-size: 2.2rem; font-weight: 800; letter-spacing: 1px;">Discover <span style="color: var(--primary-green);">Venues</span></h1>
                <p style="color: var(--text-gray); margin-top: 5px;">Explore and book premium sports venues listed by verified trainers.</p>
            </div>
            <div>
                <i class="fa-solid fa-building" style="font-size: 3rem; color: var(--primary-green); opacity: 0.2;"></i>
            </div>
        </div>

        <div class="venues-grid">
            <?php if (empty($trainerVenues)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-building-circle-exclamation" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                    <h2>No Venues Available</h2>
                    <p style="margin-top: 10px;">Currently, there are no approved venues listed by any trainers. Please check back later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($trainerVenues as $venue): ?>
                    <div class="venue-card">
                        <div class="venue-image-carousel">
                            <?php if (!empty($venue['pic1'])): ?>
                                <img src="<?php echo htmlspecialchars($venue['pic1']); ?>" alt="Venue Cover">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; opacity: 0.3;">
                                    <i class="fa-solid fa-image" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Badges -->
                            <div style="position: absolute; top: 10px; left: 10px; display: flex; gap: 8px;">
                                <span style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); padding: 5px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; border: 1px solid var(--primary-green); color: var(--primary-green);">
                                    VERIFIED
                                </span>
                            </div>
                        </div>

                        <div class="venue-info">
                            <h3 class="venue-title"><?php echo htmlspecialchars($venue['venue_name']); ?></h3>

                            <div class="venue-detail">
                                <i class="fa-solid fa-location-dot"></i>
                                <span><?php echo htmlspecialchars($venue['location']); ?></span>
                            </div>

                            <div class="venue-detail">
                                <i class="fa-solid fa-clock"></i>
                                <span><?php echo htmlspecialchars($venue['timing']); ?></span>
                            </div>

                            <div class="venue-detail">
                                <i class="fa-solid fa-futbol"></i>
                                <span><?php echo htmlspecialchars($venue['sports_available']); ?></span>
                            </div>

                            <?php if (!empty($venue['about_venue'])): ?>
                                <div class="about-text">
                                    <?php echo nl2br(htmlspecialchars($venue['about_venue'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
