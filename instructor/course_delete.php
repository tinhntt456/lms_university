<?php
// instructor/course_delete.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';
$db = new Database();
$pdo = $db->getConnection();
$instructor_id = $_SESSION['user_id'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    // Only allow delete if instructor owns the course
    $stmt = $pdo->prepare('DELETE FROM courses WHERE id = ? AND instructor_id = ?');
    $stmt->execute([$id, $instructor_id]);
}
header('Location: courses.php');
exit();
?>
