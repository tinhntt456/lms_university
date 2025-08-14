<?php
// instructor/add_student.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';
$db = new Database();
$pdo = $db->getConnection();
$instructor_id = $_SESSION['user_id'];

// Fetch courses taught by instructor
$courses_sql = "SELECT id, title FROM courses WHERE instructor_id = ?";
$stmt = $pdo->prepare($courses_sql);
$stmt->execute([$instructor_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch students not enrolled in any of instructor's courses
$students_sql = "SELECT id, CONCAT(first_name, ' ', last_name) AS name, username, email FROM users WHERE role = 'student' AND id NOT IN (SELECT student_id FROM enrollments WHERE course_id IN (SELECT id FROM courses WHERE instructor_id = ?))";
$stmt = $pdo->prepare($students_sql);
$stmt->execute([$instructor_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id']);
    $course_id = intval($_POST['course_id']);
    if ($student_id && $course_id) {
        $stmt = $pdo->prepare('INSERT INTO enrollments (student_id, course_id, enrollment_date, status) VALUES (?, ?, NOW(), "enrolled")');
        if ($stmt->execute([$student_id, $course_id])) {
            $success = 'Student added to course successfully!';
        } else {
            $error = 'Error enrolling student.';
        }
    } else {
        $error = 'Please select both student and course.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student to Course - Instructor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/instructor_navbar.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">Add Student to Course</div>
                <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label for="student_id" class="form-label">Student</label>
                            <select class="form-select" id="student_id" name="student_id" required>
                                <option value="">Select student</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['username']) ?>, <?= htmlspecialchars($student['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="course_id" class="form-label">Course</label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                <option value="">Select course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Add Student</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
