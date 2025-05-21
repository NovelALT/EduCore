<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['course_id'])) {
        throw new Exception('Course ID is required');
    }

    $course_id = $input['course_id'];

    // Start transaction
    $conn->beginTransaction();

    // Delete related exercises
    $stmt = $conn->prepare("DELETE FROM exercises WHERE lesson_id IN (SELECT lesson_id FROM lessons WHERE course_id = ?)");
    $stmt->execute([$course_id]);

    // Delete lessons
    $stmt = $conn->prepare("DELETE FROM lessons WHERE course_id = ?");
    $stmt->execute([$course_id]);

    // Delete the course
    $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
    $stmt->execute([$course_id]);

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'ลบคอร์สสำเร็จ'
    ]);

} catch(Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
