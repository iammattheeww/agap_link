<?php
// ─── SEMAPHORE SMS API CONFIGURATION ─────────────────────────────────────────
// Free tier: 500 SMS/month — no subscription required
// 1. Register at https://semaphore.co
// 2. Get your API key from https://semaphore.co/account#api
// 3. Your sender name must be approved at https://semaphore.co/sendernames

define('SEMAPHORE_API_KEY',    'YOUR_API_KEY_HERE');       // ← replace
define('SEMAPHORE_SENDER_NAME', 'AGAPLINK');                // ← replace (max 11 chars, approved)
