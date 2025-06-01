<?php
include 'db.php';
$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $confirm = $_POST['confirmPassword'];
  $license = $_POST['license'];
  $agency = $_POST['agency'];

  if ($password !== $confirm) {
    $error = "Passwords do not match.";
  } else {
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
      $error = "Email is already registered.";
    } else {
      $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, drivers_license, agency_code) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("sssss", $name, $email, $hashed, $license, $agency);
      if ($stmt->execute()) {
        $success = "Registration successful! <a href='login.php'>Login now</a>.";
      } else {
        $error = "Error: " . $stmt->error;
      }
      $stmt->close();
    }
    $check->close();
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Register - PaNorth Car Rental</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h2>Create an Account</h2>
    <?php if ($error) echo "<p class='error-msg'>$error</p>"; ?>
    <?php if ($success) echo "<p class='success-msg'>$success</p>"; ?>

    <form method="POST">
      <label>Full Name:</label>
      <input type="text" name="name" required>

      <label>Email:</label>
      <input type="email" name="email" required>

      <label>Password:</label>
      <input type="password" name="password" required>

      <label>Confirm Password:</label>
      <input type="password" name="confirmPassword" required>

      <label>Driver’s License #:</label>
      <input type="text" name="license" required>

      <label>Agency Code #:</label>
      <input type="text" name="agency" required>

      <input type="submit" value="Register">
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
  </div>
</body>
</html>
