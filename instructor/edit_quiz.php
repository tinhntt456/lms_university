<?php
// instructor/edit_quiz.php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
$instructor_id = $_SESSION['user_id'];
if (!isset($_GET['id'])) {
    header('Location: quizzes.php');
    exit();
}
$quiz_id = $_GET['id'];
// Initialize database connection
$database = new Database();
$db = $database->getConnection();
// Fetch quiz
$stmt = $db->prepare("SELECT * FROM quizzes WHERE id = ? AND created_by = ?");
$stmt->execute([$quiz_id, $instructor_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$quiz) {
    header('Location: quizzes.php');
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
    $time_limit = $_POST['time_limit'];
    $max_attempts = $_POST['max_attempts'];
    if ($title && $course_id && $due_date && $time_limit && $max_attempts) {
        $stmt = $db->prepare("UPDATE quizzes SET course_id=?, title=?, description=?, time_limit=?, max_attempts=?, due_date=? WHERE id=? AND created_by=?");
        if ($stmt->execute([$course_id, $title, $description, $time_limit, $max_attempts, $due_date, $quiz_id, $instructor_id])) {
            $success = 'Quiz updated successfully!';
            // Refresh quiz data
            $stmt = $db->prepare("SELECT * FROM quizzes WHERE id = ? AND created_by = ?");
            $stmt->execute([$quiz_id, $instructor_id]);
            $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = 'Error updating quiz.';
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
    <title>Edit Quiz</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Edit Quiz</h2>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($quiz['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description"><?= htmlspecialchars($quiz['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="course_id" class="form-label">Course</label>
            <select class="form-select" id="course_id" name="course_id" required>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id'] ?>" <?= $quiz['course_id'] == $course['id'] ? 'selected' : '' ?>><?= htmlspecialchars($course['title']) ?> (<?= htmlspecialchars($course['course_code']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="due_date" class="form-label">Due Date</label>
            <input type="datetime-local" class="form-control" id="due_date" name="due_date" value="<?= date('Y-m-d\TH:i', strtotime($quiz['due_date'])) ?>" required>
        </div>
        <div class="mb-3">
            <label for="time_limit" class="form-label">Time Limit (minutes)</label>
            <input type="number" class="form-control" id="time_limit" name="time_limit" min="1" value="<?= $quiz['time_limit'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="max_attempts" class="form-label">Max Attempts</label>
            <input type="number" class="form-control" id="max_attempts" name="max_attempts" min="1" value="<?= $quiz['max_attempts'] ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="quizzes.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
