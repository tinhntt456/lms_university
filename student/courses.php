<?php
// student/courses.php

require_once '../config/database.php';
requireLogin();
if (!hasRole('student')) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$student_id = $_SESSION['user_id'];

// Get enrolled courses using PDO
$sql = "SELECT c.*, e.status AS enrollment_status, e.final_grade
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        WHERE e.student_id = ?
        ORDER BY c.year DESC, c.semester DESC, c.title ASC";
$stmt = $db->prepare($sql);
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get courses not yet enrolled
$sql_available = "SELECT c.* FROM courses c
    WHERE c.id NOT IN (SELECT course_id FROM enrollments WHERE student_id = ?)";
$stmt_available = $db->prepare($sql_available);
$stmt_available->execute([$student_id]);
$available_courses = $stmt_available->fetchAll(PDO::FETCH_ASSOC);

// Handle enroll request
if (isset($_POST['enroll_course_id'])) {
    $enroll_course_id = intval($_POST['enroll_course_id']);
    // Check if already enrolled
    $check = $db->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
    $check->execute([$student_id, $enroll_course_id]);
    if (!$check->fetch()) {
        $enroll = $db->prepare("INSERT INTO enrollments (student_id, course_id, status) VALUES (?, ?, 'enrolled')");
        $enroll->execute([$student_id, $enroll_course_id]);
        header('Location: courses.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .courses-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .courses-table th, .courses-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .courses-table th {
            background-color: #f2f2f2;
        }
        .status-enrolled { color: #007bff; }
        .status-completed { color: #28a745; }
        .status-dropped { color: #dc3545; }
    </style>
</head>
<body>
    <?php include '../includes/student_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/student_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-book"></i> My Courses
                    </h1>
                </div>
                <table class="courses-table mb-4">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Code</th>
                            <th>Semester</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th>Final Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($courses) > 0): ?>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?= htmlspecialchars($course['title']) ?></td>
                                <td><?= htmlspecialchars($course['course_code']) ?></td>
                                <td><?= htmlspecialchars($course['semester']) ?></td>
                                <td><?= htmlspecialchars($course['year']) ?></td>
                                <td class="status-<?= htmlspecialchars(strtolower($course['enrollment_status'])) ?>">
                                    <?= htmlspecialchars(ucfirst($course['enrollment_status'])) ?>
                                </td>
                                <td><?= $course['final_grade'] !== null ? htmlspecialchars($course['final_grade']) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">You are not enrolled in any courses.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2 class="h4"><i class="fas fa-plus-circle"></i> Available Courses to Enroll</h2>
                </div>
                <table class="courses-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Code</th>
                            <th>Semester</th>
                            <th>Year</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($available_courses) > 0): ?>
                        <?php foreach ($available_courses as $ac): ?>
                            <tr>
                                <td><?= htmlspecialchars($ac['title']) ?></td>
                                <td><?= htmlspecialchars($ac['course_code']) ?></td>
                                <td><?= htmlspecialchars($ac['semester']) ?></td>
                                <td><?= htmlspecialchars($ac['year']) ?></td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="enroll_course_id" value="<?= $ac['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-sign-in-alt"></i> Enroll</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No available courses to enroll.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>
</body>
</html>
