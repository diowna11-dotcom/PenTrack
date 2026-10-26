<?php
require_once 'auth.php';
require_once '../classes/Database.php';
require_once '../classes/PensionClaim.php';

$db = (new Database())->connect();
$claim = new PensionClaim($db);

$records = isset($_GET['keyword']) && $_GET['keyword'] !== ''
    ? $claim->searchPensionClaims($_GET['keyword'])
    : $claim->getPensionClaims();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PenTrack</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef1f5;
            margin: 0;
        }
        .site-header {
            background-color: #0d3b66;
            color: white;
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .site-header h1 { margin: 0; font-size: 26px; }
        .site-header p { margin: 5px 0 0; opacity: 0.85; }
        .logout-link {
            color: white;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.6);
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 14px;
        }
        .logout-link:hover { background: rgba(255,255,255,0.15); }
        .container {
            display: flex;
            justify-content: center;
            padding: 30px 40px;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px 30px;
            width: 100%;
            max-width: 1200px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .card h2 { color: #0d3b66; margin-top: 0; }
        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; align-items: center; }
        .search-bar input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .search-bar button {
            background-color: #0d3b66;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            white-space: nowrap;
        }
        .clear-link { color: #666; text-decoration: none; white-space: nowrap; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }
        thead { background-color: #e9edf3; }
        th, td {
            text-align: left;
            padding: 14px 16px;
            border-bottom: 1px solid #eee;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        td:nth-child(3) {
            white-space: normal;
            line-height: 1.4;
        }
        th:nth-child(1), td:nth-child(1) { width: 7%; }
        th:nth-child(2), td:nth-child(2) { width: 14%; }
        th:nth-child(3), td:nth-child(3) { width: 28%; }
        th:nth-child(4), td:nth-child(4) { width: 12%; }
        th:nth-child(5), td:nth-child(5) { width: 11%; }
        th:nth-child(6), td:nth-child(6) { width: 14%; }
        th:nth-child(7), td:nth-child(7) { width: 14%; }
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }
        .badge-claimed { background-color: #d4edda; color: #155724; }
        .badge-unclaimed { background-color: #f8d7da; color: #721c24; }
        .edit-link { color: #0d6efd; text-decoration: none; margin-right: 10px; }
        .delete-link { color: #dc3545; text-decoration: none; }
        .empty-row td { text-align: center; color: #888; padding: 20px; }
        .btn-primary {
            background-color: #e0a72a;
            color: #000;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div>
            <h1>PenTrack</h1>
            <p class="subtitle">Monthly pension claim monitoring system</p>
        </div>
        <a href="transactions.php" class="logout-link" style="margin-right: 10px;">Transactions</a>
        <a href="logout.php" class="logout-link">
            Logout (<?php echo htmlspecialchars($_SESSION['full_name']); ?>)
        </a>
    </header>

    <div class="container">
        <div class="card">
            <h2>Monthly Pension Claims</h2>

            <form method="GET" action="index.php" class="search-bar">
                <input type="text" name="keyword" placeholder="Search by name, program, or status..."
                       value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                <button type="submit">Search</button>
                <a href="index.php" class="clear-link">Clear</a>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Claim ID</th>
                        <th>Senior Citizen</th>
                        <th>Pension Program</th>
                        <th>Claim Date</th>
                        <th>Status</th>
                        <th>Processed By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($records) > 0): ?>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['claim_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['senior_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['program_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['claim_date']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['processed_by']); ?></td>
                                <td class="actions">
                                    <a href="edit_claim.php?id=<?php echo $row['claim_id']; ?>" class="edit-link">Edit</a>
                                    <a href="delete_claim.php?id=<?php echo $row['claim_id']; ?>" class="delete-link"
                                       onclick="return confirm('Are you sure you want to delete this claim?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="empty-row">
                            <td colspan="7">No pension claim records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <a href="add_claim.php" class="btn-primary">+ Add New Pension Claim</a>
        </div>
    </div>
</body>
</html>