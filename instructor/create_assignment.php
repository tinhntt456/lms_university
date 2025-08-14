<?php
session_start();
require_once '../config/database.php';
// Initialize assignment count
$assignment_count = 0;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
  header("Location: ../auth/login.php");
  exit();
}

$instructor_id = $_SESSION['user_id'];
$message = '';

// Get list of courses the instructor owns
$database = new Database();
$db = $database->getConnection();
$courses_stmt = $db->prepare("SELECT id, title FROM courses WHERE instructor_id = ?");
$courses_stmt->execute([$instructor_id]);
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $course_id = $_POST['course_id'];
  $title = trim($_POST['title']);
  // Get the current assignment count
  $count_stmt = $db->prepare("SELECT COUNT(*) FROM assignments WHERE course_id IN (SELECT id FROM courses WHERE instructor_id = ?)");
  $count_stmt->execute([$instructor_id]);
  $assignment_count = $count_stmt->fetchColumn();
  $description = trim($_POST['description']);
  $due_date = $_POST['due_date'];

  $stmt = $db->prepare("INSERT INTO assignments (course_id, title, description, due_date, created_by) VALUES (?, ?, ?, ?, ?)");
  if ($stmt->execute([$course_id, $title, $description, $due_date, $instructor_id])) {
    $message = "✅ Assignment created successfully.";
  } else {
    $message = "❌ Failed to create assignment.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Assignment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body class="bg-gray-50 p-6">
  <?php include '../includes/instructor_navbar.php'; ?>
  <div class="container-fluid">
    <div class="row">
      <?php include '../includes/instructor_sidebar.php'; ?>
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2">
            <i class="fas fa-tasks"></i> Create New Assignment
          </h1>
                  <?php if (strpos($message, '✅') !== false): ?>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                      <i class="fas fa-check-circle me-2"></i>
                      <?= $message ?>
                    </div>
                  <?php else: ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                      <i class="fas fa-exclamation-circle me-2"></i>
                      <?= $message ?>
                    </div>
                  <?php endif; ?>
        <div class="row mb-4">
          <div class="col-12">
            <div class="card shadow">
              <div class="card-header bg-success text-white">
                <i class="fas fa-tasks"></i> Assignment Information
              </div>
              <div class="card-body">
                <?php if ($message): ?>
                  <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>
                <form method="POST">
                  <div class="mb-3">
                    <label for="course_id" class="form-label">Course *</label>
                    <select name="course_id" id="course_id" required class="form-select">
                      <?php foreach ($courses as $row): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" name="title" id="title" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="4" class="form-control"></textarea>
                  </div>
                  <div class="mb-3">
                    <label for="due_date" class="form-label">Due Date *</label>
                    <input type="date" name="due_date" id="due_date" class="form-control" required>
                  </div>
                  <div class="text-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Create Assignment</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>