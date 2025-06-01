<?php
// index.php
include 'db.php';

// Fetch all cars (model, image, price, description, category if you need)
$cars = $conn->query("SELECT model, image, price, description FROM cars ORDER BY model");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PaNorth Car Rental</title>
  <link rel="stylesheet" href="style.css"/>

  <style>

    .modal {
      display: none; 
      position: fixed; 
      z-index: 10000; 
      padding-top: 60px; 
      left: 0; top: 0;
      width: 100%; height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.8);
    }
    .modal-content {
      margin: auto;
      display: block;
      max-width: 80%;
      max-height: 80vh;
      border-radius: 8px;
    }
    #caption {
      margin: 15px auto;
      text-align: center;
      color: #fff;
      font-size: 18px;
    }
    .close-modal {
      position: absolute;
      top: 20px;
      right: 35px;
      color: #fff;
      font-size: 40px;
      font-weight: bold;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">PaNorth Car Rental</div>
    <nav>
      <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="cars.php">Cars</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="logout.php" class="logout-btn">Logout</a></li>
      </ul>
      <div class="menu-toggle" onclick="toggleMenu()">☰</div>
    </nav>
  </header>

  <section class="hero">
    <img src="imgs/logo/pn.png" alt="PaNorth Logo" class="hero-image"/>
    <h1>Reliable, Affordable, and Fast Car Rentals</h1>
    <p>Explore the Philippines with ease</p>
    <a href="cars.php" class="btn">View Available Cars</a>
  </section>

  <section class="features">
    <div class="feature-box">
      <h2>Wide Range of Cars</h2>
      <p>Choose from economy to luxury vehicles for any occasion.</p>
    </div>
    <div class="feature-box">
      <h2>Affordable Rates</h2>
      <p>Competitive pricing with no hidden fees.</p>
    </div>
    <div class="feature-box">
      <h2>Easy Booking</h2>
      <p>Quick online reservations with real-time availability.</p>
    </div>
  </section>

  <section class="catalogs">
    <h2>Our Car Catalog</h2>
    <div class="catalog-grid">
      <?php while($row = $cars->fetch_assoc()): ?>
        <div class="car-card">
          <img 
            src="<?= htmlspecialchars($row['image']) ?>" 
            alt="<?= htmlspecialchars($row['model']) ?>" 
          />
          <h3><?= htmlspecialchars($row['model']) ?></h3>
          <p>₱<?= number_format($row['price'], 2) ?> / day</p>
          <p><?= htmlspecialchars($row['description']) ?></p>
        </div>
      <?php endwhile; ?>
    </div>
  </section>

  <section class="services">
    <h2>Services Offered</h2>
    <div class="services-grid">
      <div class="service-card">
        <h3>Wedding Car Rentals</h3>
        <p>Arrive in style on your big day with our elegant and luxurious wedding cars.</p>
      </div>
      <div class="service-card">
        <h3>Airport Pick-up & Drop-off</h3>
        <p>Reliable and on-time transport to and from the airport for a stress-free journey.</p>
      </div>
      <div class="service-card">
        <h3>Corporate Rentals</h3>
        <p>Professional car rental services for business meetings and corporate events.</p>
      </div>
      <div class="service-card">
        <h3>Self-Drive Rentals</h3>
        <p>Drive at your own pace with our flexible self-drive car rental options.</p>
      </div>
      <div class="service-card">
        <h3>Chauffeur Services</h3>
        <p>Relax and let our professional drivers take you where you need to go.</p>
      </div>
      <div class="service-card">
        <h3>Long-Term Rentals</h3>
        <p>Special discounts and packages available for long-term car rentals.</p>
      </div>
    </div>
  </section>

  <section class="reviews">
    <h2>What Our Customers Say</h2>
    <div class="review-boxes">
      <div class="review">
        <p>"The booking was super smooth and the car was in perfect condition!"</p>
        <h4>- Maria D.</h4>
      </div>
      <div class="review">
        <p>"Excellent customer service and very affordable rates!"</p>
        <h4>- Juan C.</h4>
      </div>
      <div class="review">
        <p>"Highly recommended for road trips around Luzon!"</p>
        <h4>- Kenji P.</h4>
      </div>
    </div>
  </section>

  <!-- show large image -->
  <div id="imageModal" class="modal">
    <span class="close-modal" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage" />
    <div id="caption"></div>
  </div>

  <footer>
    <p>&copy; 2024 PaNorth Car Rental. All rights reserved.</p>
  </footer>

  <script>
     document.addEventListener("DOMContentLoaded", function() {
    const toggle = document.querySelector(".menu-toggle");
    const navLinks = document.querySelector(".nav-links");

    toggle.addEventListener("click", () => {
      navLinks.classList.toggle("show");
    });
  });
    

    // Modal functionality
    const modal = document.getElementById("imageModal");
    const modalImg = document.getElementById("modalImage");
    const captionText = document.getElementById("caption");

    document.querySelectorAll('.catalog-grid img').forEach(img => {
      img.style.cursor = 'pointer';
      img.addEventListener('click', () => {
        modal.style.display = "block";
        modalImg.src = img.src;
        captionText.textContent = img.nextElementSibling.textContent || img.alt || "";
      });
    });

    function closeModal() {
      modal.style.display = "none";
    }

    window.onclick = function(event) {
      if (event.target === modal) {
        closeModal();
      }
    };
  </script>
</body>
</html>
