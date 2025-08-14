-- ========================================
-- Create database
-- ========================================
CREATE DATABASE IF NOT EXISTS lms_university;
USE lms_university;

-- =========================
-- Users table
-- =========================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('student', 'instructor', 'admin') NOT NULL,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =========================
-- Courses table
-- =========================
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    instructor_id INT NOT NULL,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    credits INT DEFAULT 3,
    semester VARCHAR(20),
    year INT,
    max_students INT DEFAULT 50,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- Enrollments table
-- =========================
CREATE TABLE enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('enrolled', 'completed', 'dropped') DEFAULT 'enrolled',
    final_grade DECIMAL(5,2),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id)
);

-- =========================
-- Course materials table
-- =========================
CREATE TABLE course_materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_path VARCHAR(500),
    file_type VARCHAR(50),
    file_size INT,
    uploaded_by INT NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- Assignments table
-- =========================
CREATE TABLE assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    due_date DATETIME,
    max_points DECIMAL(5,2) DEFAULT 100.00,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- Submissions table
-- =========================
CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade DECIMAL(5,2),
    feedback TEXT,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission (assignment_id, student_id)
);

-- =========================
-- Quizzes table
-- =========================
CREATE TABLE quizzes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    time_limit INT,
    max_attempts INT DEFAULT 1,
    due_date DATETIME,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- Quiz questions table
-- =========================
CREATE TABLE quiz_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice', 'true_false', 'short_answer') NOT NULL,
    points DECIMAL(5,2) DEFAULT 1.00,
    correct_answer TEXT,
    question_order INT,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- =========================
-- Quiz question options table
-- =========================
CREATE TABLE quiz_question_options (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT NOT NULL,
    option_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    option_order INT,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
);

-- =========================
-- Quiz attempts table
-- =========================
CREATE TABLE quiz_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    student_id INT NOT NULL,
    attempt_number INT NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    score DECIMAL(5,2),
    total_points DECIMAL(5,2),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- Quiz answers table
-- =========================
CREATE TABLE quiz_answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_text TEXT,
    selected_option_id INT,
    is_correct BOOLEAN,
    points_earned DECIMAL(5,2),
    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (selected_option_id) REFERENCES quiz_question_options(id) ON DELETE SET NULL
);

-- =========================
-- Forum categories table
-- =========================
CREATE TABLE forum_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- =========================
-- Forum topics table
-- =========================
CREATE TABLE forum_topics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_post_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_pinned BOOLEAN DEFAULT FALSE,
    is_locked BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (category_id) REFERENCES forum_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- Forum posts table
-- =========================
CREATE TABLE forum_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    topic_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES forum_topics(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- Messages table
-- =========================
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    subject VARCHAR(200),
    content TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    is_deleted_by_sender BOOLEAN DEFAULT FALSE,
    is_deleted_by_recipient BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- Course discussions table
-- =========================
CREATE TABLE course_discussions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    sender_id INT NOT NULL,
    content TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- Analytics table
-- =========================
CREATE TABLE analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(15,2) NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- Reports table
-- =========================
CREATE TABLE reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_title VARCHAR(200) NOT NULL,
    report_type VARCHAR(100),
    generated_by INT NOT NULL,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- Settings table
-- =========================
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =========================
-- System logs table
-- =========================
CREATE TABLE system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =========================
-- Announcements table
-- =========================
CREATE TABLE announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    instructor_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- Grades table
-- =========================
CREATE TABLE grades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    assignment_id INT DEFAULT NULL,
    quiz_id INT DEFAULT NULL,
    grade DECIMAL(5,2) NOT NULL,
    feedback TEXT,
    graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- =========================
-- Student attendance table
-- =========================
CREATE TABLE IF NOT EXISTS attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'present',
    note TEXT,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, course_id, date)
);

-- =========================
-- Student notifications table
-- =========================
CREATE TABLE IF NOT EXISTS student_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- Student schedule table
-- =========================
CREATE TABLE IF NOT EXISTS student_schedule (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    day_of_week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(255),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- =========================
-- Student payments table
-- =========================
CREATE TABLE IF NOT EXISTS student_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'credit_card', 'online') NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    note TEXT,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- Student certificates table
-- =========================
CREATE TABLE IF NOT EXISTS student_certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    certificate_name VARCHAR(255) NOT NULL,
    issue_date DATE NOT NULL,
    certificate_file VARCHAR(255),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- =========================
-- Student resources table
-- =========================
CREATE TABLE IF NOT EXISTS student_resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(500),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO student_resources (student_id, title, description, file_path) VALUES
(3, 'HTML Cheat Sheet', 'Summary document for HTML syntax', 'uploads/html_cheatsheet.pdf'),
(4, 'Python Functions Guide', 'Guide document for Python functions', 'uploads/python_functions.pdf');

-- =========================
-- Sample users
-- =========================
INSERT INTO users (username, email, password, first_name, last_name, role) VALUES
('admin', 'admin@university.edu', '$2b$12$OhrHRimaGyqcWAQjqh24n.Dpl6w5mDTSzIZFsHoEeaqRTF/cHIS3O', 'System', 'Administrator', 'admin'),
('prof_smith', 'smith@university.edu', '$2b$12$.JhV6XAfHFW7o3DYElf3aexPqKv.tSpmqSGppchD/oZ0mF0OqZOGK', 'John', 'Smith', 'instructor'),
('student1', 'student1@university.edu', '$2b$12$MxObukYWvtSzngM4wR9lDeX1sziYLq50Jh.uEmNJNAloHHVVxltXm', 'Jane', 'Doe', 'student');

