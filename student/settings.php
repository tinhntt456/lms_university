<?php
// settings.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once '../config/database.php';



$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];
$message = '';

// Get user info using PDO
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $profile_image = trim($_POST['profile_image']);
    if ($first_name && $last_name && $email) {
        $sql = "UPDATE users SET first_name=?, last_name=?, email=?, profile_image=? WHERE id=?";
        $stmt = $db->prepare($sql);
        if ($stmt->execute([$first_name, $last_name, $email, $profile_image, $user_id])) {
            $message = '<span style="color:green">Settings updated successfully!</span>';
        } else {
            $message = '<span style="color:red">Error: ' . htmlspecialchars($stmt->errorInfo()[2]) . '</span>';
        }
    } else {
        $message = '<span style="color:red">Please fill in all required fields.</span>';
    }
    // Refresh user info
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Settings - University LMS</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/student_dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/student_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/student_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 mb-0"><i class="fas fa-user-cog"></i> Account Settings</h1>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-7 col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-user-edit"></i> Edit Your Information
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-info mb-3" role="alert">
                                        <?= $message ?>
                                    </div>
                                <?php endif; ?>
                                <form method="post" action="">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="profile_image" class="form-label">Profile Image URL</label>
                                        <input type="text" id="profile_image" name="profile_image" class="form-control" value="<?= htmlspecialchars($user['profile_image']) ?>">
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                                    </div>
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
