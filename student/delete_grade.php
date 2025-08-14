<?php
// student/delete_grade.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';
$db = new Database();
$pdo = $db->getConnection();
$student_id = $_SESSION['user_id'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $stmt = $pdo->prepare('DELETE FROM grades WHERE grade_id = ? AND student_id = ?');
    $stmt->execute([$id, $student_id]);
}
header('Location: grades.php');
exit();
?>
