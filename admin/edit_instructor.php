<?php
// admin/edit_instructor.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

$error = '';
$success = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: instructors.php');
    exit();
}
$stmt = $conn->prepare('SELECT * FROM users WHERE id = ? AND role = "instructor"');
$stmt->execute([$id]);
$instructor = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$instructor) {
    header('Location: instructors.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    if ($username && $email && $first_name && $last_name) {
        $stmt = $conn->prepare('UPDATE users SET username=?, email=?, first_name=?, last_name=? WHERE id=?');
        if ($stmt->execute([$username, $email, $first_name, $last_name, $id])) {
            $success = 'Instructor updated successfully!';
            $stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $instructor = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = 'Error updating instructor.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Edit Instructor - University LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/admin_navbar.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">Edit Instructor</div>
                <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($instructor['username']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($instructor['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($instructor['first_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($instructor['last_name']) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Instructor</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
