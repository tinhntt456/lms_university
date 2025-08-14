<?php
// instructor/delete_quiz.php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
$instructor_id = $_SESSION['user_id'];
if (!isset($_GET['id'])) {
    header('Location: quizzes.php');
    exit();
}

$quiz_id = $_GET['id'];
// Initialize database connection
$database = new Database();
$db = $database->getConnection();
// Only allow delete if quiz belongs to this instructor
$stmt = $db->prepare("DELETE FROM quizzes WHERE id = ? AND created_by = ?");
$stmt->execute([$quiz_id, $instructor_id]);
header('Location: quizzes.php');
exit();
