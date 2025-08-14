<?php
// instructor/quiz_create.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';
include '../includes/instructor_navbar.php';
include '../includes/instructor_sidebar.php';

$instructor_id = $_SESSION['user_id'];
$message = '';

// Fetch courses taught by this instructor
$courses_sql = "SELECT id, title FROM courses WHERE instructor_id = ?";
$stmt = $conn->prepare($courses_sql);
$stmt->bind_param('i', $instructor_id);
$stmt->execute();
$courses_result = $stmt->get_result();
$courses = $courses_result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $time_limit = intval($_POST['time_limit']);
    $max_attempts = intval($_POST['max_attempts']);
    $due_date = trim($_POST['due_date']);

    if ($course_id && $title && $due_date) {
        $sql = "INSERT INTO quizzes (course_id, title, description, time_limit, max_attempts, due_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isssisi', $course_id, $title, $description, $time_limit, $max_attempts, $due_date, $instructor_id);
        if ($stmt->execute()) {
            $message = '<span style=\"color:green\">Quiz created successfully!</span>';
        } else {
            $message = '<span style=\"color:red\">Error: ' . htmlspecialchars($stmt->error) . '</span>';
        }
    } else {
        $message = '<span style=\"color:red\">Please fill in all required fields.</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz - Instructor</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .form-container h2 {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-group textarea {
            min-height: 80px;
        }
        .form-actions {
            text-align: right;
        }
        .form-actions button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }
        .form-actions button:hover {
            background: #0056b3;
        }
        .message {
            margin-bottom: 18px;
        }
    </style>
</head>
<body>
<div class="main-content">
    <div class="form-container">
        <h2>Create New Quiz</h2>
        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="course_id">Course *</label>
                <select id="course_id" name="course_id" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="title">Quiz Title *</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>
            </div>
            <div class="form-group">
                <label for="time_limit">Time Limit (minutes)</label>
                <input type="number" id="time_limit" name="time_limit" min="0" max="300" value="0">
            </div>
            <div class="form-group">
                <label for="max_attempts">Max Attempts</label>
                <input type="number" id="max_attempts" name="max_attempts" min="1" max="10" value="1">
            </div>
            <div class="form-group">
                <label for="due_date">Due Date *</label>
                <input type="datetime-local" id="due_date" name="due_date" required>
            </div>
            <div class="form-actions">
                <button type="submit">Create Quiz</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
