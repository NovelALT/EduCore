INSERT INTO courses (title, description, level, category, image_url) VALUES
('Python Programming', 'The Python Forge: หลอมรวมความรู้จนเป็นนักพัฒนา', 'beginner', 'Programming', '/assets/images/courses/python.jpg');

INSERT INTO achievements (title, description, icon_url, points) VALUES
('เริ่มต้นใช้งาน Python', 'เขียนโปรแกรม Python แรกของคุณสำเร็จ', '/assets/icons/python-first.png', 10),
('นักคำนวณตัวน้อย', 'เขียนโปรแกรมคำนวณพื้นฐานได้สำเร็จ', '/assets/icons/calculator.png', 20);

INSERT INTO users (username, email, password_hash, firstname, lastname, role, profile_image, hint_points) VALUES
('admin', 'admin@codly.com', '$2a$12$qg0rDxtKE0HVJn4KdBRrIu3JA1XcsC5wpelneIfDVus3r9ghTt7Xa', 'Admin', 'Codly', 'admin', '/assets/images/profiles/admin.jpg', 100),
('std1', 'std01@bdc.ac.th', '$2a$12$qg0rDxtKE0HVJn4KdBRrIu3JA1XcsC5wpelneIfDVus3r9ghTt7Xa', 'สมชาย', 'ใจดี', 'bdc_student', '/assets/images/profiles/default.jpg', 20),
('std2', 'std02@bdc.ac.th', '$2a$12$qg0rDxtKE0HVJn4KdBRrIu3JA1XcsC5wpelneIfDVus3r9ghTt7Xa', 'สมหญิง', 'รักเรียน', 'bdc_student', '/assets/images/profiles/default.jpg', 20),
('std3', 'std03@bdc.ac.th', '$2a$12$qg0rDxtKE0HVJn4KdBRrIu3JA1XcsC5wpelneIfDVus3r9ghTt7Xa', 'สมหญิงใจรัก', 'ไม่เรียน', 'bdc_student', '/assets/images/profiles/default.jpg', 20),

INSERT INTO achievements (achievement_id, title, description, icon_url, points) VALUES
(1, 'เริ่มต้นการเรียนรู้', 'เริ่มต้นบทเรียนแรกของคุณ', '/assets/icons/first-lesson.png', 10),
(2, 'นักเรียนขยัน', 'เรียนจบ 5 บทเรียน', '/assets/icons/diligent.png', 20),
(3, 'นักแก้ปัญหา', 'แก้โจทย์ปัญหาสำเร็จ 3 ข้อ', '/assets/icons/problem-solver.png', 30),
(4, 'ผู้ชนะการแข่งขัน', 'ชนะการแข่งขันครั้งแรก', '/assets/icons/winner.png', 50),
(5, 'Python Master', 'จบคอร์ส Python ขั้นสูง', '/assets/icons/python-master.png', 100);

INSERT INTO user_achievements (user_id, achievement_id) VALUES
(4, 1), -- "เริ่มต้นการเรียนรู้"
(4, 2), --"นักเรียนขยัน"
(5, 1), -- "เริ่มต้นการเรียนรู้"
(6, 1); -- "เริ่มต้นการเรียนรู้"

INSERT INTO code_progress (user_id, exercise_id, current_code) VALUES
(4, 1, 'def hello_world():\n    print("Hello, World!")'),
(5, 1, 'def hello_world():\n    return "Hello, World!"'),
(6, 1, 'print("Hello World")');
