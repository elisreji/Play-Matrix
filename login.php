<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];
    $userFound = null;

    // 1. Try Database First
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM USERS WHERE email = ?");
        $stmt->execute([$email]);
        $userFound = $stmt->fetch();
        if ($userFound) {
            // Map DB fields to match JSON structure for consistency
            $userFound['password'] = $userFound['password_hash'];
        }
    }

    // 2. Fallback to JSON if not found in DB
    if (!$userFound) {
        $file = 'users.json';
        $users = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        foreach ($users as $user) {
            if (strtolower(trim($user['email'])) === $email) {
                $userFound = $user;
                break;
            }
        }
    }

    if ($userFound && password_verify($password, $userFound['password'])) {
        if (isset($userFound['is_verified']) && (int) $userFound['is_verified'] === 0) {
            $error = "Please verify your email address before logging in.";
        } elseif (isset($userFound['is_blocked']) && (int) $userFound['is_blocked'] === 1) {
            $error = "Your account has been suspended. Please contact administration.";
        } else {
            $_SESSION['user'] = $userFound['email'];
            $adminEmails = ['elisreji2028@mca.ajce.in', 'junaelsamathew2028@mca.ajce.in'];
            if (in_array($userFound['email'], $adminEmails) || (isset($userFound['role']) && $userFound['role'] === 'Admin')) {
                header("Location: admin.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PlayMatrix</title>
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
            overflow: hidden;
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
            padding: 3rem 2.5rem;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s ease-out forwards;
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
            /* Left align form group */
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-gray);
            font-size: 0.9rem;
            font-weight: 500;
            text-align: left;
            /* Left align label */
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
            box-shadow: 0 0 20px rgba(57, 255, 20, 0.4);
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
    </style>
    <!-- Google Identity Services Script -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        function handleCredentialResponse(response) {
            // Decode the JWT token to get user info
            const responsePayload = decodeJwtResponse(response.credential);
            // In a real app, you would send the response.credential (ID token) to your backend
            alert("Login Successful! Welcome, " + responsePayload.name);
            window.location.href = 'dashboard.php'; // Redirect to home/dashboard
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

            google.accounts.id.renderButton(
                document.getElementById("google-btn-wrapper"),
                { theme: "filled_black", size: "large", width: "100%", shape: "rectangular", logo_alignment: "center", text: "continue_with" }
            );
        }
    </script>
</head>

<body>

    <div class="grid-bg"></div>
    <div class="glow-spot"></div>

    <a href="1.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Home
    </a>

    <div class="login-card">
        <div class="brand-header">
            <div class="brand-icon">
                <i class="fa-solid fa-user"></i>
            </div>
            <h2>Welcome Back</h2>
            <p>Sign in to your account</p>
        </div>

        <?php if (isset($error))
            echo "<div style='color: #ff4444; background: rgba(255, 68, 68, 0.1); padding: 10px; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-size: 0.9rem;'>$error</div>"; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div
                style='color: #39ff14; background: rgba(57, 255, 20, 0.1); padding: 10px; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-size: 0.9rem;'>
                Registration successful! Please check your email to verify your account.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['verified'])): ?>
            <div
                style='color: #39ff14; background: rgba(57, 255, 20, 0.1); padding: 10px; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-size: 0.9rem;'>
                Email verified successfully! You can now log in.
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
                <div style="text-align: right; margin-top: 0.5rem;">
                    <a href="forgot-password.php"
                        style="color: var(--text-gray); font-size: 0.85rem; text-decoration: none; transition: 0.3s;"
                        onmouseover="this.style.color='var(--primary-green)'"
                        onmouseout="this.style.color='var(--text-gray)'">Forgot Password?</a>
                </div>
            </div>

            <button type="submit" class="btn-primary">Sign In</button>
        </form>

        <div class="divider">Or</div>

        <!-- Container for the Google Sign-In Button -->
        <div id="google-btn-wrapper" style="width: 100%; display: flex; justify-content: center; margin-bottom: 1rem;">
        </div>

        <div class="form-footer">
            Don't have an account? <a href="register.php">Get Started</a>
        </div>
    </div>

</body>

</html>