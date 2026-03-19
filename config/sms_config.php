<?php
// ─── PHILSMS API CONFIGURATION ────────────────────────────────────────────────
// Free tier available at https://philsms.com
// Your account shows: SMS Unit balance (top up as needed)
//
// HOW TO GET YOUR API KEY:
//   1. Log in at https://dashboard.philsms.com
//   2. Go to Developers > API Documents
//   3. Your API token is shown in the example requests as the Bearer token
//      (e.g. "Authorization: Bearer 49|LNFe8WJ7CPtvl2mzowAB4ll4enbFR0XGgnQh2qWY")
//   4. Copy ONLY the token part after "Bearer " and paste it below
//
// YOUR SENDER NAME:
//   PhilSMS uses your registered account name as sender.
//   No separate sender name approval needed.
// ─────────────────────────────────────────────────────────────────────────────

define('PHILSMS_API_KEY',  '1624|ulZCKZqRcxrUEcKSshGZLkTgkF6ArDU3Bosvb3be53970c83');
define('PHILSMS_API_URL', 'https://dashboard.philsms.com/api/v3/sms/send');
