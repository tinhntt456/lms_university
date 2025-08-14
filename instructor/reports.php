<?php
// instructor/reports.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';
// Get instructor ID from session
$instructor_id = $_SESSION['user_id'];

// Initialize database connection
$db = (new Database())->getConnection();

// Fetch reports
$reports_sql = "SELECT * FROM reports WHERE generated_by = ? ORDER BY created_at DESC";
$stmt = $db->prepare($reports_sql);
$stmt->execute([$instructor_id]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle new report submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_title'], $_POST['report_type'])) {
    $report_title = trim($_POST['report_title']);
    $report_type = trim($_POST['report_type']);
    $created_at = date('Y-m-d H:i:s');
    $file_path = '';
    if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/reports/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = basename($_FILES['report_file']['name']);
        $target_file = $upload_dir . time() . '_' . $filename;
        // Prevent duplicate file uploads
        if (!file_exists($target_file)) {
            if (move_uploaded_file($_FILES['report_file']['tmp_name'], $target_file)) {
                $file_path = $target_file;
            }
        }
    }
    if ($report_title !== '' && $report_type !== '') {
        $insert_stmt = $db->prepare('INSERT INTO reports (report_title, report_type, file_path, created_at, generated_by) VALUES (?, ?, ?, ?, ?)');
        $insert_stmt->execute([$report_title, $report_type, $file_path, $created_at, $instructor_id]);
        header('Location: reports.php');
        exit();
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $del_stmt = $db->prepare('DELETE FROM reports WHERE id = ? AND generated_by = ?');
    $del_stmt->execute([$delete_id, $instructor_id]);
    header('Location: reports.php');
    exit();
}

// Handle edit
if (isset($_POST['edit_id'], $_POST['edit_report_title'], $_POST['edit_report_type'])) {
    $edit_id = intval($_POST['edit_id']);
    $edit_report_title = trim($_POST['edit_report_title']);
    $edit_report_type = trim($_POST['edit_report_type']);
    $edit_file_path = '';
    if (isset($_FILES['edit_report_file']) && $_FILES['edit_report_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/reports/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = basename($_FILES['edit_report_file']['name']);
        $target_file = $upload_dir . time() . '_' . $filename;
        // Prevent duplicate file uploads
        if (!file_exists($target_file)) {
            if (move_uploaded_file($_FILES['edit_report_file']['tmp_name'], $target_file)) {
                $edit_file_path = $target_file;
            }
        }
    }
    if ($edit_report_title !== '' && $edit_report_type !== '') {
        if ($edit_file_path !== '') {
            $update_stmt = $db->prepare('UPDATE reports SET report_title = ?, report_type = ?, file_path = ? WHERE id = ? AND generated_by = ?');
            $update_stmt->execute([$edit_report_title, $edit_report_type, $edit_file_path, $edit_id, $instructor_id]);
        } else {
            $update_stmt = $db->prepare('UPDATE reports SET report_title = ?, report_type = ? WHERE id = ? AND generated_by = ?');
            $update_stmt->execute([$edit_report_title, $edit_report_type, $edit_id, $instructor_id]);
        }
        header('Location: reports.php');
        exit();
    }
}
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
                                <form method="post" enctype="multipart/form-data" class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <input type="text" name="report_title" class="form-control" placeholder="Report Title" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="report_type" class="form-control" placeholder="Report Type" required>
                                    </div>
                                    <div class="col-md-4">
                                        <div>
                                            <input type="file" name="report_file" class="form-control" id="report_file" lang="en">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-plus"></i> Create Report</button>
                                    </div>
                                </form>
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Title</th>
                                                <th>Type</th>
                                                <th>File</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if (count($reports) > 0) {
                                                foreach ($reports as $report) {
                                                    if (isset($_GET['edit']) && $_GET['edit'] == $report['id']) {
                                                        // Edit form row
                                                        echo '<tr>';
                                                        echo '<form method="post" enctype="multipart/form-data">';
                                                        echo '<td><input type="text" name="edit_report_type" class="form-control" value="' . htmlspecialchars($report['report_type']) . '" required></td>';
                                                        echo '<td>';
                                                        if ($report['file_path']) {
                                                            echo '<a href="' . htmlspecialchars($report['file_path']) . '" class="text-primary" target="_blank"><i class="fas fa-download"></i> Download</a><br>';
                                                        }
                                                        echo '<div class="mt-2">';
                                                        echo '<input type="file" name="edit_report_file" class="form-control" id="edit_report_file_' . $report['id'] . '">';
                                                        echo '</div>';
                                                        echo '</td>';
                                                        echo '<td>' . htmlspecialchars(date('Y-m-d', strtotime($report['created_at']))) . '</td>';
                                                        echo '<td>';
                                                        echo '<input type="hidden" name="edit_id" value="' . $report['id'] . '">';
                                                        echo '<button type="submit" class="btn btn-sm btn-success me-1"><i class="fas fa-save"></i></button>';
                                                        echo '<a href="reports.php" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i></a>';
                                                        echo '</td>';
                                                        echo '</form>';
                                                        echo '</tr>';
                                                    } else {
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
                                                        echo '<td>';
                                                        echo '<a href="reports.php?edit=' . $report['id'] . '" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a>';
                                                        echo '<a href="reports.php?delete=' . $report['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this report?\');"><i class="fas fa-trash"></i></a>';
                                                        echo '</td>';
                                                        echo '</tr>';
                                                    }
                                                }
                                            } else {
                                                echo '<tr><td colspan="5" class="text-center"><div class="alert alert-warning d-flex align-items-center justify-content-center mb-0" role="alert"><i class="fas fa-info-circle me-2"></i> No reports found.</div></td></tr>';
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
    <script>
    // Force file input label to English
    document.addEventListener('DOMContentLoaded', function() {
        var fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(function(input) {
            // Set default label to English
            var label = input.nextElementSibling;
            if (label && label.tagName === 'LABEL') {
                label.textContent = 'Choose file';
            }
            // Override browser default 'No file chosen' text
            input.addEventListener('change', function(e) {
                var fileName = input.files.length ? input.files[0].name : '';
                label.textContent = fileName ? fileName : 'Choose file';
                // For native file input, try to override the adjacent text node
                var parent = input.parentNode;
                var nodes = parent.childNodes;
                for (var i = 0; i < nodes.length; i++) {
                    if (nodes[i].nodeType === 3 && nodes[i].textContent.includes('No file chosen')) {
                        nodes[i].textContent = 'No file chosen';
                    }
                }
            });
            // On page load, override if present
            var parent = input.parentNode;
            var nodes = parent.childNodes;
            for (var i = 0; i < nodes.length; i++) {
                if (nodes[i].nodeType === 3 && nodes[i].textContent.includes('No file chosen')) {
                    nodes[i].textContent = 'No file chosen';
                }
            }
        });
    });
    </script>
</body>
</html>
