<?php
// instructor/quizzes.php
session_start();
require_once '../config/database.php';
// Initialize PDO connection
$db = new Database();
$pdo = $db->getConnection();
require_once '../includes/instructor_navbar.php';
require_once '../includes/instructor_sidebar.php';

// Check if user is logged in and is instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

$instructor_id = $_SESSION['user_id'];

// Fetch quizzes created by this instructor
$sql = "SELECT q.*, c.title AS course_title, c.course_code FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.created_by = :instructor_id ORDER BY q.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['instructor_id' => $instructor_id]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Quizzes - University LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/instructor_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-clipboard-question"></i> My Quizzes
                    </h1>
                    <a href="create_quiz.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create Quiz</a>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-clipboard-question"></i> Quiz List
                            </div>
                            <div class="card-body">
                                <?php if (empty($quizzes)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-clipboard-question fa-3x text-gray-300 mb-3"></i>
                                        <p class="text-muted">No quizzes found.</p>
                                        <a href="create_quiz.php" class="btn btn-primary">Create First Quiz</a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>STT</th>
                                                    <th>Quiz Title</th>
                                                    <th>Course</th>
                                                    <th>Due Date</th>
                                                    <th>Time (minutes)</th>
                                                    <th>Max Attempts</th>
                                                    <th>Created At</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($quizzes as $index => $quiz): ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td><strong><?= htmlspecialchars($quiz['title']) ?></strong></td>
                                                        <td><?= htmlspecialchars($quiz['course_title']) ?> (<?= htmlspecialchars($quiz['course_code']) ?>)</td>
                                                        <td><?= date('d/m/Y H:i', strtotime($quiz['due_date'])) ?></td>
                                                        <td><?= $quiz['time_limit'] ?></td>
                                                        <td><?= $quiz['max_attempts'] ?></td>
                                                        <td><?= date('d/m/Y', strtotime($quiz['created_at'])) ?></td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="view_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-sm btn-outline-primary" title="Xem"><i class="fas fa-eye"></i></a>
                                                                <a href="edit_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Sửa"><i class="fas fa-edit"></i></a>
                                                                <a href="quiz_attempts.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-sm btn-outline-success" title="Attempts"><i class="fas fa-file-alt"></i></a>
                                                                <a href="grade_quiz.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-sm btn-outline-info" title="Grade"><i class="fas fa-marker"></i></a>
                                                                <a href="delete_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-sm btn-outline-danger" title="Xoá" onclick="return confirm('Are you sure you want to delete this quiz?');"><i class="fas fa-trash"></i></a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
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
</body>
</html>
