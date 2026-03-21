<?php
session_start();
require_once 'db_connect.php';

$trainerEmail = $_GET['email'] ?? '';

if (empty($trainerEmail)) {
    die("Trainer email not provided.");
}

$trainerInfo = null;
$trainerProfile = null;
$programs = [];
$schedule = [];
$reviews = [];

if ($pdo) {
    try {
        // 1. Fetch User Info
        $stmt = $pdo->prepare("SELECT full_name, role, email FROM USERS WHERE email = ?");
        $stmt->execute([$trainerEmail]);
        $trainerInfo = $stmt->fetch();

        if (!$trainerInfo || $trainerInfo['role'] !== 'Trainer') {
            die("Trainer not found.");
        }

        // 2. Fetch Profile details
        $stmt = $pdo->prepare("SELECT * FROM TRAINER_PROFILES WHERE user_email = ?");
        $stmt->execute([$trainerEmail]);
        $trainerProfile = $stmt->fetch();

        // 3. Fetch Programs
        $stmt = $pdo->prepare("SELECT * FROM COACHING_PROGRAMS WHERE trainer_email = ?");
        $stmt->execute([$trainerEmail]);
        $programs = $stmt->fetchAll();

        // 4. Fetch Schedule
        $stmt = $pdo->prepare("SELECT * FROM TRAINER_SCHEDULE WHERE trainer_email = ?");
        $stmt->execute([$trainerEmail]);
        $rawSchedule = $stmt->fetchAll();
        foreach ($rawSchedule as $s) {
            $schedule[$s['day_of_week']] = $s;
        }

        // 5. Fetch Reviews
        $stmt = $pdo->prepare("SELECT r.*, u.full_name as reviewer_name FROM TRAINER_REVIEWS r LEFT JOIN USERS u ON r.user_email = u.email WHERE r.trainer_email = ? ORDER BY r.created_at DESC");
        $stmt->execute([$trainerEmail]);
        $reviews = $stmt->fetchAll();

        // 6. Fetch Connection Status
        $connectionStatus = null;
        if (isset($_SESSION['user'])) {
            $stmt = $pdo->prepare("SELECT status FROM TRAINER_REQUESTS WHERE user_email = ? AND trainer_email = ?");
            $stmt->execute([$_SESSION['user'], $trainerEmail]);
            $connectionStatus = $stmt->fetchColumn() ?: null;
        }

    }
    catch (PDOException $e) {
        die("Database error occurred.");
    }
}
else {
    die("Database connection failed.");
}

