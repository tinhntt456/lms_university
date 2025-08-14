<?php
require_once '../config/database.php';
requireLogin();

if (!hasRole('instructor')) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get instructor's courses
$query = "SELECT c.*, COUNT(e.id) as enrolled_students 
          FROM courses c 
          LEFT JOIN enrollments e ON c.id = e.course_id AND e.status = 'enrolled'
          WHERE c.instructor_id = ? 
          GROUP BY c.id";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent assignments
$query = "SELECT a.*, c.title as course_title, COUNT(s.id) as submissions
          FROM assignments a
          JOIN courses c ON a.course_id = c.id
          LEFT JOIN submissions s ON a.id = s.assignment_id
          WHERE c.instructor_id = ?
          GROUP BY a.id
          ORDER BY a.created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$recent_assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending grading count
$query = "SELECT COUNT(*) as pending_count
          FROM submissions s
          JOIN assignments a ON s.assignment_id = a.id
          JOIN courses c ON a.course_id = c.id
          WHERE c.instructor_id = ? AND s.grade IS NULL";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$pending_grading = $stmt->fetch(PDO::FETCH_ASSOC)['pending_count'];

// Get total students across all courses
$query = "SELECT COUNT(DISTINCT e.student_id) as total_students
          FROM enrollments e
          JOIN courses c ON e.course_id = c.id
          WHERE c.instructor_id = ? AND e.status = 'enrolled'";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total_students'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - University LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/instructor_navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/instructor_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-chalkboard-teacher"></i> Instructor Dashboard
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="course_create.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> New Course
                            </a>
                            <a href="assignment_create.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-tasks"></i> New Assignment
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Welcome Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h4><i class="fas fa-chalkboard-teacher"></i> Welcome, Professor <?php echo $_SESSION['last_name']; ?>!</h4>
                                <p class="mb-0">You are teaching <?php echo count($courses); ?> courses with <?php echo $total_students; ?> total students.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Active Courses</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($courses); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-book fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Students</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_students; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Assignments Created</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($recent_assignments); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Pending Grading</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_grading; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="row">
                    <!-- My Courses -->
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-book"></i> My Courses
                                </h6>
                                <a href="course_create.php" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> Add Course
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($courses)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-book fa-3x text-gray-300 mb-3"></i>
                                        <p class="text-muted">You haven't created any courses yet.</p>
                                        <a href="course_create.php" class="btn btn-primary">Create Your First Course</a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Course</th>
                                                    <th>Code</th>
                                                    <th>Students</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($courses as $course): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($course['title']); ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($course['semester'] . ' ' . $course['year']); ?></small>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                                        <td>
                                                            <span class="badge bg-info"><?php echo $course['enrolled_students']; ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php echo $course['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                                                <?php echo ucfirst($course['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="course_view.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="course_edit.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="course_analytics.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-info">
                                                                    <i class="fas fa-chart-bar"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions & Stats -->
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-bolt"></i> Quick Actions
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="course_create.php" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create New Course
                                    </a>
                                    <a href="assignment_create.php" class="btn btn-success">
                                        <i class="fas fa-tasks"></i> Create Assignment
                                    </a>
                                    <a href="quiz_create.php" class="btn btn-info">
                                        <i class="fas fa-question-circle"></i> Create Quiz
                                    </a>
                                    <a href="grading.php" class="btn btn-warning">
                                        <i class="fas fa-clipboard-check"></i> Grade Submissions
                                        <?php if ($pending_grading > 0): ?>
                                            <span class="badge bg-danger"><?php echo $pending_grading; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Course Enrollment Chart -->
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-chart-pie"></i> Course Enrollment
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="enrollmentChart" width="100" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Assignments -->
                <?php if (!empty($recent_assignments)): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-clipboard-list"></i> Recent Assignments
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Assignment</th>
                                                <th>Course</th>
                                                <th>Due Date</th>
                                                <th>Submissions</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_assignments as $assignment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($assignment['course_title']); ?></td>
                                                    <td><?php echo formatDate($assignment['due_date']); ?></td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo $assignment['submissions']; ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="view_assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="submissions.php?assignment_id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-outline-success">
                                                                <i class="fas fa-clipboard-check"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Course Enrollment Chart
        const ctx = document.getElementById('enrollmentChart').getContext('2d');
        const enrollmentChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php foreach ($courses as $course): ?>
                        '<?php echo addslashes($course['title']); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    data: [
                        <?php foreach ($courses as $course): ?>
                            <?php echo $course['enrolled_students']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                        '#858796', '#5a5c69', '#6f42c1', '#e83e8c', '#fd7e14'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>