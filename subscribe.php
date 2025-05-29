<?php
require 'mailer/src/PHPMailer.php';
require 'mailer/src/SMTP.php';
require 'mailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Content-Type: application/json");

// Load SMTP credentials from pass.env
function loadEnv($path = 'pass.env') {
  if (!file_exists($path)) return [];
  return parse_ini_file($path);
}
$env = loadEnv();
$smtpUser = $env['SMTP_USER'];
$smtpPass = $env['SMTP_PASS'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"]);

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "❌ Invalid email."]);
    exit;
  }

  $file = "subscribers.json";
  $subscribers = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

  if (!in_array($email, $subscribers)) {
    $subscribers[] = $email;
    file_put_contents($file, json_encode($subscribers, JSON_PRETTY_PRINT));

    // Send welcome email
    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->CharSet = 'UTF-8'; // ✅ Ensure emoji and symbols work
      $mail->Host = 'smtp-relay.brevo.com';
      $mail->SMTPAuth = true;
      $mail->Username = $smtpUser;
      $mail->Password = $smtpPass;
      $mail->SMTPSecure = 'tls';
      $mail->Port = 587;

      $mail->setFrom('cpkhanal0@gmail.com', 'NEPSE IPO Alerts');
      $mail->addAddress($email);
      $mail->isHTML(true);
      $mail->Subject = "🎉 Welcome to NEPSE IPO Alerts!";

$mail->Body = '
  <div style="max-width: 600px; margin: auto; padding: 24px; background-color: #f9f9f9; border-radius: 10px; font-family: Arial, sans-serif; color: #333;">
    <h2 style="color: #1a73e8; text-align: center;">🎉 You’re now subscribed!</h2>
    <p style="font-size: 16px; text-align: center;">Thank you for joining <strong>NEPSE IPO Alerts</strong>.</p>

    <div style="margin: 30px 0; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
      <p style="font-size: 15px;">We’ll notify you whenever new IPOs are available to apply for in Nepal.</p>
      <p style="font-size: 15px;">Make sure you’re ready to apply using your MeroShare account.</p>
    </div>

    <div style="text-align: center; margin-top: 20px;">
      <a href="https://meroshare.cdsc.com.np" style="display: inline-block; padding: 12px 24px; background-color: #1a73e8; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold;">🔗 Apply via MeroShare</a>
    </div>

<p style="font-size: 12px; color: #888; text-align: center; margin-top: 40px;">
  You are receiving this because you subscribed to NEPSE IPO alerts.<br>
  If you wish to unsubscribe, please <a href="https://ipoalert.42web.io/unsubscribe.php" style="color:#1a73e8;">click here</a> and enter your email address.
</p>

  </div>';


      $mail->send();
    } catch (Exception $e) {
      echo json_encode(["success" => true, "message" => "✅ Subscribed, but email failed to send."]);
      exit;
    }

    echo json_encode(["success" => true, "message" => "✅ You’ve been subscribed!"]);
  } else {
    echo json_encode(["success" => false, "message" => "⚠️ You’re already subscribed."]);
  }
} else {
  echo json_encode(["success" => false, "message" => "❌ Invalid request."]);
}
?>
