<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once '../classes/Database.php';
require_once '../classes/Transaction.php';

$db = (new Database())->connect();
$transaction = new Transaction($db);
$records = $transaction->getAll();
$dbError = $transaction->lastError;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PenTrack - Transactions</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #eef1f5; margin: 0; }
        .site-header { background-color: #0d3b66; color: white; padding: 30px 40px;
                       display: flex; justify-content: space-between; align-items: center; }
        .site-header h1 { margin: 0; font-size: 26px; }
        .site-header p { margin: 5px 0 0; opacity: 0.85; }
        .logout-link { color: white; text-decoration: none; border: 1px solid rgba(255,255,255,0.6);
                       padding: 8px 14px; border-radius: 6px; font-size: 14px; }
        .logout-link:hover { background: rgba(255,255,255,0.15); }
        .container { display: flex; justify-content: center; padding: 30px 40px; }
        .card { background: white; border-radius: 10px; padding: 25px 30px;
                width: 100%; max-width: 1000px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .card h2 { color: #0d3b66; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead { background-color: #e9edf3; }
        th, td { text-align: left; padding: 12px 16px; border-bottom: 1px solid #eee; font-size: 14px; }
        tr:nth-child(even) { background: #fafafa; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .btn-secondary { background-color: #6c757d; color: #fff; padding: 10px 16px;
                         border-radius: 6px; text-decoration: none; display: inline-block; }
        .btn-secondary:hover { background: #5a6268; }
        .empty { text-align: center; padding: 40px; color: #666; }
        .error-box { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px;
                     margin-bottom: 20px; font-size: 14px; border: 1px solid #f5c6cb; }
        .setup-hint { background: #fff3cd; color: #856404; padding: 15px; border-radius: 6px;
                      margin-bottom: 20px; font-size: 14px; border: 1px solid #ffeeba; }
        .setup-hint code { background: #fff; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>
    <header class="site-header">
        <div>
            <h1>PenTrack</h1>
            <p class="subtitle">Transaction &amp; session activity log</p>
        </div>
        <div>
            <a href="index.php" class="btn-secondary">Back to Claims</a>
            <a href="logout.php" class="logout-link">
                Logout (<?php echo htmlspecialchars($_SESSION['full_name']); ?>)
            </a>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>Transaction History</h2>

            <?php if ($dbError): ?>
                <div class="error-box">
                    <strong>Database error:</strong> <?php echo htmlspecialchars($dbError); ?><br>
                    Make sure you have imported <code>database_schema.sql</code> into your MySQL database.
                </div>
            <?php elseif (count($records) === 0): ?>
                <div class="setup-hint">
                    <strong>No transactions recorded yet.</strong><br>
                    Transactions are logged automatically when you:
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>Log in / Log out</li>
                        <li>Register a new account</li>
                        <li>Add, edit, or delete a pension claim</li>
                        <li>Add, update, or delete a senior citizen</li>
                    </ul>
                    Log in at <code>login.php</code> (admin / admin123) and perform some actions to see them appear here.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 8%;">#</th>
                            <th style="width: 18%;">User</th>
                            <th style="width: 22%;">Action</th>
                            <th style="width: 32%;">Details</th>
                            <th style="width: 20%;">Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['user_name'] ?? 'System'); ?></td>
                                <td>
                                    <span class="badge" style="background:#eef1f5; color:#0d3b66;">
                                        <?php echo htmlspecialchars($row['action']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['details'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>