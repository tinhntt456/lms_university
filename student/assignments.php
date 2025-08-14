<?php
// student/assignments.php

require_once '../config/database.php';
requireLogin();
if (!hasRole('student')) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$student_id = $_SESSION['user_id'];

// Handle assignment submission (dummy logic, you can expand to file upload)
if (isset($_POST['submit_assignment_id'])) {
    $assignment_id = intval($_POST['submit_assignment_id']);
    // Check if already submitted
    $check = $db->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
    $check->execute([$assignment_id, $student_id]);
    if (!$check->fetch()) {
        $submit = $db->prepare("INSERT INTO submissions (assignment_id, student_id, file_url) VALUES (?, ?, 'uploads/dummy.pdf')");
        $submit->execute([$assignment_id, $student_id]);
        header('Location: assignments.php');
        exit();
    }
}

// Get assignments using PDO
$sql = "SELECT a.*, c.title AS course_title, s.submitted_at, s.grade AS submission_grade, s.feedback
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        JOIN assignments a ON a.course_id = c.id
        LEFT JOIN submissions s ON s.assignment_id = a.id AND s.student_id = ?
        WHERE e.student_id = ?
        ORDER BY a.due_date DESC";
$stmt = $db->prepare($sql);
$stmt->execute([$student_id, $student_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments - Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .assignments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .assignments-table th, .assignments-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .assignments-table th {
            background-color: #f2f2f2;
        }
        .status-submitted { color: #28a745; }
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
                        <i class="fas fa-tasks"></i> My Assignments
                    </h1>
                </div>
                <table class="assignments-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Course</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Grade</th>
                            <th>Feedback</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($assignments) > 0): ?>
                        <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars($a['title']) ?></td>
                                <td><?= htmlspecialchars($a['course_title']) ?></td>
                                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($a['due_date']))) ?></td>
                                <td class="<?= $a['submitted_at'] ? 'status-submitted' : 'status-not' ?>">
                                    <?= $a['submitted_at'] ? 'Submitted' : 'Not Submitted' ?>
                                </td>
                                <td><?= $a['submission_grade'] !== null ? htmlspecialchars($a['submission_grade']) : '-' ?></td>
                                <td><?= $a['feedback'] ? htmlspecialchars($a['feedback']) : '-' ?></td>
                                <td>
                                    <?php if (!$a['submitted_at']): ?>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="submit_assignment_id" value="<?= $a['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload"></i> Submit</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-success"><i class="fas fa-check"></i></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No assignments found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>
</body>
</html>
