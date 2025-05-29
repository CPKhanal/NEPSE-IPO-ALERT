<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = "❌ Invalid email address.";
  } else {
    $file = "subscribers.json";
    $subscribers = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

    if (in_array($email, $subscribers)) {
      $subscribers = array_values(array_diff($subscribers, [$email]));
      file_put_contents($file, json_encode($subscribers, JSON_PRETTY_PRINT));
      $message = "✅ You have been unsubscribed successfully.";
    } else {
      $message = "⚠️ This email is not subscribed.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="robots" content="noindex, nofollow" />
  <meta name="description" content="Unsubscribe from NEPSE IPO Alerts">
  <title>Unsubscribe | NEPSE IPO Alert</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>
<body class="bg-[#f9f7f4] text-[#2f2f2f] min-h-screen flex flex-col overflow-x-hidden">

  <!-- Main Content -->
  <main class="flex-grow max-w-2xl mx-auto w-full px-6 py-16">
    <section class="">
      <h2 class="text-2xl font-semibold mb-6 text-center">📭 Unsubscribe from Alerts</h2>
      <p class="mb-4 text-sm text-gray-600 text-center">Enter your email below to stop receiving NEPSE IPO alerts.</p>

      <form method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow-sm max-w-xl mx-auto">
        <div>
          <label class="block font-medium mb-1" for="email">Email Address</label>
          <input 
            type="email" 
            name="email" 
            id="email"
            class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-red-500 transition" 
            placeholder="your@email.com"
            required
          >
        </div>

        <button 
          type="submit" 
          class="bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700 transition font-medium shadow"
        >
          Unsubscribe
        </button>

        <?php if ($message): ?>
          <div id="form-message" class="text-sm mt-3 text-center font-medium <?= strpos($message, '✅') !== false ? 'text-green-600' : 'text-red-600' ?>">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>
      </form>

      <div class="text-center mt-6">
        <a href="index.php" class="text-blue-600 text-sm hover:underline">← Back to Home</a>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="text-center py-6 text-sm text-gray-500 bg-transparent">
    &copy; 2025 NEPSE Alert. All rights reserved.
  </footer>
</body>
</html>
