<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Resolve the project root from the current request instead of hard-coding /ecoGuard/.
$projectRoot = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$parts = array_values(array_filter(explode('/', trim($projectRoot, '/'))));
$root = '/';
if (!empty($parts)) {
    $root = '/' . $parts[0] . '/';
}
// If this file is included from a nested page, the first path segment is the project folder.
?>
<link rel="stylesheet" href="<?= htmlspecialchars($root) ?>css/theme.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<header class="site-header">
    <div class="header-container">
        <a href="<?= htmlspecialchars($root) ?>home.php" class="logo" aria-label="EcoGuard Home">
            <span class="logo-mark"><i class="fa-solid fa-recycle"></i></span>
            <span>ECOGUARD</span>
        </a>

        <button class="mobile-menu-btn" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="mainNav">
            <i class="fas fa-bars"></i>
        </button>

        <nav id="mainNav" class="main-nav" aria-label="Main navigation">
            <a href="<?= htmlspecialchars($root) ?>home.php">Home</a>
            <a href="<?= htmlspecialchars($root) ?>about.php">About</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                $role = (int)($_SESSION['role_id'] ?? 0);
                $dash = match ($role) {
                    1 => 'citizen/citizen_dash.php',
                    2 => 'admin/admin_dash.php',
                    3 => 'divisional/ds_dash.php',
                    4 => 'authorities/la_dash.php',
                    5 => 'GN/gn_dash.php',
                    default => 'home.php'
                };
                ?>
                <a href="<?= htmlspecialchars($root . $dash) ?>">Dashboard</a>
                <?php if ($role === 1): ?>
                    <a href="<?= htmlspecialchars($root) ?>citizen/truck_schedule.php">Truck Schedule</a>
                <?php elseif ($role === 4): ?>
                    <a href="<?= htmlspecialchars($root) ?>authorities/la_schedule.php">Truck Schedule</a>
                <?php elseif ($role === 2): ?>
                    <a href="<?= htmlspecialchars($root) ?>admin/truck_schedules.php">Truck Schedules</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="header-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= htmlspecialchars($root . $dash) ?>" class="header-icon" title="Dashboard"><i class="fas fa-gauge-high"></i></a>
                <a href="<?= htmlspecialchars($root) ?>logout.php" class="header-icon" title="Logout"><i class="fas fa-right-from-bracket"></i></a>
            <?php else: ?>
                <a href="<?= htmlspecialchars($root) ?>login.php" class="header-login">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
(function () {
    const btn = document.querySelector('.mobile-menu-btn');
    const nav = document.getElementById('mainNav');
    if (!btn || !nav) return;
    btn.addEventListener('click', function () {
        const open = nav.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        btn.querySelector('i').className = open ? 'fas fa-xmark' : 'fas fa-bars';
    });
})();
</script>
