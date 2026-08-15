<!-- FOOTER -->
<style>
.footer {
    background: #06512a;
    color: #d8f3dc;
    padding: 40px 20px 20px;
    margin-top: 40px;
    font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
}

.footer .container {
    max-width: 1200px;
    margin: auto;
}

.footer .grid {
    display: grid;
    grid-template-columns: 2fr 2fr 1.5fr;
    gap: 40px;
}

.footer h3 {
    color: #ffffff;
    margin-bottom: 10px;
}

.footer h4 {
    color: #b7e4c7;
    margin-bottom: 10px;
}

.footer p {
    font-size: 14px;
    line-height: 1.6;
}

.footer a {
    text-decoration: none;
    color: #d8f3dc;
    font-size: 14px;
    display: block;
    margin-bottom: 6px;
    transition: 0.3s;
}

.footer a:hover {
    color: #95d5b2;
}

.footer .links {
    display: flex;
    gap: 40px;
}

.footer .contact p {
    margin: 5px 0;
}

.footer .social a {
    display: inline-block;
    margin-right: 10px;
}

.footer .bottom {
    margin-top: 30px;
    border-top: 1px solid #2d6a4f;
    padding-top: 15px;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}

.footer .small-links a {
    margin-left: 15px;
    display: inline-block;
}

/* Responsive */
@media (max-width: 768px) {
    .footer .grid {
        grid-template-columns: 1fr;
    }

    .footer .links {
        flex-direction: column;
    }

    .footer .bottom {
        flex-direction: column;
        gap: 10px;
    }

    .footer .small-links a {
        margin-left: 0;
        margin-right: 10px;
    }
}
</style>

<footer class="footer">
  <div class="container">
    <div class="grid">

      <div class="brand">
        <h3>ECOGUARD</h3>
        <p>
          Protecting our environment through community action. Report issues,
          volunteer, and help keep Sri Lanka clean and sustainable for future generations.
        </p>
      </div>

      <div class="links">
        <div class="col">
          <h4>Explore</h4>
          <a href="/ecoGuard/citizen/citizen_dash.php">Dashboard</a>
          <a href="/ecoGuard/citizen/new_complaint.php">Report Issue</a>
          <a href="/ecoGuard/citizen/history.php">My Complaints</a>
          <a href="/ecoGuard/citizen/truck_schedule.php">Truck Schedule</a>
        </div>

        <div class="col">
          <h4>Resources</h4>
          <a href="#">Help Center</a>
          <a href="#">Guidelines</a>
          <a href="#">Terms & Conditions</a>
        </div>
      </div>

      <div class="contact">
        <h4>Contact</h4>
        <p><strong>Email:</strong> ecoguard@official.com</p>
        <p><strong>Phone:</strong> +94 71 234 5678</p>
        <p><strong>Address:</strong> Colombo, Sri Lanka</p>

        <div class="social">
          <a href="#">Facebook</a>
          <a href="#">Instagram</a>
          <a href="#">Twitter</a>
        </div>
      </div>

    </div>

    <div class="bottom">
      <p>©️ <?php echo date("Y"); ?> ECOGUARD. All rights reserved.</p>

      <div class="small-links">
        <a href="#">Privacy</a>
        <a href="#">Legal</a>
        <a href="#">Contact</a>
      </div>
    </div>
  </div>
</footer>