<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if ($id) {
        $stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ?");
        $stmt->execute([$id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'course' => $course]);
    } else {
        $stmt = $conn->query("SELECT * FROM courses ORDER BY created_at DESC");
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'courses' => $courses]);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
