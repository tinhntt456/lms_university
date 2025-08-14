
<?php
require_once '../config/database.php';
// requireLogin(); // Uncomment if authentication is needed
$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $title = $_POST["title"];
    $description = $_POST["description"];
    $course_id = isset($_POST["course_id"]) ? intval($_POST["course_id"]) : 0;
    $uploaded_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    $uploadDir = '../uploads/materials/';
    $filePath = '';
    $fileType = '';
    $fileSize = 0;
    // Validate course_id
    $valid_course = false;
    if ($course_id > 0) {
        $instructor_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
        $check_stmt = $db->prepare("SELECT id FROM courses WHERE id = ? AND instructor_id = ?");
        $check_stmt->execute([$course_id, $instructor_id]);
        $valid_course = $check_stmt->fetchColumn() !== false;
    }
    if (!$valid_course) {
        $feedback .= '<div class="alert alert-danger mt-3">Invalid course selected.</div>';
    } else {
        if (!empty($_FILES['file']['name'])) {
            $fileName = basename($_FILES['file']['name']);
            $filePath = $uploadDir . $fileName;
            $fileType = $_FILES['file']['type'];
            $fileSize = $_FILES['file']['size'];
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                $feedback .= '<div class="alert alert-success mt-3">File uploaded successfully.</div>';
            } else {
                $feedback .= '<div class="alert alert-danger mt-3">File upload failed.</div>';
            }
        }
        // Insert into course_materials table
        $stmt = $db->prepare("INSERT INTO course_materials (course_id, title, description, file_path, file_type, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$course_id, $title, $description, $filePath, $fileType, $fileSize, $uploaded_by])) {
            $feedback .= '<div class="alert alert-info mt-3">Material created: ' . htmlspecialchars($title) . '</div>';
        } else {
            $feedback .= '<div class="alert alert-danger mt-3">Failed to save material to database.</div>';
        }
    }
}
// Fetch instructor's courses for dropdown
$database = new Database();
$db = $database->getConnection();
$instructor_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
$courses_stmt = $db->prepare("SELECT id, title, course_code FROM courses WHERE instructor_id = ?");
$courses_stmt->execute([$instructor_id]);
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Material - University LMS</title>
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
                        <i class="fas fa-upload"></i> Create Course Material
                    </h1>
                </div>
                <?php if (!empty($feedback)): ?>
                    <?= $feedback ?>
                <?php endif; ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form action="material_create.php" method="post" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="course_id" class="form-label">Course *</label>
                                        <select class="form-select" id="course_id" name="course_id" required>
                                            <option value="">Select Course</option>
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?> (<?= htmlspecialchars($course['course_code']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="file" class="form-label">Upload File</label>
                                        <input class="form-control" type="file" id="file" name="file">
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Create Material
                                    </button>
                                </form>
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
