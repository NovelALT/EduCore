<?php
header('Content-Type: application/json');
require_once('../../../config/db_admin.php');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $hint_id = $data['hint_id'];
    
    $stmt = $conn->prepare("DELETE FROM hints WHERE hint_id = ?");
    $stmt->execute([$hint_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'ลบคำใบ้สำเร็จ'
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
