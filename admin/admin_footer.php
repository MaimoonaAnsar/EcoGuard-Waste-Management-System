<?php
$path = '/ecoGuard/';
?>

<!-- FOOTER -->
<style>
/* =========================
   Footer Base Styles
========================= */
footer.footer {
    background: #06512a;
    color: #d8f3dc;
    padding: 40px 20px 20px;
    margin-top: 40px;
    font-family: Arial, sans-serif;
}

footer.footer .container {
    max-width: 1200px;
    margin: auto;
}

footer.footer .grid {
    display: grid;
    grid-template-columns: 2fr 2fr 1.5fr;
    gap: 40px;
}

footer.footer h3 {
    color: #ffffff;
    margin-bottom: 10px;
}

footer.footer h4 {
    color: #b7e4c7;
    margin-bottom: 10px;
}

footer.footer p {
    font-size: 14px;
    line-height: 1.6;
}

footer.footer a {
    text-decoration: none;
    color: #d8f3dc;
    font-size: 14px;
    display: block;
    margin-bottom: 6px;
    transition: 0.3s;
}

footer.footer a:hover {
    color: #95d5b2;
}

footer.footer .links {
    display: flex;
    gap: 40px;
}

footer.footer .contact p {
    margin: 5px 0;
}

footer.footer .social a {
    display: inline-block;
    margin-right: 10px;
}

footer.footer .bottom {
    margin-top: 30px;
    border-top: 1px solid #2d6a4f;
    padding-top: 15px;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}

footer.footer .small-links a {
    margin-left: 15px;
    display: inline-block;
}

/* =========================
   Footer Overrides (Force Correct Colors)
========================= */
footer.footer,
footer.footer * {
    font-family: Arial, sans-serif !important;
    color: #d8f3dc !important;
}

footer.footer h3 {
    color: #ffffff !important;
}

footer.footer h4 {
    color: #b7e4c7 !important;
}

footer.footer a {
    color: #d8f3dc !important;
}

footer.footer a:hover {
    color: #95d5b2 !important;
}

footer.footer .contact strong {
    color: #d8f3dc !important;
}

footer.footer .bottom p,
footer.footer .small-links a {
    color: #d8f3dc !important;
}

/* =========================
   Responsive
========================= */
@media (max-width: 768px) {
    footer.footer .grid {
        grid-template-columns: 1fr;
    }

    footer.footer .links {
        flex-direction: column;
    }

    footer.footer .bottom {
        flex-direction: column;
        gap: 10px;
    }

    footer.footer .small-links a {
        margin-left: 0;
        margin-right: 10px;
    }
}
</style>

<footer class="footer">
  <div class="container">
    <div class="grid">

      <!-- BRAND -->
      <div class="brand">
        <h3>ECOGUARD Admin</h3>
        <p>
          Administrative control panel for managing environmental complaints,
          users, and volunteer activities across the platform.
        </p>
      </div>

      <!-- LINKS -->
      <div class="links">
        <div class="col">
          <h4>Management</h4>
          <a href="<?= $path ?>admin/admin_dash.php">Dashboard</a>
          <a href="<?= $path ?>admin/manage_users.php">Manage Users</a>
          <a href="<?= $path ?>admin/manage_complaints.php">Complaints</a>
          <a href="<?= $path ?>admin/view_volunteer_events.php">Events</a>
        </div>

        <div class="col">
          <h4>System</h4>
          <a href="<?= $path ?>admin/reports.php">Reports</a>
          <a href="<?= $path ?>admin/admin_feedback.php">Feedback</a>
          <a href="#">Logs</a>
          <a href="#">Settings</a>
        </div>
      </div>

      <!-- STATUS -->
      <div class="contact">
        <h4>System Status</h4>
        <p><strong>Status:</strong> Operational</p>
        <p><strong>Version:</strong> 1.0</p>
        <p><strong>Region:</strong> Sri Lanka</p>

        <div class="social">
          <a href="#">Security</a>
          <a href="#">Privacy</a>
          <a href="#">Support</a>
        </div>
      </div>

    </div>

    <div class="bottom">
      <p>©️ <?php echo date("Y"); ?> ECOGUARD Admin Panel</p>

      <div class="small-links">
        <a href="#">Logs</a>
        <a href="#">Settings</a>
        <a href="#">Help</a>
      </div>
    </div>
  </div>
</footer>