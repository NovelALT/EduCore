<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    $stmt = $conn->query("
        SELECT exercise_id, title 
        FROM exercises 
        ORDER BY exercise_id DESC
    ");
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'exercises' => $exercises
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
