<?php
session_start();
include __DIR__ . '/../includes/db.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit();
}

// Allowed roles for admin to add
$allowed_roles = [3, 4, 5]; // Divisional Secretary, Grama Niladhari, Local Authority
$stmt = $pdo->prepare("SELECT * FROM role WHERE R_Id IN (" . implode(',', $allowed_roles) . ")");
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $F_name = $_POST['F_name'];
    $L_name = $_POST['L_name'];
    $Email = $_POST['Email'];
    $Tele = $_POST['Tele'];
    $Role_Id = $_POST['Role_Id'];
    $Nic = $_POST['NIC'];
    $Username = $_POST['Username'];
    $Password = password_hash($_POST['Password'], PASSWORD_DEFAULT);

    // Validate role
    if (!in_array($Role_Id, $allowed_roles)) {
        $message = "Error: You are not allowed to assign this role.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (F_name, L_name, Email, Tele, Role_Id, NIC, Username, Password) VALUES (?, ?, ?, ?, ?, ? ,? ,? )");
        $stmt->execute([$F_name, $L_name, $Email, $Tele, $Role_Id, $Nic, $Username, $Password]);
        $message = "User added successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User | Admin</title>
    <link rel="stylesheet" href="../css/add_users2.css">
</head>
<body>
    <header>
        <h1>EcoGuard Admin Panel - Add User</h1>
    </header>

    <div class="dashboard">
        <div class="sidebar">
            <h3><?= htmlspecialchars($_SESSION['user_id']) ?></h3>
            <button onclick="location.href='admin/admin_dash.php'">Back to Dashboard</button>
        </div>

        <div class="main-content">
            <?php if ($message): ?>
                <p><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <form method="POST">
                <label>First Name:</label>
                <input type="text" name="F_name" required>

                <label>Last Name:</label>
                <input type="text" name="L_name" required>

                <label>Email:</label>
                <input type="email" name="Email" required>

                <label>Telephone:</label>
                <input type="text" name="Tele">

                <label>Role:</label>
                <select name="Role_Id" required>
                    <option value="">-- Select Role --</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['R_Id'] ?>"><?= htmlspecialchars($role['R_Name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>NIC:</label>
                <input type="text" name="NIC">

                <label>Username:</label>
                <input type="text" name="Username">

                <label>Password:</label>
                <input type="password" name="Password" required>

                <button type="submit">Add User</button>
            </form>
        </div>
    </div>
</body>
</html>