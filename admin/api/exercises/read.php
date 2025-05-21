<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    
    if ($id) {
        $stmt = $conn->prepare("SELECT * FROM exercises WHERE exercise_id = ?");
        $stmt->execute([$id]);
        $exercise = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'exercise' => $exercise
        ]);
    } else {
        $stmt = $conn->query("
            SELECT e.*, l.title as lesson_title, c.title as course_title
            FROM exercises e
            JOIN lessons l ON e.lesson_id = l.lesson_id
            JOIN courses c ON l.course_id = c.course_id
            ORDER BY c.title, l.order_number, e.exercise_id
        ");
        $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'exercises' => $exercises
        ]);
    }
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
