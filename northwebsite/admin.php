<?php
include 'db.php';

// Handle add car
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['model']) && !isset($_POST['update'])) {
  $model = $_POST['model'];
  $category = $_POST['category'];
  $service_type = $_POST['service_type'];
  $description = $_POST['description'];
  $price = $_POST['price'];

  $targetDir = "imgs/";
  $imageName = basename($_FILES["image"]["name"]);
  $targetFile = $targetDir . $imageName;

  if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
    $stmt = $conn->prepare(
      "INSERT INTO cars (model, category, service_type, description, price, image) 
       VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssds", $model, $category, $service_type, $description, $price, $targetFile);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit;
  } else {
    echo "<p class='error-msg'>Image upload failed. Please try again.</p>";
  }
}

// Handle update car
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $id = $_POST['id'];
  $model = $_POST['model'];
  $category = $_POST['category'];
  $service_type = $_POST['service_type'];
  $price = $_POST['price'];
  
  // For image update logic:
  if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
    $targetDir = "imgs/";
    $imageName = basename($_FILES["new_image"]["name"]);
    $targetFile = $targetDir . $imageName;
    if (move_uploaded_file($_FILES["new_image"]["tmp_name"], $targetFile)) {
      $image = $targetFile;
    } else {
      $image = $_POST['current_image']; // fallback to current image if upload fails
    }
  } else {
    $image = $_POST['current_image']; // no new image uploaded
  }

  $stmt = $conn->prepare(
    "UPDATE cars SET model=?, category=?, service_type=?, price=?, image=? WHERE id=?"
  );
  $stmt->bind_param("sssssi", $model, $category, $service_type, $price, $image, $id);
  $stmt->execute();
  $stmt->close();
  header("Location: admin.php");
  exit;
}

// Handle delete car
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $conn->query("DELETE FROM cars WHERE id = $id");
  header("Location: admin.php");
  exit;
}

// Handle add route
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_route'])) {
  $route_model  = $_POST['route_model'];
  $service_type = $_POST['route_service'];
  $destination  = $_POST['destination'];
  $fare         = $_POST['fare'];

  $stmt = $conn->prepare("INSERT INTO routes (car_model, service_type, destination, fare) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("sssd", $route_model, $service_type, $destination, $fare);
  $stmt->execute();
  $stmt->close();
  header("Location: admin.php");
  exit;
}

// Handle update route
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_route'])) {
  $route_id = $_POST['route_id'];
  $route_model = $_POST['edit_route_model'];
  $service_type = $_POST['edit_route_service'];
  $destination = $_POST['edit_destination'];
  $fare = $_POST['edit_fare'];

  $stmt = $conn->prepare("UPDATE routes SET car_model=?, service_type=?, destination=?, fare=? WHERE id=?");
  $stmt->bind_param("sssdi", $route_model, $service_type, $destination, $fare, $route_id);
  $stmt->execute();
  $stmt->close();
  header("Location: admin.php");
  exit;
}

// Handle delete route
if (isset($_GET['delete_route'])) {
  $route_id = $_GET['delete_route'];
  $conn->query("DELETE FROM routes WHERE id = $route_id");
  header("Location: admin.php");
  exit;
}

// Fetch data
$cars = $conn->query("SELECT * FROM cars");
$routes = $conn->query("SELECT * FROM routes ORDER BY car_model, service_type");
?>

