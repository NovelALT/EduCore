<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Submission ID is required');
    }

    $stmt = $conn->prepare("
        SELECT s.*, u.username, e.title as exercise_title, e.initial_code, e.solution_code
        FROM submissions s
        LEFT JOIN users u ON s.user_id = u.user_id
        LEFT JOIN exercises e ON s.exercise_id = e.exercise_id
        WHERE s.submission_id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$submission) {
        throw new Exception('Submission not found');
    }

    echo json_encode([
        'success' => true,
        'submission' => $submission
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
