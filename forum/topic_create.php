<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$message = '';
$db = (new Database())->getConnection();
$categories = [];
if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    $stmt = $db->prepare("SELECT id, name FROM forum_categories WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $db->query("SELECT id, name FROM forum_categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    if ($category_id && $title && $content) {
        $stmt = $db->prepare("INSERT INTO forum_topics (category_id, title, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$category_id, $title, $user_id]);
        $topic_id = $db->lastInsertId();
        $stmt2 = $db->prepare("INSERT INTO forum_posts (topic_id, user_id, content) VALUES (?, ?, ?)");
        $stmt2->execute([$topic_id, $user_id, $content]);
        header('Location: topic_view.php?id=' . $topic_id);
        exit;
    } else {
        $message = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Please fill in all fields.</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Forum Topic</title>
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
                    <h1 class="h2 mb-0"><i class="fas fa-plus-circle"></i> Create New Forum Topic</h1>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-edit"></i> Enter Topic Information
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-danger mb-3" role="alert">
                                        <?= $message ?>
                                    </div>
                                <?php endif; ?>
                                <form method="post" action="">
                                    <div class="mb-3">
                                        <label class="form-label"> *</label>
                                        <select name="category_id" class="form-select" required>
                                            <option value="">-- Select Category --</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Topic Title *</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Content *</label>
                                        <textarea name="content" class="form-control" rows="5" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Create Topic</button>
                                </form>
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