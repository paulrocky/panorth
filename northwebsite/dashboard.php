<?php
// dashboard.php
session_start();

// 1. Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

include 'db.php';

// 2. Get user info from session
$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// 3. Fetch this user’s bookings from the database
$stmt = $conn->prepare("
  SELECT car_model, pickup_date, return_date, contact_number
    FROM bookings
   WHERE user_id = ?
   ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($car, $pickup, $return, $contact);

// 4. Collect bookings into an array
$bookings = [];
while ($stmt->fetch()) {
  $bookings[] = [
    'car'           => $car,
    'pickup_date'   => $pickup,
    'return_date'   => $return,
    'contact_number'=> $contact
  ];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard - PaNorth Car Rental</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <header>
    <div class="logo">PaNorth Car Rental</div>
    <nav>
      <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="cars.php">Cars</a></li>
        <li><a href="book.php">Book</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="logout.php" class="logout-btn">Logout</a></li>
      </ul>
      <div class="menu-toggle" onclick="toggleMenu()">☰</div>
    </nav>
  </header>

  <div class="container">
    <h2>Welcome, <?= htmlspecialchars($user_name) ?>!</h2>
    <h3>Your Bookings</h3>
    <table>
      <thead>
        <tr>
          <th>Car</th>
          <th>Pick-up Date</th>
          <th>Return Date</th>
          <th>Contact Number</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($bookings)): ?>
          <tr><td colspan="4">No bookings yet.</td></tr>
        <?php else: ?>
          <?php foreach ($bookings as $b): ?>
            <tr>
              <td><?= htmlspecialchars($b['car']) ?></td>
              <td><?= $b['pickup_date'] ?></td>
              <td><?= $b['return_date'] ?></td>
              <td><?= htmlspecialchars($b['contact_number']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <footer>
    <p>&copy; 2025 PaNorth Car Rental. All rights reserved.</p>
  </footer>

  <script>
    function toggleMenu() {
      document.querySelector('.nav-links').classList.toggle('active');
    }
  </script>
</body>
</html>
