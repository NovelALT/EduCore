<?php
header('Content-Type: application/json');
require_once('../../../config/db_admin.php');

try {
    $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    $stmt = $conn->query("SELECT COUNT(*) FROM hints");
    $total_hints = $stmt->fetch(PDO::FETCH_COLUMN);
    $total_pages = ceil($total_hints / $per_page);

    $stmt = $conn->prepare("
        SELECT h.*, e.title as exercise_title 
        FROM hints h
        LEFT JOIN exercises e ON h.exercise_id = e.exercise_id 
        ORDER BY h.exercise_id, h.order_number 
        LIMIT :offset, :per_page
    ");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $hints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'hints' => $hints,
        'current_page' => $page,
        'total_pages' => $total_pages
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
