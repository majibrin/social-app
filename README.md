# Termux Social Chat

A lightweight PHP/vanilla JS real-time chat application with full authentication, built and run entirely on Android via Termux.

## Stack

- **Backend:** PHP 8, PDO/MySQL, PHPMailer
- **Frontend:** Vanilla JS, HTML, CSS
- **Architecture:** MVC — Controllers, Models, Config separated under `src/`
- **Server:** PHP built-in server via `php -S localhost:8080 router.php`

## Features

- Register and login with email + password
- Session-based authentication
- Real-time chat with 2-second polling
- Forgot password flow:
  - Email OTP delivery via Gmail SMTP (PHPMailer)
  - 6-block OTP input with auto-focus and paste support
  - Code verified server-side with expiry (1 hour)
  - Password reset without re-entering email after code is sent
- Loading spinner and disabled button state during all API calls
- Reset codes logged locally to `reset_codes.log` as fallback

## Setup

1. Clone the repo and install dependencies:
   ```bash
   composer install
   ```

2. Create a `.env` file in the project root:
   ```
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USER=your@gmail.com
   SMTP_PASS=your_app_password
   ```

3. Import the database schema and run the seeder:
   ```bash
   php seed.php
   ```

4. Start the server:
   ```bash
   php -S localhost:8080 router.php
   ```

5. Open `http://localhost:8080` in your browser.

## Notes

- Gmail SMTP requires a Google App Password (2FA must be enabled on the account)
- Emails may land in spam when sent from localhost — expected in development
- All SMTP debug output is written to `smtp_debug.log` during development
