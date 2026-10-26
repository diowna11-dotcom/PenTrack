<?php
session_start();

// Session timeout: 30 minutes of inactivity
$inactive_timeout = 1800; // 30 minutes

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_timeout)) {
        session_unset();
        session_destroy();
        header('Location: login.php?expired=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}