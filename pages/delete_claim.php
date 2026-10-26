<?php
require_once 'auth.php';
require_once '../classes/Database.php';
require_once '../classes/PensionClaim.php';
require_once '../classes/Transaction.php';

$db = (new Database())->connect();
$claim = new PensionClaim($db);
$id = $_GET['id'];

// Fetch claim details before deleting for the transaction log
$stmt = $db->prepare("SELECT senior_id, program_id, user_id FROM pension_claims WHERE claim_id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$claimInfo = $stmt->fetch(PDO::FETCH_ASSOC);

$claim->deletePensionClaim($id);

// Log transaction
$txn = new Transaction($db);
$txn->log($_SESSION['user_id'], 'delete_claim', "Deleted claim #{$id} (senior_id=" . ($claimInfo['senior_id'] ?? 'N/A') . ", program_id=" . ($claimInfo['program_id'] ?? 'N/A') . ")");

header("Location: index.php");