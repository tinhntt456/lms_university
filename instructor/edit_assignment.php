<?php
// instructor/edit_assignment.php
require_once '../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
$instructor_id = $_SESSION['user_id'];
if (!isset($_GET['id'])) {
    header('Location: assignments.php');
    exit();
}
$assignment_id = $_GET['id'];
// Initialize database connection
$database = new Database();
$db = $database->getConnection();
// Fetch assignment
$stmt = $db->prepare("SELECT * FROM assignments WHERE id = ? AND created_by = ?");
$stmt->execute([$assignment_id, $instructor_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$assignment) {
    header('Location: assignments.php');
    exit();
}
// Fetch courses
$stmt = $db->prepare("SELECT id, title, course_code FROM courses WHERE instructor_id = ?");
$stmt->execute([$instructor_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $course_id = $_POST['course_id'];
    $due_date = $_POST['due_date'];
    $max_points = $_POST['max_points'];
    if ($title && $course_id && $due_date && $max_points) {
        $stmt = $db->prepare("UPDATE assignments SET course_id=?, title=?, description=?, due_date=?, max_points=? WHERE id=? AND created_by=?");
        if ($stmt->execute([$course_id, $title, $description, $due_date, $max_points, $assignment_id, $instructor_id])) {
            $success = 'Assignment updated successfully!';
            // Refresh assignment data
            $stmt = $db->prepare("SELECT * FROM assignments WHERE id = ? AND created_by = ?");
            $stmt->execute([$assignment_id, $instructor_id]);
            $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = 'Error updating assignment.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Assignment</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Edit Assignment</h2>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($assignment['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description"><?= htmlspecialchars($assignment['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="course_id" class="form-label">Course</label>
            <select class="form-select" id="course_id" name="course_id" required>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id'] ?>" <?= $assignment['course_id'] == $course['id'] ? 'selected' : '' ?>><?= htmlspecialchars($course['title']) ?> (<?= htmlspecialchars($course['course_code']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="due_date" class="form-label">Due Date</label>
            <input type="datetime-local" class="form-control" id="due_date" name="due_date" value="<?= date('Y-m-d\TH:i', strtotime($assignment['due_date'])) ?>" required>
        </div>
        <div class="mb-3">
            <label for="max_points" class="form-label">Max Points</label>
            <input type="number" step="0.01" class="form-control" id="max_points" name="max_points" value="<?= $assignment['max_points'] ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="assignments.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
