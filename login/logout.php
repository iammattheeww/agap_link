<?php
session_start();
session_unset();
session_destroy();

header("Location: /agap_link/login/index.php");
exit;