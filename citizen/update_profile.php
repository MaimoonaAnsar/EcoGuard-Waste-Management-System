<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle form submission
if (isset($_POST['update'])) {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = $_POST['password']; // optional: leave empty if not changing

    if (empty($name) || empty($email)) {
        $error = "Name and Email cannot be empty.";
    } else {
        if (!empty($password)) {
            // update with new password
            $stmt = $pdo->prepare(
                "UPDATE users SET name=?, email=?, password=? WHERE id=?"
            );
            $stmt->execute([$name, $email, $password, $user_id]);
        } else {
            // update without changing password
            $stmt = $pdo->prepare(
                "UPDATE users SET name=?, email=? WHERE id=?"
            );
            $stmt->execute([$name, $email, $user_id]);
        }
        $success = "Profile updated successfully.";
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>EcoGaurd | Update Profile</title>
    <link rel="stylesheet" href="css/update_profile.css">
</head>
<body>

<div class="profile-container">
    <h2>Update Profile</h2>

    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>

    <form method="POST">
        <label>Full Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Password (leave empty to keep current)</label>
        <input type="password" name="password" placeholder="New Password">

        <button type="submit" name="update">Update Profile</button>
    </form>

    <p style="margin-top: 10px;">
        <a href="citizen_dash.php">Back to Dashboard</a>
    </p>
</div>

</body>
</html>
