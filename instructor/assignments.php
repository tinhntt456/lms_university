<?php
// instructor/assignments.php
session_start();
require_once '../config/database.php';
// Initialize PDO connection
$db = new Database();
$pdo = $db->getConnection();
require_once '../includes/instructor_sidebar.php';

// Check if user is logged in and is instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

$instructor_id = $_SESSION['user_id'];

// Fetch assignments created by this instructor
$sql = "SELECT a.*, c.title AS course_title, c.course_code, s.feedback FROM assignments a JOIN courses c ON a.course_id = c.id LEFT JOIN submissions s ON s.assignment_id = a.id WHERE a.created_by = :instructor_id ORDER BY a.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['instructor_id' => $instructor_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments - University LMS</title>
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
                        <i class="fas fa-clipboard-list"></i> My Assignments
                    </h1>
                    <a href="create_assignment.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Assignment</a>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-clipboard-list"></i> Assignment List
                            </div>
                            <div class="card-body">
                                <?php if (empty($assignments)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-clipboard-list fa-3x text-gray-300 mb-3"></i>
                                        <p class="text-muted">You haven't created any assignments yet.</p>
                                        <a href="create_assignment.php" class="btn btn-primary">Create First Assignment</a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>STT</th>
                                                    <th>Assignment Name</th>
                                                    <th>Course</th>
                                                    <th>Due Date</th>
                                                    <th>Max Points</th>
                                                    <th>Created At</th>
                                                    <th>Feedback</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($assignments as $index => $assignment): ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td><strong><?= htmlspecialchars($assignment['title']) ?></strong></td>
                                                        <td><?= htmlspecialchars($assignment['course_title']) ?> (<?= htmlspecialchars($assignment['course_code']) ?>)</td>
                                                        <td><?= date('d/m/Y H:i', strtotime($assignment['due_date'])) ?></td>
                                                        <td><?= $assignment['max_points'] ?></td>
                                                        <td><?= date('d/m/Y', strtotime($assignment['created_at'])) ?></td>
                                                        <td><?= $assignment['feedback'] ? htmlspecialchars($assignment['feedback']) : '-' ?></td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="view_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                                                <a href="edit_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                                                                <a href="submissions.php?assignment_id=<?= $assignment['id'] ?>" class="btn btn-sm btn-outline-success" title="Submissions"><i class="fas fa-file-alt"></i></a>
                                                                <a href="delete_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this assignment?');"><i class="fas fa-trash"></i></a>
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
