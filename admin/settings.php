<?php
require_once '../config/database.php';
requireLogin();
if (!hasRole('admin')) {
    header('Location: ../auth/login.php');
    exit();
}
$database = new Database();
$conn = $database->getConnection();
// Handle settings update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setting_key'])) {
    $key = trim($_POST['setting_key']);
    $value = trim($_POST['setting_value']);
    $stmt = $conn->prepare("UPDATE settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?");
    $stmt->execute([$value, $key]);
    $message = 'Settings updated successfully.';
}
// Load all settings
$stmt = $conn->prepare("SELECT * FROM settings ORDER BY setting_key ASC");
$stmt->execute();
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage System Settings - University LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-cogs"></i> Manage System Settings
                    </h1>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-cogs"></i> Settings List
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-success"> <?= $message ?> </div>
                                <?php endif; ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Key</th>
                                                <th scope="col">Value</th>
                                                <th scope="col">Updated At</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if ($settings && count($settings) > 0): ?>
                                            <?php foreach($settings as $row): ?>
                                                <tr>
                                                    <form method="POST" class="align-middle">
                                                        <td class="align-middle"><input type="hidden" name="setting_key" value="<?= htmlspecialchars($row['setting_key']) ?>"> <b><?= htmlspecialchars($row['setting_key']) ?></b></td>
                                                        <td class="align-middle"><input type="text" name="setting_value" value="<?= htmlspecialchars($row['setting_value']) ?>" class="form-control"></td>
                                                        <td class="align-middle"><?= $row['updated_at'] ?></td>
                                                        <td class="align-middle"><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Lưu</button></td>
                                                    </form>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center">No settings found.</td></tr>
                                        <?php endif; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
