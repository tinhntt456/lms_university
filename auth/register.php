<?php
session_start();
require_once '../config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = trim($_POST['full_name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];
  $role = $_POST['role'];

  if ($password !== $confirm_password) {
    $message = "❌ Passwords do not match.";
  } else {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hashed, $role);
    
    if ($stmt->execute()) {
      $message = "✅ Registration successful. <a href='login.php' class='text-blue-500 underline'>Login here</a>";
    } else {
      $message = "❌ Registration failed. Email may already be in use.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Create an Account</h2>

    <?php if ($message): ?>
      <p class="mb-4 text-sm text-red-600"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <input type="text" name="full_name" placeholder="Full Name" required class="w-full px-3 py-2 border rounded">
      <input type="email" name="email" placeholder="Email" required class="w-full px-3 py-2 border rounded">
      <input type="password" name="password" placeholder="Password" required class="w-full px-3 py-2 border rounded">
      <input type="password" name="confirm_password" placeholder="Confirm Password" required class="w-full px-3 py-2 border rounded">
      
      <select name="role" required class="w-full px-3 py-2 border rounded">
        <option value="student">Student</option>
        <option value="instructor">Instructor</option>
      </select>

      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Register</button>
    </form>

    <p class="mt-4 text-sm text-center">Already have an account? <a href="login.php" class="text-blue-500 underline">Login</a></p>
  </div>

</body>
</html>
