<?php
// instructor/create_quiz.php
session_start();
require_once '../config/database.php';
$database = new Database();
$pdo = $database->getConnection();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
$instructor_id = $_SESSION['user_id'];
// Fetch courses taught by this instructor
$stmt = $pdo->prepare("SELECT id, title, course_code FROM courses WHERE instructor_id = :instructor_id");
$stmt->execute(['instructor_id' => $instructor_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $course_id = $_POST['course_id'];
    $due_date = $_POST['due_date'];
    $time_limit = $_POST['time_limit'];
    $max_attempts = $_POST['max_attempts'];
    if ($title && $course_id && $due_date && $time_limit && $max_attempts) {
        $stmt = $pdo->prepare("INSERT INTO quizzes (course_id, title, description, time_limit, max_attempts, due_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$course_id, $title, $description, $time_limit, $max_attempts, $due_date, $instructor_id])) {
            $success = 'Quiz created successfully!';
        } else {
            $error = 'Error creating quiz.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create New Quiz - University LMS</title>
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
                        <i class="fas fa-clipboard-question"></i> Create New Quiz
                    </h1>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-clipboard-question"></i> Quiz Information
                            </div>
                            <div class="card-body">
                                <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                                <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                                <form method="post">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="course_id" class="form-label">Course *</label>
                                        <select class="form-select" id="course_id" name="course_id" required>
                                            <option value="">Select Course</option>
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?> (<?= htmlspecialchars($course['course_code']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="due_date" class="form-label">Due Date *</label>
                                        <input type="datetime-local" class="form-control" id="due_date" name="due_date" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="time_limit" class="form-label">Time Limit (minutes) *</label>
                                        <input type="number" class="form-control" id="time_limit" name="time_limit" min="1" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="max_attempts" class="form-label">Max Attempts *</label>
                                        <input type="number" class="form-control" id="max_attempts" name="max_attempts" min="1" value="1" required>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Create Quiz</button>
                                        <a href="quizzes.php" class="btn btn-secondary">Back</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
