<?php
session_start();
require_once '../classes/Database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT user_id, full_name, password FROM users WHERE username = :u");
    $stmt->execute([':u' => trim($_POST['username'])]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['password'] && password_verify($_POST['password'], $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];

        // Log successful login transaction
        require_once '../classes/Transaction.php';
        $txn = new Transaction($db);
        $txn->log($user['user_id'], 'login', 'User logged in');

        header('Location: index.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PenTrack - Log in</title>
    <style>
        body { font-family: Arial, sans-serif; background: #eef1f5; margin: 0;
               display: flex; justify-content: center; align-items: center; height: 100vh; }
        .box { background: white; padding: 35px; border-radius: 10px; width: 340px;
               box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #0d3b66; margin: 0 0 5px; }
        p.sub { color: #666; margin: 0 0 20px; }
        label { font-size: 14px; color: #333; }
        input { width: 100%; padding: 10px; margin: 5px 0 15px; border: 1px solid #ccc;
                border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; background: #0d3b66; color: white; border: none; padding: 11px;
                 border-radius: 6px; cursor: pointer; font-size: 15px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 6px;
                 margin-bottom: 15px; font-size: 14px; }
        .link { text-align: center; margin-top: 15px; font-size: 14px; }
        .link a { color: #0d3b66; text-decoration: none; }
        .link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="box">
        <h1>PenTrack</h1>
        <p class="sub">Log in to continue</p>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['expired']) && $_GET['expired'] == 1): ?>
            <div class="error">Your session has expired due to inactivity. Please log in again.</div>
        <?php endif; ?>
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required autofocus>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit">Log in</button>
        </form>
        <div class="link">
            Don't have an account? <a href="register.php">Register</a>
        </div>
    </div>
</body>
</html>