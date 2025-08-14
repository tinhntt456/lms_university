<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit;
}
$instructor_id = $_SESSION['user_id'];
$db = (new Database())->getConnection();
$materials_sql = "
SELECT m.*, c.title AS course_title, u.first_name, u.last_name
FROM course_materials m
JOIN courses c ON m.course_id = c.id
JOIN users u ON m.uploaded_by = u.id
WHERE c.instructor_id = ?
ORDER BY m.upload_date DESC
";
$stmt = $db->prepare($materials_sql);
$stmt->execute([$instructor_id]);
$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
$material_count = count($materials);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Materials - University LMS</title>
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
                    <div>
                        <h1 class="h2 mb-0">
                            <i class="fas fa-folder-open"></i> Course Materials
                        </h1>
                        <span class="text-muted">Total materials: <strong><?= $material_count ?></strong></span>
                    </div>
                    <a href="material_create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Material</a>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-folder-open"></i> Course Materials List
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th>Course</th>
                                                <th>File</th>
                                                <th>Type</th>
                                                <th>Size (KB)</th>
                                                <th>Uploaded By</th>
                                                <th>Upload Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($material_count > 0) {
                                                foreach ($materials as $material) {
                                                    echo '<tr>';
                                                    echo '<td>' . htmlspecialchars($material['title']) . '</td>';
                                                    echo '<td>' . htmlspecialchars($material['description']) . '</td>';
                                                    echo '<td>' . htmlspecialchars($material['course_title']) . '</td>';
                                                    echo '<td>';
                                                    if ($material['file_path']) {
                                                        echo '<a href="' . htmlspecialchars($material['file_path']) . '" class="text-primary" target="_blank"><i class="fas fa-download"></i> Download</a>';
                                                    } else {
                                                        echo '-';
                                                    }
                                                    echo '</td>';
                                                    echo '<td>' . htmlspecialchars($material['file_type']) . '</td>';
                                                    echo '<td>' . ($material['file_size'] ? round($material['file_size']/1024, 2) : '-') . '</td>';
                                                    echo '<td>' . htmlspecialchars($material['first_name'] . ' ' . $material['last_name']) . '</td>';
                                                    echo '<td>' . htmlspecialchars(date('Y-m-d', strtotime($material['upload_date']))) . '</td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="8" class="text-center"><div class="alert alert-warning d-flex align-items-center justify-content-center mb-0" role="alert"><i class="fas fa-info-circle me-2"></i> No materials found.</div></td></tr>';
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
