<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
  header("Location: ../auth/login.php");
  exit();
}


$instructor_id = $_SESSION['user_id'];
$message = '';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $submission_id = $_POST['submission_id'];
  $grade = $_POST['grade'];
  // Update submissions table
  $stmt = $db->prepare("UPDATE submissions SET grade = ? WHERE id = ?");
  $stmt->execute([$grade, $submission_id]);

  // Lấy thông tin assignment, student, course từ submission
  $info_stmt = $db->prepare("SELECT assignment_id, student_id FROM submissions WHERE id = ?");
  $info_stmt->execute([$submission_id]);
  $info = $info_stmt->fetch(PDO::FETCH_ASSOC);
  if ($info) {
    $assignment_id = $info['assignment_id'];
    $student_id = $info['student_id'];
    // Lấy course_id từ assignment
    $course_stmt = $db->prepare("SELECT course_id FROM assignments WHERE id = ?");
    $course_stmt->execute([$assignment_id]);
    $course_id = $course_stmt->fetchColumn();
    // Kiểm tra đã có điểm chưa
    $check_stmt = $db->prepare("SELECT grade_id FROM grades WHERE student_id = ? AND assignment_id = ?");
    $check_stmt->execute([$student_id, $assignment_id]);
    $grade_id = $check_stmt->fetchColumn();
    if ($grade_id) {
      // Update điểm
      $update_stmt = $db->prepare("UPDATE grades SET grade = ?, graded_at = NOW() WHERE grade_id = ?");
      $update_stmt->execute([$grade, $grade_id]);
    } else {
      // Insert điểm mới
      $insert_stmt = $db->prepare("INSERT INTO grades (student_id, course_id, assignment_id, grade, graded_at) VALUES (?, ?, ?, ?, NOW())");
      $insert_stmt->execute([$student_id, $course_id, $assignment_id, $grade]);
    }
  }
  $message = "✅ Grade updated!";
}

// Fetch submissions for instructor's courses
$sql = "
  SELECT s.id, s.file_url, s.submitted_at, s.grade,
         u.first_name, u.last_name,
         a.title AS assignment_title,
         c.title AS course_title
  FROM submissions s
  JOIN users u ON s.student_id = u.id
  JOIN assignments a ON s.assignment_id = a.id
  JOIN courses c ON a.course_id = c.id
  WHERE c.instructor_id = ?
  ORDER BY s.submitted_at DESC
";
$stmt = $db->prepare($sql);
$stmt->execute([$instructor_id]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submissions</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
  <div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">📤 Student Submissions</h1>

    <?php if ($message): ?>
      <p class="mb-4 text-green-600"><?= $message ?></p>
    <?php endif; ?>

    <?php if (count($result) > 0): ?>
      <div class="space-y-4">
        <?php foreach ($result as $row): ?>
          <div class="bg-white p-4 shadow rounded">
            <h2 class="text-lg font-semibold"><?= htmlspecialchars($row['assignment_title']) ?> – <?= htmlspecialchars($row['course_title']) ?></h2>
            <p class="text-sm text-gray-600">Student: <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></p>
            <p class="text-sm">Submitted: <?= $row['submitted_at'] ?></p>
            <?php
              $file_url = $row['file_url'];
              if (strpos($file_url, 'uploads/') === false) {
                $file_url = '../uploads/materials/' . basename($file_url);
              }
            ?>
            <p class="text-sm"><a href="<?= htmlspecialchars($file_url) ?>" target="_blank" class="text-blue-500 underline">📎 View File</a></p>

            <form method="POST" class="mt-2 flex items-center gap-2">
              <input type="hidden" name="submission_id" value="<?= $row['id'] ?>">
              <input type="number" step="0.1" name="grade" placeholder="Grade" value="<?= $row['grade'] ?>" class="px-2 py-1 border rounded w-24">
              <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded">Save Grade</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-gray-600">No submissions found.</p>
    <?php endif; ?>
  </div>
</body>
</html>