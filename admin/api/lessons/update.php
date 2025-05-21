<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    if (!isset($_POST['lesson_id'])) {
        throw new Exception('Lesson ID is required');
    }

    $lesson_id = $_POST['lesson_id'];
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $content = $_POST['content'] ?? null;
    $order_number = $_POST['order_number'];

    // Update lesson
    $stmt = $conn->prepare("UPDATE lessons SET 
        course_id = :course_id,
        title = :title,
        content = :content,
        order_number = :order_number
        WHERE lesson_id = :lesson_id");

    $stmt->execute([
        ':lesson_id' => $lesson_id,
        ':course_id' => $course_id,
        ':title' => $title,
        ':content' => $content,
        ':order_number' => $order_number
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'อัปเดตบทเรียนสำเร็จ'
    ]);

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
