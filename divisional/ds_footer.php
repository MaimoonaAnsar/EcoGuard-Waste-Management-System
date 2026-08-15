<?php
$base_url = '/ecoGuard/';
?>

<footer class="site-footer">
  <div class="site-footer-container">
    <div class="site-footer-grid">

      <!-- BRAND -->
      <div class="site-footer-brand">
        <h3>ECOGUARD DS Panel</h3>
        <p>
          Divisional Secretariat portal for overseeing escalated environmental complaints,
          coordinating regional responses, and ensuring accountability across local authorities.
        </p>
      </div>

      <!-- LINKS -->
      <div class="site-footer-links">
        <div class="site-footer-col">
          <h4>Navigation</h4>
          <a href="<?= $base_url ?>DS/ds_dash.php">Dashboard</a>
          <a href="<?= $base_url ?>DS/ds_profile.php">Profile</a>
          <a href="<?= $base_url ?>DS/ds_reports.php">Regional Reports</a>
          <a href="<?= $base_url ?>DS/ds_complaints.php">Assigned Complaints</a>
        </div>

        <div class="site-footer-col">
          <h4>System</h4>
          <a href="<?= $base_url ?>DS/ds_feedback.php">Feedback</a>
          <a href="<?= $base_url ?>DS/guidelines.php">Guidelines</a>
          <a href="#">Logs</a>
          <a href="#">Settings</a>
        </div>
      </div>

      <!-- STATUS -->
      <div class="site-footer-contact">
        <h4>System Status</h4>
        <p><strong>Status:</strong> Operational</p>
        <p><strong>Access Level:</strong> Divisional Secretariat</p>
        <p><strong>Region:</strong> Sri Lanka</p>

        <div class="site-footer-social">
          <a href="#">Security</a>
          <a href="#">Privacy</a>
          <a href="#">Support</a>
        </div>
      </div>

    </div>

    <div class="site-footer-bottom">
      <p>©️ <?php echo date("Y"); ?> ECOGUARD DS Panel</p>

      <div class="site-footer-small-links">
        <a href="#">Logs</a>
        <a href="#">Settings</a>
        <a href="#">Help</a>
      </div>
    </div>
  </div>
</footer>

<style>
/* =========================
   Footer Isolation & Base
========================= */
.site-footer {
    all: unset;
    box-sizing: border-box;
    display: block;
    background: #06512a;
    color: #d8f3dc;
    padding: 40px 20px 20px;
    font-family: Arial, sans-serif;
}

/* Container */
.site-footer-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* Grid */
.site-footer-grid {
    display: grid;
    grid-template-columns: 2fr 2fr 1.5fr;
    gap: 40px;
}

/* Brand */
.site-footer-brand h3 {
    color: #ffffff;
    margin-bottom: 10px;
}

.site-footer-brand h4 {
    color: #b7e4c7;
    margin-bottom: 10px;
}

.site-footer-brand p {
    font-size: 14px;
    line-height: 1.6;
}

/* Links */
.site-footer-links {
    display: flex;
    gap: 40px;
}

.site-footer-col h4 {
    color: #b7e4c7;
    margin-bottom: 10px;
}

.site-footer-col a {
    display: block;
    text-decoration: none;
    color: #d8f3dc;
    font-size: 14px;
    margin-bottom: 6px;
    transition: 0.3s;
}

.site-footer-col a:hover {
    color: #95d5b2;
}

/* Contact */
.site-footer-contact h4 {
    color: #b7e4c7;
    margin-bottom: 10px;
}

.site-footer-contact p {
    margin: 5px 0;
    font-size: 14px;
}

.site-footer-social a {
    display: inline-block;
    margin-right: 10px;
    color: #d8f3dc;
    text-decoration: none;
    transition: 0.3s;
}

.site-footer-social a:hover {
    color: #95d5b2;
}

/* Bottom */
.site-footer-bottom {
    margin-top: 30px;
    border-top: 1px solid #2d6a4f;
    padding-top: 15px;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}

.site-footer-small-links a {
    display: inline-block;
    margin-left: 15px;
    color: #d8f3dc;
    text-decoration: none;
    transition: 0.3s;
}

.site-footer-small-links a:hover {
    color: #95d5b2;
}

/* =========================
   Responsive
========================= */
@media (max-width: 768px) {
    .site-footer-grid {
        grid-template-columns: 1fr;
    }

    .site-footer-links {
        flex-direction: column;
    }

    .site-footer-bottom {
        flex-direction: column;
        gap: 10px;
    }

    .site-footer-small-links a {
        margin-left: 0;
        margin-right: 10px;
    }
}
</style>