<?php
header('Content-Type: application/json');
require_once('../../../config/db_admin.php');

try {
    $data = [
        'exercise_id' => $_POST['exercise_id'],
        'content' => $_POST['content'],
        'type' => $_POST['type'],
        'cost' => $_POST['cost'],
        'order_number' => $_POST['order_number']
    ];

    $sql = "INSERT INTO hints (exercise_id, content, type, cost, order_number) 
            VALUES (:exercise_id, :content, :type, :cost, :order_number)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
    
    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มคำใบ้สำเร็จ',
        'hint_id' => $conn->lastInsertId()
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
