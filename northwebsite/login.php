<?php
include 'db.php';
session_start();
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $name, $hashed, $role);
    $stmt->fetch();

    if (password_verify($password, $hashed)) {
      $_SESSION['user_id'] = $id;
      $_SESSION['name'] = $name;
      $_SESSION['role'] = $role;

      // Redirect based on user role
      if ($role === 'admin') {
        header("Location: admin.php");
      } else {
        header("Location: dashboard.php");
      }
      exit;
    } else {
      $error = "Invalid password.";
    }
  } else {
    $error = "No user found.";
  }

  $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login - PaNorth Car Rental</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h2>Login</h2>
    <?php if ($error) echo "<p class='error-msg'>$error</p>"; ?>
    <form method="POST">
      <label>Email:</label>
      <input type="email" name="email" required>

      <label>Password:</label>
      <input type="password" name="password" required>

      <input type="submit" value="Login">
    </form>

    <p>No account? <a href="register.php">Register here</a></p>
  </div>
</body>
</html>
