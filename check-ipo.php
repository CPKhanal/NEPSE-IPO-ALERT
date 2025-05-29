<?php
require 'mailer/src/PHPMailer.php';
require 'mailer/src/SMTP.php';
require 'mailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load environment variables from pass.env
function loadEnv($path = 'pass.env') {
  if (!file_exists($path)) return [];
  return parse_ini_file($path);
}

$env = loadEnv();
$smtpUser = $env['SMTP_USER'];
$smtpPass = $env['SMTP_PASS'];

// Fetch IPO data
$apiUrl = "https://nepse-ipo-alert.onrender.com/api/ipo-listings";
$newIpos = json_decode(file_get_contents($apiUrl), true);
$lastIpos = json_decode(file_get_contents("data.json"), true);

// Exit if no changes
if ($newIpos === $lastIpos) {
  exit;
}

// Build HTML Email Body
$html = '
<div style="font-family: Arial, sans-serif; padding: 24px;">
  <h2 style="color: #1a73e8; text-align: center;">📢 New NEPSE IPOs Announced!</h2>
  <p style="text-align:center; color:#444;">Here are the latest IPOs you can apply for:</p>
  <div style="margin-top: 24px;">';

foreach ($newIpos as $ipo) {
  $isReserved = strpos($ipo['Company Name'], 'RESERVED') !== false;
  $tagColor = $isReserved ? '#d97706' : '#7c3aed';
  $tagBg = $isReserved ? '#fef3c7' : '#ede9fe';

  $html .= '
  <div style="
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  ">
    <h3 style="font-size: 16px; color: #1e3a8a; margin-bottom: 8px;">' . htmlspecialchars($ipo['Company Name']) . '</h3>
    
    <span style="
      display: inline-block;
      background: ' . $tagBg . ';
      color: ' . $tagColor . ';
      font-size: 12px;
      padding: 4px 10px;
      border-radius: 16px;
      font-weight: 500;
      margin-bottom: 12px;
    ">' . ($isReserved ? 'Reserved' : 'General Public') . '</span>

    <ul style="list-style: none; padding: 0; font-size: 14px; color: #374151;">
      <li style="margin: 6px 0;"><strong>📅 Opens:</strong> ' . $ipo['Open-Date'] . '</li>
      <li style="margin: 6px 0;"><strong>⏳ Closes:</strong> ' . $ipo['Close-Date'] . '</li>
      <li style="margin: 6px 0;"><strong>📦 Units:</strong> ' . $ipo['Issued Unit'] . '</li>
      <li style="margin: 6px 0;"><strong>🏢 Issue Manager:</strong> ' . htmlspecialchars($ipo['Issue Manager']) . '</li>
    </ul>
  </div>';
}

$html .= '
  </div>
  <div style="text-align:center; margin-top: 30px;">
    <a href="https://meroshare.cdsc.com.np" style="background-color: #1a73e8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">Apply via MeroShare</a>
  </div>
  <p style="font-size: 12px; color: #777; text-align: center; margin-top: 40px;">
    You are receiving this alert from <strong>NEPSE IPO Alert</strong>.<br>
    To unsubscribe, <a href="https://ipoalert.42web.io/unsubscribe.php" style="color: #1a73e8;">click here</a> or contact <a href="mailto:cpkhanal0@gmail.com">cpkhanal0@gmail.com</a>.
  </p>
</div>';


// Load subscribers from subscribers.json
$emails = json_decode(file_get_contents("subscribers.json"), true);

// Send emails
foreach ($emails as $email) {
  $mail = new PHPMailer(true);
  $mail->CharSet = 'UTF-8';

  try {
    $mail->isSMTP();
    $mail->Host = 'smtp-relay.brevo.com';
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = $smtpPass;
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom("cpkhanal0@gmail.com", 'NEPSE Alerts');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = "New NEPSE IPO Alert";
    $mail->Body = $html;

    $mail->send();
  } catch (Exception $e) {
    // Suppressed in production to avoid leaking errors
  }
}

// Update the IPO data
file_put_contents("data.json", json_encode($newIpos, JSON_PRETTY_PRINT));
