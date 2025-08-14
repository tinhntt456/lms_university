<?php
// profile.php

require_once '../config/database.php';
requireLogin();
if (!hasRole('student')) {
    header('Location: auth/login.php');
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
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }
        .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 24px;
            background: #f2f2f2;
        }
        .profile-info label {
            font-weight: 500;
            margin-top: 10px;
            display: block;
        }
        .profile-info span {
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-user"></i> My Profile
                    </h1>
                </div>
                <div class="profile-container">
                    <div class="profile-header">
                        <img src="<?= $user['profile_image'] ? htmlspecialchars($user['profile_image']) : '../assets/img/default_profile.png' ?>" class="profile-img" alt="Profile">
                        <div>
                            <h2><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>
                            <span>@<?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['role']) ?>)</span>
                        </div>
                    </div>
                    <form method="post" enctype="multipart/form-data" style="margin-bottom:20px;">
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Change Profile Image:</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </form>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                        $targetDir = '../assets/img/';
                        $fileName = basename($_FILES['profile_image']['name']);
                        $targetFile = $targetDir . $user_id . '_' . $fileName;
                        $imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));
                        $check = getimagesize($_FILES['profile_image']['tmp_name']);
                        if($check !== false && ($imageFileType == 'jpg' || $imageFileType == 'jpeg' || $imageFileType == 'png' || $imageFileType == 'gif')) {
                            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
                                // Update DB
                                $updateSql = "UPDATE users SET profile_image = ? WHERE id = ?";
                                $updateStmt = $db->prepare($updateSql);
                                $updateStmt->execute([$targetFile, $user_id]);
                                echo '<div class="alert alert-success">Profile image updated!</div>';
                                // Reload user info
                                $stmt = $db->prepare($sql);
                                $stmt->execute([$user_id]);
                                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                            } else {
                                echo '<div class="alert alert-danger">Upload failed.</div>';
                            }
                        } else {
                            echo '<div class="alert alert-danger">Invalid image file.</div>';
                        }
                    }
                    ?>
                    <div class="profile-info">
                        <label>Email:</label>
                        <span><?= htmlspecialchars($user['email']) ?></span>
                        <label>Created At:</label>
                        <span><?= htmlspecialchars($user['created_at']) ?></span>
                        <label>Last Updated:</label>
                        <span><?= htmlspecialchars($user['updated_at']) ?></span>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
