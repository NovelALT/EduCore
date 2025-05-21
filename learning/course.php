<?php
require_once('../config/db_connect.php');
session_start();

$isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjaxRequest && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $jsonInput = file_get_contents('php://input');
    $data = json_decode($jsonInput, true) ?? [];
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'search_courses':
            $search = '%' . ($data['search'] ?? '') . '%';
            $category = $data['category'] ?? '';
            $level = $data['level'] ?? '';
            
            $sql = "SELECT c.*, 
                    COUNT(DISTINCT l.lesson_id) as total_lessons,
                    COUNT(DISTINCT up.lesson_id) as completed_lessons
                   FROM courses c
                   LEFT JOIN lessons l ON c.course_id = l.course_id
                   LEFT JOIN user_progress up ON l.lesson_id = up.lesson_id 
                        AND up.user_id = ? AND up.status = 'completed'
                   WHERE c.title LIKE ?";
            
            if ($category) {
                $sql .= " AND c.category = ?";
            }
            if ($level) {
                $sql .= " AND c.level = ?";
            }
            
            $sql .= " GROUP BY c.course_id";
            
            $stmt = $pdo->prepare($sql);
            $params = [$_SESSION['user_id'], $search];
            if ($category) $params[] = $category;
            if ($level) $params[] = $level;
            
            $stmt->execute($params);
            echo json_encode(['courses' => $stmt->fetchAll()]);
            exit;
            
        case 'enroll':
            $stmt = $pdo->prepare("
                INSERT INTO user_progress (user_id, lesson_id, status)
                SELECT ?, l.lesson_id, 'not_started'
                FROM lessons l
                WHERE l.course_id = ?
                ON DUPLICATE KEY UPDATE status = status
            ");
            $result = $stmt->execute([$_SESSION['user_id'], $_POST['course_id']]);
            echo json_encode(['success' => $result]);
            exit;
    }
}


$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: ../auth/login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT 
        c.*,
        COUNT(DISTINCT l.lesson_id) as total_lessons,
        COUNT(DISTINCT CASE WHEN up.status = 'completed' THEN l.lesson_id END) as completed_lessons,
        COUNT(DISTINCT e.exercise_id) as total_exercises,
        COUNT(DISTINCT u.user_id) as student_count,
        (
            SELECT COUNT(DISTINCT e2.exercise_id) 
            FROM lessons l2 
            JOIN exercises e2 ON l2.lesson_id = e2.lesson_id 
            WHERE l2.course_id = c.course_id
        ) as exercise_count
    FROM courses c
    LEFT JOIN lessons l ON c.course_id = l.course_id
    LEFT JOIN exercises e ON l.lesson_id = e.lesson_id
    LEFT JOIN user_progress up ON l.lesson_id = up.lesson_id AND up.user_id = ?
    LEFT JOIN user_progress u ON l.lesson_id = u.lesson_id
    GROUP BY c.course_id
