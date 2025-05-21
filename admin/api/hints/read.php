<?php
header('Content-Type: application/json');
require_once('../../../config/db_admin.php');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Hint ID is required');
    }

    $stmt = $conn->prepare("
        SELECT h.*, e.title as exercise_title 
        FROM hints h
        LEFT JOIN exercises e ON h.exercise_id = e.exercise_id 
        WHERE h.hint_id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $hint = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hint) {
        throw new Exception('Hint not found');
    }

    echo json_encode([
        'success' => true,
        'hint' => $hint
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
