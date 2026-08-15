<?php
$base_url = '/ecoGuard/';
?>
<!-- ECOGUARD LA Footer -->
<footer class="eco-footer">
  <div class="eco-footer-container">
    <div class="eco-footer-grid">

      <!-- BRAND -->
      <div class="eco-footer-brand">
        <h3>ECOGUARD Local Authority Panel</h3>
        <p>
          Local Authority portal for handling assigned environmental complaints,
          coordinating clean-up actions, and ensuring timely resolution within communities.
        </p>
      </div>

      <!-- LINKS -->
      <div class="eco-footer-links">
        <div class="eco-footer-col">
          <h4>Navigation</h4>
          <a href="<?= $base_url ?>LA/la_dash.php">Dashboard</a>
          <a href="<?= $base_url ?>LA/la_profile.php">Profile</a>
          <a href="<?= $base_url ?>LA/la_complaints.php">Assigned Complaints</a>
          <a href="<?= $base_url ?>LA/la_reports.php">Work Reports</a>
        </div>

        <div class="eco-footer-col">
          <h4>System</h4>
          <a href="<?= $base_url ?>LA/la_feedback.php">Feedback</a>
          <a href="<?= $base_url ?>LA/guidelines.php">Guidelines</a>
          <a href="#">Logs</a>
          <a href="#">Settings</a>
        </div>
      </div>

      <!-- STATUS -->
      <div class="eco-footer-contact">
        <h4>System Status</h4>
        <p><strong>Status:</strong> Operational</p>
        <p><strong>Access Level:</strong> Local Authority</p>
        <p><strong>Region:</strong> Sri Lanka</p>

        <div class="eco-footer-social">
          <a href="#">Security</a>
          <a href="#">Privacy</a>
          <a href="#">Support</a>
        </div>
      </div>

    </div>

    <div class="eco-footer-bottom">
      <p>©️ <?php echo date("Y"); ?> ECOGUARD Local Authority Panel</p>

      <div class="eco-footer-small-links">
        <a href="#">Logs</a>
        <a href="#">Settings</a>
        <a href="#">Help</a>
      </div>
    </div>
  </div>

  <!-- Scoped Styles -->
  <style>
    .eco-footer, .eco-footer * {
      box-sizing: border-box;
      font-family: Arial, sans-serif;
      color: #d8f3dc;
    }

    .eco-footer {
      display: block;
      background-color: #06512a;
      padding: 40px 20px 20px;
      margin-top: 40px;
    }

    .eco-footer-container {
      display: block;
      max-width: 1200px;
      margin: 0 auto;
    }

    .eco-footer-grid {
      display: grid;
      grid-template-columns: 2fr 2fr 1.5fr;
      gap: 40px;
    }

    .eco-footer h3 {
      font-size: 20px;
      font-weight: bold;
      color: #ffffff;
      margin-bottom: 10px;
    }

    .eco-footer h4 {
      font-size: 16px;
      font-weight: 600;
      color: #b7e4c7;
      margin-bottom: 10px;
    }

    .eco-footer p {
      font-size: 14px;
      line-height: 1.6;
      margin: 5px 0;
      color: #d8f3dc;
    }

    .eco-footer a {
      font-size: 14px;
      color: #d8f3dc;
      text-decoration: none;
      margin-bottom: 6px;
      display: block;
      transition: color 0.3s;
    }

    .eco-footer a:hover {
      color: #95d5b2;
    }

    .eco-footer-links {
      display: flex;
      gap: 40px;
    }

    .eco-footer-social a {
      display: inline-block;
      margin-right: 10px;
    }

    .eco-footer-bottom {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      margin-top: 30px;
      padding-top: 15px;
      border-top: 1px solid #2d6a4f;
    }

    .eco-footer-small-links a {
      display: inline-block;
      margin-left: 15px;
    }

    @media (max-width: 768px) {
      .eco-footer-grid {
        grid-template-columns: 1fr;
      }

      .eco-footer-links {
        flex-direction: column;
      }

      .eco-footer-bottom {
        flex-direction: column;
        gap: 10px;
      }

      .eco-footer-small-links a {
        margin-left: 0;
        margin-right: 10px;
      }
    }
  </style>
</footer>