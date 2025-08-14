<?php
// instructor/delete_grade.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';
$db = new Database();
$pdo = $db->getConnection();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $stmt = $pdo->prepare('DELETE FROM grades WHERE grade_id = ?');
    $stmt->execute([$id]);
}
header('Location: grading.php');
exit();
?>
