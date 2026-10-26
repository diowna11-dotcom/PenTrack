<?php
require_once 'auth.php';
require_once '../classes/Database.php';
require_once '../classes/SeniorCitizen.php';
require_once '../classes/Transaction.php';

$db = (new Database())->connect();
$senior = new SeniorCitizen($db);
$txn = new Transaction($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $senior->full_name = $_POST['full_name'];
        if ($senior->add()) {
            $txn->log($_SESSION['user_id'], 'add_senior_citizen', "Added senior citizen: {$_POST['full_name']}");
        }
    }
    if (isset($_POST['update'])) {
        $senior->full_name = $_POST['full_name'];
        if ($senior->update($_POST['senior_id'])) {
            $txn->log($_SESSION['user_id'], 'update_senior_citizen', "Updated senior citizen #{$_POST['senior_id']}: {$_POST['full_name']}");
        }
    }
    header("Location: senior_citizens.php");
    exit();
}

if (isset($_GET['delete'])) {
    $senior->delete($_GET['delete']);
    $txn->log($_SESSION['user_id'], 'delete_senior_citizen', "Deleted senior citizen #{$_GET['delete']}");
    header("Location: senior_citizens.php");
    exit();
}

$records = $senior->getAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Senior Citizens</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header class="site-header">
        <h1>Barangay Senior Citizen Pension Distribution Tracker</h1>
        <p class="subtitle">Manage senior citizen records</p>
    </header>

    <div class="container">
        <div class="card">
            <h2>Senior Citizens</h2>

            <form method="POST" class="claim-form" autocomplete="off">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Full Name" required>
                </div>
                <div class="form-actions">
                    <button type="submit" name="add" class="btn-primary">Add Senior Citizen</button>
                </div>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Senior ID</th>
                        <th>Full Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($records) > 0): ?>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['senior_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td class="actions">
                                    <a href="#" class="edit-link" onclick="editRow(<?php echo $row['senior_id']; ?>, '<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES); ?>')">Edit</a>
                                    <a href="senior_citizens.php?delete=<?php echo $row['senior_id']; ?>" class="delete-link" onclick="return confirm('Delete this senior citizen?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="empty-row">
                            <td colspan="3">No senior citizens found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <a href="index.php" class="btn-secondary">Back to Claims</a>
        </div>
    </div>

    <!-- Simple edit modal-like inline form (shown/hidden with JS) -->
    <div id="editBox" style="display:none; margin-top:20px;">
        <h3>Edit Senior Citizen</h3>
        <form method="POST" class="claim-form" autocomplete="off">
            <input type="hidden" name="senior_id" id="edit_senior_id">
            <div class="form-group">
                <label for="edit_full_name">Full Name</label>
                <input type="text" name="full_name" id="edit_full_name" required>
            </div>
            <div class="form-actions">
                <button type="submit" name="update" class="btn-primary">Save Changes</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('editBox').style.display='none';">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        function editRow(id, name) {
            document.getElementById('edit_senior_id').value = id;
            document.getElementById('edit_full_name').value = name;
            document.getElementById('editBox').style.display = 'block';
        }
    </script>
</body>
</html>