<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if ($id) {
        // Get specific lesson
        $stmt = $conn->prepare("SELECT * FROM lessons WHERE lesson_id = ?");
        $stmt->execute([$id]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'lesson' => $lesson
        ]);
    } else {
        // Get all lessons
        $stmt = $conn->query("
            SELECT l.*, c.title as course_title 
            FROM lessons l
            JOIN courses c ON l.course_id = c.course_id
            ORDER BY c.title, l.order_number
        ");
        $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'lessons' => $lessons
        ]);
    }
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
