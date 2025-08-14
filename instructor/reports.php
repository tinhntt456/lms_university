<?php
// instructor/reports.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';
include '../includes/instructor_sidebar.php';

$instructor_id = $_SESSION['user_id'];

// Initialize database connection
$db = (new Database())->getConnection();
$reports_sql = "SELECT * FROM reports WHERE generated_by = ? ORDER BY created_at DESC";
$stmt = $db->prepare($reports_sql);
$stmt->execute([$instructor_id]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Instructor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../includes/instructor_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block bg-light sidebar">
                <?php include '../includes/instructor_sidebar.php'; ?>
            </nav>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 mb-0"><i class="fas fa-file-alt"></i> Reports</h1>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-file-alt"></i> Report List
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Title</th>
                                                <th>Type</th>
                                                <th>File</th>
                                                <th>Created At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if (count($reports) > 0) {
                                                foreach ($reports as $report) {
                                                    echo '<tr>';
                                                    echo '<td>' . htmlspecialchars($report['report_title']) . '</td>';
                                                    echo '<td>' . htmlspecialchars($report['report_type']) . '</td>';
                                                    echo '<td>';
                                                    if ($report['file_path']) {
                                                        echo '<a href="' . htmlspecialchars($report['file_path']) . '" class="text-primary" target="_blank"><i class="fas fa-download"></i> Download</a>';
                                                    } else {
                                                        echo '-';
                                                    }
                                                    echo '</td>';
                                                    echo '<td>' . htmlspecialchars(date('Y-m-d', strtotime($report['created_at']))) . '</td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="4" class="text-center"><div class="alert alert-warning d-flex align-items-center justify-content-center mb-0" role="alert"><i class="fas fa-info-circle me-2"></i> No reports found.</div></td></tr>';
                                            }
                                            ?>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
