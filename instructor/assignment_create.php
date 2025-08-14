<?php
// instructor/assignment_create.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

include '../includes/instructor_navbar.php';
include '../includes/instructor_sidebar.php';

$instructor_id = $_SESSION['user_id'];
$message = '';

// Get list of courses taught by this instructor
$courses_sql = "SELECT id, title FROM courses WHERE instructor_id = ?";
$stmt = $conn->prepare($courses_sql);
$stmt->execute([$instructor_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = trim($_POST['due_date']);
    $max_points = floatval($_POST['max_points']);

    if ($course_id && $title && $due_date) {
        $sql = "INSERT INTO assignments (course_id, title, description, due_date, max_points, created_by) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$course_id, $title, $description, $due_date, $max_points, $instructor_id])) {
            $message = '<span style=\"color:green\">Assignment created successfully!</span>';
        } else {
            $errorInfo = $stmt->errorInfo();
            $message = '<span style=\"color:red\">Error: ' . htmlspecialchars($errorInfo[2]) . '</span>';
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
    <title>Create Assignment - Instructor</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 40px auto;
<?php include '../includes/instructor_navbar.php'; ?>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/instructor_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-tasks"></i> Create New Assignment
                    </h1>
                </div>
                <?php
                session_start();
                if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
                    header('Location: ../auth/login.php');
                    exit();
                }

                require_once '../config/database.php';

                $database = new Database();
                $conn = $database->getConnection();

                $instructor_id = $_SESSION['user_id'];
                $message = '';

                // Get list of courses taught by this instructor
                $courses_sql = "SELECT id, title FROM courses WHERE instructor_id = ?";
                $stmt = $conn->prepare($courses_sql);
                $stmt->execute([$instructor_id]);
                $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $course_id = intval($_POST['course_id']);
                    $title = trim($_POST['title']);
                    $description = trim($_POST['description']);
                    $due_date = trim($_POST['due_date']);
                    $max_points = floatval($_POST['max_points']);

                    if ($course_id && $title && $due_date) {
                        $sql = "INSERT INTO assignments (course_id, title, description, due_date, max_points, created_by) VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        if ($stmt->execute([$course_id, $title, $description, $due_date, $max_points, $instructor_id])) {
                            $message = '<span style="color:green">Assignment created successfully!</span>';
                        } else {
                            $errorInfo = $stmt->errorInfo();
                            $message = '<span style="color:red">Error: ' . htmlspecialchars($errorInfo[2]) . '</span>';
                        }
                    } else {
                        $message = '<span style="color:red">Please fill in all required fields.</span>';
                    }
                }
                ?>
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Create Assignment - Instructor</title>
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
                    <?php include '../includes/instructor_navbar.php'; ?>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-3 col-lg-2 p-0 bg-light">
                                <?php include '../includes/instructor_sidebar.php'; ?>
                            </div>
                            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                                    <h1 class="h2">
                                        <i class="fas fa-tasks"></i> Create New Assignment
                                    </h1>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-12 col-lg-8 mx-auto">
                                        <div class="card shadow">
                                            <div class="card-body">
                                                <h2 class="mb-4">Create New Assignment</h2>
                                                <?php if ($message): ?>
                                                    <div class="message mb-3"><?= $message ?></div>
                                                <?php endif; ?>
                                                <form method="post" action="">
                                                    <div class="form-group mb-3">
                                                        <label for="course_id">Course *</label>
                                                        <select id="course_id" name="course_id" class="form-control" required>
                                                            <option value="">-- Select Course --</option>
                                                            <?php foreach ($courses as $course): ?>
                                                                <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label for="title">Assignment Title *</label>
                                                        <input type="text" id="title" name="title" class="form-control" required>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label for="description">Description</label>
                                                        <textarea id="description" name="description" class="form-control"></textarea>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label for="due_date">Due Date *</label>
                                                        <input type="datetime-local" id="due_date" name="due_date" class="form-control" required>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label for="max_points">Max Points</label>
                                                        <input type="number" id="max_points" name="max_points" class="form-control" min="1" max="1000" value="100">
                                                    </div>
                                                    <div class="form-actions text-end">
                                                        <button type="submit" class="btn btn-primary">Create Assignment</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </main>
                        </div>
                    </div>
                </body>
