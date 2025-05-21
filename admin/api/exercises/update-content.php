<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    if (!isset($_POST['exercise_id'])) {
        throw new Exception('Exercise ID is required');
    }

    $exercise_id = $_POST['exercise_id'];
    $initial_code = $_POST['initial_code'] ?? null;
    $solution_code = $_POST['solution_code'] ?? null;
    $test_cases = $_POST['test_cases'] ?? null;

    // Update exercise content
    $stmt = $conn->prepare("UPDATE exercises SET 
        initial_code = :initial_code,
        solution_code = :solution_code,
        test_cases = :test_cases
        WHERE exercise_id = :exercise_id");

    $stmt->execute([
        ':exercise_id' => $exercise_id,
        ':initial_code' => $initial_code,
        ':solution_code' => $solution_code,
        ':test_cases' => $test_cases
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'อัปเดตเนื้อหาแบบฝึกหัดสำเร็จ'
    ]);

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
