<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
$database = new Database();
$db = $database->getConnection();
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$stmt = $db->prepare('SELECT q.*, c.title AS course_title FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ? AND q.created_by = ?');
$stmt->execute([$quiz_id, $_SESSION['user_id']]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$quiz) {
    echo '<div class="alert alert-danger">Quiz not found or access denied.</div>';
    exit();
}

// Handle score update (if implemented)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attempt_id'], $_POST['score'])) {
    $attempt_id = $_POST['attempt_id'];
    $score = $_POST['score'];
    // Update score in quiz_attempts
    $update_stmt = $db->prepare('UPDATE quiz_attempts SET score = ? WHERE id = ?');
    $update_stmt->execute([$score, $attempt_id]);
    // Get student_id, quiz_id from attempt
    $info_stmt = $db->prepare('SELECT student_id, quiz_id FROM quiz_attempts WHERE id = ?');
    $info_stmt->execute([$attempt_id]);
    $info = $info_stmt->fetch(PDO::FETCH_ASSOC);
    if ($info) {
        $student_id = $info['student_id'];
        $quiz_id = $info['quiz_id'];
        // Get course_id from quiz
        $course_stmt = $db->prepare('SELECT course_id FROM quizzes WHERE id = ?');
        $course_stmt->execute([$quiz_id]);
        $course_id = $course_stmt->fetchColumn();
        // Check if grade exists
        $check_stmt = $db->prepare('SELECT id FROM grades WHERE student_id = ? AND quiz_id = ?');
        $check_stmt->execute([$student_id, $quiz_id]);
        $grade_id = $check_stmt->fetchColumn();
        if ($grade_id) {
            $update_grade = $db->prepare('UPDATE grades SET grade = ?, graded_at = NOW() WHERE id = ?');
            $update_grade->execute([$score, $grade_id]);
        } else {
            $insert_grade = $db->prepare('INSERT INTO grades (student_id, course_id, quiz_id, grade, graded_at) VALUES (?, ?, ?, ?, NOW())');
            $insert_grade->execute([$student_id, $course_id, $quiz_id, $score]);
        }
    }
}

$attempts_stmt = $db->prepare('SELECT a.*, u.first_name, u.last_name FROM quiz_attempts a JOIN users u ON a.student_id = u.id WHERE a.quiz_id = ? ORDER BY a.completed_at DESC');
$attempts_stmt->execute([$quiz_id]);
$attempts = $attempts_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Attempts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/instructor_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/instructor_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <h1 class="h2 mt-3 mb-3"><i class="fas fa-file-alt"></i> Quiz Attempts for <?= htmlspecialchars($quiz['title']) ?></h1>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">Attempts</div>
                    <div class="card-body">
                        <?php if (count($attempts) > 0): ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Score</th>
                                    <th>Completed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attempts as $attempt): ?>
                                <tr>
                                    <td><?= htmlspecialchars($attempt['first_name'] . ' ' . $attempt['last_name']) ?></td>
                                    <td><?= htmlspecialchars($attempt['score']) ?></td>
                                    <td><?= htmlspecialchars($attempt['completed_at']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p>No attempts found for this quiz.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
