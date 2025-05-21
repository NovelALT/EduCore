<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    // Validate required fields
    if (empty($_POST['title'])) {
        throw new Exception('กรุณากรอกชื่อคอร์ส');
    }

    $title = $_POST['title'];
    $description = $_POST['description'] ?? null;
    $level = $_POST['level'] ?? 'beginner';
    $category = $_POST['category'] ?? null;

    // Handle image upload
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../../../assets/images/courses/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception('รองรับไฟล์ภาพนามสกุล jpg, jpeg, png, gif เท่านั้น');
        }

        $filename = 'course_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_url = 'assets/images/courses/' . $filename;
        }
    }

    // Insert course
    $stmt = $conn->prepare("
        INSERT INTO courses (title, description, level, category, image_url)
        VALUES (:title, :description, :level, :category, :image_url)
    ");

    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':level' => $level,
        ':category' => $category,
        ':image_url' => $image_url
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มคอร์สสำเร็จ',
        'course_id' => $conn->lastInsertId()
    ]);

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
