<?php
session_start();
require_once '../classes/Database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if ($username === '' || $full_name === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $db = (new Database())->connect();

        // Check if username already exists
        $stmt = $db->prepare("SELECT user_id FROM users WHERE username = :u");
        $stmt->execute([':u' => $username]);
        if ($stmt->fetch()) {
            $error = 'Username is already taken.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare(
                "INSERT INTO users (username, full_name, password) VALUES (:u, :fn, :pw)"
            );
            try {
                $stmt->execute([
                    ':u' => $username,
                    ':fn' => $full_name,
                    ':pw' => $hashed_password,
                ]);
                $success = 'Account created successfully. You can now log in.';

                // Log registration transaction
                require_once '../classes/Transaction.php';
                $txn = new Transaction($db);
                $txn->log(null, 'register', "New account registered: {$full_name} ({$username})");
            } catch (PDOException $e) {
                $error = 'Registration failed. Please try again.';
                error_log("Registration failed: " . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PenTrack - Register</title>
    <style>
        body { font-family: Arial, sans-serif; background: #eef1f5; margin: 0;
               display: flex; justify-content: center; align-items: center; height: 100vh; }
        .box { background: white; padding: 35px; border-radius: 10px; width: 360px;
               box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #0d3b66; margin: 0 0 5px; }
        p.sub { color: #666; margin: 0 0 20px; }
        label { font-size: 14px; color: #333; display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; margin: 0 0 15px; border: 1px solid #ccc;
                border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        button { width: 100%; background: #0d3b66; color: white; border: none; padding: 11px;
                 border-radius: 6px; cursor: pointer; font-size: 15px; }
        button:hover { background: #0a2f4f; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 6px;
                 margin-bottom: 15px; font-size: 14px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 6px;
                   margin-bottom: 15px; font-size: 14px; }
        .link { text-align: center; margin-top: 15px; font-size: 14px; }
        .link a { color: #0d3b66; text-decoration: none; }
        .link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="box">
        <h1>PenTrack</h1>
        <p class="sub">Create your account</p>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <label>Username</label>
            <input type="text" name="username" required autofocus maxlength="50">

            <label>Full Name</label>
            <input type="text" name="full_name" required maxlength="100">

            <label>Password</label>
            <input type="password" name="password" required minlength="6">

            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required minlength="6">

            <button type="submit">Register</button>
        </form>

        <div class="link">
            Already have an account? <a href="login.php">Log in</a>
        </div>
    </div>
</body>
</html>