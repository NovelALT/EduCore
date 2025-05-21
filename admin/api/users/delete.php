<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    $user_id = $_POST['id'];
    
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'ลบผู้ใช้สำเร็จ'
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
