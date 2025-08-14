<?php
// instructor/students.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}


require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();
include '../includes/instructor_sidebar.php';

$instructor_id = $_SESSION['user_id'];

// Fetch courses taught by this instructor
$courses_sql = "SELECT id, title FROM courses WHERE instructor_id = ?";
$stmt = $db->prepare($courses_sql);
$stmt->execute([$instructor_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// List students enrolled in these courses
$students_sql = "
SELECT DISTINCT u.id, u.username, u.email, u.first_name, u.last_name, u.profile_image, u.created_at, u.updated_at, c.title AS course_title
FROM enrollments e
JOIN users u ON e.student_id = u.id
JOIN courses c ON e.course_id = c.id
WHERE c.instructor_id = ? AND u.role = 'student'
ORDER BY u.last_name, u.first_name
";
$stmt = $db->prepare($students_sql);
$stmt->execute([$instructor_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>


    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Students - University LMS</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link href="../assets/css/dashboard.css" rel="stylesheet">
        <style>
            .profile-img {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                object-fit: cover;
            }
        </style>
    </head>
    <body>
        <?php include '../includes/instructor_navbar.php'; ?>
        <div class="container-fluid">
            <div class="row">
                <?php include '../includes/instructor_sidebar.php'; ?>
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">
                            <i class="fas fa-users"></i> Student List
                        </h1>
                    </div>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow">
                                <div class="card-header bg-success text-white">
                                    <i class="fas fa-users"></i> Student List
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Images</th>
                                                    <th>Full Name</th>
                                                    <th>Username</th>
                                                    <th>Email</th>
                                                    <th>Course</th>
                                                    <th>Joined At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($students) > 0): ?>
                                                    <?php foreach ($students as $student): ?>
                                                        <tr>
                                                            <td>
                                                                <?php if ($student['profile_image']): ?>
                                                                    <img src="<?= htmlspecialchars($student['profile_image']) ?>" class="profile-img" alt="Profile">
                                                                <?php else: ?>
                                                                    <img src="../assets/img/default_profile.png" class="profile-img" alt="Profile">
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                                            <td><?= htmlspecialchars($student['username']) ?></td>
                                                            <td><?= htmlspecialchars($student['email']) ?></td>
                                                            <td><?= htmlspecialchars($student['course_title']) ?></td>
                                                            <td><?= htmlspecialchars(date('Y-m-d', strtotime($student['created_at']))) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr><td colspan="6" class="text-center"><div class="alert alert-warning d-flex align-items-center justify-content-center mb-0" role="alert"><i class="fas fa-info-circle me-2"></i> No students found.</div></td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
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