$name = $trainerInfo['full_name'];
$bio = $trainerProfile['bio'] ?? "No biography provided.";
$specs = $trainerProfile['specializations'] ?? "Fitness Training";
$exp = $trainerProfile['years_experience'] ?? 0;
$hourly = $trainerProfile['hourly_rate'] ?? 0;
$rating = $trainerProfile['rating'] ?? 5.0;
$photo = !empty($trainerProfile['profile_photo']) ? $trainerProfile['profile_photo'] : 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&q=80&w=300';
$certDoc = $trainerProfile['certification_doc'] ?? '';
$certsList = $trainerProfile['certifications'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($name); ?> - Trainer Profile | PlayMatrix
    </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg-dark: #050505;
            --bg-card: #121212;
            --primary-green: #39ff14;
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
            background: var(--bg-dark);
            color: white;
            min-height: 100vh;
            padding-bottom: 3rem;
        }

        .header-bg {
            height: 250px;
            background: linear-gradient(135deg, #111, #000);
            position: relative;
            overflow: hidden;
        }

        .header-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center, rgba(57, 255, 20, 0.05), transparent);
        }

        .container {
            max-width: 1000px;
            margin: -100px auto 0;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }

        .profile-card {
            background: var(--bg-card);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            margin-bottom: 2rem;
        }

        .profile-header {
            display: flex;
            gap: 40px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .profile-img-wrapper {
            position: relative;
        }

        .profile-img {
            width: 200px;
            height: 200px;
            border-radius: 20px;
            object-fit: cover;
            border: 4px solid var(--bg-card);
            outline: 1px solid var(--glass-border);
        }

        .verified-badge {
            position: absolute;
            bottom: -10px;
            right: -10px;
            background: var(--primary-green);
            color: black;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            border: 4px solid var(--bg-card);
        }

        .profile-info {
            flex: 1;
            min-width: 300px;
        }

        .profile-info h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .specs {
            color: var(--primary-green);
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }

        .stats-row {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
            color: var(--text-gray);
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }

        .stat-item i {
            color: var(--primary-green);
        }

        .action-btns {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn-primary {
            padding: 12px 30px;
            background: var(--primary-green);
            color: black;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(57, 255, 20, 0.3);
        }

        .btn-outline {
            padding: 12px 30px;
            background: transparent;
            color: white;
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: white;
        }

        .section-grid {
            display: grid;
            grid-template-columns: 2fr 1.2fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        @media (max-width: 850px) {
            .section-grid {
                grid-template-columns: 1fr;
            }
        }

        .section-card {
            background: var(--bg-card);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--primary-green);
            font-size: 1.1rem;
        }

        p.bio {
            color: var(--text-gray);
            line-height: 1.8;
            font-size: 1rem;
            white-space: pre-wrap;
        }

        .program-card {
            background: #0a0a0a;
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .prog-info h4 {
            margin-bottom: 5px;
        }

        .prog-info p {
            color: var(--text-gray);
            font-size: 0.85rem;
        }

        .prog-price {
            font-weight: 800;
            color: var(--primary-green);
            font-size: 1.2rem;
        }

        .schedule-list {
            list-style: none;
        }

        .schedule-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        .schedule-item:last-child {
            border-bottom: none;
        }

        .schedule-item span.active {
            color: var(--primary-green);
            font-weight: 600;
        }

        .review-card {
            padding: 15px;
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            margin-bottom: 10px;
            background: rgba(255,255,255,0.02);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            align-items: center;
        }

        .reviewer {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-white);
        }

        .review-rating {
            color: #ffc107;
            font-size: 0.8rem;
        }

        .review-text {
            color: var(--text-gray);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--bg-card);
            border: 1px solid var(--glass-border);
            width: 90%;
            max-width: 500px;
            border-radius: 15px;
            padding: 2rem;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-gray);
        }

        .rating-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
            margin: 1.5rem 0;
        }

        .rating-input input {
            display: none;
        }

        .rating-input label {
            font-size: 2rem;
            color: #333;
            cursor: pointer;
            transition: 0.2s;
        }

        .rating-input label:hover,
        .rating-input label:hover ~ label,
        .rating-input input:checked ~ label {
            color: #ffc107;
        }

        .comment-input {
            width: 100%;
            background: #0a0a0a;
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: white;
            padding: 1rem;
            margin-bottom: 1.5rem;
            resize: vertical;
            min-height: 100px;
        }

        nav {
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .back-btn {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .back-btn:hover {
            color: var(--primary-green);
        }
    </style>
</head>

<body>

    <nav>
        <a href="book_trainer.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Trainers</a>
        <div style="font-weight: 800; letter-spacing: 2px; color: var(--primary-green);">PLAYMATRIX</div>
    </nav>

    <div class="header-bg"></div>

    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-img-wrapper">
                    <img src="<?php echo htmlspecialchars($photo); ?>" class="profile-img"
                        alt="<?php echo htmlspecialchars($name); ?>">
                    <div class="verified-badge" title="Verified Coach">
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>
                <div class="profile-info">
                    <h1>
                        <?php echo htmlspecialchars($name); ?>
                    </h1>
                    <div class="specs">
                        <?php echo htmlspecialchars($specs); ?>
                    </div>
                    <div class="stats-row">
                        <div class="stat-item"><i class="fa-solid fa-briefcase"></i>
                            <?php echo $exp; ?> Years Experience
                        </div>
                        <div class="stat-item"><i class="fa-solid fa-star"></i>
                            <?php echo number_format($rating, 1); ?> Rating
                        </div>
                        <div class="stat-item"><i class="fa-solid fa-location-dot"></i> Online / On-site</div>
                    </div>
                    <p class="bio">
                        <?php echo htmlspecialchars($bio); ?>
                    </p>

                    <?php if (!empty($certsList)): ?>
                        <div style="margin-top: 15px; color: var(--text-gray); font-size: 0.9rem;">
                            <i class="fa-solid fa-award" style="color: var(--primary-green); margin-right: 8px;"></i>
                            <strong>Certifications:</strong> <?php echo htmlspecialchars($certsList); ?>
                        </div>
                    <?php endif; ?>

                    <div class="action-btns">
                        <?php if ($connectionStatus === 'Accepted'): ?>
                            <button class="btn-primary" style="background:var(--primary-green); color:black; cursor:default;" disabled>
                                <i class="fa-solid fa-check-circle"></i> OFFICIAL TRAINER
                            </button>
                        <?php elseif ($connectionStatus === 'Pending'): ?>
                            <button class="btn-primary" style="background:#444; color:white; cursor:default;" disabled>
                                <i class="fa-solid fa-hourglass-half"></i> REQUEST PENDING
                            </button>
                        <?php elseif ($connectionStatus === 'Rejected'): ?>
                            <button class="btn-primary" onclick="requestTrainer('<?php echo $trainerEmail; ?>')">
                                <i class="fa-solid fa-bolt"></i> REQUEST CONNECTION
                            </button>
                        <?php else: ?>
                            <button class="btn-primary" onclick="requestTrainer('<?php echo $trainerEmail; ?>')">
                                <i class="fa-solid fa-bolt"></i> REQUEST CONNECTION
                            </button>
                        <?php endif; ?>
                        <button class="btn-outline" onclick="openReviewModal('trainer', '<?php echo $trainerEmail; ?>')">
                            <i class="fa-solid fa-star"></i> Rate Trainer
                        </button>
                        <?php if (!empty($certDoc)): ?>
                            <a href="<?php echo htmlspecialchars($certDoc); ?>" target="_blank" class="btn-outline"
                                style="text-decoration:none;">
                                <i class="fa-solid fa-certificate"></i> View Certifications
                            </a>
                        <?php
endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-grid">
            <div class="main-side">

                <div class="section-card">
                    <h2 class="section-title"><i class="fa-solid fa-star-half-stroke"></i> Recent Reviews</h2>
                    <?php if (empty($reviews)): ?>
                        <p style="color:var(--text-gray);">No reviews yet. Be the first to train!</p>
                    <?php
else: ?>
                        <?php foreach ($reviews as $rev): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <span class="reviewer">
                                        <?php echo htmlspecialchars($rev['reviewer_name'] ?? 'User'); ?>
                                    </span>
                                    <div class="review-rating">
                                        <?php for ($i = 0; $i < $rev['rating']; $i++): ?><i class="fa-solid fa-star"></i>
                                        <?php
        endfor; ?>
                                    </div>
                                </div>
                                <p class="review-text">"
                                    <?php echo htmlspecialchars($rev['comment']); ?>"
                                </p>
                            </div>
                        <?php
    endforeach; ?>
                    <?php
endif; ?>
                </div>
            </div>

            <div class="sticky-side">
                <div class="section-card">
                    <h2 class="section-title"><i class="fa-solid fa-calendar-days"></i> Availability</h2>
                    <ul class="schedule-list">
                        <?php
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
foreach ($days as $day):
    $s = $schedule[$day] ?? null;
?>
                            <li class="schedule-item">
                                <span>
                                    <?php echo $day; ?>
                                </span>
                                <?php if ($s): ?>
                                    <span class="active">
                                        <?php echo date('H:i', strtotime($s['start_time'])); ?> -
                                        <?php echo date('H:i', strtotime($s['end_time'])); ?>
                                    </span>
                                <?php
    else: ?>
                                    <span>Unavailable</span>
                                <?php
    endif; ?>
                            </li>
                        <?php
endforeach; ?>
                    </ul>
                </div>

                <div class="section-card"
                    style="border-color: rgba(57, 255, 20, 0.2); background: rgba(57, 255, 20, 0.02);">
                    <h2 class="section-title"><i class="fa-solid fa-circle-info"></i> Session Info</h2>
                    <div style="color:var(--text-gray); font-size:0.9rem; line-height:1.6;">
                        <p><i class="fa-solid fa-clock" style="margin-right:8px;"></i> Personalized training sessions.</p>
                        <p style="margin-top:10px;"><i class="fa-solid fa-indian-rupee-sign" style="margin-right:8px;"></i> <strong>₹<?php echo number_format($hourly, 2); ?></strong> / hour</p>
                        <p style="margin-top:10px;"><i class="fa-solid fa-hand-holding-dollar"
                                style="margin-right:8px;"></i> Group discounts available.</p>
                        <hr style="border:none; border-top:1px solid var(--glass-border); margin:15px 0;">
                        <?php if ($connectionStatus === 'Accepted'): ?>
                            <button class="btn-primary" style="width:100%;" onclick="bookSession()">Book a Session</button>
                        <?php elseif ($connectionStatus === 'Pending'): ?>
                            <button class="btn-primary" style="width:100%; opacity: 0.7; cursor: not-allowed;" disabled>Request Pending...</button>
                        <?php elseif ($connectionStatus === 'Rejected'): ?>
                            <button class="btn-primary" style="width:100%; background: #666; cursor: not-allowed;" disabled>Request Rejected</button>
                        <?php else: ?>
                            <button class="btn-primary" style="width:100%;"
                                onclick="alert('Please request connection first to book sessions.')">Book a Session</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeReviewModal">&times;</span>
            <h2 id="modalTitle" style="margin-bottom: 1rem; text-align: center;">Rate Trainer</h2>
            <form id="reviewForm">
                <input type="hidden" name="type" id="reviewType">
                <input type="hidden" name="target_id" id="reviewTargetId">
                <div class="rating-input">
                    <input type="radio" name="rating" id="star5" value="5"><label for="star5"><i class="fa-solid fa-star"></i></label>
                    <input type="radio" name="rating" id="star4" value="4"><label for="star4"><i class="fa-solid fa-star"></i></label>
                    <input type="radio" name="rating" id="star3" value="3"><label for="star3"><i class="fa-solid fa-star"></i></label>
                    <input type="radio" name="rating" id="star2" value="2"><label for="star2"><i class="fa-solid fa-star"></i></label>
                    <input type="radio" name="rating" id="star1" value="1"><label for="star1"><i class="fa-solid fa-star"></i></label>
                </div>
                <textarea class="comment-input" name="comment" placeholder="Write your feedback here... (Optional)"></textarea>
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">Submit Review</button>
            </form>
        </div>
    </div>

    <div id="viewReviewsModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeViewReviewsModal">&times;</span>
            <h2 id="viewReviewsTitle" style="margin-bottom: 1.5rem; text-align: center;">Event Reviews</h2>
            <div id="programReviewsContainer" style="max-height: 400px; overflow-y: auto;">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>

    <script>
        const reviewModal = document.getElementById('reviewModal');
        const viewReviewsModal = document.getElementById('viewReviewsModal');
        const closeReviewModal = document.getElementById('closeReviewModal');
        const closeViewReviewsModal = document.getElementById('closeViewReviewsModal');
        const reviewForm = document.getElementById('reviewForm');

        function openReviewModal(type, id) {
            document.getElementById('reviewType').value = type;
            document.getElementById('reviewTargetId').value = id;
            document.getElementById('modalTitle').textContent = type === 'trainer' ? 'Rate Trainer' : 'Rate Coaching Event';
            reviewModal.style.display = 'flex';
        }

        async function viewProgramReviews(id, title) {
            document.getElementById('viewReviewsTitle').textContent = `${title} - Reviews`;
            const container = document.getElementById('programReviewsContainer');
            container.innerHTML = '<p style="text-align:center; color: var(--text-gray);">Loading reviews...</p>';
            viewReviewsModal.style.display = 'flex';

            try {
                const response = await fetch(`get_reviews.php?type=program&target_id=${id}`);
                const data = await response.json();
                if (data.success) {
                    if (data.reviews.length === 0) {
                        container.innerHTML = '<p style="text-align:center; color: var(--text-gray); padding: 2rem;">No reviews yet for this event.</p>';
                    } else {
                        container.innerHTML = data.reviews.map(r => `
                            <div class="review-card">
                                <div class="review-header">
                                    <span class="reviewer">${r.user_email.split('@')[0]}</span>
                                    <div class="review-rating">
                                        ${Array(5).fill(0).map((_, i) => `<i class="fa-solid fa-star" style="color: ${i < r.rating ? '#ffc107' : '#333'}"></i>`).join('')}
                                    </div>
                                </div>
                                <p class="review-text">${r.comment || 'No comment provided.'}</p>
                            </div>
                        `).join('');
                    }
                }
            } catch (err) {
                console.error(err);
                container.innerHTML = '<p style="text-align:center; color: #ff4444;">Error loading reviews.</p>';
            }
        }

        closeReviewModal.onclick = () => reviewModal.style.display = 'none';
        closeViewReviewsModal.onclick = () => viewReviewsModal.style.display = 'none';
        
        window.onclick = (e) => { 
            if(e.target == reviewModal) reviewModal.style.display = 'none';
            if(e.target == viewReviewsModal) viewReviewsModal.style.display = 'none';
        }

        reviewForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(reviewForm);
            
            if(!formData.get('rating')) {
                alert('Please select a rating!');
                return;
            }

            try {
                const response = await fetch('submit_review.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if(data.success) {
                    alert('Review submitted successfully!');
                    location.reload(); // Simple refresh to show new review
                } else {
                    alert(data.message);
                }
            } catch(err) {
                console.error(err);
                alert('Error submitting review');
            }
        });

        async function bookSession() {
            // Redirect to a booking page or show a modal
            // Use the existing submit_venue_booking.php or similar if appropriate
            window.location.href = 'submit_venue_booking.php?trainer_email=<?php echo urlencode($trainerEmail); ?>';
        }

        async function requestTrainer(email) {
            const btn = document.querySelector('.btn-primary');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Sending...';

            try {
                const response = await fetch('submit_trainer_request.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ trainerEmail: email })
                });
                const result = await response.json();

                if (result.success) {
                    btn.innerHTML = 'REQUEST PENDING <i class="fa-solid fa-hourglass-half"></i>';
                    btn.style.background = '#444';
                    btn.style.color = 'white';
                    alert(result.message);
                } else {
                    alert(result.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                console.error(err);
                alert('Connection error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    </script>
</body>

</html>