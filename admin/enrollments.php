<?php
require_once '../config/database.php';
requireLogin();
if (!hasRole('admin')) {
    header('Location: ../auth/login.php');
    exit();
}
$database = new Database();
$conn = $database->getConnection();
// Get all enrollments with student and course names
$sql = "SELECT e.*, u.username AS student_username, u.first_name AS student_first, u.last_name AS student_last, c.title AS course_title
        FROM enrollments e
        JOIN users u ON e.student_id = u.id
        JOIN courses c ON e.course_id = c.id
        ORDER BY e.enrollment_date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Management - University LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-user-check"></i> Manage Enrollments
                    </h1>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-user-check"></i> Enrollment List
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">ID</th>
                                                <th scope="col">Student</th>
                                                <th scope="col">Course</th>
                                                <th scope="col">Enrollment Date</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Final Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if ($enrollments && count($enrollments) > 0): ?>
                                            <?php foreach($enrollments as $row): ?>
                                                <tr>
                                                    <td><?= $row['id'] ?></td>
                                                    <td><?= htmlspecialchars($row['student_last'] . ' ' . $row['student_first']) ?> <br><span class="text-muted small">(<?= htmlspecialchars($row['student_username']) ?>)</span></td>
                                                    <td><?= htmlspecialchars($row['course_title']) ?></td>
                                                    <td><?= $row['enrollment_date'] ?></td>
                                                    <td>
                                                        <?php if ($row['status'] === 'enrolled'): ?>
                                                            <span class="badge bg-info">Enrolled</span>
                                                        <?php elseif ($row['status'] === 'completed'): ?>
                                                            <span class="badge bg-success">Completed</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Dropped</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= is_null($row['final_grade']) ? '-' : $row['final_grade'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="6" class="text-center">No enrollments found.</td></tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
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
