<?php
// instructor/delete_assignment.php

require_once '../config/database.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
$instructor_id = $_SESSION['user_id'];
if (!isset($_GET['id'])) {
    header('Location: assignments.php');
    exit();
}
$assignment_id = $_GET['id'];
// Initialize database connection
$database = new Database();
$db = $database->getConnection();
// Only allow delete if assignment belongs to this instructor
$stmt = $db->prepare("DELETE FROM assignments WHERE id = ? AND created_by = ?");
$stmt->execute([$assignment_id, $instructor_id]);
header('Location: assignments.php');
exit();
