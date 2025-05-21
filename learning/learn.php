<?php
require_once('../config/db_connect.php');

session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: ../auth/login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT c.*, COUNT(l.lesson_id) as total_lessons,
    COUNT(DISTINCT CASE WHEN up.status = 'completed' THEN l.lesson_id END) as completed_lessons
    FROM courses c
    LEFT JOIN lessons l ON c.course_id = l.course_id
    LEFT JOIN user_progress up ON l.lesson_id = up.lesson_id AND up.user_id = ?
    GROUP BY c.course_id
");
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll();

$statsQuery = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT CASE WHEN up.status = 'in_progress' THEN c.course_id END) as courses_in_progress,
        COUNT(DISTINCT CASE WHEN up.status = 'completed' THEN c.course_id END) as courses_completed,
        COALESCE(SUM(up.score), 0) as total_score
    FROM courses c
    LEFT JOIN lessons l ON c.course_id = l.course_id
    LEFT JOIN user_progress up ON l.lesson_id = up.lesson_id
    WHERE up.user_id = ?
");
$statsQuery->execute([$user_id]);
$stats = $statsQuery->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codly - Learning Dashboard</title>
    <link rel="stylesheet" href="../assets/main.css">
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
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $stats['courses_in_progress'] ?? 0; ?></div>
                    <div class="stat-label">คอร์สที่เรียนอยู่</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $stats['courses_completed'] ?? 0; ?></div>
                    <div class="stat-label">คอร์สที่เรียนจบแล้ว</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_score'] ?? 0; ?></div>
                    <div class="stat-label">คะแนนรวม</div>
                </div>
            </div>

            <h2 style="color: #FFD700; margin-bottom: 1.5rem;">คอร์สของคุณ</h2>
            <div class="course-grid">
                <?php foreach($courses as $course): 
                    $progress = ($course['total_lessons'] > 0) 
                        ? round(($course['completed_lessons'] / $course['total_lessons']) * 100) 
                        : 0;
                ?>
                <div class="course-card">
                    <div class="course-image">
                        <?php if($course['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($course['image_url']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <?php else: ?>
                            <i class="fab fa-html5"></i>
                        <?php endif; ?>
                    </div>
                    <div class="course-content">
                        <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                        <div class="course-progress">
                            <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <div class="course-stats">
                            <span><?php echo $progress; ?>% completed</span>
                            <span><?php echo $course['completed_lessons']; ?>/<?php echo $course['total_lessons']; ?> Lessons</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <?php if (isset($_SESSION['login_success'])): ?>
    <div class="toast success">
        <div class="toast-content">
            <i class="fas fa-check-circle"></i>
            <span>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
        </div>
        <div class="toast-progress"></div>
    </div>
    <?php unset($_SESSION['login_success']); endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function loadNavigation() {
                const apiEndpoint = window.innerWidth <= 768 ? 'api/navbar-mobile.php' : 'api/navbar-desktop.php';
                fetch(apiEndpoint + '?page=dashboard')
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

            const mobileToggle = document.querySelector('.mobile-toggle');
            const sidebar = document.querySelector('.sidebar');
            const dashboard = document.querySelector('.dashboard');

            mobileToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('active');
                this.setAttribute('aria-expanded', sidebar.classList.contains('active'));
            });

            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                        sidebar.classList.remove('active');
                        mobileToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });

            let touchStartX = 0;
            let touchEndX = 0;

            document.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, false);

            document.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, false);

            function handleSwipe() {
                const swipeThreshold = 100;
                const diff = touchEndX - touchStartX;

                if (Math.abs(diff) < swipeThreshold) return;

                if (diff > 0) {
                    sidebar.classList.add('active');
                } else {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>
    <script src="../assets/js/preload.js"></script>
</body>
</html>
