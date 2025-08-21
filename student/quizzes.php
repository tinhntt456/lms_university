<?php
// student/quizzes.php

require_once '../config/database.php';
requireLogin();
if (!hasRole('student')) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$student_id = $_SESSION['user_id'];

// Handle quiz attempt (dummy logic, you can expand to real quiz logic)
if (isset($_POST['attempt_quiz_id'])) {
    $quiz_id = intval($_POST['attempt_quiz_id']);
    // Check if already attempted
    $check = $db->prepare("SELECT * FROM quiz_attempts WHERE quiz_id = ? AND student_id = ?");
    $check->execute([$quiz_id, $student_id]);
    if (!$check->fetch()) {
        $attempt = $db->prepare("INSERT INTO quiz_attempts (quiz_id, student_id, attempt_number, score, total_points) VALUES (?, ?, 1, 0, 20)");
        $attempt->execute([$quiz_id, $student_id]);
    $_SESSION['quiz_in_progress'] = $quiz_id;
        header('Location: quizzes.php');
        exit();
    }
}

// Get quizzes using PDO

$sql = "SELECT q.*, c.title AS course_title, qa.attempt_number, qa.score, qa.completed_at, qa.feedback
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        JOIN quizzes q ON q.course_id = c.id
        LEFT JOIN (
            SELECT quiz_id, student_id, MAX(attempt_number) AS attempt_number, score, completed_at, feedback
            FROM quiz_attempts
            WHERE student_id = ?
            GROUP BY quiz_id, student_id
        ) qa ON qa.quiz_id = q.id AND qa.student_id = ?
        WHERE e.student_id = ?
        ORDER BY q.due_date DESC";
$stmt = $db->prepare($sql);
$stmt->execute([$student_id, $student_id, $student_id]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle quiz submission
if (isset($_POST['submit_quiz_id'])) {
    $quiz_id = intval($_POST['submit_quiz_id']);
    $update = $db->prepare("UPDATE quiz_attempts SET completed_at = NOW() WHERE quiz_id = ? AND student_id = ?");
    $update->execute([$quiz_id, $student_id]);
    unset($_SESSION['quiz_in_progress']);
    $_SESSION['quiz_submit_success'] = true;
    header('Location: quizzes.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Quizzes - Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .quizzes-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .quizzes-table th, .quizzes-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .quizzes-table th {
            background-color: #f2f2f2;
        }
        .status-completed { color: #28a745; }
        .status-not { color: #dc3545; }
    </style>
</head>
<body>
    <?php include '../includes/student_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/student_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-question-circle"></i> My Quizzes
                    </h1>
                </div>
                    <?php if (!empty($_SESSION['quiz_submit_success'])): ?>
                        <div class="alert alert-success">Submission successful!</div>
                        <?php unset($_SESSION['quiz_submit_success']); ?>
                    <?php endif; ?>
                <table class="quizzes-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Course</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Feedback</th>
                            <th>Last Attempt</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($quizzes) > 0): ?>
                        <?php foreach ($quizzes as $q): ?>
                            <tr>
                                <td><?= htmlspecialchars($q['title']) ?></td>
                                <td><?= htmlspecialchars($q['course_title']) ?></td>
                                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($q['due_date']))) ?></td>
                                <td class="<?= $q['completed_at'] ? 'status-completed' : 'status-not' ?>">
                                    <?= $q['completed_at'] ? 'Completed' : 'Not Attempted' ?>
                                </td>
                                <td><?= ($q['score'] !== null && $q['feedback']) ? htmlspecialchars($q['score']) : '-' ?></td>
                                <td><?= !empty($q['feedback']) ? htmlspecialchars($q['feedback']) : '-' ?></td>
                                <td><?= $q['completed_at'] ? htmlspecialchars(date('Y-m-d H:i', strtotime($q['completed_at']))) : '-' ?></td>
                                <td>
                                        <?php
                                        // If there is an attempt but not completed, show Submit button
                                        if ($q['attempt_number'] && !$q['completed_at']) {
                                            ?>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="submit_quiz_id" value="<?= $q['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-upload"></i> Submit</button>
                                            </form>
                                        <?php
                                        } elseif (!$q['attempt_number']) {
                                            ?>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="attempt_quiz_id" value="<?= $q['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-play"></i> Start</button>
                                            </form>
                                        <?php
                                        } else {
                                            ?>
                                            <span class="text-success"><i class="fas fa-check"></i></span>
                                        <?php
                                        }
                                        ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No quizzes found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>
</body>
</html>
