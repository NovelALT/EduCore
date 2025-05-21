<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('User ID is required');
    }

    $stmt = $conn->prepare("SELECT user_id, username, email, firstname, lastname, role FROM users WHERE user_id = ?");
    $stmt->execute([$_GET['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