");
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT DISTINCT category FROM courses");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codly - Courses</title>
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/css/course.css">
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
            <div class="course-header">
                <h1>คอร์สเรียนทั้งหมด</h1>
                <div class="course-filters">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="courseSearch" placeholder="ค้นหาคอร์ส...">
                    </div>
                    <div class="filter-group">
                        <select id="categoryFilter">
                            <option value="">ทุกหมวดหมู่</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select id="difficultyFilter">
                            <option value="">ทุกระดับ</option>
                            <option value="beginner">เริ่มต้น</option>
                            <option value="intermediate">ปานกลาง</option>
                            <option value="advanced">ขั้นสูง</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="category-tabs">
                <button class="category-tab active" data-category="all">
                    <i class="fas fa-globe"></i>
                    ทั้งหมด
                </button>
                <?php foreach ($categories as $category): ?>
                    <button class="category-tab" data-category="<?php echo htmlspecialchars($category); ?>">
                        <i class="fas fa-folder"></i>
                        <?php echo htmlspecialchars($category); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="course-grid">
                <?php foreach($courses as $course):
                    $progress = ($course['total_lessons'] > 0) 
                        ? round(($course['completed_lessons'] / $course['total_lessons']) * 100) 
                        : 0;
                ?>
                <div class="course-card" data-category="<?php echo htmlspecialchars($course['category']); ?>" 
                     data-difficulty="<?php echo htmlspecialchars($course['level']); ?>">
                    <div class="course-header">
                        <div class="course-icon">
                            <?php if($course['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($course['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($course['title']); ?>">
                            <?php else: ?>
                                <i class="fab fa-html5"></i>
                            <?php endif; ?>
                        </div>
                        <span class="difficulty-badge <?php echo htmlspecialchars($course['level']); ?>">
                            <?php echo htmlspecialchars($course['level']); ?>
                        </span>
                    </div>
                    <div class="course-content">
                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p><?php echo htmlspecialchars($course['description']); ?></p>
                        <div class="course-stats">
                            <span><i class="fas fa-book"></i> <?php echo $course['total_lessons']; ?> บทเรียน</span>
                            <span><i class="fas fa-code"></i> <?php echo $course['exercise_count']; ?> แบบฝึกหัด</span>
                            <span><i class="fas fa-users"></i> <?php echo $course['student_count']; ?> ผู้เรียน</span>
                        </div>
                        <div class="course-progress">
                            <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <a href="lesson.php?course_id=<?php echo urlencode($course['course_id']); ?>" class="start-course-btn">
                            <?php echo $progress > 0 ? 'เรียนต่อ' : 'เริ่มเรียน'; ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function loadNavigation() {
                const apiEndpoint = window.innerWidth <= 768 ? 'api/navbar-mobile.php' : 'api/navbar-desktop.php';
                fetch(apiEndpoint + '?page=courses')
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

            const courseSearch = document.getElementById('courseSearch');
            const categoryFilter = document.getElementById('categoryFilter');
            const difficultyFilter = document.getElementById('difficultyFilter');
            const courseCards = document.querySelectorAll('.course-card');
            const categoryTabs = document.querySelectorAll('.category-tab');

            function filterCourses() {
                const searchTerm = courseSearch.value.toLowerCase();
                const category = categoryFilter.value;
                const difficulty = difficultyFilter.value;

                courseCards.forEach(card => {
                    const title = card.querySelector('h3').textContent.toLowerCase();
                    const cardCategory = card.dataset.category;
                    const cardDifficulty = card.dataset.difficulty;

                    const matchesSearch = title.includes(searchTerm);
                    const matchesCategory = !category || cardCategory === category;
                    const matchesDifficulty = !difficulty || cardDifficulty === difficulty;

                    if (matchesSearch && matchesCategory && matchesDifficulty) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            courseSearch.addEventListener('input', filterCourses);
            categoryFilter.addEventListener('change', filterCourses);
            difficultyFilter.addEventListener('change', filterCourses);

            categoryTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    categoryTabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    
                    const selectedCategory = tab.dataset.category;
                    categoryFilter.value = selectedCategory === 'all' ? '' : selectedCategory;
                    filterCourses();
                });
            });

            const mobileToggle = document.querySelector('.mobile-toggle');
            const sidebar = document.querySelector('.sidebar');

            mobileToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('active');
            });

            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                        sidebar.classList.remove('active');
                    }
                }
            });
        });

        function searchCourses(search = '', category = '', level = '') {
            fetch('course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'search_courses',
                    search: search,
                    category: category,
                    level: level
                })
            })
            .then(response => response.json())
            .then(data => {
                updateCourseGrid(data.courses);
            });
        }

        function enrollCourse(courseId) {
            fetch('course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'enroll',
                    course_id: courseId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `lesson.php?course_id=${courseId}`;
                }
            });
        }
    </script>
    <script src="../assets/js/preload.js"></script>
</body>
</html>
