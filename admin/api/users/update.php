<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    $data = $_POST;
    $user_id = $data['user_id'];
    unset($data['user_id']);

    if (isset($data['password']) && !empty($data['password'])) {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
    } else {
        unset($data['password']);
    }

    $set = implode('=?, ', array_keys($data)) . '=?';
    $sql = "UPDATE users SET $set WHERE user_id = ?";
    
    $values = array_values($data);
    $values[] = $user_id;

    $stmt = $conn->prepare($sql);
    $stmt->execute($values);
    
    echo json_encode([
        'success' => true,
        'message' => 'อัปเดตข้อมูลสำเร็จ'
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
