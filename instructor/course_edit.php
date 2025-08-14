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
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $course_code = trim($_POST['course_code']);
    $credits = intval($_POST['credits']);
    $semester = trim($_POST['semester']);
    $year = intval($_POST['year']);
    $max_students = intval($_POST['max_students']);
    $status = $_POST['status'];
    $sql = "UPDATE courses SET title=?, description=?, course_code=?, credits=?, semester=?, year=?, max_students=?, status=? WHERE id=? AND instructor_id=?";
    $stmt = $db->prepare($sql);
    if ($stmt->execute([$title, $description, $course_code, $credits, $semester, $year, $max_students, $status, $course_id, $_SESSION['user_id']])) {
        $message = '<span style="color:green">Course updated successfully!</span>';
        // Refresh course info
        $stmt = $db->prepare('SELECT * FROM courses WHERE id = ? AND instructor_id = ?');
        $stmt->execute([$course_id, $_SESSION['user_id']]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $message = '<span style="color:red">Error: ' . htmlspecialchars($stmt->errorInfo()[2]) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Course</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/instructor_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/instructor_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <h1 class="h2 mt-3 mb-3"><i class="fas fa-edit"></i> Edit Course</h1>
                <?php if ($message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Course Name *</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($course['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control"><?= htmlspecialchars($course['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="course_code" class="form-label">Course Code *</label>
                        <input type="text" id="course_code" name="course_code" class="form-control" value="<?= htmlspecialchars($course['course_code']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="credits" class="form-label">Credits</label>
                        <input type="number" id="credits" name="credits" class="form-control" min="1" max="10" value="<?= htmlspecialchars($course['credits']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="semester" class="form-label">Semester</label>
                        <input type="text" id="semester" name="semester" class="form-control" value="<?= htmlspecialchars($course['semester']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="year" class="form-label">Year *</label>
                        <input type="number" id="year" name="year" class="form-control" min="2000" max="2100" value="<?= htmlspecialchars($course['year']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="max_students" class="form-label">Maximum Students</label>
                        <input type="number" id="max_students" name="max_students" class="form-control" min="1" max="500" value="<?= htmlspecialchars($course['max_students']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="active" <?= $course['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $course['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                    </div>
                </form>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
