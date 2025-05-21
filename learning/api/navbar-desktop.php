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
        'text' => 'ความคืบหน้า',
        'href' => 'progress',
        'icon' => 'fas fa-chart-line',
        'isActive' => $currentPage === 'progress'
    ],
    [
        'text' => 'การตั้งค่า',
        'href' => 'settings',
        'icon' => 'fas fa-cog',
        'isActive' => $currentPage === 'settings'
    ],
    [
        'text' => 'ออกจากระบบ',
        'href' => '../auth/logout.php',
        'icon' => 'fas fa-sign-out-alt',
        'isActive' => false
    ]
];

echo json_encode(['items' => $navItems]);
?>
