<?php
// instructor/edit_grade.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';
$db = new Database();
$pdo = $db->getConnection();
$instructor_id = $_SESSION['user_id'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: grading.php');
    exit();
}
$stmt = $pdo->prepare('SELECT * FROM grades WHERE grade_id = ?');
$stmt->execute([$id]);
$grade = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$grade) {
    header('Location: grading.php');
    exit();
}
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_grade = floatval($_POST['grade']);
    $feedback = trim($_POST['feedback']);
    $stmt = $pdo->prepare('UPDATE grades SET grade = ?, feedback = ?, graded_at = NOW() WHERE grade_id = ?');
    if ($stmt->execute([$new_grade, $feedback, $id])) {
        $success = 'Grade updated successfully!';
        // Optionally, update related student view logic if needed
        $stmt = $pdo->prepare('SELECT * FROM grades WHERE grade_id = ?');
        $stmt->execute([$id]);
        $grade = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = 'Error updating grade.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Edit Grade - University LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/instructor_navbar.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">Edit Grade</div>
                <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label for="grade" class="form-label">Grade</label>
                            <input type="number" step="0.01" class="form-control" id="grade" name="grade" value="<?= htmlspecialchars($grade['grade']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="feedback" class="form-label">Feedback</label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="3"><?= htmlspecialchars($grade['feedback']) ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Update Grade</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
