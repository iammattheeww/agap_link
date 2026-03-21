<?php
// ─── PHILSMS API CONFIGURATION ────────────────────────────────────────────────
// Free tier available at https://philsms.com
//
// HOW TO GET YOUR API KEY:
//   1. Log in at https://dashboard.philsms.com
//   2. Go to Developers > API Documents
//   3. Copy the token shown after "Bearer " in the example requests
//
// SENDER ID:
//   "PhilSMS" is the built-in default sender available on ALL PhilSMS accounts.
//   It works immediately with no registration required.
//   If you register a custom sender ID in your dashboard (e.g. "AGAPLink"),
//   update PHILSMS_SENDER_ID below to match it exactly.
// ─────────────────────────────────────────────────────────────────────────────

define('PHILSMS_API_KEY',   '1624|ulZCKZqRcxrUEcKSshGZLkTgkF6ArDU3Bosvb3be53970c83');
define('PHILSMS_API_URL',   'https://dashboard.philsms.com/api/v3/sms/send');
define('PHILSMS_SENDER_ID', 'PhilSMS');
