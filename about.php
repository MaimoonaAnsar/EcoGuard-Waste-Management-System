<?php
session_start();

// Include DB only if needed
if (!isset($pdo)) {
    include __DIR__ . '/includes/db.php';
}

// Default header/footer (Citizen)
$header_file = __DIR__ . '/header.php';
$footer_file = __DIR__ . '/footer.php';

// Get role (prefer session, fallback to DB)
$role_id = $_SESSION['role_id'] ?? null;

if (!$role_id && isset($_SESSION['user_id']) && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT Role_Id FROM users WHERE U_Id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $role_id = $user['Role_Id'] ?? null;
}

// Switch headers/footers based on role
switch ($role_id) {
    case 1: // Citizen
        $header_file = __DIR__ . '/header.php';
        $footer_file = __DIR__ . '/footer.php';
        break;

    case 5: // GN
        $header_file = __DIR__ . '/GN/gn_header.php';
        $footer_file = __DIR__ . '/GN/gn_footer.php';
        break;

    case 4: // Local Authority
        $header_file = __DIR__ . '/authorities/la_header.php';
        $footer_file = __DIR__ . '/authorities/la_footer.php';
        break;

    case 3: // Divisional Secretary
        $header_file = __DIR__ . '/divisional/ds_header.php';
        $footer_file = __DIR__ . '/divisional/ds_footer.php';
        break;

    default:
        // Not logged in → normal public header
        $header_file = __DIR__ . '/header.php';
        $footer_file = __DIR__ . '/footer.php';
        break;
}

// Include header
if (file_exists($header_file)) {
    include $header_file;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | EcoGuard</title>
    <link rel="stylesheet" href="css/about.css">
</head>
<body>

<div class="about-container">
    <section class="hero">
        <h1>About EcoGuard</h1>
        <p>
            EcoGuard is an environmental complaint management system designed to empower citizens
            to report issues, participate in community actions, and help keep Sri Lanka clean,
            sustainable, and safe for future generations.
        </p>
    </section>

    <section class="cards">
        <div class="card">
            <h2>Our Purpose</h2>
            <p>
                To create a platform that connects citizens, volunteers, and local authorities
                for effective environmental action and accountability.
            </p>
        </div>

        <div class="card">
            <h2>Our Features</h2>
            <ul>
                <li>Submit environmental complaints easily.</li>
                <li>Track complaint status and progress.</li>
                <li>Participate in volunteer events and community cleanups.</li>
                <li>Receive certificates for your contributions.</li>
            </ul>
        </div>

        <div class="card">
            <h2>Our Vision</h2>
            <p>
                A cleaner, greener Sri Lanka where every citizen plays an active role in
                protecting the environment and preserving natural beauty for generations to come.
            </p>
        </div>
    </section>

    <section class="cta">
        <h2>Get Involved</h2>
        <p>
            Join our community initiatives, report issues, or volunteer for upcoming events.
            Together, we can make a real difference.
        </p>
    </section>
</div>

<?php
// Include footer
if (file_exists($footer_file)) {
    include $footer_file;
}
?>

</body>
</html>