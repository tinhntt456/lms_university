<?php
// instructor/course_create.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();
$instructor_id = $_SESSION['user_id'];
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

    // Validate required fields
    if ($title && $course_code && $year) {
        $sql = "INSERT INTO courses (title, description, instructor_id, course_code, credits, semester, year, max_students, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        if ($stmt->execute([$title, $description, $instructor_id, $course_code, $credits, $semester, $year, $max_students, $status])) {
            $message = '<span style="color:green">Course created successfully!</span>';
        } else {
            $message = '<span style="color:red">Error: ' . htmlspecialchars($stmt->errorInfo()[2]) . '</span>';
        }
    } else {
        $message = '<span style="color:red">Please fill in all required fields.</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course - University LMS</title>
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
                        <i class="fas fa-book"></i> Create New Course
                    </h1>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-book"></i> Enter Course Information
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-info"><?= $message ?></div>
                                <?php endif; ?>
                                <form method="post" action="">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Course Name *</label>
                                        <input type="text" id="title" name="title" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea id="description" name="description" class="form-control"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="course_code" class="form-label">Course Code *</label>
                                        <input type="text" id="course_code" name="course_code" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="credits" class="form-label">Credits</label>
                                        <input type="number" id="credits" name="credits" class="form-control" min="1" max="10" value="3">
                                    </div>
                                    <div class="mb-3">
                                        <label for="semester" class="form-label">Semester</label>
                                        <input type="text" id="semester" name="semester" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="year" class="form-label">Year *</label>
                                        <input type="number" id="year" name="year" class="form-control" min="2000" max="2100" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="max_students" class="form-label">Maximum Students</label>
                                        <input type="number" id="max_students" name="max_students" class="form-control" min="1" max="500" value="50">
                                    </div>
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select id="status" name="status" class="form-select">
                                            <option value="active" selected>Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Create Course</button>
                                    </div>
                                </form>
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
