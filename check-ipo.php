<?php
// Prevent duplicate runs if already called
if (defined('IPO_CHECK_RAN')) return;
define('IPO_CHECK_RAN', true);

require 'mailer/src/PHPMailer.php';
require 'mailer/src/SMTP.php';
require 'mailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load environment variables
function loadEnv($path = 'pass.env') {
  return file_exists($path) ? parse_ini_file($path) : [];
}

// Logger (shows only if &debug=1 in URL)
function logMessage($msg, $display = false) {
  $line = "[" . date("Y-m-d H:i:s") . "] $msg\n";
  file_put_contents("log.txt", $line, FILE_APPEND);
  if ($display) echo htmlspecialchars($line) . "<br>";
}

// Detect if we should show output (for debug)
$showOutput = isset($_GET['debug']) && $_GET['debug'] == '1';

// Config
$env = loadEnv();
$smtpUser = $env['SMTP_USER'] ?? '';
$smtpPass = $env['SMTP_PASS'] ?? '';
$apiUrl   = "https://nepse-ipo-alert.onrender.com/api/ipo-listings";
$dataFile = "data.json";
$subFile  = "subscribers.json";

// Use cURL to fetch IPO data reliably
function fetchJson($url) {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_USERAGENT => "Mozilla/5.0"
  ]);
  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return $httpCode === 200 ? json_decode($response, true) : null;
}

// Step 1: Fetch IPOs
$newIpos = fetchJson($apiUrl);
$lastIpos = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

if (!$newIpos || !is_array($newIpos)) {
  logMessage("❌ Failed to fetch new IPO data.", $showOutput);
  return;
}

if ($newIpos === $lastIpos) {
  logMessage("✅ No new IPOs.", $showOutput);
  return;
}

// Step 2: Prepare HTML Email
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
  <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px; margin-bottom:16px;">
    <h3 style="font-size:16px; color:#1e3a8a; margin-bottom:8px;">' . htmlspecialchars($ipo['Company Name']) . '</h3>
    <span style="display:inline-block; background:' . $tagBg . '; color:' . $tagColor . '; font-size:12px; padding:4px 10px; border-radius:16px; font-weight:500; margin-bottom:12px;">'
    . ($isReserved ? 'Reserved' : 'General Public') . '</span>
    <ul style="list-style:none; padding:0; font-size:14px; color:#374151;">
      <li><strong>📅 Opens:</strong> ' . $ipo['Open-Date'] . '</li>
      <li><strong>⏳ Closes:</strong> ' . $ipo['Close-Date'] . '</li>
      <li><strong>📦 Units:</strong> ' . $ipo['Issued Unit'] . '</li>
      <li><strong>🏢 Issue Manager:</strong> ' . htmlspecialchars($ipo['Issue Manager']) . '</li>
    </ul>
  </div>';
}

$html .= '
  </div>
  <div style="text-align:center; margin-top: 30px;">
    <a href="https://meroshare.cdsc.com.np" style="background-color:#1a73e8; color:white; padding:12px 24px; text-decoration:none; border-radius:6px; font-weight:bold;">Apply via MeroShare</a>
  </div>
  <p style="font-size:12px; color:#777; text-align:center; margin-top:40px;">
    You are receiving this alert from <strong>NEPSE IPO Alert</strong>.<br>
    To unsubscribe, <a href="https://ipoalert.42web.io/unsubscribe.php" style="color:#1a73e8;">click here</a> or contact <a href="mailto:cpkhanal0@gmail.com">cpkhanal0@gmail.com</a>.
  </p>
</div>';

// Step 3: Send Emails
$emails = file_exists($subFile) ? json_decode(file_get_contents($subFile), true) : [];

if (empty($emails)) {
  logMessage("⚠️ No subscribers found.", $showOutput);
  return;
}

foreach ($emails as $email) {
  try {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = 'smtp-relay.brevo.com';
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = $smtpPass;
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('cpkhanal0@gmail.com', 'NEPSE Alerts');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = "New NEPSE IPO Alert";
    $mail->Body = $html;

    $mail->send();
    logMessage("📧 Email sent to $email", $showOutput);
  } catch (Exception $e) {
    logMessage("❌ Failed to send to $email: " . $e->getMessage(), $showOutput);
  }
}

// Step 4: Save New Data
file_put_contents($dataFile, json_encode($newIpos, JSON_PRETTY_PRINT));
logMessage("✅ IPO data updated.", $showOutput);
?>
