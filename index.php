<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="robots" content="noindex, nofollow" />
  <meta name="description" content="Get NEPSE IPO alerts instantly via email.">
  <title>NEPSE IPO Alert</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="icon" href="/favicon.ico" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>
<body class="bg-[#f9f7f4] text-[#2f2f2f] min-h-screen flex flex-col overflow-x-hidden">

  <!-- Header -->
  <header class="bg-white shadow-md sticky top-0 z-10">
    <div class="max-w-5xl mx-auto px-4 py-4 flex flex-wrap justify-between items-center gap-4 sm:flex-nowrap">
      <h1 class="text-xl font-bold text-center sm:text-left"><a href="index.php">NEPSE IPO Alert</a></h1>
      <nav class="space-x-4 text-sm border-b border-gray-200 pb-1">
        <button onclick="switchTab('ipos', this)" class="tab-btn font-medium px-2 py-1 rounded transition focus:outline-none text-blue-600 border-b-2 border-blue-600 font-semibold">Active IPOs</button>
        <button onclick="switchTab('notifications', this)" class="tab-btn font-medium px-2 py-1 rounded transition focus:outline-none">Notification Preferences</button>
      </nav>
    </div>
  </header>

  <!-- Main -->
  <main class="flex-grow max-w-4xl mx-auto w-full px-6 py-10 space-y-10">

    <!-- IPO Cards -->
    <section id="tab-ipos">
      <h2 class="text-2xl font-semibold mb-4">📋 Current Active IPOs</h2>
      <div id="ipo-card-wrapper" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div id="loading-text" class="col-span-full text-center py-4">
          <svg class="animate-spin h-5 w-5 text-blue-600 mx-auto mb-2" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
          </svg>
          <p class="text-sm text-gray-500">Loading IPOs...</p>
        </div>
      </div>
    </section>

    <!-- Notification Form -->
    <section id="tab-notifications" class="hidden">
      <h2 class="text-2xl font-semibold mb-6">🔔 Subscribe to Alerts</h2>
      <p class="mb-4 text-sm text-gray-600">You can subscribe using <strong>Email</strong>.</p>

      <form id="subscribe-form" class="space-y-6 bg-white p-6 rounded-lg shadow-sm">
        <div>
          <label class="block font-medium mb-1" for="email">Email Address</label>
          <input 
            type="email" 
            name="email" 
            id="email"
            class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-blue-500 transition" 
            placeholder="your@email.com"
            required
          >
        </div>

        <button 
          type="submit" 
          class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition font-medium shadow"
        >
          Subscribe
        </button>

        <div id="form-message" class="text-sm mt-3"></div>
      </form>
    </section>
  </main>

  <!-- Footer -->
<footer class="text-center py-6 text-sm text-gray-500 bg-transparent space-y-2">
  <p>&copy; 2025 NEPSE Alert. All rights reserved.</p>
  <p>
    <a href="unsubscribe.php" class="text-blue-600 hover:underline">
      Unsubscribe from email alerts
    </a>
  </p>
</footer>


  <!-- Script -->
  <script>
    function switchTab(tab, el) {
      document.getElementById('tab-ipos').classList.add('hidden');
      document.getElementById('tab-notifications').classList.add('hidden');
      document.getElementById(`tab-${tab}`).classList.remove('hidden');

      document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600', 'font-semibold');
      });
      el.classList.add('text-blue-600', 'border-b-2', 'border-blue-600', 'font-semibold');
    }

    async function loadIPOs() {
      try {
        const res = await fetch('https://nepse-ipo-alert.onrender.com/api/ipo-listings');
        const data = await res.json();
        const wrapper = document.getElementById('ipo-card-wrapper');
        document.getElementById('loading-text').classList.add('hidden');
        wrapper.innerHTML = '';

        data.forEach((ipo) => {
          const closeDate = new Date(ipo['Close-Date']);
          const today = new Date();
          const daysLeft = Math.ceil((closeDate - today) / (1000 * 3600 * 24));

          const statusBadge = `
            <div class="flex flex-col items-end shrink-0 text-right whitespace-nowrap">
              <span class="text-green-700 bg-green-100 px-2 py-0.5 rounded-full text-xs font-semibold">Open</span>
              <span class="text-xs text-gray-600 mt-1">${daysLeft} day${daysLeft !== 1 ? 's' : ''} left</span>
            </div>`;

          const tagType = ipo['Company Name'].includes("RESERVED")
            ? `<span class="text-orange-700 bg-orange-100 px-2 py-0.5 rounded-full text-xs font-medium">Reserved</span>`
            : `<span class="text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full text-xs font-medium">General Public</span>`;

          const card = `
            <div class="bg-white rounded-xl border shadow hover:shadow-md transition p-5 flex flex-col justify-between">
              <div>
                <div class="flex justify-between gap-2 mb-1">
                  <h3 class="text-base font-semibold text-blue-900 max-w-[75%] break-words">${ipo['Company Name']}</h3>
                  ${statusBadge}
                </div>
                ${tagType}
                <div class="mt-3 space-y-2 text-sm text-gray-700">
                  <div class="flex items-center gap-2">
                    <span class="text-gray-500">📦</span>
                    <span><strong>Units:</strong> ${ipo['Issued Unit']}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-blue-500">📅</span>
                    <span><strong>Opens:</strong> ${ipo['Open-Date']}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-red-500">⏳</span>
                    <span><strong>Closes:</strong> ${ipo['Close-Date']}</span>
                  </div>
                </div>
              </div>
            </div>`;
          wrapper.innerHTML += card;
        });
      } catch (err) {
        document.getElementById('ipo-card-wrapper').innerHTML =
          '<p class="text-red-500 text-center col-span-full">❌ Failed to load IPOs. Please try again later.</p>';
      }
    }

    // Load IPOs on page load
    loadIPOs();

    // Handle subscription form
    document.getElementById('subscribe-form').addEventListener('submit', async function (e) {
      e.preventDefault();
      const email = document.getElementById('email').value;
      const messageEl = document.getElementById('form-message');

      try {
        const res = await fetch('subscribe.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `email=${encodeURIComponent(email)}`
        });
        const result = await res.json();

        messageEl.textContent = result.message;
        messageEl.className = result.success
          ? 'text-green-600 font-medium mt-3'
          : 'text-orange-600 font-medium mt-3';

        if (result.success) document.getElementById('subscribe-form').reset();
      } catch (err) {
        messageEl.textContent = '❌ Something went wrong.';
        messageEl.className = 'text-red-600 font-medium mt-3';
      }
    });
  </script>
</body>
</html>
