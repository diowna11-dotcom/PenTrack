<?php
require_once 'auth.php';
require_once '../classes/Database.php';
require_once '../classes/PensionClaim.php';

$db = (new Database())->connect();
$claim = new PensionClaim($db);

$id = $_GET['id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Find/create Senior Citizen by typed name
    $stmt = $db->prepare("SELECT senior_id FROM senior_citizens WHERE full_name = :name");
    $stmt->bindParam(':name', $_POST['senior_name']);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $senior_id = $row['senior_id'];
    } else {
        $stmt = $db->prepare("INSERT INTO senior_citizens (full_name) VALUES (:name)");
        $stmt->bindParam(':name', $_POST['senior_name']);
        $stmt->execute();
        $senior_id = $db->lastInsertId();
    }

    // Find/create Program by typed name
    $stmt = $db->prepare("SELECT program_id FROM pension_programs WHERE name = :name");
    $stmt->bindParam(':name', $_POST['program_name']);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $program_id = $row['program_id'];
    } else {
        $stmt = $db->prepare("INSERT INTO pension_programs (name) VALUES (:name)");
        $stmt->bindParam(':name', $_POST['program_name']);
        $stmt->execute();
        $program_id = $db->lastInsertId();
    }

    // Find/create Staff by typed name
    $stmt = $db->prepare("SELECT user_id FROM users WHERE full_name = :name");
    $stmt->bindParam(':name', $_POST['staff_name']);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $user_id = $row['user_id'];
    } else {
        $stmt = $db->prepare("INSERT INTO users (full_name) VALUES (:name)");
        $stmt->bindParam(':name', $_POST['staff_name']);
        $stmt->execute();
        $user_id = $db->lastInsertId();
    }

    $claim->senior_id  = $senior_id;
    $claim->program_id = $program_id;
    $claim->user_id    = $user_id;
    $claim->claim_date = $_POST['claim_date'];
    $claim->status     = $_POST['status'];

    if ($claim->updatePensionClaim($id)) {
        // Log transaction
        require_once '../classes/Transaction.php';
        $txn = new Transaction($db);
        $txn->log($_SESSION['user_id'], 'edit_claim', "Updated claim #{$id}");

        header("Location: index.php");
        exit();
    } else {
        $message = "Failed to update claim.";
    }
}

// Fetch current claim details to pre-fill the form
$stmt = $db->prepare("SELECT pc.*, sc.full_name AS senior_name, pp.name AS program_name, u.full_name AS staff_name
                       FROM pension_claims pc
                       JOIN senior_citizens sc ON pc.senior_id = sc.senior_id
                       JOIN pension_programs pp ON pc.program_id = pp.program_id
                       JOIN users u ON pc.user_id = u.user_id
                       WHERE pc.claim_id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$current = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Pension Claim</title>
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
        }
        .site-header h1 { margin: 0; font-size: 26px; }
        .site-header p { margin: 5px 0 0; opacity: 0.85; }
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
            max-width: 500px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .card h2 { color: #0d3b66; margin-top: 0; }
        .form-group { margin-bottom: 15px; display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-weight: 600; color: #333; }
        .form-group input, .form-group select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-actions { display: flex; gap: 10px; margin-top: 15px; }
        .btn-primary {
            background-color: #e0a72a;
            color: #000;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <header class="site-header">
        <h1>PenTrack</h1>
        <p>Edit pension claim</p>
    </header>

    <div class="container">
        <div class="card">
            <h2>Edit Pension Claim</h2>

            <?php if ($message): ?>
                <p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="senior_name">Senior Citizen Name</label>
                    <input type="text" id="senior_name" name="senior_name" value="<?php echo htmlspecialchars($current['senior_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="program_name">Pension Program</label>
                    <input type="text" id="program_name" name="program_name" value="<?php echo htmlspecialchars($current['program_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="staff_name">Processed By</label>
                    <input type="text" id="staff_name" name="staff_name" value="<?php echo htmlspecialchars($current['staff_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="claim_date">Claim Date</label>
                    <input type="date" id="claim_date" name="claim_date" value="<?php echo htmlspecialchars($current['claim_date']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option <?php echo $current['status'] === 'Claimed' ? 'selected' : ''; ?>>Claimed</option>
                        <option <?php echo $current['status'] === 'Unclaimed' ? 'selected' : ''; ?>>Unclaimed</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <a href="index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>