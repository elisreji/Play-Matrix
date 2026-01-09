s
<!DOCTYPE html>
<html>

<head>
    <title>Email Test - PlayMatrix</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #050505;
            color: #fff;
            padding: 40px;
            text-align: center;
        }

        .result {
            background: #1a1a1a;
            border: 1px solid #39ff14;
            padding: 20px;
            border-radius: 10px;
            max-width: 600px;
            margin: 20px auto;
        }

        .success {
            color: #39ff14;
        }

        .error {
            color: #ff3939;
        }
    </style>
</head>

<body>
    <h1>Email System Test</h1>

    <?php
    require 'PHPMailer/Exception.php';
    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/SMTP.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'elisreji2028@mca.ajce.in';
        $mail->Password = 'dtedqdekswzflopc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('elisreji2028@mca.ajce.in', 'PlayMatrix');
        $mail->addAddress('elisreji2028@mca.ajce.in');

        $mail->isHTML(true);
        $mail->Subject = 'Test Email from PlayMatrix';
        $mail->Body = '<h1 style="color: #39ff14;">Success!</h1><p>Your email system is working perfectly!</p>';

        $mail->send();
        echo '<div class="result success">✓ Email sent successfully! Check your inbox at elisreji2028@mca.ajce.in</div>';
    } catch (Exception $e) {
        echo '<div class="result error">✗ Error: ' . $mail->ErrorInfo . '</div>';
    }
    ?>

    <p><a href="forgot-password.php" style="color: #39ff14;">← Back to Forgot Password</a></p>
</body>

</html>