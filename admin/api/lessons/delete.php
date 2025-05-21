<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    // Check if lesson_id is provided
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['lesson_id'])) {
        throw new Exception('Lesson ID is required');
    }

    $lesson_id = $input['lesson_id'];

    // Start transaction
    $conn->beginTransaction();

    // Delete related exercises
    $stmt = $conn->prepare("DELETE FROM exercises WHERE lesson_id = ?");
    $stmt->execute([$lesson_id]);

    // Delete user progress
    $stmt = $conn->prepare("DELETE FROM user_progress WHERE lesson_id = ?");
    $stmt->execute([$lesson_id]);

    // Delete the lesson
    $stmt = $conn->prepare("DELETE FROM lessons WHERE lesson_id = ?");
    $stmt->execute([$lesson_id]);

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'ลบบทเรียนสำเร็จ'
    ]);

} catch(Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
