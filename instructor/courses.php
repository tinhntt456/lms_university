<?php
require_once '../config/database.php';
requireLogin();
if (!hasRole('instructor')) {
    header('Location: ../auth/login.php');
    exit();
}
$database = new Database();
$conn = $database->getConnection();
// Fetch courses created by this instructor
$stmt = $conn->prepare("SELECT * FROM courses WHERE instructor_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - University LMS</title>
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
                        <i class="fas fa-book"></i> My Courses
                    </h1>
                    <a href="course_create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Course</a>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-book"></i> Course List
                            </div>
                            <div class="card-body">
                                <?php if (empty($courses)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-book fa-3x text-gray-300 mb-3"></i>
                                        <p class="text-muted">You have not created any courses yet.</p>
                                        <a href="course_create.php" class="btn btn-primary">Create Your First Course</a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Course Name</th>
                                                    <th>Course Code</th>
                                                    <th>Credits</th>
                                                    <th>Semester</th>
                                                    <th>Year</th>
                                                    <th>Status</th>
                                                    <th>Created At</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($courses as $course): ?>
                                                    <tr>
                                                        <td><strong><?= htmlspecialchars($course['title']) ?></strong></td>
                                                        <td><?= htmlspecialchars($course['course_code']) ?></td>
                                                        <td><?= $course['credits'] ?></td>
                                                        <td><?= htmlspecialchars($course['semester']) ?></td>
                                                        <td><?= $course['year'] ?></td>
                                                        <td>
                                                            <span class="badge <?= $course['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                                <?= ucfirst($course['status']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= $course['created_at'] ?></td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="course_view.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-outline-primary" title="Xem"><i class="fas fa-eye"></i></a>
                                                                <a href="course_edit.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Sửa"><i class="fas fa-edit"></i></a>
                                                                <a href="course_analytics.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-outline-info" title="Phân tích"><i class="fas fa-chart-bar"></i></a>
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
