<?php
session_start();

// Log logout transaction before destroying session
$user_id = $_SESSION['user_id'] ?? null;
if ($user_id) {
    require_once '../classes/Database.php';
    require_once '../classes/Transaction.php';
    $db = (new Database())->connect();
    $transaction = new Transaction($db);
    $transaction->log($user_id, 'logout', 'User logged out');
}

session_destroy();
header('Location: login.php');
exit;