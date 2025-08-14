<?php
// instructor/delete_student.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';
$db = new Database();
$pdo = $db->getConnection();

$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
if ($student_id > 0 && $course_id > 0) {
    $instructor_id = $_SESSION['user_id'];
    // Verify instructor owns the course
    $stmt = $pdo->prepare('SELECT id FROM courses WHERE id = ? AND instructor_id = ?');
    $stmt->execute([$course_id, $instructor_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare('DELETE FROM enrollments WHERE student_id = ? AND course_id = ?');
        $stmt->execute([$student_id, $course_id]);
    }
}
header('Location: students.php');
exit();
?>
