<?php
// instructor/grading.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}


require_once '../config/database.php';
$db = new Database();
$pdo = $db->getConnection();
include '../includes/instructor_sidebar.php';
$instructor_id = $_SESSION['user_id'];

// Fetch courses taught by this instructor
$courses_sql = "SELECT c.id, c.title FROM courses c WHERE c.instructor_id = :instructor_id";
$stmt = $pdo->prepare($courses_sql);
$stmt->execute(['instructor_id' => $instructor_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch grades for assignments and quizzes in instructor's courses
$grades_sql = "
SELECT g.*, CONCAT(u.first_name, ' ', u.last_name) AS student_name, c.title AS course_name, a.title AS assignment_title, q.title AS quiz_title,
       s.feedback AS submission_feedback
FROM grades g
JOIN users u ON g.student_id = u.id
JOIN courses c ON g.course_id = c.id
LEFT JOIN assignments a ON g.assignment_id = a.id
LEFT JOIN quizzes q ON g.quiz_id = q.id
LEFT JOIN submissions s ON s.assignment_id = g.assignment_id AND s.student_id = g.student_id
WHERE c.instructor_id = :instructor_id
ORDER BY g.graded_at DESC
";
$stmt = $pdo->prepare($grades_sql);
$stmt->execute(['instructor_id' => $instructor_id]);
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading - University LMS</title>
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
                            <i class="fas fa-marker"></i> Grading
                    </h1>
                </div>
                <div class="row mb-4">
                       <div class="col-12 mb-3">
                           <a href="add_grade.php" class="btn btn-success"><i class="fas fa-plus"></i> Add Grade</a>
                       </div>
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header bg-success text-white">
                                    <i class="fas fa-marker"></i> Grading List
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                    <th>Student</th>
                                                    <th>Course</th>
                                                    <th>Assignment/Quiz</th>
                                                    <th>Grade</th>
                                                    <th>Feedback</th>
                                                    <th>Graded At</th>
                                                    <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($grades) > 0): ?>
                                                <?php foreach ($grades as $grade): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($grade['student_name']) ?></td>
                                                        <td><?= htmlspecialchars($grade['course_name']) ?></td>
                                                        <td>
                                                                <?php
                                                                if ($grade['assignment_id']) {
                                                                    echo 'Assignment: ' . htmlspecialchars($grade['assignment_title']);
                                                                } elseif ($grade['quiz_id']) {
                                                                    echo 'Quiz: ' . htmlspecialchars($grade['quiz_title']);
                                                                } else {
                                                                    echo '-';
                                                                }
                                                                ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($grade['grade']) ?></td>
                                                        <td class="feedback">
                                                            <?php
                                                            if (!empty($grade['submission_feedback'])) {
                                                                echo nl2br(htmlspecialchars($grade['submission_feedback']));
                                                            } else {
                                                                echo nl2br(htmlspecialchars($grade['feedback']));
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($grade['graded_at']) ?></td>
                                                           <td>
                                                               <a href="edit_grade.php?id=<?= $grade['grade_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                               <a href="delete_grade.php?id=<?= $grade['grade_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this grade?');">Delete</a>
                                                           </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr><td colspan="6" class="text-center">No grades available.</td></tr>
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
