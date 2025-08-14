<?php
// instructor/analytics.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';
include '../includes/instructor_sidebar.php';

// Connect to database
$db = (new Database())->getConnection();
$analytics_sql = "SELECT * FROM analytics ORDER BY recorded_at DESC";
$stmt = $db->query($analytics_sql);
$analytics = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Analytics Statistics - Instructor</title>
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
                    <h1 class="h2 mb-0"><i class="fas fa-chart-line"></i> Analytics Statistics</h1>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header bg-info text-white">
                                <i class="fas fa-chart-bar"></i> Analytics Data
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Metric Name</th>
                                                <th>Value</th>
                                                <th>Recorded At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if (count($analytics) > 0) {
                                                foreach ($analytics as $row) {
                                                    echo '<tr>';
                                                    echo '<td>' . htmlspecialchars($row['metric_name']) . '</td>';
                                                    echo '<td>' . htmlspecialchars($row['metric_value']) . '</td>';
                                                    echo '<td>' . htmlspecialchars(date('Y-m-d H:i', strtotime($row['recorded_at']))) . '</td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="3" class="text-center"><div class="alert alert-warning d-flex align-items-center justify-content-center mb-0" role="alert"><i class="fas fa-info-circle me-2"></i> No analytics data available.</div></td></tr>';
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
