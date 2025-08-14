<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
$database = new Database();
$db = $database->getConnection();
$assignment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $db->prepare('SELECT a.*, c.title AS course_title FROM assignments a JOIN courses c ON a.course_id = c.id WHERE a.id = ? AND a.created_by = ?');
$stmt->execute([$assignment_id, $_SESSION['user_id']]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$assignment) {
    echo '<div class="alert alert-danger">Assignment not found or access denied.</div>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Assignment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/instructor_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/instructor_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <h1 class="h2 mt-3 mb-3"><i class="fas fa-tasks"></i> <?= htmlspecialchars($assignment['title']) ?></h1>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Assignment Information</div>
                    <div class="card-body">
                        <p><strong>Course:</strong> <?= htmlspecialchars($assignment['course_title']) ?></p>
                        <p><strong>Description:</strong> <?= htmlspecialchars($assignment['description']) ?></p>
                        <p><strong>Due Date:</strong> <?= htmlspecialchars($assignment['due_date']) ?></p>
                        <p><strong>Max Points:</strong> <?= htmlspecialchars($assignment['max_points']) ?></p>
                    </div>
                </div>
                <a href="edit_assignment.php?id=<?= $assignment_id ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit Assignment</a>
                <a href="submissions.php?assignment_id=<?= $assignment_id ?>" class="btn btn-info"><i class="fas fa-file-alt"></i> View Submissions</a>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
