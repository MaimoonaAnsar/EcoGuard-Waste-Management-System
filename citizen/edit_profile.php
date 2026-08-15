<?php
session_start();
include __DIR__ . '/../includes/db.php';

// Redirect if not logged in or not a citizen
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE U_Id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $tele = $_POST['tele'];

    $update = $pdo->prepare("UPDATE users SET F_name = ?, Email = ?, Tele = ? WHERE U_Id = ?");
    if ($update->execute([$name, $email, $tele, $user_id])) {
        $message = "Profile updated successfully!";
        // Refresh user data
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $message = "Error updating profile. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile | EcoGuard</title>
    <link rel="stylesheet" href="../css/edit_profile2.css">
</head>
<body>
<?php 
$path_to_root = "../";
include __DIR__ . '/../header.php'; 
?>

<div class="container">
    <h2>Edit Profile</h2>

    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" class="profile-form">
        <label>Full Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['F_name']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['Email']) ?>" required>

        <label>Telephone</label>
        <input type="text" name="tele" value="<?= htmlspecialchars($user['Tele']) ?>" required>

        <button type="submit">Save Changes</button>
    </form>

</div>
<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>