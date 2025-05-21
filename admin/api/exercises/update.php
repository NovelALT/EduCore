<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    if (!isset($_POST['exercise_id'])) {
        throw new Exception('Exercise ID is required');
    }

    $exercise_id = $_POST['exercise_id'];
    $lesson_id = $_POST['lesson_id'];
    $title = $_POST['title'];
    $description = $_POST['description'] ?? null;

    // Update exercise
    $stmt = $conn->prepare("UPDATE exercises SET 
        lesson_id = :lesson_id,
        title = :title,
        description = :description
        WHERE exercise_id = :exercise_id");

    $stmt->execute([
        ':exercise_id' => $exercise_id,
        ':lesson_id' => $lesson_id,
        ':title' => $title,
        ':description' => $description
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'อัปเดตแบบฝึกหัดสำเร็จ'
    ]);

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
