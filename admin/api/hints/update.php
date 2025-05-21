<?php
header('Content-Type: application/json');
require_once('../../../config/db_admin.php');

try {
    $hint_id = $_POST['hint_id'];
    $data = [
        'exercise_id' => $_POST['exercise_id'],
        'content' => $_POST['content'],
        'type' => $_POST['type'],
        'cost' => $_POST['cost'],
        'order_number' => $_POST['order_number'],
        'hint_id' => $hint_id
    ];

    $sql = "UPDATE hints SET 
            exercise_id = :exercise_id,
            content = :content,
            type = :type,
            cost = :cost,
            order_number = :order_number
            WHERE hint_id = :hint_id";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
    
    echo json_encode([
        'success' => true,
        'message' => 'อัปเดตคำใบ้สำเร็จ'
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
