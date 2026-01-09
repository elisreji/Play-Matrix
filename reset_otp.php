<?php
session_start();
$email = $_SESSION['reset_email'] ?? '';

if (!$email) {
    header("Location: forgot-password.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredOtp = $_POST['otp'];
    $file = 'users.json';
    $users = json_decode(file_get_contents($file), true);

    $found = false;
    foreach ($users as $user) {
        if (strtolower(trim($user['email'])) === $email) {
            if (isset($user['reset_otp']) && $user['reset_otp'] == $enteredOtp) {
                $found = true;
                break;
            }
        }
    }

    if ($found) {
        $_SESSION['reset_otp_verified'] = true;
        header("Location: reset_password.php");
        exit;
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - PlayMatrix</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #050505;
            --primary-green: #39ff14;
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-gray: #a1a1a1;
        }

        body {
            background: var(--bg-dark);
            color: white;
            font-family: 'Outfit', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .grid-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(rgba(57, 255, 20, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(57, 255, 20, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: -1;
        }

        .otp-card {
            background: linear-gradient(145deg, #1a1a1a, #0d0d0d);
            border: 1px solid var(--glass-border);
            padding: 3rem;
            border-radius: 24px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-box {
            width: 70px;
            height: 70px;
            background: rgba(57, 255, 20, 0.1);
            border: 1px solid var(--primary-green);
            color: var(--primary-green);
            font-size: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            margin: 0 auto 2rem;
            box-shadow: 0 0 20px rgba(57, 255, 20, 0.2);
        }

        h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 800;
        }

        p {
            color: var(--text-gray);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .otp-input-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .otp-field {
            width: 60px;
            height: 70px;
            background: #0a0a0a;
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-green);
            transition: 0.3s;
        }

        .otp-field:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 15px rgba(57, 255, 20, 0.3);
            background: #111;
        }

        .btn-verify {
            width: 100%;
            padding: 1rem;
            background: var(--primary-green);
            color: black;
            border: none;
            border-radius: 12px;
            font-weight: 800;
            font-size: 1.1rem;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-verify:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(57, 255, 20, 0.3);
        }

        .error-msg {
            color: #ff4444;
            background: rgba(255, 68, 68, 0.1);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="grid-bg"></div>
    <div class="otp-card">
        <div class="icon-box"><i class="fa-solid fa-key"></i></div>
        <h2>Reset OTP</h2>
        <p>Enter the 4-digit code sent to<br><strong>
                <?php echo htmlspecialchars($email); ?>
            </strong></p>

        <?php if ($error): ?>
            <div class="error-msg">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="otpForm">
            <input type="hidden" name="otp" id="finalOtp">
            <div class="otp-input-group">
                <input type="text" class="otp-field" maxlength="1" pattern="\d*" inputmode="numeric" required>
                <input type="text" class="otp-field" maxlength="1" pattern="\d*" inputmode="numeric" required>
                <input type="text" class="otp-field" maxlength="1" pattern="\d*" inputmode="numeric" required>
                <input type="text" class="otp-field" maxlength="1" pattern="\d*" inputmode="numeric" required>
            </div>
            <button type="submit" class="btn-verify">Verify Code</button>
        </form>
    </div>

    <script>
        const fields = document.querySelectorAll('.otp-field');
        const form = document.getElementById('otpForm');
        const finalInput = document.getElementById('finalOtp');

        fields.forEach((field, index) => {
            field.addEventListener('input', (e) => {
                if (e.target.value.length > 1) e.target.value = e.target.value.slice(0, 1);
                if (e.target.value && index < fields.length - 1) fields[index + 1].focus();
            });

            field.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !field.value && index > 0) fields[index - 1].focus();
            });
        });

        form.addEventListener('submit', (e) => {
            let otp = "";
            fields.forEach(f => otp += f.value);
            if (otp.length < 4) {
                e.preventDefault();
                alert("Please enter the full 4-digit code.");
            } else {
                finalInput.value = otp;
            }
        });
    </script>
</body>

</html>