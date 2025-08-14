<?php
require_once '../config/database.php';
requireLogin();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Get user info using PDO
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .settings-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .settings-header {
            margin-bottom: 24px;
        }
        .settings-info label {
            font-weight: 500;
            margin-top: 10px;
            display: block;
        }
        .settings-info span {
            display: block;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <?php include '../includes/student_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/student_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom settings-header">
                    <h1 class="h2">
                        <i class="fas fa-cog"></i> Settings
                    </h1>
                </div>
                <div class="settings-container">
                    <div class="settings-info">
                        <label>Email:</label>
                        <span><?= htmlspecialchars($user['email']) ?></span>
                        <label>Username:</label>
                        <span><?= htmlspecialchars($user['username']) ?></span>
                        <label>Change Password:</label>
                        <span><a href="#" class="btn btn-sm btn-outline-primary">Change Password</a></span>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
