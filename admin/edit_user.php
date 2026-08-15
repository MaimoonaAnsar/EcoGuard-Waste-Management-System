<?php
session_start();
include __DIR__ . '/../includes/db.php';

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit();
}

$message = '';
$user = null;

// Handle search
if (isset($_POST['search_user'])) {
    $search = trim($_POST['search']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE Email = ? OR Username = ?");
    $stmt->execute([$search, $search]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = "No user found with that email or username.";
    }
}

// Handle update
if (isset($_POST['update_user']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $f_name = $_POST['f_name'];
    $l_name = $_POST['l_name'];
    $email = $_POST['email'];
    $tele = $_POST['tele'];
    $username = $_POST['username'];
    $role_id = $_POST['role_id'];

    $stmt = $pdo->prepare("UPDATE users SET F_name = ?, L_name = ?, Email = ?, Tele = ?, Username = ?, Role_Id = ? WHERE U_Id = ?");
    $stmt->execute([$f_name, $l_name, $email, $tele, $username, $role_id, $user_id]);

    $message = "User updated successfully!";
    // Reload updated data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE U_Id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle delete
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    // Optional: Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        $message = "You cannot delete your own account.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE U_Id = ?");
        $stmt->execute([$user_id]);
        $message = "User deleted successfully!";
        $user = null; // Clear form
    }
}

// Fetch all roles for dropdown
$stmtRoles = $pdo->query("SELECT * FROM role");
$roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User | Admin</title>
    <link rel="stylesheet" href="../css/edit_user.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
<header>
    <h1>Edit User</h1>
</header>

<div class="dashboard">
    <div class="main-content">

        <!-- Search Form -->
        <h2>Search User</h2>
        <form method="POST">
            <input type="text" name="search" placeholder="Email or Username" required>
            <button type="submit" name="search_user">Search</button>
        </form>
        <?php if($message): ?>
            <p style="color:<?= $user ? 'green':'red' ?>;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <!-- Edit Form -->
        <?php if($user): ?>
            <h2>Edit User Details</h2>
            <form method="POST" onsubmit="return confirm('Are you sure you want to update this user?');">
                <input type="hidden" name="user_id" value="<?= $user['U_Id'] ?>">
                
                <label>First Name:</label><br>
                <input type="text" name="f_name" value="<?= htmlspecialchars($user['F_name']) ?>" required><br><br>

                <label>Last Name:</label><br>
                <input type="text" name="l_name" value="<?= htmlspecialchars($user['L_name']) ?>" required><br><br>

                <label>Email:</label><br>
                <input type="email" name="email" value="<?= htmlspecialchars($user['Email']) ?>" required><br><br>

                <label>Telephone:</label><br>
                <input type="text" name="tele" value="<?= htmlspecialchars($user['Tele']) ?>"><br><br>

                <label>Username:</label><br>
                <input type="text" name="username" value="<?= htmlspecialchars($user['Username']) ?>" required><br><br>

                <label>Role:</label><br>
                <select name="role_id" required>
                    <?php foreach($roles as $role): ?>
                        <option value="<?= $role['R_Id'] ?>" <?= $user['Role_Id'] == $role['R_Id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['R_Name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>

                <button type="submit" name="update_user">Update User</button>
            </form>

            <!-- Delete Button -->
            <form method="POST" style="margin-top:10px;" onsubmit="return confirm('Are you sure you want to delete this user permanently?');">
                <input type="hidden" name="user_id" value="<?= $user['U_Id'] ?>">
                <button type="submit" name="delete_user" style="background-color:red; color:white;">Delete User</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php include 'admin_footer.php'; ?>
</body>
</html>