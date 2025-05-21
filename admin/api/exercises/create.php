<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    // Validate required fields
    if (empty($_POST['lesson_id']) || empty($_POST['title'])) {
        throw new Exception('กรุณากรอกข้อมูลที่จำเป็น');
    }

    $lesson_id = $_POST['lesson_id'];
    $title = $_POST['title'];
    $description = $_POST['description'] ?? null;
    $initial_code = $_POST['initial_code'] ?? null;
    $solution_code = $_POST['solution_code'] ?? null;
    $test_cases = $_POST['test_cases'] ?? null;

    // Insert exercise
    $stmt = $conn->prepare("
        INSERT INTO exercises (
            lesson_id, title, description, initial_code, solution_code, test_cases
        ) VALUES (
            :lesson_id, :title, :description, :initial_code, :solution_code, :test_cases
        )
    ");

    $stmt->execute([
        ':lesson_id' => $lesson_id,
        ':title' => $title,
        ':description' => $description,
        ':initial_code' => $initial_code,
        ':solution_code' => $solution_code,
        ':test_cases' => $test_cases
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มแบบฝึกหัดสำเร็จ',
        'exercise_id' => $conn->lastInsertId()
    ]);

} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
