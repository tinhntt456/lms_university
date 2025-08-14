<?php
// messages/sent.php

require_once '../config/database.php';
requireLogin();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Fetch sent messages using PDO
$sql = "SELECT m.*, u.first_name, u.last_name, u.username, u.role
        FROM messages m
        JOIN users u ON m.recipient_id = u.id
        WHERE m.sender_id = ? AND m.is_deleted_by_sender = 0
        ORDER BY m.sent_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sent Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .messages-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .messages-table th, .messages-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .messages-table th {
            background-color: #f2f2f2;
        }
        .subject-link {
            color: #007bff;
            text-decoration: underline;
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
                        <i class="fas fa-paper-plane"></i> Sent Messages
                    </h1>
                </div>
                <table class="messages-table">
                    <thead>
                        <tr>
                            <th>To</th>
                            <th>Subject</th>
                            <th>Sent At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($messages) > 0): ?>
                        <?php foreach ($messages as $msg): ?>
                            <tr>
                                <td><?= htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']) ?> (<?= htmlspecialchars($msg['username']) ?>, <?= htmlspecialchars($msg['role']) ?>)</td>
                                <td><a href="view.php?id=<?= $msg['id'] ?>" class="subject-link"><?= htmlspecialchars($msg['subject']) ?></a></td>
                                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($msg['sent_at']))) ?></td>
                                <td><?= $msg['read_at'] ? 'Read' : 'Unread' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No sent messages found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>
</body>
</html>
