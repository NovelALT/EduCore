CREATE DATABASE IF NOT EXISTS codly;
USE codly;

-- Users Table (ตารางผู้ใช้)
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    firstname VARCHAR(100),
    lastname VARCHAR(100),
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    role ENUM('student', 'bdc_student', 'teacher', 'admin') DEFAULT 'student',
    hint_points INT DEFAULT 10
);

-- Courses Table (ตารางคอร์ส)
CREATE TABLE courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    level ENUM('beginner', 'intermediate', 'advanced'),
    category VARCHAR(50),
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP
);

-- Lessons Table (ตารางบทเรียน)
CREATE TABLE lessons (
    lesson_id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT,
    title VARCHAR(100) NOT NULL,
    content TEXT,
    order_number INT,
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);

-- Exercises Table (ตารางแบบฝึกหัด)
CREATE TABLE exercises (
    exercise_id INT PRIMARY KEY AUTO_INCREMENT,
    lesson_id INT,
    title VARCHAR(100),
    description TEXT,
    initial_code TEXT,
    solution_code TEXT,
    test_cases TEXT,
    FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id)
);

-- User Progress Table (ตารางความคืบหน้าของผู้ใช้)
CREATE TABLE user_progress (
    user_id INT,
    lesson_id INT,
    status ENUM('not_started', 'in_progress', 'completed'),
    score INT DEFAULT 0,
    completed_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id)
);

-- เพิ่ม unique constraint และ index สำหรับ user_progress
ALTER TABLE user_progress 
ADD CONSTRAINT unique_user_lesson 
UNIQUE (user_id, lesson_id);

CREATE INDEX idx_user_progress_status 
ON user_progress (user_id, lesson_id, status);

-- Exercise Submissions Table (ตารางการส่งแบบฝึกหัด)
CREATE TABLE submissions (
    submission_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    exercise_id INT,
    submitted_code TEXT,
    status ENUM('pending', 'passed', 'failed'),
    feedback TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (exercise_id) REFERENCES exercises(exercise_id)
);

-- Achievements Table (ตารางความสำเร็จ)
CREATE TABLE achievements (
    achievement_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100),
    description TEXT,
    icon_url VARCHAR(255),
    points INT
);

-- User Achievements Table (ตารางความสำเร็จของผู้ใช้)
CREATE TABLE user_achievements (
    user_id INT,
    achievement_id INT,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (achievement_id) REFERENCES achievements(achievement_id)
);

-- Code Progress Table (ตารางความคืบหน้าของโค้ด)
CREATE TABLE code_progress (
    progress_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    exercise_id INT,
    current_code TEXT,
    cursor_position INT,
    scroll_position INT,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (exercise_id) REFERENCES exercises(exercise_id)
);

-- Exercise Checkpoints Table (ตารางจุดเช็คพอยท์ของแบบฝึกหัด)
CREATE TABLE exercise_checkpoints (
    checkpoint_id INT PRIMARY KEY AUTO_INCREMENT,
    exercise_id INT,
    checkpoint_name VARCHAR(100),
    expected_output TEXT,
    order_number INT,
    FOREIGN KEY (exercise_id) REFERENCES exercises(exercise_id)
);

-- User Checkpoint Progress (ตารางความคืบหน้าของจุดเช็คพอยท์)
CREATE TABLE user_checkpoint_progress (
    user_id INT,
    checkpoint_id INT,
    completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (checkpoint_id) REFERENCES exercise_checkpoints(checkpoint_id)
);

-- Hints Table (ตารางคำใบ้)
CREATE TABLE hints (
    hint_id INT PRIMARY KEY AUTO_INCREMENT,
    exercise_id INT,
    content TEXT NOT NULL,
    type ENUM('code_snippet', 'explanation', 'example', 'solution_step') NOT NULL,
    cost INT DEFAULT 1,
    order_number INT,
    FOREIGN KEY (exercise_id) REFERENCES exercises(exercise_id)
);

-- User Hints Usage Table (ตารางการใช้คำใบ้ของผู้ใช้)
CREATE TABLE user_hints (
    user_id INT,
    hint_id INT,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (hint_id) REFERENCES hints(hint_id)
);

-- ลบตารางเหล่านี้ออก
-- CREATE TABLE certificate_templates
-- CREATE TABLE user_certificates
-- CREATE TABLE competitions