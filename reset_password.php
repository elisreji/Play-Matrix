<?php
session_start();
$email = $_SESSION['reset_email'] ?? '';
$verified = $_SESSION['reset_otp_verified'] ?? false;

if (!$email || !$verified) {
    header("Location: forgot-password.php");
    exit;
}

$error = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $file = 'users.json';
        $users = json_decode(file_get_contents($file), true);

        foreach ($users as &$user) {
            if (strtolower(trim($user['email'])) === strtolower(trim($email))) {
                $user['password'] = password_hash($password, PASSWORD_DEFAULT);
                unset($user['reset_otp']);
                break;
            }
        }

        file_put_contents($file, json_encode($users));

        // Clear session
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_otp_verified']);

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - PlayMatrix</title>
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
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-gray);
            font-size: 0.9rem;
            font-weight: 500;
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
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .btn-primary:hover {
            background-color: var(--primary-green-dim);
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(57, 255, 20, 0.4);
        }

        .error-msg {
            color: #ff4444;
            background: rgba(255, 68, 68, 0.1);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
        }

        .success-msg {
            color: var(--primary-green);
            background: rgba(57, 255, 20, 0.1);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 1rem;
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
</head>

<body>

    <div class="grid-bg"></div>
    <div class="glow-spot"></div>

    <div class="login-card">
        <?php if ($success): ?>
            <div class="brand-header">
                <div class="brand-icon"><i class="fa-solid fa-check"></i></div>
                <h2>Success!</h2>
                <div class="success-msg">Your password has been updated.</div>
                <a href="login.php" class="btn-primary">Sign In Now</a>
            </div>
        <?php else: ?>
            <div class="brand-header">
                <div class="brand-icon"><i class="fa-solid fa-key"></i></div>
                <h2>New Password</h2>
                <p>Create a secure password for your account.</p>
            </div>

            <?php if ($error): ?>
                <div class="error-msg">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Create new password" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-input" placeholder="Confirm new password"
                        required>
                </div>

                <button type="submit" class="btn-primary">Update Password</button>
            </form>
        <?php endif; ?>
    </div>

</body>

</html>