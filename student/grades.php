<?php
// student/grades.php

require_once '../config/database.php';
requireLogin();
if (!hasRole('student')) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$student_id = $_SESSION['user_id'];

// Get grades using PDO
$sql = "SELECT g.*, c.title AS course_title, a.title AS assignment_title, q.title AS quiz_title
        FROM grades g
        JOIN courses c ON g.course_id = c.id
        LEFT JOIN assignments a ON g.assignment_id = a.id
        LEFT JOIN quizzes q ON g.quiz_id = q.id
        WHERE g.student_id = ?
        ORDER BY g.graded_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute([$student_id]);
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades - Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .grades-table th, .grades-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .grades-table th {
            background-color: #f2f2f2;
        }
        .feedback {
            max-width: 300px;
            word-break: break-word;
        }
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
                        <i class="fas fa-graduation-cap"></i> My Grades
                    </h1>
                </div>
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Assignment/Quiz</th>
                            <th>Grade</th>
                            <th>Feedback</th>
                            <th>Graded At</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($grades) > 0): ?>
                        <?php foreach ($grades as $grade): ?>
                            <tr>
                                <td><?= htmlspecialchars($grade['course_title']) ?></td>
                                <td>
                                    <?php
                                    if ($grade['assignment_id']) {
                                        echo 'Assignment: ' . htmlspecialchars($grade['assignment_title']);
                                    } elseif ($grade['quiz_id']) {
                                        echo 'Quiz: ' . htmlspecialchars($grade['quiz_title']);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($grade['grade']) ?></td>
                                <td class="feedback"><?= nl2br(htmlspecialchars($grade['feedback'])) ?></td>
                                <td><?= htmlspecialchars($grade['graded_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No grades found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>
</body>
</html>
