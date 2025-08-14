<?php
// instructor/add_grade.php
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

// Fetch students
$students_sql = "SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE role = 'student'";
$stmt = $pdo->query($students_sql);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch assignments and quizzes for instructor's courses
$assignments_sql = "SELECT id, title, course_id FROM assignments WHERE course_id IN (SELECT id FROM courses WHERE instructor_id = ?)";
$stmt = $pdo->prepare($assignments_sql);
$stmt->execute([$instructor_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$quizzes_sql = "SELECT id, title, course_id FROM quizzes WHERE course_id IN (SELECT id FROM courses WHERE instructor_id = ?)";
$stmt = $pdo->prepare($quizzes_sql);
$stmt->execute([$instructor_id]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id']);
    $course_id = intval($_POST['course_id']);
    $assignment_id = !empty($_POST['assignment_id']) ? intval($_POST['assignment_id']) : null;
    $quiz_id = !empty($_POST['quiz_id']) ? intval($_POST['quiz_id']) : null;
    $grade = floatval($_POST['grade']);
    $feedback = trim($_POST['feedback']);
    if ($student_id && $course_id && ($assignment_id || $quiz_id) && $grade !== '') {
        $stmt = $pdo->prepare('INSERT INTO grades (student_id, course_id, assignment_id, quiz_id, grade, feedback, graded_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        if ($stmt->execute([$student_id, $course_id, $assignment_id, $quiz_id, $grade, $feedback])) {
            $success = 'Grade added successfully!';
        } else {
            $error = 'Error adding grade.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Add Grade - University LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/instructor_navbar.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">Add Grade</div>
                <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label for="student_id" class="form-label">Student</label>
                            <select class="form-select" id="student_id" name="student_id" required>
                                <option value="">Select student</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?></option>
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
                        <div class="mb-3">
                            <label for="assignment_id" class="form-label">Assignment (optional)</label>
                            <select class="form-select" id="assignment_id" name="assignment_id">
                                <option value="">None</option>
                                <?php foreach ($assignments as $assignment): ?>
                                    <option value="<?= $assignment['id'] ?>"><?= htmlspecialchars($assignment['title']) ?> (<?= htmlspecialchars($assignment['course_id']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quiz_id" class="form-label">Quiz (optional)</label>
                            <select class="form-select" id="quiz_id" name="quiz_id">
                                <option value="">None</option>
                                <?php foreach ($quizzes as $quiz): ?>
                                    <option value="<?= $quiz['id'] ?>"><?= htmlspecialchars($quiz['title']) ?> (<?= htmlspecialchars($quiz['course_id']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="grade" class="form-label">Grade</label>
                            <input type="number" step="0.01" class="form-control" id="grade" name="grade" required>
                        </div>
                        <div class="mb-3">
                            <label for="feedback" class="form-label">Feedback</label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Add Grade</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
