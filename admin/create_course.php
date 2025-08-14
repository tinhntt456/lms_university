<?php
require_once '../config/database.php';
requireLogin();
if (!hasRole('admin')) {
  header('Location: ../auth/login.php');
  exit();
}
$database = new Database();
$conn = $database->getConnection();
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $title = trim($_POST['title']);
  $description = trim($_POST['description']);
  $instructor_id = intval($_POST['instructor_id']);
  $course_code = trim($_POST['course_code']);
  $credits = intval($_POST['credits']);
  $semester = trim($_POST['semester']);
  $year = intval($_POST['year']);
  $max_students = intval($_POST['max_students']);
  $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';
  $stmt = $conn->prepare("INSERT INTO courses (title, description, instructor_id, course_code, credits, semester, year, max_students, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([$title, $description, $instructor_id, $course_code, $credits, $semester, $year, $max_students, $status]);
  if ($stmt->rowCount() > 0) {
    $message = "✅ Add course successful.";
  } else {
    $message = "❌ Add course failed.";
  }
}

// Get instructor list
$instructors = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'instructor'");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thêm khoá học mới - University LMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
  <?php include '../includes/admin_navbar.php'; ?>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card shadow">
          <div class="card-header bg-success text-white">
            <i class="fas fa-plus"></i> Add course
          </div>
          <div class="card-body">
            <?php if ($message): ?>
              <div class="alert alert-info"> <?= $message ?> </div>
            <?php endif; ?>
            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Course name</label>
                <input type="text" name="title" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" required></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Instructor</label>
                <select name="instructor_id" class="form-control" required>
                  <option value="">-- Select instructor --</option>
                  <?php while ($ins = $instructors->fetch()): ?>
                    <option value="<?= $ins['id'] ?>"><?= htmlspecialchars($ins['last_name'] . ' ' . $ins['first_name']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Course code</label>
                <input type="text" name="course_code" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Credits</label>
                <input type="number" name="credits" class="form-control" value="3" min="1" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Semester</label>
                <input type="text" name="semester" class="form-control">
              </div>
              <div class="mb-3">
                <label class="form-label">Year</label>
                <input type="number" name="year" class="form-control" value="<?= date('Y') ?>" min="2000" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Maximum number of students</label>
                <input type="number" name="max_students" class="form-control" value="50" min="1" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
              <button type="submit" class="btn btn-success w-100">Create course</button>
            </form>
            <div class="mt-3 text-center">
              <a href="courses.php" class="btn btn-link">← Back to course list</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
