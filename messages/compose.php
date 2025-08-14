<?php
// messages/compose.php
require_once '../config/database.php';
requireLogin();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$message = '';

// Fetch users excluding the current user
$users_sql = "SELECT id, username, first_name, last_name, role FROM users WHERE id != ? ORDER BY role, last_name, first_name";
$stmt = $db->prepare($users_sql);
$stmt->execute([$user_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_id = intval($_POST['recipient_id']);
    $subject = trim($_POST['subject']);
    $content = trim($_POST['content']);
    if ($recipient_id && $subject && $content) {
        $sql = "INSERT INTO messages (sender_id, recipient_id, subject, content) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        if ($stmt->execute([$user_id, $recipient_id, $subject, $content])) {
            $message = '<span style=\'color:green\'>Message sent successfully!</span>';
        } else {
            $message = '<span style=\'color:red\'>Error: ' . htmlspecialchars($stmt->errorInfo()[2]) . '</span>';
        }
    } else {
        $message = '<span style=\'color:red\'>Please fill in all required fields.</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compose Message</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .form-container h2 {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-group textarea {
            min-height: 80px;
        }
        .form-actions {
            text-align: right;
        }
        .form-actions button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }
        .form-actions button:hover {
            background: #0056b3;
        }
        .message {
            margin-bottom: 18px;
        }
    </style>
</head>
<body>
    <?php include '../includes/student_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/student_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="form-container">
                    <h2>Compose Message</h2>
                    <?php if ($message): ?>
                        <div class="message"><?= $message ?></div>
                    <?php endif; ?>
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="recipient_id">To *</label>
                            <select id="recipient_id" name="recipient_id" required>
                                <option value="">-- Select Recipient --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>">
                                        <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?> (<?= htmlspecialchars($u['username']) ?>, <?= htmlspecialchars($u['role']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="content">Message *</label>
                            <textarea id="content" name="content" required></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit">Send Message</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
