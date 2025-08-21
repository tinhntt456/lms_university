<?php
// instructor/grade_quiz.php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
$db = new Database();
$pdo = $db->getConnection();
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
if ($quiz_id <= 0) {
    echo "Invalid quiz ID.";
    exit();
}
// Get quiz information
$stmt = $pdo->prepare("SELECT q.*, c.title AS course_title FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$quiz) {
    echo "Quiz not found.";
    exit();
}
// Get quiz attempts
$stmt = $pdo->prepare("SELECT qa.*, u.first_name, u.last_name FROM quiz_attempts qa JOIN users u ON qa.student_id = u.id WHERE qa.quiz_id = ? ORDER BY qa.attempt_number DESC");
$stmt->execute([$quiz_id]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Handle grading
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_attempt_id'])) {
    $attempt_id = intval($_POST['grade_attempt_id']);
    $score = floatval($_POST['score']);
    $feedback = trim($_POST['feedback']);
    $stmt = $pdo->prepare("UPDATE quiz_attempts SET score = ?, feedback = ?, completed_at = NOW() WHERE id = ?");
    $stmt->execute([$score, $feedback, $attempt_id]);
    header("Location: grade_quiz.php?quiz_id=" . $quiz_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Quiz - University LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/instructor_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/instructor_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-marker"></i> Grade Quiz: <?= htmlspecialchars($quiz['title']) ?>
                    </h1>
                    <span class="text-muted">Course: <?= htmlspecialchars($quiz['course_title']) ?></span>
                </div>
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-users"></i> Student Attempts
                    </div>
                    <div class="card-body">
                        <?php if (empty($attempts)): ?>
                            <div class="text-center py-4">
                                <p class="text-muted">No student attempts found for this quiz.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Student</th>
                                            <th>Attempt</th>
                                            <th>Score</th>
                                            <th>Feedback</th>
                                            <th>Completed At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($attempts as $idx => $a): ?>
                                            <tr>
                                                <td><?= $idx + 1 ?></td>
                                                <td><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></td>
                                                <td><?= $a['attempt_number'] ?></td>
                                                <td><?= $a['score'] ?></td>
                                                <td><?= isset($a['feedback']) ? htmlspecialchars($a['feedback']) : '' ?></td>
                                                <td><?= $a['completed_at'] ? htmlspecialchars(date('Y-m-d H:i', strtotime($a['completed_at']))) : '-' ?></td>
                                                <td>
                                                    <form method="post" class="d-flex flex-column gap-2">
                                                        <input type="hidden" name="grade_attempt_id" value="<?= $a['id'] ?>">
                                                        <input type="number" step="0.01" name="score" class="form-control mb-1" value="<?= $a['score'] ?>" placeholder="Score" required>
                                                        <input type="text" name="feedback" class="form-control mb-1" value="<?= isset($a['feedback']) ? htmlspecialchars($a['feedback']) : '' ?>" placeholder="Feedback">
                                                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Save</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
