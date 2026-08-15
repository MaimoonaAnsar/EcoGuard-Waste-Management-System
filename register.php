<?php
session_start();
include "includes/db.php";
include "includes/csrf.php";

$error = "";
$success = "";

if (isset($_POST['register'])) {
    csrf_verify();

    $fname    = trim($_POST['fname']);
    $lname    = trim($_POST['lname']);
    $tele     = trim($_POST['tele']);
    $email    = trim($_POST['email']);
    $nic      = trim($_POST['nic']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role_id  = (int)($_POST['role_id'] ?? 1);
    $allowedRoles = [1, 3, 4, 5];

    if (empty($fname) || empty($lname) || empty($email) || empty($nic) || empty($username) || empty($password)) {
        $error = "Please fill all fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!in_array($role_id, $allowedRoles, true)) {
        $error = "Please select a valid account role.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/^[0-9]{9,12}[VvXx]?$/', $nic)) {
        $error = "Please enter a valid NIC number.";
    } else {
        $stmt = $pdo->prepare("SELECT U_Id FROM users WHERE Email = ? OR Username = ?");
        $stmt->execute([$email, $username]);

        if ($stmt->fetch()) {
            $error = "Email or Username already registered";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare(
                "INSERT INTO users
                (F_name, L_name, Tele, Email, NIC, Username, Password, Role_Id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            if ($insert->execute([$fname, $lname, $tele, $email, $nic, $username, $hashedPassword, $role_id])) {
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Something went wrong. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>EcoGuard | Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/ecoguard_responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="form-container">
    <h2>🌿 Join EcoGuard</h2>
    <p style="text-align:center;color:var(--eg-text-muted);margin-top:-10px;">Create your EcoGuard account</p>

    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>

    <form method="POST">
        <?php csrf_field(); ?>
        <label>First Name</label>
        <input type="text" name="fname" placeholder="First Name" required>

        <label>Last Name</label>
        <input type="text" name="lname" placeholder="Last Name" required>

        <label>Telephone</label>
        <input type="tel" name="tele" placeholder="07XXXXXXXX" required>

        <label>Email</label>
        <input type="email" name="email" placeholder="Email" required>

        <label>NIC</label>
        <input type="text" name="nic" placeholder="NIC Number" required>

        <label>Username</label>
        <input type="text" name="username" placeholder="Username" required>

        <label>Account Role</label>
        <select name="role_id" required>
            <option value="1">Citizen</option>
            <option value="5">Grama Niladhari (GN)</option>
            <option value="4">Local Authority (LA)</option>
            <option value="3">Divisional Secretary (DS)</option>
        </select>
        <p style="font-size:.75rem;color:var(--eg-text-muted);margin:6px 0 0;"></p>

        <label>Password</label>
        <input type="password" name="password" placeholder="At least 8 characters" required minlength="8">

        <button type="submit" name="register" class="block" style="margin-top:24px;">Register</button>
    </form>

    <p class="link">Already have an account? <a href="login.php">Login</a></p>
</div>

</body>
</html>
