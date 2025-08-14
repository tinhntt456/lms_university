<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
$database = new Database();
$db = $database->getConnection();
$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $db->prepare('SELECT q.*, c.title AS course_title FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ? AND q.created_by = ?');
$stmt->execute([$quiz_id, $_SESSION['user_id']]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$quiz) {
    echo '<div class="alert alert-danger">Quiz not found or access denied.</div>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/instructor_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/instructor_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <h1 class="h2 mt-3 mb-3"><i class="fas fa-question-circle"></i> <?= htmlspecialchars($quiz['title']) ?></h1>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Quiz Information</div>
                    <div class="card-body">
                        <p><strong>Course:</strong> <?= htmlspecialchars($quiz['course_title']) ?></p>
                        <p><strong>Description:</strong> <?= htmlspecialchars($quiz['description']) ?></p>
                        <p><strong>Time Limit:</strong> <?= htmlspecialchars($quiz['time_limit']) ?> minutes</p>
                        <p><strong>Max Attempts:</strong> <?= htmlspecialchars($quiz['max_attempts']) ?></p>
                        <p><strong>Due Date:</strong> <?= htmlspecialchars($quiz['due_date']) ?></p>
                    </div>
                </div>
                <a href="edit_quiz.php?id=<?= $quiz_id ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit Quiz</a>
                <a href="submissions.php?quiz_id=<?= $quiz_id ?>" class="btn btn-info"><i class="fas fa-file-alt"></i> View Submissions</a>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
