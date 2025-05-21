<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    // Check if exercise_id is provided
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['exercise_id'])) {
        throw new Exception('Exercise ID is required');
    }

    $exercise_id = $input['exercise_id'];

    // Delete associated submissions first (due to foreign key constraint)
    $stmt = $conn->prepare("DELETE FROM submissions WHERE exercise_id = ?");
    $stmt->execute([$exercise_id]);

    // Delete the exercise
    $stmt = $conn->prepare("DELETE FROM exercises WHERE exercise_id = ?");
    $stmt->execute([$exercise_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'ลบแบบฝึกหัดสำเร็จ'
        ]);
    } else {
        throw new Exception('ไม่พบแบบฝึกหัดที่ต้องการลบ');
    }

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
