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

// Handle new analytics submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['metric_name'], $_POST['value'], $_POST['recorded_at'])) {
    $metric_name = trim($_POST['metric_name']);
    $value = trim($_POST['value']);
    $recorded_at = $_POST['recorded_at'];
    if ($metric_name !== '' && $value !== '' && $recorded_at !== '') {
        $insert_stmt = $db->prepare('INSERT INTO analytics (metric_name, value, recorded_at) VALUES (?, ?, ?)');
        $insert_stmt->execute([$metric_name, $value, $recorded_at]);
        header('Location: analytics.php');
        exit();
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $del_stmt = $db->prepare('DELETE FROM analytics WHERE id = ?');
    $del_stmt->execute([$delete_id]);
    header('Location: analytics.php');
    exit();
}

// Handle edit
if (isset($_POST['edit_id'], $_POST['edit_metric_name'], $_POST['edit_value'], $_POST['edit_recorded_at'])) {
    $edit_id = intval($_POST['edit_id']);
    $edit_metric_name = trim($_POST['edit_metric_name']);
    $edit_value = trim($_POST['edit_value']);
    $edit_recorded_at = $_POST['edit_recorded_at'];
    if ($edit_metric_name !== '' && $edit_value !== '' && $edit_recorded_at !== '') {
        $update_stmt = $db->prepare('UPDATE analytics SET metric_name = ?, value = ?, recorded_at = ? WHERE id = ?');
        $update_stmt->execute([$edit_metric_name, $edit_value, $edit_recorded_at, $edit_id]);
        header('Location: analytics.php');
        exit();
    }
}

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
                                    <!-- Form to add new analytics metric -->
                                    <!-- Form to add new analytics metric -->
                                    <form method="post" class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <input type="text" name="metric_name" class="form-control" placeholder="Metric Name" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="value" class="form-control" placeholder="Value" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="date" name="recorded_at" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-info w-100"><i class="fas fa-plus"></i> Add Metric</button>
                                        </div>
                                    </form>
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Metric Name</th>
                                                <th>Value</th>
                                                <th>Recorded At</th>
                                                <th>Actions</th>
                                            <?php
                                            if (count($analytics) > 0) {
                                                foreach ($analytics as $row) {
                                                    if (isset($_GET['edit']) && $_GET['edit'] == $row['id']) {
                                                        // Edit form row
                                                        echo '<tr>';
                                                        echo '<form method="post">';
                                                        echo '<td><input type="text" name="edit_metric_name" class="form-control" value="' . htmlspecialchars($row['metric_name']) . '" required></td>';
                                                        echo '<td><input type="text" name="edit_value" class="form-control" value="' . htmlspecialchars($row['value']) . '" required></td>';
                                                        echo '<td><input type="date" name="edit_recorded_at" class="form-control" value="' . htmlspecialchars(date('Y-m-d', strtotime($row['recorded_at']))) . '" required></td>';
                                                        echo '<td>';
                                                        echo '<input type="hidden" name="edit_id" value="' . $row['id'] . '">';
                                                        echo '<button type="submit" class="btn btn-sm btn-success me-1"><i class="fas fa-save"></i></button>';
                                                        echo '<a href="analytics.php" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i></a>';
                                                        echo '</td>';
                                                        echo '</form>';
                                                        echo '</tr>';
                                                    } else {
                                                        echo '<tr>';
                                                        echo '<td>' . htmlspecialchars($row['metric_name']) . '</td>';
                                                        echo '<td>' . htmlspecialchars($row['value']) . '</td>';
                                                        echo '<td>' . htmlspecialchars(date('Y-m-d', strtotime($row['recorded_at']))) . '</td>';
                                                        echo '<td>';
                                                        echo '<a href="analytics.php?edit=' . $row['id'] . '" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a>';
                                                        echo '<a href="analytics.php?delete=' . $row['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this metric?\');"><i class="fas fa-trash"></i></a>';
                                                        echo '</td>';
                                                        echo '</tr>';
                                                    }
                                                }
                                            } else {
                                                echo '<tr><td colspan="4" class="text-center"><div class="alert alert-warning d-flex align-items-center justify-content-center mb-0" role="alert"><i class="fas fa-info-circle me-2"></i> No analytics data available.</div></td></tr>';
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
