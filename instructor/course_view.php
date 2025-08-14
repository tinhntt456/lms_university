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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/instructor_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/instructor_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <h1 class="h2 mt-3 mb-3"><i class="fas fa-book"></i> <?= htmlspecialchars($course['title']) ?></h1>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Course Information</div>
                    <div class="card-body">
                        <p><strong>Code:</strong> <?= htmlspecialchars($course['course_code']) ?></p>
                        <p><strong>Description:</strong> <?= htmlspecialchars($course['description']) ?></p>
                        <p><strong>Semester:</strong> <?= htmlspecialchars($course['semester']) ?></p>
                        <p><strong>Year:</strong> <?= htmlspecialchars($course['year']) ?></p>
                        <p><strong>Credits:</strong> <?= htmlspecialchars($course['credits']) ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($course['status']) ?></p>
                        <p><strong>Max Students:</strong> <?= htmlspecialchars($course['max_students']) ?></p>
                    </div>
                </div>
                <a href="course_edit.php?id=<?= $course_id ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit Course</a>
                <a href="course_analytics.php?id=<?= $course_id ?>" class="btn btn-info"><i class="fas fa-chart-bar"></i> Analytics</a>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
