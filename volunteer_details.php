<?php
session_start();
include __DIR__ . '/../includes/db.php';

// Only Citizen
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get event ID
if (!isset($_GET['id'])) {
    header("Location: citizen_dash.php"); // fixed: was citizen_dashboard.php (wrong filename, 404'd)
    exit();
}

$event_id = $_GET['id'];

// Fetch event details
$stmt = $pdo->prepare("SELECT * FROM volunteer_event WHERE Event_Id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    echo "Event not found.";
    exit();
}

// Fetch participation record
$stmtCheck = $pdo->prepare("SELECT * FROM user_participate_volunteer_event WHERE U_Id = ? AND Event_Id = ?");
$stmtCheck->execute([$user_id, $event_id]);
$participation = $stmtCheck->fetch(PDO::FETCH_ASSOC);

$alreadyRegistered = $participation ? true : false;

// Handle registration
if (isset($_POST['register']) && !$alreadyRegistered) {

    $stmtRegister = $pdo->prepare("INSERT INTO user_participate_volunteer_event (U_Id, Event_Id) VALUES (?, ?)");
    $stmtRegister->execute([$user_id, $event_id]);

    $successMessage = "You have successfully registered for this event!";

    // refresh participation
    $stmtCheck->execute([$user_id, $event_id]);
    $participation = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    $alreadyRegistered = true;
}

// Handle proof upload
if (isset($_POST['upload_proof']) && $alreadyRegistered) {

    if (!empty($_FILES['proof']['name'])) {

        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
        $ext = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            $errorMessage = "Invalid file type. Allowed: " . implode(', ', $allowedExt);
        } elseif ($_FILES['proof']['size'] > 5 * 1024 * 1024) {
            $errorMessage = "File too large. Max 5MB.";
        } else {
            $targetDir = "../uploads/";
            $fileName = time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES["proof"]["tmp_name"], $targetFile)) {

                $stmtUpload = $pdo->prepare("
                    UPDATE user_participate_volunteer_event
                    SET Proof_Image = ?
                    WHERE U_Id = ? AND Event_Id = ?
                ");

                $stmtUpload->execute([$targetFile, $user_id, $event_id]);

                $successMessage = "Proof uploaded successfully!";
                $participation['Proof_Image'] = $targetFile;

            } else {
                $errorMessage = "File upload failed.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>EcoGuard | Volunteer Event Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/theme.css">
    <link rel="stylesheet" href="../css/volunteer_details.css">
</head>
<body>

<?php
$path_to_root = "../";
include __DIR__ . '/../header.php';
?>

<div class="eg-page-header">
    <h1><?= htmlspecialchars($event['Name']) ?></h1>
</div>

<div class="dashboard">
    <div class="main-content" style="max-width:720px;margin:0 auto;">

        <p><strong>Date:</strong> <?= htmlspecialchars($event['Date']) ?></p>
        <p><strong>Time:</strong> <?= htmlspecialchars($event['Starting_Time']) ?></p>
        <p><strong>Location:</strong> <?= htmlspecialchars($event['Location']) ?></p>
        <p><strong>Organized By:</strong> <?= htmlspecialchars($event['Organized_By']) ?></p>
        <p><strong>Note:</strong> <?= nl2br(htmlspecialchars($event['Note'])) ?></p>

        <?php if (!empty($successMessage)): ?>
            <p class="success"><?= htmlspecialchars($successMessage) ?></p>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <p class="error"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>

        <!-- REGISTRATION SECTION -->
        <?php if (!$alreadyRegistered): ?>

            <form method="post">
                <button type="submit" name="register" class="block">Register for this Event</button>
            </form>

        <?php else: ?>

            <?php
            $today = date("Y-m-d");
            $eventDate = $event['Date'];
            ?>

            <!-- PROOF SECTION -->
            <?php if ($today >= $eventDate): ?>

                <?php if (empty($participation['Proof_Image'])): ?>

                    <h3 style="margin-top:24px;">Upload Proof of Participation</h3>

                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="proof" accept="image/*,.pdf" required>
                        <button type="submit" name="upload_proof" class="block" style="margin-top:12px;">Upload Proof</button>
                    </form>

                <?php else: ?>

                    <p class="success">Proof already submitted. Waiting for verification.</p>
                    <img src="<?= htmlspecialchars($participation['Proof_Image']) ?>" width="250">

                <?php endif; ?>

            <?php else: ?>

                <p style="color:orange; font-weight:bold;">You can upload proof after the event date.</p>

            <?php endif; ?>

        <?php endif; ?>

        <div style="margin-top:24px;">
            <button onclick="location.href='citizen_dash.php'">⬅ Back to Dashboard</button>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>
