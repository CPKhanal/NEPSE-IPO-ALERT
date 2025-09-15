# 📢 NEPSE IPO Alert System

Get notified via email when new IPOs are listed on the NEPSE platform — designed especially for Nepali investors who don’t check MeroShare daily.

## 🚀 Features

- 📋 Lists all active IPOs in a card layout
- 🔔 Allows users to subscribe using their email
- 📧 Sends welcome email upon subscription
- 📤 Sends IPO alert emails with company details
- ❌ Includes an unsubscribe feature via link or form
- 🔁 Supports automated execution via Cron Job
- 🧾 All emails stored in `subscribers.json`

## 🌐 Live Demo

🔗 [https://ipoalert.42web.io](https://ipoalert.42web.io)

## 🛠 Tech Stack

- HTML + Tailwind CSS
- Vanilla JavaScript
- PHP (no framework)
- JSON for storage
- PHPMailer with Brevo SMTP

## 📝 Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/cpkhanal/nepse-ipo-alert.git
cd nepse-ipo-alert
```

### 2. Configure PHPMailer

Download PHPMailer into the `/mailer/src/` directory or use Composer.

```
composer require phpmailer/phpmailer
```

### 3. Create `pass.env`

```env
SMTP_USER=your_brevo_email@example.com
SMTP_PASS=your_brevo_smtp_password
```

⚠️ Keep this file private and **do not upload it to GitHub**

### 4. Cron Job Setup

Use [https://cron-job.org](https://cron-job.org) or any free cron service.

**URL:** `https://ipoalert.42web.io/check-ipo.php`  
**Interval:** Every 15–30 minutes  
**Method:** GET

## 📁 File Structure

```
/NEPSE-IPO-ALERT
│
├── index.php              # Frontend UI
├── subscribe.php          # Subscribes email and sends welcome email
├── unsubscribe.php        # Handles email unsubscription
├── check-ipo.php          # Cron job endpoint to check & send IPO alerts
├── subscribers.json       # Email list (used by subscribe/unsubscribe)
├── data.json              # Latest IPO data from API
├── mailer/                # PHPMailer library
└── pass.env               # SMTP credentials (keep this secret)
```

## ✅ License

MIT License — Free to use, modify, and share.

## 🤝 Contributing

Pull requests and forks are welcome to improve the system for all NEPSE investors.