<!-- HTML starts below -->
<!DOCTYPE html>
<html>
<head>
  <title>Admin</title>
  <link rel="stylesheet" href="style.css">
  <style>
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
    img { max-width: 100px; }
    .container { padding: 20px; }

    /* Modal styling */
    #editModal, #overlay, #editRouteModal {
      display: none;
      position: fixed;
      z-index: 1000;
    }
    #editModal {
      top: 10%;
      left: 50%;
      transform: translateX(-50%);
      background: #fff;
      padding: 20px;
      border: 1px solid #ccc;
    }
    #editRouteModal {
      top: 15%;
      left: 50%;
      transform: translateX(-50%);
      background: #fff;
      padding: 20px;
      border: 1px solid #ccc;
      width: 300px;
    }
    #overlay {
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 999;
    }
  </style>
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

  <div class="container">
    <h1>Admin Dashboard</h1>

    <!-- Add Car Form -->
    <form method="POST" enctype="multipart/form-data">
      <h3>Add New Car</h3>
      <label>Model:</label>
      <input type="text" name="model" required>
      <label>Category:</label>
      <select name="category" required>
        <option value="sedan">Sedan</option>
        <option value="suv">SUV</option>
        <option value="van">Van</option>
      </select>
      <label>Description:</label>
      <textarea name="description" required></textarea>
      <select name="service_type" required>
        <option value="driver">With Driver</option>
        <option value="self">Self Drive</option>
      </select><br>
      <label>Price:</label>
      <input type="number" name="price" required>
      <label>Image:</label>
      <input type="file" name="image" accept="image/*" required>
      <input type="submit" value="Add Car">
    </form>

    <!-- Car Listings Table -->
    <h3>Current Car Listings</h3>
    <table>
      <tr>
        <th>ID</th>
        <th>Model</th>
        <th>Category</th>
        <th>Service</th>
        <th>Price</th>
        <th>Image</th>
        <th>Action</th>
      </tr>
      <?php
        $cars->data_seek(0);
        while ($car = $cars->fetch_assoc()):
      ?>
      <tr>
        <td><?= $car['id'] ?></td>
        <td><?= htmlspecialchars($car['model']) ?></td>
        <td><?= ucfirst(htmlspecialchars($car['category'])) ?></td>
        <td><?= $car['service_type'] === 'driver' ? 'With Driver' : 'Self Drive' ?></td>
        <td>₱<?= number_format($car['price'], 2) ?></td>
        <td><img src="<?= htmlspecialchars($car['image']) ?>" alt="Car Image"></td>
        <td>
          <button onclick='openEditModal(<?= json_encode($car) ?>)'>✏️ Edit</button> |
          <a href="admin.php?delete=<?= $car['id'] ?>" onclick="return confirm('Delete this car?')">🗑 Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>

    <!-- Edit Car Modal -->
    <div id="editModal">
      <h3>Edit Car</h3>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" id="editId">
        <label>Model:</label>
        <input type="text" name="model" id="editModel" required><br>
        <label>Category:</label>
        <input type="text" name="category" id="editCategory" required><br>
        <label>Service Type:</label>
        <select name="service_type" id="editService" required>
          <option value="driver">With Driver</option>
          <option value="self">Self Drive</option>
        </select><br>
        <label>Price:</label>
        <input type="number" step="0.01" name="price" id="editPrice" required><br>
        <label>Current Image:</label>
        <img id="editImagePreview" src="" style="max-width:100px;"><br>
        <label>Upload New Image:</label>
        <input type="file" name="new_image" accept="image/*"><br>
        <input type="hidden" name="current_image" id="editImage">
        <button type="submit" name="update">Update</button>
        <button type="button" onclick="closeEditModal()">Cancel</button>
      </form>
    </div>

    <!-- Route -->
    <h2>Route Management</h2>
    <form method="POST">
      <label>Car Model:</label>
      <select name="route_model" required>
        <option value="">-- Select Car --</option>
        <?php
          $carList = $conn->query("SELECT model FROM cars");
          while ($car = $carList->fetch_assoc()):
        ?>
          <option value="<?= htmlspecialchars($car['model']) ?>"><?= htmlspecialchars($car['model']) ?></option>
        <?php endwhile; ?>
      </select>
      <label>Service Type:</label>
      <select name="route_service" required>
        <option value="driver">With Driver</option>
        <option value="self">Self Drive</option>
      </select>
      <label>Destination:</label>
      <input type="text" name="destination" required>
      <label>Fare (₱):</label>
      <input type="number" name="fare" step="0.01" required>
      <input type="submit" name="add_route" value="Add Route">
    </form>

    <h3>Existing Routes</h3>
    <table>
      <tr>
        <th>ID</th>
        <th>Car Model</th>
        <th>Service Type</th>
        <th>Destination</th>
        <th>Fare (₱)</th>
        <th>Action</th>
      </tr>
      <?php while ($r = $routes->fetch_assoc()): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['car_model']) ?></td>
        <td><?= $r['service_type'] === 'driver' ? 'With Driver' : 'Self Drive' ?></td>
        <td><?= htmlspecialchars($r['destination']) ?></td>
        <td>₱<?= number_format($r['fare'], 2) ?></td>
        <td>
          <button onclick='openEditRouteModal(<?= json_encode($r) ?>)'> Edit</button> 
          <a href="admin.php?delete_route=<?= $r['id'] ?>" onclick="return confirm('Delete this route?')"> Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>

  <!-- Edit Route Modal -->
  <div id="editRouteModal">
    <h3>Edit Route</h3>
    <form method="POST">
      <input type="hidden" name="route_id" id="editRouteId">
      <label>Car Model:</label>
      <select name="edit_route_model" id="editRouteModel" required>
        <option value="">-- Select Car --</option>
        <?php
          // Fetch again inside modal for select options
          $carList2 = $conn->query("SELECT model FROM cars");
          while ($car2 = $carList2->fetch_assoc()):
        ?>
          <option value="<?= htmlspecialchars($car2['model']) ?>"><?= htmlspecialchars($car2['model']) ?></option>
        <?php endwhile; ?>
      </select>
      <label>Service Type:</label>
      <select name="edit_route_service" id="editRouteService" required>
        <option value="driver">With Driver</option>
        <option value="self">Self Drive</option>
      </select>
      <label>Destination:</label>
      <input type="text" name="edit_destination" id="editDestination" required>
      <label>Fare (₱):</label>
      <input type="number" name="edit_fare" step="0.01" id="editFare" required>
      <br><br>
      <button type="submit" name="update_route">Update Route</button>
      <button type="button" onclick="closeEditRouteModal()">Cancel</button>
    </form>
  </div>

  <div id="overlay" onclick="closeEditModal(); closeEditRouteModal();"></div>

  <script>
    // Car edit modal
    function openEditModal(car) {
      document.getElementById('editId').value = car.id;
      document.getElementById('editModel').value = car.model;
      document.getElementById('editCategory').value = car.category;
      document.getElementById('editService').value = car.service_type;
      document.getElementById('editPrice').value = car.price;
      document.getElementById('editImage').value = car.image;
      document.getElementById('editImagePreview').src = car.image;

      document.getElementById('editModal').style.display = 'block';
      document.getElementById('overlay').style.display = 'block';
    }
    function closeEditModal() {
      document.getElementById('editModal').style.display = 'none';
      document.getElementById('overlay').style.display = 'none';
    }

    // Route edit modal
    function openEditRouteModal(route) {
      document.getElementById('editRouteId').value = route.id;
      document.getElementById('editRouteModel').value = route.car_model;
      document.getElementById('editRouteService').value = route.service_type;
      document.getElementById('editDestination').value = route.destination;
      document.getElementById('editFare').value = route.fare;

      document.getElementById('editRouteModal').style.display = 'block';
      document.getElementById('overlay').style.display = 'block';
    }
    function closeEditRouteModal() {
      document.getElementById('editRouteModal').style.display = 'none';
      document.getElementById('overlay').style.display = 'none';
    }
  </script>
</body>
</html>
