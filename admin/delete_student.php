<?php
// admin/delete_student.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $stmt = $conn->prepare('DELETE FROM users WHERE id = ? AND role = "student"');
    $stmt->execute([$id]);
}
header('Location: students.php');
exit();
?>
