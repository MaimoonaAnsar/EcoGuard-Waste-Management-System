<?php
session_start();
include "includes/db.php";
include "includes/csrf.php";

$error = "";

// Basic brute-force throttling (per session)
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['login_locked_until'])) $_SESSION['login_locked_until'] = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    if (time() < $_SESSION['login_locked_until']) {
        $error = "Too many failed attempts. Please wait a minute and try again.";
    } else {
        csrf_verify();

        $loginInput = trim($_POST['loginInput']);
        $password   = trim($_POST['password']);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE Email = :login OR Username = :login LIMIT 1");
        $stmt->execute(['login' => $loginInput]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['Password'])) {
            $_SESSION['login_attempts'] = 0;
            session_regenerate_id(true);

            $_SESSION['user_id']  = $user['U_Id'];
            $_SESSION['role_id']  = $user['Role_Id'];
            $_SESSION['username'] = $user['Username'];

            switch ($user['Role_Id']) {
                case 1: header("Location: citizen/citizen_dash.php"); exit();
                case 2: header("Location: admin/admin_dash.php"); exit();
                case 3: header("Location: divisional/ds_dash.php"); exit();
                case 4: header("Location: authorities/la_dash.php"); exit();
                case 5: header("Location: GN/gn_dash.php"); exit();
                default:
                    $error = "No dashboard assigned for your role.";
                    session_destroy();
            }
        } else {
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['login_locked_until'] = time() + 60;
                $error = "Too many failed attempts. Please wait a minute and try again.";
            } else {
                // Deliberately vague — don't reveal whether the email/username exists
                $error = "Incorrect email/username or password.";
            }
        }
    }

    if ($error) {
        header("Location: login.php?error=" . urlencode($error));
        exit();
    }
}

if (isset($_GET['error'])) {
    $error = $_GET['error'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>EcoGuard | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/ecoguard_responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="form-container">
    <h2>🌿 Welcome Back</h2>
    <p style="text-align:center;color:var(--eg-text-muted);margin-top:-10px;">Log in to EcoGuard</p>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <?php csrf_field(); ?>
        <label>Email or Username</label>
        <input type="text" name="loginInput" placeholder="Email or Username" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="login" class="block" style="margin-top:24px;">Login</button>
    </form>

    <p class="link">Don't have an account? <a href="register.php">Register </a></p>
</div>

</body>
</html>
