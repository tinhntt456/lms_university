<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
$database = new Database();
$db = $database->getConnection();
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $db->prepare('SELECT * FROM courses WHERE id = ? AND instructor_id = ?');
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) {
    echo '<div class="alert alert-danger">Course not found or access denied.</div>';
    exit();
}
// Example analytics: student count, assignment count
$student_stmt = $db->prepare('SELECT COUNT(DISTINCT student_id) FROM enrollments WHERE course_id = ? AND status = "enrolled"');
$student_stmt->execute([$course_id]);
$student_count = $student_stmt->fetchColumn();
$assignment_stmt = $db->prepare('SELECT COUNT(*) FROM assignments WHERE course_id = ?');
$assignment_stmt->execute([$course_id]);
$assignment_count = $assignment_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/instructor_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/instructor_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <h1 class="h2 mt-3 mb-3"><i class="fas fa-chart-bar"></i> Analytics for <?= htmlspecialchars($course['title']) ?></h1>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-header bg-info text-white">Student Enrollment</div>
                            <div class="card-body">
                                <h4><?= $student_count ?> students enrolled</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-header bg-success text-white">Assignments</div>
                            <div class="card-body">
                                <h4><?= $assignment_count ?> assignments created</h4>
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
