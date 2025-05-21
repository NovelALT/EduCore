<?php
require_once('../config/db_connect.php');
session_start();

$isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjaxRequest && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'get_weekly_stats':
            $stmt = $pdo->prepare("
                SELECT 
                    DATE_FORMAT(up.completed_at, '%Y-%m-%d') as date,
                    COUNT(*) as completed_count
                FROM user_progress up
                WHERE up.user_id = ? 
                AND up.completed_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
                GROUP BY DATE_FORMAT(up.completed_at, '%Y-%m-%d')
            ");
            $stmt->execute([$_SESSION['user_id']]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            exit;
            
        case 'get_achievements':
            $stmt = $pdo->prepare("
                SELECT a.*, ua.earned_at 
                FROM achievements a
                LEFT JOIN user_achievements ua 
                    ON a.achievement_id = ua.achievement_id 
                    AND ua.user_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            exit;
    }
}

$user_id = $_SESSION['user_id'] ?? 1;
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT l.lesson_id) as total_lessons,
        COUNT(DISTINCT CASE WHEN up.status = 'completed' THEN l.lesson_id END) as completed_lessons,
        COUNT(DISTINCT e.exercise_id) as total_exercises,
        COUNT(DISTINCT CASE WHEN s.status = 'passed' THEN e.exercise_id END) as completed_exercises
    FROM lessons l
    LEFT JOIN user_progress up ON l.lesson_id = up.lesson_id AND up.user_id = ?
    LEFT JOIN exercises e ON l.lesson_id = e.lesson_id
    LEFT JOIN submissions s ON e.exercise_id = s.exercise_id AND s.user_id = ?
");
$stmt->execute([$user_id, $user_id]);
$progress = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT a.*, ua.earned_at 
    FROM achievements a
    LEFT JOIN user_achievements ua ON a.achievement_id = ua.achievement_id AND ua.user_id = ?
");
$stmt->execute([$user_id]);
$achievements = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT 
        'lesson' as type,
        l.title,
        l.lesson_id,
        up.completed_at as action_date,
        c.title as course_title
    FROM user_progress up
    JOIN lessons l ON up.lesson_id = l.lesson_id
    JOIN courses c ON l.course_id = c.course_id
    WHERE up.user_id = ? AND up.status = 'completed'
    UNION ALL
    SELECT 
        'exercise' as type,
        e.title,
        e.exercise_id,
        s.submitted_at as action_date,
        c.title as course_title
    FROM submissions s
    JOIN exercises e ON s.exercise_id = e.exercise_id
    JOIN lessons l ON e.lesson_id = l.lesson_id
    JOIN courses c ON l.course_id = c.course_id
    WHERE s.user_id = ? AND s.status = 'passed'
    ORDER BY action_date DESC
    LIMIT 5
");
$stmt->execute([$user_id, $user_id]);
$recent_activities = $stmt->fetchAll();

$overall_progress = 0;
if ($progress['total_lessons'] > 0) {
    $lesson_progress = ($progress['completed_lessons'] / $progress['total_lessons']) * 100;
    $exercise_progress = ($progress['completed_exercises'] / max(1, $progress['total_exercises'])) * 100;
    $overall_progress = round(($lesson_progress + $exercise_progress) / 2);
}

function getTimeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) {
        return $diff->y . ' ปีที่แล้ว';
    }
    if ($diff->m > 0) {
        return $diff->m . ' เดือนที่แล้ว';
    }
    if ($diff->d > 0) {
        return $diff->d . ' วันที่แล้ว';
    }
    if ($diff->h > 0) {
        return $diff->h . ' ชั่วโมงที่แล้ว';
    }
    if ($diff->i > 0) {
        return $diff->i . ' นาทีที่แล้ว';
    }
    return 'เมื่อสักครู่';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codly - ความคืบหน้าการเรียน</title>
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/css/progress.css">
    <link rel="stylesheet" href="../assets/css/learn.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/preload.css">
