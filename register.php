<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)));
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $plan = $_POST['plan'] ?? 'Free';
    $duration = $_POST['duration'] ?? 1;
    $name = explode('@', $email)[0];

    if ($password !== $confirm) {
        $error = "Passwords do not match";
    } else {
        $file = 'users.json';
        $users = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        if (!is_array($users))
            $users = [];

        // Check availability and blocked status
        $exists = false;
        $isBlocked = false;

        if ($pdo) {
            $stmt = $pdo->prepare("SELECT is_blocked FROM USERS WHERE email = ?");
            $stmt->execute([$email]);
            $result = $stmt->fetch();
            if ($result) {
                $exists = true;
                $isBlocked = (int) $result['is_blocked'] === 1;
            }
        }

        if (!$exists) {
            foreach ($users as $user) {
                if (strtolower(trim($user['email'])) === $email) {
                    $exists = true;
                    $isBlocked = isset($user['is_blocked']) && $user['is_blocked'];
                    break;
                }
            }
        }

        if ($isBlocked) {
            $error = "This account has been blocked. Please contact administration.";
        } elseif ($exists) {
            $error = "Email already registered";
        } else {
            $otp = rand(100000, 999999);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Determine verification status: Verified by email OTP OR by successful Payment
            $is_email_verified = (isset($_POST['is_verified']) && $_POST['is_verified'] == '1');
            $is_payment_verified = !empty($_POST['razorpay_payment_id']);
            $is_verified = ($is_email_verified || $is_payment_verified);
            $is_verified_int = $is_verified ? 1 : 0;

            // 1. Save to Database
            if ($pdo) {
                // Determine expiration date if needed, or just save duration if column exists (omitted for safety)
                $stmt = $pdo->prepare("INSERT INTO USERS (full_name, email, password_hash, otp, plan, is_verified) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashed_password, $otp, $plan, $is_verified_int]);
            }

            // 2. Save to JSON
            $users[] = [
                'email' => $email,
                'password' => $hashed_password,
                'name' => $name,
                'is_verified' => $is_verified,
                'otp' => $otp,
                'plan' => $plan,
                'duration' => $duration,
                'payment_id' => $_POST['razorpay_payment_id'] ?? null
            ];
            file_put_contents($file, json_encode($users));

            // Logic to skip email if already verified or paid
            if ($is_verified) {
                $_SESSION['user'] = $email; // Log them in
                header("Location: dashboard.php");
                exit;
            }

            // Send Verification Email (Legacy/Fallback flow for unverified free users)
            require_once 'PHPMailer/Exception.php';
            require_once 'PHPMailer/PHPMailer.php';
            require_once 'PHPMailer/SMTP.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'elisreji2028@mca.ajce.in';
                $mail->Password = 'dtedqdekswzflopc';
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('elisreji2028@mca.ajce.in', 'PlayMatrix');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Your Verification Code - PlayMatrix';
                $mail->Body = "
                <div style='background: #050505; color: white; padding: 40px; font-family: Outfit, sans-serif; border-radius: 20px; text-align: center;'>
                    <h1 style='color: #39ff14;'>Verify Your Account</h1>
                    <p>Thank you for signing up to PlayMatrix with the <strong>$plan Plan</strong>. Use the code below to complete your registration:</p>
                    <div style='background: rgba(57, 255, 20, 0.1); border: 2px solid #39ff14; color: #39ff14; font-size: 32px; font-weight: 900; letter-spacing: 10px; padding: 20px; border-radius: 12px; margin: 30px 0; display: inline-block;'>
                        $otp
                    </div>
                    <p style='color: #a1a1a1; font-size: 14px;'>This code will expire in 10 minutes.</p>
                </div>";

                $mail->send();
                $_SESSION['temp_email'] = $email;
                header("Location: otp_verify.php");
                exit;
            } catch (Exception $e) {
                $error = "OTP email failed: " . $mail->ErrorInfo;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Started - PlayMatrix</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Razorpay SDK -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <style>
        :root {
            --bg-dark: #050505;
            --bg-card: #121212;
            --primary-green: #39ff14;
            --primary-green-dim: #2cb812;
            --text-white: #ffffff;
            --text-gray: #a1a1a1;
            --glass-border: rgba(255, 255, 255, 0.08);
            --gradient-card: linear-gradient(145deg, #1a1a1a, #0d0d0d);
            --input-bg: #0a0a0a;
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
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-y: auto;
            /* Allow scrolling */
            padding: 20px 0;
        }

        /* Subtle Grid Background */
        .grid-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                linear-gradient(rgba(57, 255, 20, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(57, 255, 20, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: -1;
            mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
            -webkit-mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
        }

        .glow-spot {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(57, 255, 20, 0.1) 0%, transparent 70%);
            filter: blur(80px);
            z-index: -1;
            pointer-events: none;
        }

        .login-card {
            background: var(--gradient-card);
            border: 1px solid var(--glass-border);
            padding: 2.5rem;
            border-radius: 20px;
            width: 100%;
            max-width: 850px;
            margin: 2rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s ease-out forwards;
            position: relative;
            z-index: 10;
        }

        #step1 {
            max-width: 420px;
            margin: 0 auto;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-icon {
            font-size: 2rem;
            color: var(--bg-dark);
            background: var(--primary-green);
            width: 50px;
            height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            margin-bottom: 1rem;
            box-shadow: 0 0 15px rgba(57, 255, 20, 0.4);
        }

        .brand-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .brand-header p {
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            width: 100%;
            text-align: left;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-gray);
            font-size: 0.9rem;
            font-weight: 500;
            text-align: left;
        }

        .form-input {
            width: 100%;
            padding: 1rem;
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--text-white);
            font-size: 1rem;
            transition: 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(57, 255, 20, 0.1);
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            background-color: var(--primary-green);
            color: var(--bg-dark);
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 1rem;
        }

        .btn-primary:hover {
            background-color: var(--primary-green-dim);
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(57, 255, 255, 0.15);
        }

        .divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            color: var(--text-gray);
            font-size: 0.85rem;
            margin: 1.5rem 0;
            width: 100%;
        }

        .divider::before,
        .divider::after {
            content: '';
            height: 1px;
            background: #333;
            flex: 1;
        }

        /* Google Button */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 14px;
            background: transparent;
            color: var(--text-white);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-google:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--text-gray);
        }

        .form-footer {
            margin-top: 2rem;
            color: var(--text-gray);
            font-size: 0.9rem;
            text-align: center;
        }

        .form-footer a {
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: 0.3s;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        /* Back Home Link */
        .back-link {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: var(--text-gray);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: 0.3s;
        }

        .back-link:hover {
            color: var(--primary-green);
            transform: translateX(-5px);
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

        .error-msg {
            color: #ff4444;
            background: rgba(255, 68, 68, 0.1);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }

        /* Multi-step Logic */
        #step2 {
            display: none;
        }

        /* Plan Selection Styles */
        .plan-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            /* 2 columns for better visibility */
            gap: 1.5rem;
            margin-top: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 650px) {
            .plan-grid {
                grid-template-columns: 1fr;
            }

            .login-card {
                margin: 1rem;
                padding: 1.5rem;
            }
        }

        .plan-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.5rem;
            cursor: pointer;
            transition: 0.3s;
            text-align: left;
            position: relative;
            overflow: hidden;
        }

        .plan-card:hover {
            border-color: var(--primary-green);
            background: rgba(57, 255, 20, 0.05);
            transform: translateY(-3px);
        }

        .plan-card.selected {
            border-color: var(--primary-green);
            background: rgba(57, 255, 20, 0.1);
            box-shadow: 0 0 20px rgba(57, 255, 20, 0.2);
        }

        .plan-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0.8rem;
        }

        .plan-badge {
            font-size: 1.2rem;
        }

        .plan-name {
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
        }

        .plan-features {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .plan-features li {
            font-size: 0.85rem;
            color: var(--text-gray);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .plan-features li::before {
            content: '•';
            color: var(--primary-green);
        }

        .selected-icon {
            position: absolute;
            top: 15px;
            right: 15px;
            color: var(--primary-green);
            display: none;
        }

        .plan-card.selected .selected-icon {
            display: block;
        }

        /* Duration Selection Styles */
        .duration-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 1rem;
            cursor: pointer;
            transition: 0.3s;
            text-align: center;
        }

        .duration-card:hover {
            border-color: var(--primary-green);
            background: rgba(57, 255, 20, 0.05);
        }

        .duration-card.selected {
            border-color: var(--primary-green);
            background: rgba(57, 255, 20, 0.1);
            box-shadow: 0 0 15px rgba(57, 255, 20, 0.2);
        }

        .duration-months {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-white);
            margin-bottom: 5px;
        }

        .duration-save {
            font-size: 0.8rem;
            color: var(--primary-green);
            font-weight: 500;
        }
    </style>
    <!-- Google Identity Services Script -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>function handleCredentialResponse(response) {
            // Decode the JWT token
            const responsePayload = decodeJwtResponse(response.credential);

            // In a real app, send this to backend to create a user
            console.log("Creating account for: " + responsePayload.email);

            alert("Registration Successful! Welcome to PlayMatrix, " + responsePayload.name);
            window.location.href = 'dashboard.php';
        }

        function decodeJwtResponse(token) {
            var base64Url = token.split('.')[1];
            var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');

            var jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function (c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
            return JSON.parse(jsonPayload);
        }

        window.onload = function () {
            google.accounts.id.initialize({
                client_id: "841643636702-5v6e119knc1bq56enrh5bfallfetnqrc.apps.googleusercontent.com",
                callback: handleCredentialResponse,
                auto_select: false,
                itp_support: true
            });
            google.accounts.id.disableAutoSelect();

            google.accounts.id.renderButton(document.getElementById("google-signup-wrapper"),
                {
                    theme: "filled_black", size: "large", width: "100%", shape: "rectangular", text: "continue_with", logo_alignment: "center"
                });
        }

        function goToPlanSelection(event) {
            event.preventDefault();
            const email = document.getElementById('reg-email').value;
            const pass = document.getElementById('reg-pass').value;
            const confirmPass = document.getElementById('reg-confirm').value;

            // Basic JS Validation
            const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            if (!emailPattern.test(email)) {
                alert("Please enter a valid email address.");
                return;
            }
            if (pass.length < 6) {
                alert("Password must be at least 6 characters.");
                return;
            }
            if (pass !== confirmPass) {
                alert("Passwords do not match!");
                return;
            }

            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
            document.getElementById('reg-title').textContent = "Select Your Plan";
            document.getElementById('reg-subtitle').textContent = "Choose the membership that fits your game.";
        }

        function selectPlan(plan, event) {
            document.querySelectorAll('.plan-card').forEach(c => {
                c.classList.remove('selected');
                const btn = c.querySelector('button');
                if (btn) {
                    btn.textContent = "Select " + c.querySelector('.plan-name').textContent.split(' ')[0].charAt(0).toUpperCase() + c.querySelector('.plan-name').textContent.split(' ')[0].slice(1).toLowerCase();
                    btn.style.background = "transparent";
                    btn.style.color = "var(--primary-green)";
                    btn.style.border = "1px solid var(--primary-green)";
                }
            });

            const selectedCard = event.currentTarget;
            selectedCard.classList.add('selected');
            const selectedBtn = selectedCard.querySelector('button');
            if (selectedBtn) {
                selectedBtn.textContent = "Selected";
                selectedBtn.style.background = "var(--primary-green)";
                selectedBtn.style.color = "var(--bg-dark)";
                selectedBtn.style.border = "none";
            }
            document.getElementById('selectedPlanInput').value = plan;

            // Show/Hide Duration Selection
            const durationSection = document.getElementById('duration-section');
            if (plan === 'Free') {
                durationSection.style.display = 'none';
                document.getElementById('selectedDuration').value = '1';
            } else {
                durationSection.style.display = 'block';
                // Reset duration selection visual if needed, or keep default
            }
        }

        function selectDuration(months, element) {
            document.querySelectorAll('.duration-card').forEach(c => c.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('selectedDuration').value = months;
        }

        function prevStep() {
            document.getElementById('step1').style.display = 'block';
            document.getElementById('step2').style.display = 'none';
            document.getElementById('reg-title').textContent = "Create Account";
            document.getElementById('reg-subtitle').textContent = "Join PlayMatrix and start your journey.";
        }
    </script>
</head>

<body>
    <div class="grid-bg"></div>
    <div class="glow-spot"></div><a href="1.php" class="back-link"><i class="fa-solid fa-arrow-left"></i>Back to Home
    </a>
    <div class="login-card">
        <div class="brand-header">
            <div class="brand-icon"><i class="fa-solid fa-rocket"></i></div>
            <h2 id="reg-title">Create Account</h2>
            <p id="reg-subtitle">Join PlayMatrix and start your journey.</p>
            <?php if (isset($error))
                echo "<div class='error-msg'>$error</div>"; ?>

            <form method="POST" action="" id="regForm">
                <!-- STEP 1: ACCOUNT DETAILS -->
                <div id="step1">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="email" name="email" id="reg-email" class="form-input"
                                placeholder="Enter your email" required style="flex:1">
                            <button type="button" id="btn-verify-email" class="btn-primary"
                                style="width: auto; margin-top: 0; padding: 0 20px; font-size: 0.9rem;"
                                onclick="sendVerificationCode()">Verify</button>
                        </div>
                        <div id="verified-badge"
                            style="display:none; color: var(--primary-green); margin-top: 5px; font-size: 0.9rem;">
                            <i class="fa-solid fa-circle-check"></i> Email Verified
                        </div>
                        <div id="otp-area" style="display:none; margin-top: 10px;">
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="email-otp-input" class="form-input" placeholder="Enter code"
                                    maxlength="6" style="flex:1">
                                <button type="button" class="btn-primary"
                                    style="width: auto; margin-top: 0; padding: 0 20px; font-size: 0.9rem;"
                                    onclick="verifyCode()">Submit</button>
                            </div>
                            <small style="color: var(--text-gray);">Check your inbox for the code.</small>
                        </div>
                        <input type="hidden" name="is_verified" id="is_verified" value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" id="reg-pass" class="form-input"
                            placeholder="Create a password" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" id="reg-confirm" class="form-input"
                            placeholder="Confirm your password" required>
                    </div>
                    <button type="button" class="btn-primary" onclick="goToPlanSelection(event)">Next: Select
                        Plan</button>

                    <div class="divider">Or</div>
                    <div id="google-signup-wrapper"
                        style="width: 100%; display: flex; justify-content: center; margin-bottom: 1rem;"></div>
                    <div class="form-footer">Already have an account? <a href="login.php">Sign In</a></div>
                </div>

                <!-- STEP 2: PLAN SELECTION -->
                <div id="step2">
                    <input type="hidden" name="plan" id="selectedPlanInput" value="Free">

                    <div class="plan-grid">
                        <?php
                        $plans = json_decode(file_get_contents('plans.json'), true);
                        foreach ($plans as $index => $plan):
                            ?>
                            <div class="plan-card <?php echo ($plan['id'] == 'Free') ? 'selected' : ''; ?>"
                                onclick="selectPlan('<?php echo $plan['id']; ?>', event)">
                                <i class="fa-solid fa-circle-check selected-icon"></i>
                                <div class="plan-header">
                                    <span class="plan-badge"><?php echo $plan['badge']; ?></span>
                                    <span class="plan-name"><?php echo $plan['name']; ?></span>
                                </div>
                                <div
                                    style="font-size: 1.3rem; font-weight: 800; margin-bottom: 0.8rem; color: var(--text-white);">
                                    ₹<?php echo $plan['price']; ?><span
                                        style="font-size: 0.75rem; color: var(--text-gray); font-weight: 400;">/mo</span>
                                </div>
                                <ul class="plan-features">
                                    <?php foreach ($plan['features'] as $feature): ?>
                                        <li><?php echo $feature; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button"
                                    class="btn-primary <?php echo ($plan['id'] != 'Free') ? 'select-btn' : ''; ?>"
                                    style="margin-top: 1.5rem; padding: 0.6rem; font-size: 0.9rem; <?php echo ($plan['id'] == 'Free') ? 'pointer-events: none;' : 'background: transparent; border: 1px solid var(--primary-green); color: var(--primary-green);'; ?>">
                                    <?php echo ($plan['id'] == 'Free') ? 'Selected' : 'Select ' . ucfirst(strtolower($plan['id'])); ?>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="duration-section"
                        style="display: none; margin-top: 2rem; animation: slideUp 0.4s ease-out;">
                        <h3 style="margin-bottom: 1rem; color: var(--text-white); font-size: 1.2rem;">Select Duration
                        </h3>
                        <input type="hidden" name="duration" id="selectedDuration" value="1">
                        <div class="duration-grid"
                            style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                            <div class="duration-card" onclick="selectDuration(3, this)">
                                <div class="duration-months">3 Months</div>
                                <div class="duration-save">Save 5%</div>
                            </div>
                            <div class="duration-card" onclick="selectDuration(6, this)">
                                <div class="duration-months">6 Months</div>
                                <div class="duration-save">Save 10%</div>
                            </div>
                            <div class="duration-card" onclick="selectDuration(12, this)">
                                <div class="duration-months">12 Months</div>
                                <div class="duration-save">Save 20%</div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 2rem;">
                        <!-- Changed type to button to trigger modal logic first -->
                        <button type="button" class="btn-primary" onclick="showConfirmation()">Confirm & Sign
                            Up</button>
                        <button type="button" class="btn-google" style="margin-top: 10px; border:none;"
                            onclick="prevStep()">Back</button>
                    </div>
                </div>

                <!-- Confirmation Modal -->
                <div id="confirmModal"
                    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center;">
                    <div
                        style="background: var(--bg-card); padding: 2rem; border-radius: 16px; border: 1px solid var(--primary-green); max-width: 500px; width: 90%; position: relative; animation: slideUp 0.3s ease-out;">
                        <h3 style="margin-bottom: 1.5rem; text-align: center; color: var(--primary-green);">Confirm
                            Subscription</h3>

                        <div
                            style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span style="color: var(--text-gray);">Plan</span>
                                <span id="confirmPlanName" style="font-weight: 700;">Gold Plan</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span style="color: var(--text-gray);">Duration</span>
                                <span id="confirmDuration" style="font-weight: 700;">6 Months</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span style="color: var(--text-gray);">Monthly Price</span>
                                <span id="confirmMonthlyPrice">₹1500</span>
                            </div>
                            <div style="border-top: 1px solid rgba(255,255,255,0.1); margin: 10px 0;"></div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: var(--text-gray);">Subtotal</span>
                                <span id="confirmSubtotal">₹9000</span>
                            </div>
                            <div
                                style="display: flex; justify-content: space-between; margin-bottom: 5px; color: var(--primary-green);">
                                <span>Discount (<span id="confirmDiscountPercent">10%</span>)</span>
                                <span id="confirmDiscountAmount">-₹900</span>
                            </div>
                            <div style="border-top: 1px solid rgba(255,255,255,0.1); margin: 10px 0;"></div>
                            <div
                                style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 800;">
                                <span>Total</span>
                                <span id="confirmTotal" style="color: var(--primary-green);">₹8100</span>
                            </div>
                        </div>

                        <button type="button" class="btn-primary" onclick="startPayment()">Proceed to Pay & Sign
                            Up</button>
                        <button type="button" class="btn-google" style="margin-top: 10px; border:none;"
                            onclick="closeConfirmation()">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Pass PHP plan data to JS
        const plansData = <?php echo json_encode(json_decode(file_get_contents('plans.json'), true)); ?>;

        function showConfirmation() {
            const planId = document.getElementById('selectedPlanInput').value;
            const duration = parseInt(document.getElementById('selectedDuration').value);

            // Should submit directly if Free plan
            if (planId === 'Free') {
                document.getElementById('regForm').submit();
                return;
            }

            const plan = plansData.find(p => p.id === planId);
            if (!plan) return;

            const monthlyPrice = parseInt(plan.price);
            const subtotal = monthlyPrice * duration;

            let discountPercent = 0;
            if (duration === 3) discountPercent = 5;
            else if (duration === 6) discountPercent = 10;
            else if (duration === 12) discountPercent = 20;

            const discountAmount = (subtotal * discountPercent) / 100;
            const total = subtotal - discountAmount;

            // Populate Modal
            document.getElementById('confirmPlanName').textContent = plan.name;
            document.getElementById('confirmDuration').textContent = duration + " Months";
            document.getElementById('confirmMonthlyPrice').textContent = "₹" + monthlyPrice;
            document.getElementById('confirmSubtotal').textContent = "₹" + subtotal;
            document.getElementById('confirmDiscountPercent').textContent = discountPercent + "%";
            document.getElementById('confirmDiscountAmount').textContent = "-₹" + discountAmount;
            document.getElementById('confirmTotal').textContent = "₹" + total;

            // Show Modal
            const modal = document.getElementById('confirmModal');
            modal.style.display = 'flex';
        }

        function closeConfirmation() {
            document.getElementById('confirmModal').style.display = 'none';
        }

        function startPayment() {
            const planId = document.getElementById('selectedPlanInput').value;
            const duration = parseInt(document.getElementById('selectedDuration').value);
            const plan = plansData.find(p => p.id === planId);

            if (!plan) return;

            const monthlyPrice = parseInt(plan.price);
            const subtotal = monthlyPrice * duration;

            let discountPercent = 0;
            if (duration === 3) discountPercent = 5;
            else if (duration === 6) discountPercent = 10;
            else if (duration === 12) discountPercent = 20;

            const discountAmount = (subtotal * discountPercent) / 100;
            const total = subtotal - discountAmount;

            // Amount in paise
            const amountPaise = Math.round(total * 100);
            const userEmail = document.getElementById('reg-email').value;

            var options = {
                "key": "rzp_test_S6NQuN1S6Lvj2T",
                "amount": amountPaise,
                "currency": "INR",
                "name": "PlayMatrix",
                "description": plan.name + " (" + duration + " Months)",
                "image": "https://example.com/your_logo.png",
                "handler": function (response) {
                    // Payment successful
                    // Create hidden input for payment ID
                    const form = document.getElementById('regForm');
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'razorpay_payment_id';
                    hiddenInput.value = response.razorpay_payment_id;
                    form.appendChild(hiddenInput);

                    form.submit();
                },
                "prefill": {
                    "email": userEmail
                },
                "theme": {
                    "color": "#39ff14"
                }
            };
            var rzp1 = new Razorpay(options);
            rzp1.on('payment.failed', function (response) {
                alert("Payment Failed: " + response.error.description);
            });
            rzp1.open();
        }

        function sendVerificationCode() {
            const email = document.getElementById('reg-email').value;
            const btn = document.getElementById('btn-verify-email');

            if (!email || !email.includes('@')) {
                alert("Please enter a valid email address.");
                return;
            }

            btn.textContent = "Sending...";
            btn.disabled = true;

            fetch('send_otp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('otp-area').style.display = 'block';
                        btn.textContent = "Sent";
                    } else {
                        alert(data.message || "Failed to send OTP");
                        btn.textContent = "Verify";
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Error sending OTP");
                    btn.textContent = "Verify";
                    btn.disabled = false;
                });
        }

        function verifyCode() {
            const email = document.getElementById('reg-email').value;
            const otp = document.getElementById('email-otp-input').value;

            fetch('verify_otp_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, otp: otp })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('otp-area').style.display = 'none';
                        document.getElementById('verified-badge').style.display = 'block';
                        document.getElementById('btn-verify-email').style.display = 'none';
                        document.getElementById('reg-email').readOnly = true;
                        document.getElementById('is_verified').value = '1';
                    } else {
                        alert(data.message || "Invalid OTP");
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Error verifying OTP");
                });
        }
    </script>
</body>

</html>