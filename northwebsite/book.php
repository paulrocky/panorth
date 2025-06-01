<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$driverRoutes = $conn->query("SELECT * FROM routes WHERE service_type = 'driver' ORDER BY destination");
$selfRoutes = $conn->query("SELECT * FROM routes WHERE service_type = 'self' ORDER BY destination");
$carResult = $conn->query("SELECT model, image FROM cars ORDER BY model");

$success = $error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmed']) && $_POST['confirmed'] == 'yes') {
    // This runs only after confirmation from modal

    $user_id     = $_SESSION['user_id'];
    $car         = $_POST['car'];
    $destination = $_POST['destination'];
    $fare        = $_POST['fare'];
    $pickup      = $_POST['pickup'];
    $return      = $_POST['return'];
    $name        = $_POST['name'];
    $contact     = $_POST['contact'];

    $stmt = $conn->prepare(
        "INSERT INTO bookings
          (user_id, full_name, contact_number, car_model, destination, fare, pickup_date, return_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issssdds",
      $user_id, $name, $contact, $car, $destination, $fare, $pickup, $return
    );

    if ($stmt->execute()) {
        $success = "Thanks, $name! Your booking for a $car<br>"
                 . "Route: $destination (₱$fare)<br>"
                 . "from $pickup to $return has been confirmed.";
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Book a Car - PaNorth Car Rental</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
  <style>
    .car-preview {
      display: block;
      margin: 20px auto;
      max-width: 300px;
    }
    .fare-display {
      font-weight: bold;
      margin: 10px 0;
    }
    /* Modal */
    .modal {
      position: fixed;
      z-index: 999;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.5);
      display: none;
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background-color: #133E87;
      color: #F3F3E0;
      padding: 20px;
      border-radius: 8px;
      max-width: 400px;
      text-align: center;
      box-shadow: 0 0 10px #000000aa;
      position: relative;
    }
    .close {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 24px;
      font-weight: bold;
      cursor: pointer;
    }
    .modal-buttons {
      margin-top: 20px;
    }
    .modal-buttons button {
      margin: 0 10px;
      padding: 8px 20px;
      font-size: 16px;
      cursor: pointer;
    }
    .btn-confirm {
       background-color: #F3F3E0;
       color: #133E87;
      border: none;
      border-radius: 4px;
    }
    .btn-cancel {
      background-color: #F3F3E0;
       color: #133E87;
      border: none;
      border-radius: 4px;
    }
  </style>
</head>
<body>
  <header>
    <h1>Book Your Car</h1>
    <nav>
      <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="cars.php">Cars</a></li>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="logout.php" class="logout-btn">Logout</a></li>
      </ul>
      <div class="menu-toggle" onclick="toggleMenu()">☰</div>
  </header>

  <section class="booking-form">
    <?php if ($success): ?>
      <div style="background:#d4edda;color:#155724;padding:10px;margin-bottom:15px;border-radius:5px;">
        <?= $success ?>
      </div>
    <?php elseif ($error): ?>
      <div style="background:#f8d7da;color:#721c24;padding:10px;margin-bottom:15px;border-radius:5px;">
        <?= $error ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="bookingForm" novalidate>
      <label>Select Car:</label>
      <select name="car" id="carSelect" required>
        <option value="">-- Choose a Car --</option>
        <?php while ($c = $carResult->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($c['model']) ?>" data-image="<?= htmlspecialchars($c['image']) ?>">
            <?= htmlspecialchars($c['model']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <img id="carPreview" class="car-preview" style="display:none" alt="Car Preview" />

      <label for="serviceType">Service Type:</label>
      <select id="serviceType" required>
        <option value="">-- Select Service --</option>
        <option value="driver">With Driver</option>
        <option value="self">Self Drive</option>
      </select>

      <div id="driverRoutes" style="display:none;">
        <label for="destinationDriver">Select Route With Driver:</label>
        <select name="destination" id="destinationDriver">
          <option value="">-- Choose a Route --</option>
          <?php while ($row = $driverRoutes->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($row['destination']) ?>" data-price="<?= htmlspecialchars($row['fare']) ?>">
              <?= htmlspecialchars($row['destination']) ?> (₱<?= number_format($row['fare'], 2) ?>)
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div id="selfRoutes" style="display:none;">
        <label for="destinationSelf">Select Self-Drive Package:</label>
        <select name="destination" id="destinationSelf">
          <option value="">-- Choose a Package --</option>
          <?php while ($row = $selfRoutes->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($row['destination']) ?>" data-price="<?= htmlspecialchars($row['fare']) ?>">
              <?= htmlspecialchars($row['destination']) ?> (₱<?= number_format($row['fare'], 2) ?>/day)
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="fare-display">
        Fare: ₱<span id="fareAmount">0.00</span>
      </div>
      <input type="hidden" name="fare" id="fareInput" />

      <label>Pickup Date:</label>
      <input type="text" name="pickup" class="datepicker" required />

      <label>Return Date:</label>
      <input type="text" name="return" class="datepicker" required />

      <label>Full Name:</label>
      <input type="text" name="name" required />

      <label>Contact Number:</label>
      <input type="tel" name="contact" required />

      <!-- Hidden input to detect confirmed submission -->
      <input type="hidden" name="confirmed" id="confirmedInput" value="no" />

      <button type="submit" class="btn">Book Now</button>
    </form>
  </section>

  <!-- Confirmation Modal -->
  <div id="confirmationModal" class="modal">
    <div class="modal-content">
      <span id="modalClose" class="close">&times;</span>
      <h3>Please confirm your booking details</h3>
      <div id="modalDetails" style="text-align:left; margin-top:15px;"></div>
      <div class="modal-buttons">
        <button id="confirmBtn" class="btn-confirm">Confirm</button>
        <button id="cancelBtn" class="btn-cancel">Cancel</button>
      </div>
    </div>
  </div>

  <footer>
    <p>&copy; 2025 PaNorth Car Rental. All rights reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    const serviceType = document.getElementById('serviceType');
    const driverDiv = document.getElementById('driverRoutes');
    const selfDiv = document.getElementById('selfRoutes');
    const fareAmountSpan = document.getElementById('fareAmount');
    const fareInput = document.getElementById('fareInput');

    serviceType.addEventListener('change', () => {
      driverDiv.style.display = serviceType.value === 'driver' ? 'block' : 'none';
      selfDiv.style.display = serviceType.value === 'self' ? 'block' : 'none';
      fareAmountSpan.textContent = '0.00';
      fareInput.value = '';
      // Clear destination selects
      document.getElementById('destinationDriver').value = '';
      document.getElementById('destinationSelf').value = '';
    });

    document.getElementById('destinationDriver').addEventListener('change', e => {
      const price = parseFloat(e.target.selectedOptions[0]?.dataset.price) || 0;
      fareAmountSpan.textContent = price.toFixed(2);
      fareInput.value = price;
    });

    document.getElementById('destinationSelf').addEventListener('change', e => {
      const price = parseFloat(e.target.selectedOptions[0]?.dataset.price) || 0;
      fareAmountSpan.textContent = price.toFixed(2);
      fareInput.value = price;
    });

    flatpickr(".datepicker", {
      dateFormat: "Y-m-d",
      minDate: "today",
      allowInput: true
    });

    // Car image preview
    const carSelect = document.getElementById('carSelect');
    const carPreview = document.getElementById('carPreview');
    carSelect.addEventListener('change', () => {
      const selectedOption = carSelect.selectedOptions[0];
      const imgSrc = selectedOption?.dataset.image || '';
      if (imgSrc) {
        carPreview.src = imgSrc;
        carPreview.style.display = 'block';
      } else {
        carPreview.style.display = 'none';
      }
    });

    // Modal & form confirmation logic
    const form = document.getElementById('bookingForm');
    const modal = document.getElementById('confirmationModal');
    const modalClose = document.getElementById('modalClose');
    const modalDetails = document.getElementById('modalDetails');
    const confirmBtn = document.getElementById('confirmBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const confirmedInput = document.getElementById('confirmedInput');

    form.addEventListener('submit', function(e) {
      e.preventDefault(); // Prevent default form submit

      // Validate required fields (basic)
      if (!form.car.value) {
        alert('Please select a car.');
        return;
      }
      if (!serviceType.value) {
        alert('Please select a service type.');
        return;
      }
      let destinationSelect = serviceType.value === 'driver' ? 
                              document.getElementById('destinationDriver') : 
                              document.getElementById('destinationSelf');
      if (!destinationSelect.value) {
        alert('Please select a destination or package.');
        return;
      }
      if (!form.pickup.value || !form.return.value) {
        alert('Please enter pickup and return dates.');
        return;
      }
      if (!form.name.value.trim()) {
        alert('Please enter your full name.');
        return;
      }
      if (!form.contact.value.trim()) {
        alert('Please enter your contact number.');
        return;
      }

      // Prepare modal details for confirmation
      modalDetails.innerHTML = `
        <strong>Car:</strong> ${form.car.value}<br>
        <strong>Service Type:</strong> ${serviceType.options[serviceType.selectedIndex].text}<br>
        <strong>Destination/Package:</strong> ${destinationSelect.value}<br>
        <strong>Fare:</strong> ₱${fareInput.value}<br>
        <strong>Pickup Date:</strong> ${form.pickup.value}<br>
        <strong>Return Date:</strong> ${form.return.value}<br>
        <strong>Full Name:</strong> ${form.name.value}<br>
        <strong>Contact Number:</strong> ${form.contact.value}
      `;

      modal.style.display = 'flex';
    });

    modalClose.addEventListener('click', () => {
      modal.style.display = 'none';
    });

    cancelBtn.addEventListener('click', () => {
      modal.style.display = 'none';
    });

    confirmBtn.addEventListener('click', () => {
      // Mark form as confirmed and submit for real
      confirmedInput.value = 'yes';
      modal.style.display = 'none';
      form.submit();
    });

    // Close modal if clicked outside content
    window.addEventListener('click', e => {
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    });
  </script>
</body>
</html>
