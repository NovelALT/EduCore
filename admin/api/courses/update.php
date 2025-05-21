<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    if (!isset($_POST['course_id'])) {
        throw new Exception('Course ID is required');
    }

    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $level = $_POST['level'];
    $category = $_POST['category'];

    // Handle image upload if provided
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../../assets/images/courses/';
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = 'course_' . time() . '.' . $file_ext;
        $upload_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_url = '../assets/images/courses/' . $file_name;
        }
    }

    // Update course
    $sql = "UPDATE courses SET 
            title = :title, 
            description = :description, 
            level = :level, 
            category = :category";

    if ($image_url) {
        $sql .= ", image_url = :image_url";
    }

    $sql .= " WHERE course_id = :course_id";

    $stmt = $conn->prepare($sql);
    $params = [
        ':course_id' => $course_id,
        ':title' => $title,
        ':description' => $description,
        ':level' => $level,
        ':category' => $category
    ];

    if ($image_url) {
        $params[':image_url'] = $image_url;
    }

    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'message' => 'อัปเดตคอร์สสำเร็จ'
    ]);

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