-- Sample enrollments for student1
INSERT INTO enrollments (student_id, course_id, status) VALUES
(3, 1, 'enrolled'),
(3, 2, 'enrolled');

-- Sample grades for student1
INSERT INTO grades (student_id, course_id, assignment_id, grade, feedback) VALUES
(3, 1, 1, 95.00, 'Excellent work!'),
(3, 2, 2, 88.50, 'Good effort!');


INSERT INTO submissions (assignment_id, student_id, file_url, grade, feedback) VALUES
(1, 3, 'uploads/assignment1_student1.pdf', 95.00, 'Well done!'),
(2, 3, 'uploads/assignment2_student1.pdf', 88.50, 'Nice job!');

-- Sample quiz grades for student1
INSERT INTO grades (student_id, course_id, quiz_id, grade, feedback) VALUES
(3, 1, 1, 18.00, 'Great quiz performance!'),
(3, 2, 2, 15.50, 'Good quiz effort!');

-- Sample quiz attempts for student1
INSERT INTO quiz_attempts (quiz_id, student_id, attempt_number, score, total_points) VALUES
(1, 3, 1, 18.00, 20.00),
(2, 3, 1, 15.50, 20.00);

-- Sample notifications for student1
INSERT INTO student_notifications (student_id, title, message) VALUES
(3, 'Welcome to the LMS!', 'Your account has been created.'),
(3, 'Assignment Due', 'Don\'t forget to submit Assignment 1 by Friday!');

-- Sample attendance for student1
INSERT INTO attendance (student_id, course_id, date, status) VALUES
(3, 1, '2025-08-10', 'present'),
(3, 2, '2025-08-11', 'absent');

-- Sample schedule for student1
INSERT INTO student_schedule (student_id, course_id, day_of_week, start_time, end_time, location) VALUES
(3, 1, 'Monday', '08:00', '10:00', 'Room 101'),
(3, 2, 'Wednesday', '10:00', '12:00', 'Room 202');

-- Sample payment for student1
INSERT INTO student_payments (student_id, amount, payment_date, payment_method, status) VALUES
(3, 500.00, '2025-08-01', 'online', 'completed');

-- Sample certificate for student1
INSERT INTO student_certificates (student_id, course_id, certificate_name, issue_date, certificate_file) VALUES
(3, 1, 'HTML Basics Certificate', '2025-08-12', 'uploads/certificate_html.pdf');

-- Sample courses for student1 (explicit IDs)
INSERT INTO courses (id, title, description, instructor_id, course_code, semester, year) VALUES
(1, 'Web Development', 'Learn HTML, CSS, JS', 2, 'WD101', 'Fall', 2025),
(2, 'Python Programming', 'Intro to Python', 2, 'PY202', 'Fall', 2025);

-- Sample assignments for these courses
INSERT INTO assignments (course_id, title, description, due_date, created_by) VALUES
(1, 'HTML Assignment', 'Create a simple HTML page', '2025-08-20 23:59:00', 2),
(2, 'Python Functions', 'Write Python functions', '2025-08-22 23:59:00', 2);

-- Sample quizzes for these courses
INSERT INTO quizzes (course_id, title, description, time_limit, max_attempts, due_date, created_by) VALUES
(1, 'HTML Basics Quiz', 'Quiz on HTML basics', 20, 1, '2025-08-21 23:59:00', 2),
(2, 'Python Quiz', 'Quiz on Python basics', 30, 1, '2025-08-23 23:59:00', 2);

-- Sample forum categories for these courses
INSERT INTO forum_categories (course_id, name, description) VALUES
(1, 'Web Dev Q&A', 'Questions and answers for Web Development'),
(2, 'Python Help', 'Discussion for Python Programming');

-- Enroll student1 in both courses
INSERT INTO enrollments (student_id, course_id, status) VALUES
(3, 1, 'enrolled'),
(3, 2, 'enrolled');

INSERT INTO submissions (assignment_id, student_id, file_url, grade, feedback) VALUES
(1, 3, 'uploads/assignment1_student1.pdf', 95.00, 'Well done!'),
(2, 3, 'uploads/assignment2_student1.pdf', 88.50, 'Nice job!');
-- Assignment chưa nộp cho student3
INSERT INTO assignments (id, course_id, title, due_date) VALUES
(101, 1, 'Research Paper', '2025-09-20 23:59');

-- Quiz chưa làm cho student3
INSERT INTO quizzes (id, course_id, title, due_date) VALUES
(201, 1, 'Midterm Quiz', '2025-09-25 23:59');

-- Quiz đã làm cho student3
INSERT INTO quiz_attempts (quiz_id, student_id, attempt_number, score, total_points, completed_at) VALUES
(201, 3, 1, 17.00, 20.00, '2025-09-25 20:00');

-- Thêm điểm cho assignment và quiz mới
INSERT INTO grades (student_id, course_id, assignment_id, grade, feedback, graded_at) VALUES
(3, 1, 101, 92.00, 'Good job on research!', '2025-09-21 10:00');
INSERT INTO grades (student_id, course_id, quiz_id, grade, feedback, graded_at) VALUES
(3, 1, 201, 17.00, 'Well done on midterm quiz!', '2025-09-26 09:00');

-- Sample quiz attempts for student1
INSERT INTO quiz_attempts (quiz_id, student_id, attempt_number, score, total_points) VALUES
(1, 3, 1, 18.00, 20.00),
(2, 3, 1, 15.50, 20.00);

-- Sample grades for student1
INSERT INTO grades (student_id, course_id, assignment_id, grade, feedback) VALUES
(3, 1, 1, 95.00, 'Excellent work!'),
(3, 2, 2, 88.50, 'Good effort!');