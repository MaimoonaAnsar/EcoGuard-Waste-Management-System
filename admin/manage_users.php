<?php
session_start();
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/csrf.php';

requireRole(2, '../login.php');

/* DELETE USER (now POST + CSRF protected, was GET before) */
if (isset($_POST['delete_user'])) {
    csrf_verify();
    $id = (int)$_POST['delete_user'];
    if ($id !== (int)$_SESSION['user_id']) { // prevent an admin deleting themselves by mistake
        $stmt = $pdo->prepare("DELETE FROM users WHERE U_Id=?");
        $stmt->execute([$id]);
    }
    header("Location: manage_users.php");
    exit();
}

/* ADD USER */
if (isset($_POST['add_user'])) {
    csrf_verify();

    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $tele = trim($_POST['tele']);
    $email = trim($_POST['email']);
    $nic = trim($_POST['nic']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = (int)$_POST['role'];

    $stmt = $pdo->prepare("
        INSERT INTO users
        (F_name, L_name, Tele, Email, NIC, Username, Password, Role_Id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$fname,$lname,$tele,$email,$nic,$username,$password,$role]);

    header("Location: manage_users.php");
    exit();
}

$stmt = $pdo->query("
    SELECT users.*, role.R_Name
    FROM users
    JOIN role ON users.Role_Id = role.R_Id
    ORDER BY U_Id ASC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
$roles = $pdo->query("SELECT * FROM role")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Users</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/theme.css">
</head>
<body>
<?php include 'admin_header.php'; ?>
<div class="eg-page-header"><h1>Manage Users</h1></div>

<div class="dashboard">
<div class="main-content">

<h2>Add New User</h2>
<form method="POST" style="max-width:480px;">
    <?php csrf_field(); ?>
    <label>First Name</label>
    <input type="text" name="fname" placeholder="First Name" required>
    <label>Last Name</label>
    <input type="text" name="lname" placeholder="Last Name" required>
    <label>Telephone</label>
    <input type="tel" name="tele" placeholder="Telephone">
    <label>Email</label>
    <input type="email" name="email" placeholder="Email">
    <label>NIC</label>
    <input type="text" name="nic" placeholder="NIC">
    <label>Username</label>
    <input type="text" name="username" placeholder="Username" required>
    <label>Password</label>
    <input type="password" name="password" placeholder="Password" required minlength="8">
    <label>Role</label>
    <select name="role" required>
        <?php foreach($roles as $r): ?>
            <option value="<?= (int)$r['R_Id'] ?>"><?= htmlspecialchars($r['R_Name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" name="add_user" class="block" style="margin-top:20px;">Add User</button>
</form>

<hr style="margin:32px 0;border:none;border-top:1px solid var(--eg-border);">

<h2>All Users</h2>
<table>
<tr>
    <th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Role</th><th>Actions</th>
</tr>
<?php foreach($users as $u): ?>
<tr>
    <td><?= (int)$u['U_Id'] ?></td>
    <td><?= htmlspecialchars($u['F_name']." ".$u['L_name']) ?></td>
    <td><?= htmlspecialchars($u['Email']) ?></td>
    <td><?= htmlspecialchars($u['Username']) ?></td>
    <td><span class="badge-pill"><?= htmlspecialchars($u['R_Name']) ?></span></td>
    <td>
        <a href="edit_user.php?id=<?= (int)$u['U_Id'] ?>">Edit</a>
        &nbsp;|&nbsp;
        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user? This cannot be undone.');">
            <?php csrf_field(); ?>
            <input type="hidden" name="delete_user" value="<?= (int)$u['U_Id'] ?>">
            <button type="submit" class="danger" style="padding:4px 12px;font-size:0.78rem;">Delete</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

</div>
</div>
<?php include 'admin_footer.php'; ?>
</body>
</html>
