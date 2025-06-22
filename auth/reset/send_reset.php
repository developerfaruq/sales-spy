
<?php
require '../../config/db.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if the email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate reset token and expiry
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save token and expiry
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $stmt->execute([$token, $expiry, $email]);

        $resetLink = "http://localhost/sales/auth/reset/index.php?token=$token";

        // Send email with Mailtrap
        $mail = new PHPMailer(true);

        try {
            //SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.ethereal.email';
            $mail->SMTPAuth = true;
            $mail->Username = 'dorcas.thiel@ethereal.email'; // Replace with your Mailtrap username
            $mail->Password = 'Ff733PqPp1PUSDvDup'; // Replace with your Mailtrap password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('no-reply@sales-spy.com', 'Sales-Spy');
            $mail->addAddress($email, $user['full_name']);
            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Sales-Spy Password';
            $mail->Body = "
                <div style='font-family: Poppins, sans-serif; padding: 20px; background: #f9f9f9; color: #333;'>
                    <h2 style='color: #1E3A8A;'>Hello {$user['full_name']},</h2>
                    <p>You recently requested to reset your password for your Sales-Spy account.</p>
                    <p>
                        <a href='$resetLink' style='background: #1E3A8A; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Click here to reset your password</a>
                    </p>
                    <p>This link will expire in 1 hour. If you did not request a password reset, you can safely ignore this email.</p>
                    <br>
                    <p style='font-size: 14px; color: #888;'>– The Sales-Spy Team</p>
                </div>
            ";

            $mail->send();
            echo "✅ Reset link sent successfully. Please check your email.";
        } catch (Exception $e) {
            echo "❌ Mail Error: {$mail->ErrorInfo}";
        }

    } else {
        echo "❌ No user found with that email.";
    }
}
?>
