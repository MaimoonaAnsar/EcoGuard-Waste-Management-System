<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$segments = array_values(array_filter(explode('/', trim(dirname($script), '/'))));
$path = !empty($segments) ? '/' . $segments[0] . '/' : '/';
?>
<link rel="stylesheet" href="<?= htmlspecialchars($path) ?>css/theme.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* =========================
   ADMIN HEADER
========================= */
.admin-header {
    background: linear-gradient(135deg, #04381b, #1b4332);
    color: #fff;
    border-bottom: 2px solid #2d6a4f;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Top Bar */
.admin-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 25px;
}

/* Logo */
.logo {
    font-size: 24px;
    font-weight: bold;
    color: #ffffff;
    text-decoration: none;
    letter-spacing: 1px;
}
.logo span {
    color: #95d5b2;
}

/* Icons */
.admin-icons a {
    color: #d8f3dc;
    margin-left: 15px;
    font-size: 18px;
    transition: 0.3s;
}
.admin-icons a:hover {
    color: #95d5b2;
    transform: scale(1.1);
}

/* =========================
   ADMIN TABS (MAIN FEATURE)
========================= */
.admin-tabs {
    display: flex;
    justify-content: center;
    overflow-x: auto;
    gap: 30px;
    background: #1b4332;
    padding: 10px 0;
    border-top: 1px solid #2d6a4f;
}

.admin-tabs a {
    color: #d8f3dc;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    padding: 8px 14px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

/* Hover */
.admin-tabs a:hover {
    background: #2d6a4f;
    color: #ffffff;
}

/* Active Tab */
.admin-tabs a.active {
    background: #40916c;
    color: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

/* =========================
   MOBILE
========================= */
@media (max-width: 768px) {
    .admin-tabs {
        flex-wrap: wrap;
        gap: 10px;
        padding: 10px;
    }
}

@media (max-width: 700px) {
    .admin-top { padding: 10px 14px; }
    .admin-tabs { justify-content:flex-start; overflow-x:auto; flex-wrap:nowrap; padding:8px 12px; -webkit-overflow-scrolling:touch; }
    .admin-tabs a { white-space:nowrap; }
}
</style>

<header class="admin-header">

    <!-- TOP BAR -->
    <div class="admin-top">
        <a href="<?= htmlspecialchars($path) ?>admin/admin_dash.php" class="logo">
            ECOGUARD <span>Admin</span>
        </a>

        <div class="admin-icons">
            <a href="<?= htmlspecialchars($path) ?>admin/admin_dash.php" title="Dashboard">
                <i class="fas fa-user-shield"></i>
            </a>
            <a href="<?= htmlspecialchars($path) ?>logout.php" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>

    <!-- TABS -->
    <div class="admin-tabs">
        <a href="<?= htmlspecialchars($path) ?>admin/admin_dash.php"> Dashboard</a>
        <a href="<?= htmlspecialchars($path) ?>admin/manage_users.php"> Manage Users</a>
        <a href="<?= htmlspecialchars($path) ?>admin/edit_user.php"> Edit Users</a>
        <a href="<?= htmlspecialchars($path) ?>admin/view_volunteer_events.php"> Events</a>
        <a href="<?= htmlspecialchars($path) ?>admin/admin_feedback.php"> Feedback</a>
        <a href="<?= htmlspecialchars($path) ?>admin/report.php"> Reports</a>
        <a href="<?= htmlspecialchars($path) ?>admin/truck_schedules.php"> Truck Schedules</a>
    </div>

</header>