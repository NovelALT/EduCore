<?php
header('Content-Type: application/json');
require_once('../../../config/db_connect.php');

try {
    $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    $stmt = $conn->query("SELECT COUNT(*) FROM courses");
    $total_courses = $stmt->fetch(PDO::FETCH_COLUMN);
    $total_pages = ceil($total_courses / $per_page);

    $stmt = $conn->prepare("SELECT * FROM courses ORDER BY created_at DESC LIMIT :offset, :per_page");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'courses' => $courses,
        'total_pages' => $total_pages,
        'current_page' => $page
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