</head>
<body>
    <div class="preloader">
        <div class="loader"></div>
        <div class="preloader-text">Codly</div>
    </div>
    
    <div class="dashboard">
        <button class="mobile-toggle" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>

        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="#" class="sidebar-logo">CODLY</a>
            </div>
            <nav class="sidebar-nav" id="sidebarNav">
            </nav>
            <div class="user-profile">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                    <div class="profile-name"><?php echo htmlspecialchars($user['firstname']); ?></div>
                    <div class="profile-role"><?php echo htmlspecialchars($user['role']); ?></div>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <div class="progress-header">
                <h1>
                    <i class="fas fa-chart-line"></i>
                    ความคืบหน้าการเรียน
                </h1>
                <p class="header-subtitle">ติดตามความก้าวหน้าและผลลัพธ์การเรียนรู้ของคุณ</p>
            </div>

            <div class="progress-overview">
                <div class="progress-card">
                    <div class="progress-title">ความคืบหน้าโดยรวม</div>
                    <div class="progress-circle">
                        <div class="percent"><?php echo $overall_progress; ?>%</div>
                    </div>
                    <div class="progress-details">
                        <div class="detail-item">
                            <i class="fas fa-book-open"></i>
                            <span>บทเรียนที่เรียนจบ: <?php echo $progress['completed_lessons']; ?>/<?php echo $progress['total_lessons']; ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-code"></i>
                            <span>แบบฝึกหัดที่ทำ: <?php echo $progress['completed_exercises']; ?>/<?php echo $progress['total_exercises']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="progress-chart">
                    <h2>ความคืบหน้าในสัปดาห์นี้</h2>
                    <canvas id="weeklyProgress"></canvas>
                </div>

                <div class="achievements-section">
                    <h2>ความสำเร็จ</h2>
                    <div class="achievements-grid">
                        <?php foreach($achievements as $achievement): ?>
                        <div class="achievement-card <?php echo $achievement['earned_at'] ? '' : 'locked'; ?>">
                            <i class="<?php echo $achievement['earned_at'] ? 'fas fa-star' : 'fas fa-lock'; ?>"></i>
                            <h3><?php echo htmlspecialchars($achievement['title']); ?></h3>
                            <p><?php echo htmlspecialchars($achievement['description']); ?></p>
                            <?php if($achievement['earned_at']): ?>
                            <span class="earned-date">ได้รับเมื่อ: <?php echo date('d/m/Y', strtotime($achievement['earned_at'])); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="recent-activities">
                    <h2>กิจกรรมล่าสุด</h2>
                    <div class="activity-timeline">
                        <?php foreach($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas <?php echo $activity['type'] == 'lesson' ? 'fa-book' : 'fa-code'; ?>"></i>
                            </div>
                            <div class="activity-content">
                                <h4><?php echo htmlspecialchars($activity['title']); ?></h4>
                                <p><?php echo htmlspecialchars($activity['course_title']); ?></p>
                                <span class="time"><?php echo getTimeAgo($activity['action_date']); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const weeklyData = <?php echo json_encode($weekly_stats); ?>;
        new Chart(document.getElementById('weeklyProgress'), {
            type: 'line',
            data: {
                labels: weeklyData.map(data => data.date),
                datasets: [{
                    label: 'บทเรียนที่เรียนจบ',
                    data: weeklyData.map(data => data.completed_count),
                    borderColor: '#4CAF50',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'ความคืบหน้าการเรียนรายวัน'
                    }
                }
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function loadNavigation() {
                const apiEndpoint = window.innerWidth <= 768 ? 'api/navbar-mobile.php' : 'api/navbar-desktop.php';
                fetch(apiEndpoint + '?page=progress')
                    .then(response => response.json())
                    .then(data => {
                        const nav = document.getElementById('sidebarNav');
                        nav.innerHTML = data.items.map(item => `
                            <a href="${item.href}" class="nav-link ${item.isActive ? 'active' : ''}">
                                <div class="nav-indicator"></div>
                                <i class="${item.icon}"></i>
                                <span>${item.text}</span>
                            </a>
                        `).join('');
                    });
            }

            loadNavigation();
            window.addEventListener('resize', loadNavigation);
        });
    </script>
    <script src="../assets/js/preload.js"></script>
    <script>
    function loadWeeklyStats() {
        fetch('progress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'get_weekly_stats'
            })
        })
        .then(response => response.json())
        .then(data => {
            updateWeeklyChart(data.data);
        });
    }

    function loadAchievements() {
        fetch('progress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'get_achievements'
            })
        })
        .then(response => response.json())
        .then(data => {
            updateAchievements(data.data);
        });
    }
    </script>
</body>
</html>
