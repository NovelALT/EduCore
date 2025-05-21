<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    $data = $_POST;
    if (isset($data['password'])) {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
    }

    $columns = implode(', ', array_keys($data));
    $values = implode(', ', array_fill(0, count($data), '?'));
    
    $stmt = $conn->prepare("INSERT INTO users ($columns) VALUES ($values)");
    $stmt->execute(array_values($data));
    
    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มผู้ใช้สำเร็จ',
        'user_id' => $conn->lastInsertId()
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
