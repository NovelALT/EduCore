<?php
header('Content-Type: application/json');
require_once('../../config/db_connect.php');

function response($success, $data = null, $message = '') {
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $table = $_POST['table'] ?? $_GET['table'] ?? '';
    
    switch($action) {
        case 'create':
            $data = $_POST['data'];
            unset($data['id']);
            
            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_fill(0, count($data), '?'));
            
            $stmt = $conn->prepare("INSERT INTO $table ($columns) VALUES ($values)");
            $stmt->execute(array_values($data));
            
            response(true, ['id' => $conn->lastInsertId()], 'เพิ่มข้อมูลสำเร็จ');
            break;

        case 'read':
            $id = $_GET['id'] ?? null;
            if ($id) {
                $stmt = $conn->prepare("SELECT * FROM $table WHERE {$table}_id = ?");
                $stmt->execute([$id]);
                response(true, $stmt->fetch(PDO::FETCH_ASSOC));
            } else {
                $stmt = $conn->query("SELECT * FROM $table ORDER BY created_at DESC");
                response(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;

        case 'update':
            $id = $_POST['id'];
            $data = $_POST['data'];
            
            $sets = implode(', ', array_map(fn($key) => "$key = ?", array_keys($data)));
            $stmt = $conn->prepare("UPDATE $table SET $sets WHERE {$table}_id = ?");
            
            $values = array_values($data);
            $values[] = $id;
            
            $stmt->execute($values);
            response(true, null, 'อัปเดตข้อมูลสำเร็จ');
            break;

        case 'delete':
            $id = $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM $table WHERE {$table}_id = ?");
            $stmt->execute([$id]);
            response(true, null, 'ลบข้อมูลสำเร็จ');
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch(Exception $e) {
    response(false, null, $e->getMessage());
}
