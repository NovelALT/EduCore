<?php
header('Content-Type: application/json');

$currentPage = isset($_GET['page']) ? $_GET['page'] : '';

$navItems = [
    [
        'text' => 'แดชบอร์ด',
        'href' => 'learn',
        'icon' => 'fas fa-home',
        'isActive' => $currentPage === 'dashboard'
    ],
    [
        'text' => 'คอร์สเรียน',
        'href' => 'course',
        'icon' => 'fas fa-book',
        'isActive' => $currentPage === 'courses'
    ],
    [
        'text' => 'การตั้งค่า',
        'href' => 'settings',
        'icon' => 'fas fa-tasks',
        'isActive' => $currentPage === 'quizzes'
    ]
];

echo json_encode(['items' => $navItems]);
?>
