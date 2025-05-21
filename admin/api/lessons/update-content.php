<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    if (!isset($_POST['lesson_id']) || !isset($_POST['content'])) {
        throw new Exception('Missing required fields');
    }

    $lesson_id = $_POST['lesson_id'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("UPDATE lessons SET content = :content WHERE lesson_id = :lesson_id");
    $stmt->execute([
        ':lesson_id' => $lesson_id,
        ':content' => $content
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'อัปเดตเนื้อหาบทเรียนสำเร็จ'
    ]);

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
