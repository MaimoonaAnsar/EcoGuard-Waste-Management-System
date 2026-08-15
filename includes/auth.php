<?php
// ============================================================
// Auth helper — include after session_start() + db.php
// Role_Id map: 1=Citizen, 2=Admin, 3=Divisional Secretary,
//              4=Local Authority, 5=Grama Niladhari
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require the logged-in user to have one of the given role IDs.
 * Redirects to the login page (with a $loginPath relative to the caller) if not.
 */
function requireRole($allowedRoles, $loginPath = 'login.php') {
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], $allowedRoles, true)) {
        header("Location: $loginPath");
        exit();
    }
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}
