<?php
include 'db.php';
$result = $conn->query("SELECT * FROM cars");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cars - PaNorth Car Rental</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <header>
     <h1>PaNorth Car Rental</h1>
    <div style="display: flex; justify-content: center; align-items: center; margin: 24px 0;">
      <img src="imgs/logo/pn.png" alt="logo" style="max-width: 180px; height: 100px;" />
     
    </div>
    <nav>
      <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="book.php">Book</a></li>
         <li><a href="logout.php" class="logout-btn">Logout</a></li>
      </ul>
    </nav>
  </header>

  <main>
    
    <h1 style="text-align:center">Available Cars</h1>

    <div class="category-filters">
      <button class="filter-btn active" onclick="filterCars('all')">All</button>
      <button class="filter-btn" onclick="filterCars('sedan')">Sedan</button>
      <button class="filter-btn" onclick="filterCars('suv')">SUV</button>
      <button class="filter-btn" onclick="filterCars('van')">Van</button>
    </div>

    <div class="cars-container">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="car-card" data-category="<?= $row['category'] ?>">
          <img src="<?= $row['image'] ?>" alt="<?= $row['model'] ?>" />
          <div class="car-details">
            <h3><?= $row['model'] ?></h3>
            <p><?= $row['description'] ?></p>
            <p class="price">₱<?= $row['price'] ?> / day</p>
            <button class="book-btn" onclick="location.href='book.php?car=<?= urlencode($row['model']) ?>'">Book Now</button>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </main>

  <footer>
    <p style="text-align:center;">&copy; 2025 PaNorth Car Rental. All rights reserved.</p>
  </footer>

  <script>
    function toggleMenu() {
      document.querySelector(".nav-links").classList.toggle("active");
    }
    function filterCars(category) {
      const cards = document.querySelectorAll('.car-card');
      const buttons = document.querySelectorAll('.filter-btn');
      buttons.forEach(btn => btn.classList.remove('active'));
      event.target.classList.add('active');
      cards.forEach(card => {
        card.style.display = (category === 'all' || card.dataset.category === category) ? 'flex' : 'none';
      });
    }
  </script>
</body>
</html>
