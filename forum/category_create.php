<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit;
}
$db = (new Database())->getConnection();
$message = '';
// Fetch instructor's courses for dropdown
$instructor_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT id, title FROM courses WHERE instructor_id = ?");
$stmt->execute([$instructor_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $course_id = intval($_POST['course_id']);
    if ($name && $course_id) {
        $stmt = $db->prepare("INSERT INTO forum_categories (name, description, course_id, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$name, $description, $course_id]);
        $message = '<div class="alert alert-success">Category created successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Please enter all required fields.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Forum Category</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../includes/instructor_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/instructor_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 mb-0"><i class="fas fa-folder-plus"></i> Create Forum Category</h1>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-edit"></i> Enter Category Information
                            </div>
                            <div class="card-body">
                                <?= $message ?>
                                <form method="post" action="">
                                    <div class="mb-3">
                                        <label class="form-label">Category Name *</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Course *</label>
                                        <select name="course_id" class="form-select" required>
                                            <option value="">-- Select Course --</option>
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Create Category</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
