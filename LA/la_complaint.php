<?php
session_start();
include "../includes/db.php";

// Only Local Authority (Role_Id = 4)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 4) {
    header("Location: ../login.php");
    exit();
}

$la_id = $_SESSION['user_id'];

if(!isset($_GET['id'])){
    header("Location: la_dash.php");
    exit();
}

$complaint_id = $_GET['id'];

/* FETCH COMPLAINT DETAILS */
$stmt = $pdo->prepare("
SELECT c.*, u.F_name, u.L_name, u.Email
FROM complaint c
JOIN users u ON c.U_Id = u.U_Id
WHERE c.C_Id = ? 
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$complaint){
    echo "Complaint not found.";
    exit();
}

/* ADD NOTE */
if(isset($_POST['submit_note'])){
    $note = $_POST['note'];
    $stmt = $pdo->prepare("
        INSERT INTO complaint_notes (C_Id, User_Id, Note)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$complaint_id, $la_id, $note]);
    header("Location: la_complaint.php?id=".$complaint_id);
    exit();
}

/* UPDATE STATUS */
if(isset($_POST['update_status'])){
    $status = $_POST['status'];
    $stmt = $pdo->prepare("
        UPDATE complaint
        SET Status = ?
        WHERE C_Id = ?
    ");
    $stmt->execute([$status, $complaint_id]);
    header("Location: la_complaint.php?id=".$complaint_id);
    exit();
}

/* ESCALATE COMPLAINT */
if(isset($_POST['escalate'])){
    // Escalation only to Divisional Secretariat (role_id = 3)
    // Assuming Divisional Secretariat has user_id = 3
    $ds_id = 3;
    $stmt = $pdo->prepare("
        UPDATE complaint
        SET Assigned_To = ?, Status='Escalated'
        WHERE C_Id = ?
    ");
    $stmt->execute([$ds_id, $complaint_id]);
    header("Location: la_dash.php");
    exit();
}

/* FETCH PREVIOUS NOTES */
$stmt = $pdo->prepare("
SELECT n.*, u.F_name, u.L_name
FROM complaint_notes n
JOIN users u ON n.User_Id = u.U_Id
WHERE C_Id = ?
ORDER BY Created_At DESC
");
$stmt->execute([$complaint_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>Complaint Details</title>
<link rel="stylesheet" href="../css/la_complaint2.css">
</head>

<body>
<?php include 'la_header.php'; ?>

<div class="container">

<h2>Complaint #<?= htmlspecialchars($complaint['C_Id']) ?></h2>

<p><b>Citizen:</b> <?= htmlspecialchars($complaint['F_name']." ".$complaint['L_name']) ?></p>
<p><b>Email:</b> <?= htmlspecialchars($complaint['Email']) ?></p>
<p><b>Location:</b> <?= htmlspecialchars($complaint['Location']) ?></p>
<p><b>Severity:</b> <?= htmlspecialchars($complaint['Severity']) ?></p>
<p><b>Status:</b> <?= htmlspecialchars($complaint['Status']) ?></p>
<p><b>Details:</b> <?= htmlspecialchars($complaint['Details']) ?></p>

<hr>

<h3>Add Note</h3>
<form method="POST">
<textarea name="note" required></textarea><br><br>
<button type="submit" name="submit_note">Submit Note</button>
</form>

<hr>

<h3>Update Complaint Status</h3>
<form method="POST">
<select name="status" required>
<option value="">Select Status</option>
<option value="Pending">Pending</option>
<option value="In Progress">In Progress</option>
<option value="Inspection Scheduled">Inspection Scheduled</option>
<option value="Resolved">Resolved</option>
<option value="Rejected">Rejected</option>
</select>
<br><br>
<button type="submit" name="update_status">Update Status</button>
</form>

<hr>

<h3>Escalate Complaint</h3>
<form method="POST">
<!-- Only option: Divisional Secretariat -->
<select name="authority" disabled>
<option value="3">Divisional Secretariat</option>
</select>
<br><br>
<button type="submit" name="escalate">Escalate Complaint</button>
</form>

<hr>

<h3>Previous Notes</h3>
<?php foreach($notes as $n): ?>
<p>
<b><?= htmlspecialchars($n['F_name']." ".$n['L_name']) ?></b><br>
<?= htmlspecialchars($n['Note']) ?><br>
<small><?= $n['Created_At'] ?></small>
</p>
<hr>
<?php endforeach; ?>

</div>

<?php include 'la_footer.php'; ?>
</body>
</html>