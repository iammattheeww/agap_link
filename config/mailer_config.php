<?php
// ─── SMTP CONFIGURATION ─────────────────────────────────────────────────────
// Fill in your SMTP credentials below.
// For Gmail: host=smtp.gmail.com, port=587, encryption=tls
// Generate an App Password at https://myaccount.google.com/apppasswords

define('MAIL_HOST',       'smtp.gmail.com');
define('MAIL_PORT',       587);
define('MAIL_USERNAME',   'your-email@gmail.com');      // ← replace
define('MAIL_PASSWORD',   'your-app-password');           // ← replace (App Password)
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_EMAIL', 'no-reply@agap-link.com');
define('MAIL_FROM_NAME',  'AGAP-Link');
