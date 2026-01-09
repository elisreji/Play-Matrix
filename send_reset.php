<?php
session_start();
// Check if PHPMailer is available
$phpmailerExists = file_exists(__DIR__ . '/PHPMailer/PHPMailer.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = strtolower(trim($_POST['email']));
    $otp = rand(1000, 9999);

    // Save OTP to users.json
    $file = 'users.json';
    $users = json_decode(file_get_contents($file), true);
    $userFound = false;

    foreach ($users as &$user) {
        if (strtolower(trim($user['email'])) === $email) {
            $user['reset_otp'] = $otp;
            $userFound = true;
            break;
        }
    }

    if (!$userFound) {
        header("Location: forgot-password.php?error=notfound");
        exit;
    }

    file_put_contents($file, json_encode($users));
    $_SESSION['reset_email'] = $email;

    if ($phpmailerExists) {
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
            $mail->Subject = 'Password Reset OTP - PlayMatrix';
            $mail->Body = '
            <html>
            <body style="font-family: Arial, sans-serif; background-color: #050505; color: #ffffff; padding: 20px;">
                <div style="max-width: 600px; margin: 0 auto; background: linear-gradient(145deg, #1a1a1a, #0d0d0d); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; padding: 40px; text-align: center;">
                    <div style="background: #39ff14; width: 60px; height: 60px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px; margin-left: auto; margin-right: auto;">
                        <span style="font-size: 30px;">🔐</span>
                    </div>
                    <h1 style="color: #39ff14; margin-bottom: 20px;">Password Reset OTP</h1>
                    <p style="color: #a1a1a1; font-size: 16px; line-height: 1.6;">
                        Your verification code to reset your password is:
                    </p>
                    <div style="background: rgba(57, 255, 20, 0.1); border: 2px solid #39ff14; color: #39ff14; font-size: 42px; font-weight: 900; letter-spacing: 15px; padding: 20px; border-radius: 12px; margin: 30px 0; display: inline-block;">
                        ' . $otp . '
                    </div>
                    <p style="color: #a1a1a1; font-size: 14px;">This code will expire in 10 minutes.</p>
                </div>
            </body>
            </html>';

            $mail->send();
            header("Location: reset_otp.php?status=sent");
            exit();
        } catch (Exception $e) {
            $phpmailerExists = false;
        }
    }

    if (!$phpmailerExists) {
        $emailContent = "================================\nTO: $email\nSubject: Password Reset OTP\nOTP: $otp\n================================\n\n";
        file_put_contents('email_outbox.txt', $emailContent, FILE_APPEND);
        header("Location: reset_otp.php?status=sent&method=file");
        exit();
    }
}
?>