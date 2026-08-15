<?php
session_start();
include __DIR__ . '/../includes/db.php';

// Redirect if not logged in or not Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Fetch admin info
$stmtAdmin = $pdo->prepare("SELECT * FROM users WHERE U_Id = ?");
$stmtAdmin->execute([$admin_id]);
$admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

// Fetch all complaints submitted by citizens, ordered by Location
$stmt = $pdo->prepare("
    SELECT c.*, u.F_name, u.L_name, u.Email 
    FROM complaint c 
    JOIN users u ON c.U_Id = u.U_Id 
    ORDER BY c.Location ASC, c.C_Id ASC
");
$stmt->execute();
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle volunteer opportunity submission
if (isset($_POST['submit_volunteer'])) {
    $name = $_POST['name'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = $_POST['location'];
    $note = $_POST['note'];
    $organized_by = $admin['F_name'] . ' ' . $admin['L_name'];

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $image_name = time() . "_" . basename($_FILES['image']['name']);
        $image_path_full = $uploadDir . $image_name;

        move_uploaded_file($_FILES['image']['tmp_name'], $image_path_full);
        $image_path = 'uploads/' . $image_name; // Save relative path
    }

    // Insert into volunteer_event table
    $stmt = $pdo->prepare("
        INSERT INTO volunteer_event 
        (Name, Date, Starting_Time, Location, Note, Organized_By, Image) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $date, $time, $location, $note, $organized_by, $image_path]);

    // Redirect with success message
    header("Location: admin_dash.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>EcoGuard | Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admin_dash2.css">
<link rel="stylesheet" href="../css/ecoguard_responsive.css">
</head>
<body>
<?php include 'admin_header.php'; ?>



<header>
    <h1>EcoGuard Admin Panel</h1>
</header>

<div class="dashboard">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <h3><?= htmlspecialchars($admin['F_name']) ?></h3>
        <p><?= htmlspecialchars($admin['Email']) ?></p>
        <p><?= htmlspecialchars($admin['Tele']) ?></p>
        <hr>
        <button onclick="location.href='../logout.php'">Logout</button>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- Complaints Section -->
        <h2>All Complaints</h2>

        <?php if (count($complaints) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Citizen</th>
                        <th>Email</th>
                        <th>Location</th>
                        <th>Severity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($complaints as $index => $row): ?>
                    <tr class="complaint-row <?= $index >= 5 ? 'extra-row' : '' ?>"
                        onclick="window.location.href='../view_complaint.php?id=<?= $row['C_Id'] ?>'"
                        style="<?= $index >= 5 ? 'display:none;' : '' ?>">
                        <td><?= htmlspecialchars($row['C_Id']) ?></td>
                        <td><?= htmlspecialchars($row['F_name'] . ' ' . $row['L_name']) ?></td>
                        <td><?= htmlspecialchars($row['Email']) ?></td>
                        <td><?= htmlspecialchars($row['Location']) ?></td>
                        <td><?= htmlspecialchars($row['Severity']) ?></td>
                        <td>
                            <span class="status <?= strtolower($row['Status']) ?>">
                                <?= htmlspecialchars($row['Status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (count($complaints) > 5): ?>
            <div style="text-align:center; margin-top:10px;">
                <button onclick="toggleRows()" id="toggleBtn">⬇ Show More</button>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <p>No complaints submitted yet.</p>
        <?php endif; ?>

        <hr>

        <!-- Volunteer Opportunity Section -->
        <h2>Post Volunteer Opportunity</h2>

        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="success-message">
                 Volunteer opportunity posted successfully!
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="volunteer-form">
            <label for="name">Opportunity Name:</label>
            <input type="text" name="name" id="name" required>

            <label for="date">Date:</label>
            <input type="date" name="date" id="date" required>

            <label for="time">Starting Time:</label>
            <input type="time" name="time" id="time" required>

            <label for="location">Location:</label>
            <input type="text" name="location" id="location" required>

            <label for="note">Note / Description:</label>
            <textarea name="note" id="note" rows="4" required></textarea>

            <label for="image">Event Image (optional):</label>
            <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png">

            <button type="submit" name="submit_volunteer">Post Opportunity</button>
        </form>
    </div>
</div>

<script>
function toggleRows() {
    const extraRows = document.querySelectorAll('.extra-row');
    const btn = document.getElementById('toggleBtn');
    let isVisible = false;

    extraRows.forEach(row => {
        if (row.style.display === 'none') {
            row.style.display = '';
            isVisible = true;
        } else {
            row.style.display = 'none';
        }
    });

    btn.textContent = isVisible ? '⬆ Show Less' : '⬇ Show More';
}
</script>

<?php include 'admin_footer.php'; ?>
</body>
</html>