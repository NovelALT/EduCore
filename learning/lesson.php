<?php
// เพิ่ม security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

require_once('../config/db_connect.php');
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: ../auth/login.php');
    exit;
}

$course_id = isset($_GET['course_id']) ? filter_var($_GET['course_id'], FILTER_SANITIZE_NUMBER_INT) : null;
$lesson_id = isset($_GET['lesson_id']) ? filter_var($_GET['lesson_id'], FILTER_SANITIZE_NUMBER_INT) : null;

// ตรวจสอบความถูกต้องของ input
if ($course_id !== null && $lesson_id !== null) {
    if (!is_numeric($course_id) || !is_numeric($lesson_id)) {
        header('Location: /learning/courses.php');
        exit;
    }
}

$isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjaxRequest && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'check_attendance':
            $lesson_id = $data['lesson_id'] ?? null;
            if ($lesson_id) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO user_progress (user_id, lesson_id, status)
                        VALUES (?, ?, 'in_progress')
                        ON DUPLICATE KEY UPDATE 
                            status = 'in_progress',
                            score = COALESCE(score, 0)
                    ");
                    $result = $stmt->execute([$user_id, $lesson_id]);
                    echo json_encode([
                        'success' => $result,
                        'message' => 'เช็คสำเร็จ!'
                    ]);
                } catch (PDOException $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
                    ]);
                }
            }
            exit;
            
        case 'mark_complete':
            $lesson_id = $data['lesson_id'] ?? null;
            if ($lesson_id) {
                $stmt = $pdo->prepare("
                    INSERT INTO user_progress (user_id, lesson_id, status, score)
                    VALUES (?, ?, 'completed', 100)
                    ON DUPLICATE KEY UPDATE status = 'completed', score = 100
                ");
                $result = $stmt->execute([$user_id, $lesson_id]);
                echo json_encode(['success' => $result]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Missing lesson_id']);
            }
            exit;
            
        case 'save_progress':
            $exercise_id = $data['exercise_id'] ?? null;
            $code = $data['code'] ?? null;
            if ($exercise_id && $code) {
                $stmt = $pdo->prepare("
                    INSERT INTO code_progress (user_id, exercise_id, current_code)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE current_code = ?
                ");
                $result = $stmt->execute([$user_id, $exercise_id, $code, $code]);
                echo json_encode(['success' => $result]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Missing required data']);
            }
            exit;
            
        case 'submit_exercise':
            $exercise_id = $data['exercise_id'] ?? null;
            $code = $data['code'] ?? '';
            
            $stmt = $pdo->prepare("
                SELECT * FROM exercise_checkpoints 
                WHERE exercise_id = ? 
                ORDER BY order_number
            ");
            $stmt->execute([$exercise_id]);
            $checkpoints = $stmt->fetchAll();
            
            $all_passed = true;
            $results = [];
            
            foreach ($checkpoints as $checkpoint) {
                $tmpfname = tempnam(sys_get_temp_dir(), 'python_');
                file_put_contents($tmpfname, $code);
                
                $output = [];
                $return_var = 0;
                exec("python " . escapeshellarg($tmpfname) . " 2>&1", $output, $return_var);
                unlink($tmpfname);
                
                $passed = trim(implode("\n", $output)) === trim($checkpoint['expected_output']);
                if (!$passed) $all_passed = false;
                
                $results[] = [
                    'checkpoint' => $checkpoint['checkpoint_name'],
                    'passed' => $passed,
                    'expected' => $checkpoint['expected_output'],
                    'actual' => implode("\n", $output)
                ];
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO submissions (user_id, exercise_id, submitted_code, status)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $exercise_id, $code, $all_passed ? 'passed' : 'failed']);
            
            echo json_encode([
                'success' => true,
                'passed' => $all_passed,
                'results' => $results
            ]);
            exit;
            
        case 'run_code':
            try {
                $code = $data['code'] ?? '';
                
                exec('python --version', $pythonVersion, $returnCode);
                if ($returnCode !== 0) {
                    throw new Exception('Python interpreter not found');
                }
                
                $tmpfname = tempnam(sys_get_temp_dir(), 'python_');
                file_put_contents($tmpfname, $code);
                
                $output = [];
                $return_var = 0;
                $command = sprintf('timeout 5s python %s 2>&1', escapeshellarg($tmpfname));
                exec($command, $output, $return_var);
                
                unlink($tmpfname);
                
                $outputText = implode("\n", $output);
                if ($return_var === 124) {
                    throw new Exception('Code execution timed out');
                }
                
                echo json_encode([
                    'success' => true,
                    'output' => $outputText,
                    'error' => $return_var !== 0
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'error' => true,
                    'message' => $e->getMessage()
                ]);
            }
            exit;
    }
}

$stmt = $pdo->prepare("
    SELECT c.*, 
           COUNT(DISTINCT l.lesson_id) as total_lessons,
           COUNT(DISTINCT CASE WHEN up.status = 'completed' THEN l.lesson_id END) as completed_lessons,
           COUNT(DISTINCT CASE WHEN up.status = 'in_progress' THEN l.lesson_id END) as in_progress_lessons,
           (
               SELECT COUNT(DISTINCT e.exercise_id)
               FROM lessons l2
               LEFT JOIN exercises e ON l2.lesson_id = e.lesson_id
               WHERE l2.course_id = c.course_id
           ) as total_exercises,
           (
               SELECT COUNT(DISTINCT s.submission_id)
               FROM lessons l3
               LEFT JOIN exercises e ON l3.lesson_id = e.lesson_id
               LEFT JOIN submissions s ON e.exercise_id = s.exercise_id
               WHERE l3.course_id = c.course_id AND s.status = 'passed' AND s.user_id = ?
           ) as completed_exercises
    FROM courses c
    LEFT JOIN lessons l ON c.course_id = l.course_id
    LEFT JOIN user_progress up ON l.lesson_id = up.lesson_id AND up.user_id = ?
    WHERE c.course_id = ?
    GROUP BY c.course_id
");

$stmt->execute([$user_id, $user_id, $course_id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: /learning/courses.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT l.*, 
           'บทเรียนทั่วไป' as section,
           up.status as progress_status,
           (SELECT COUNT(*) FROM exercises WHERE lesson_id = l.lesson_id) as exercise_count
    FROM lessons l
    LEFT JOIN user_progress up ON l.lesson_id = up.lesson_id AND up.user_id = ?
    WHERE l.course_id = ?
    ORDER BY l.order_number
");
$stmt->execute([$user_id, $course_id]);
$lessons = $stmt->fetchAll();

if (!$lesson_id && !empty($lessons)) {
    $lesson_id = $lessons[0]['lesson_id'];
}

$current_lesson = null;

if ($lesson_id) {
    $stmt = $pdo->prepare("
        SELECT l.*, up.status as progress_status
        FROM lessons l
        LEFT JOIN user_progress up ON l.lesson_id = up.lesson_id AND up.user_id = ?
        WHERE l.lesson_id = ? AND l.course_id = ?
    ");
    $stmt->execute([$user_id, $lesson_id, $course_id]);
    $current_lesson = $stmt->fetch();

    if (!$current_lesson) {
        header('Location: ?course_id=' . $course_id);
        exit;
    }
}

$current_order = $current_lesson['order_number'] ?? null;

if ($current_order !== null) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM lessons
        WHERE course_id = ? AND order_number < ?
        ORDER BY order_number DESC
        LIMIT 1
    ");
    $stmt->execute([$course_id, $current_order]);
    $prev_lesson = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT *
        FROM lessons
        WHERE course_id = ? AND order_number > ?
        ORDER BY order_number ASC
        LIMIT 1
    ");
    $stmt->execute([$course_id, $current_order]);
    $next_lesson = $stmt->fetch();
} else {
    $prev_lesson = null;
    $next_lesson = null;
}

$stmt = $pdo->prepare("
    SELECT e.*, 
           (SELECT status FROM submissions 
            WHERE exercise_id = e.exercise_id 
            AND user_id = ? 
            ORDER BY submitted_at DESC LIMIT 1) as submission_status
    FROM exercises e
    WHERE e.lesson_id = ?
    ORDER BY e.exercise_id
");
$stmt->execute([$user_id, $lesson_id]);
$exercises = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - Codly</title>
    <link rel="stylesheet" href="../assets/css/lesson.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/firacode@6.2.0/distr/fira_code.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/preload.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/atom-one-dark.min.css">
    
    <style>
    .py-terminal {
        background: #1e1e1e;
        color: #fff;
        padding: 10px;
        border-radius: 4px;
        font-family: 'Fira Code', monospace;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    
    .py-loading {
        padding: 10px;
        color: #666;
        font-style: italic;
    }
    
    .py-error {
        color: #ff4444;
        padding: 10px;
        border-left: 3px solid #ff4444;
        background: rgba(255, 68, 68, 0.1);
    }
    
    .exercise-card {
        text-decoration: none;
        color: inherit;
        display: block;
        transition: transform 0.2s;
    }
    
    .exercise-card:hover {
        transform: translateY(-2px);
    }
    
    .start-exercise-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 15px;
        padding: 10px;
        background: #4CAF50;
        color: white;
        border-radius: 4px;
        font-weight: 500;
    }
    
    .start-exercise-btn i {
        transition: transform 0.2s;
    }
    
    .exercise-card:hover .start-exercise-btn i {
        transform: translateX(4px);
    }
    </style>
</head>
<body>
    <!-- Remove previous py-script tag -->
    
    <!-- Add Navigation Bar -->
    <nav class="top-nav">
        <div class="nav-left">
            <button id="lessonSidebarToggle" class="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <a href="learn" class="nav-brand">
                <i class="fas fa-code"></i> Codly
            </a>
        </div>
        <div class="nav-right">
            <a href="learn" class="nav-link">
                <i class="fas fa-home"></i> หน้าแรก
            </a>
            <a href="settings" class="nav-link">
                <i class="fas fa-user"></i> โปรไฟล์
            </a>
        </div>
    </nav>

    <main class="main-content">
        <!-- Update Lesson Container Structure -->
        <div class="lesson-container">
            <div class="lesson-sidebar" id="lessonSidebar">
                <!-- Add Close Button for Mobile -->
                <button class="close-sidebar" id="closeSidebar">
                    <i class="fas fa-times"></i>
                </button>
                
                <div class="course-info">
                    <div class="course-header">
                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        <span class="course-category">
                            <i class="fas <?php 
                                switch($course['level']) {
                                    case 'beginner': echo 'fa-seedling'; break;
                                    case 'intermediate': echo 'fa-tree'; break;
                                    case 'advanced': echo 'fa-gem'; break;
                                    default: echo 'fa-book';
                                }
                            ?>"></i>
                            <?php echo ucfirst($course['level'] ?? 'beginner'); ?>
                        </span>
                    </div>

                    <div class="progress-stats">
                        <div class="progress-circle" data-progress="<?php 
                            echo $course['total_lessons'] > 0 
                                ? round(($course['completed_lessons'] / $course['total_lessons']) * 100) 
                                : 0; 
                        ?>">
                            <div class="progress-text">
                                <strong><?php echo $course['completed_lessons']; ?></strong>
                                <span>/ <?php echo $course['total_lessons']; ?></span>
                            </div>
                            <div class="progress-label">บทเรียน</div>
                        </div>

                        <?php if ($course['total_exercises'] > 0): ?>
                        <div class="exercise-stats">
                            <div class="stat-item">
                                <i class="fas fa-code"></i>
                                <span><?php echo $course['completed_exercises']; ?>/<?php echo $course['total_exercises']; ?></span>
                                <small>แบบฝึกหัด</small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="lesson-list">
                    <?php 
                    $current_section = null;
                    $lesson_number = 1;
                    foreach ($lessons as $index => $lesson):
                        $lesson['section'] = $lesson['section'] ?? 'บทเรียนทั่วไป';
                        
                        if ($lesson['section'] !== $current_section):
                            $current_section = $lesson['section'];
                    ?>
                        <div class="lesson-section">
                            <h4><i class="fas fa-book-open"></i> <?php echo htmlspecialchars($lesson['section']); ?></h4>
                            <span class="section-progress">
                                <?php 
                                $section_completed = array_filter($lessons, function($l) use ($current_section) {
                                    return $l['section'] === $current_section && $l['progress_status'] === 'completed';
                                });
                                $section_total = array_filter($lessons, function($l) use ($current_section) {
                                    return $l['section'] === $current_section;
                                });
                                echo count($section_completed) . '/' . count($section_total);
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <a href="?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo $lesson['lesson_id']; ?>" 
                       class="lesson-item <?php echo $lesson['lesson_id'] == $lesson_id ? 'active' : ''; ?>
                                        <?php echo $lesson['progress_status'] == 'completed' ? 'completed' : ''; ?>">
                        <div class="lesson-status">
                            <div class="status-icon">
                                <?php if ($lesson['progress_status'] == 'completed'): ?>
                                    <i class="fas fa-check-circle"></i>
                                <?php else: ?>
                                    <span class="lesson-number"><?php echo $lesson_number; ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($index < count($lessons) - 1): ?>
                                <div class="status-line <?php echo $lesson['progress_status'] == 'completed' ? 'completed' : ''; ?>"></div>
                            <?php endif; ?>
                        </div>
                        <div class="lesson-info">
                            <div class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></div>
                            <div class="lesson-meta">
                                <?php if ($lesson['exercise_count'] > 0): ?>
                                    <span class="exercise-badge">
                                        <i class="fas fa-code"></i> <?php echo $lesson['exercise_count']; ?>
                                    </span>
                                <?php endif; ?>
                                <span class="duration">
                                    <i class="fas fa-clock"></i> <?php echo $lesson['duration'] ?? '15 นาที'; ?>
                                </span>
                            </div>
                        </div>
                    </a>
                    <?php $lesson_number++; endforeach; ?>
                </div>
            </div>

            <!-- Enhance Lesson Content Area -->
            <?php if ($current_lesson): ?>
            <div class="lesson-content">
                <div class="lesson-header">
                    <nav class="breadcrumb">
                        <a href="../courses.php">คอร์สทั้งหมด</a>
                        <i class="fas fa-chevron-right"></i>
                        <a href="../course.php?id=<?php echo urlencode(htmlspecialchars($course_id)); ?>">
                            <?php echo htmlspecialchars($course['title']); ?>
                        </a>
                        <i class="fas fa-chevron-right"></i>
                        <span><?php echo htmlspecialchars($current_lesson['title']); ?></span>
                    </nav>
                    <div class="lesson-title-section">
                        <h1><?php echo htmlspecialchars($current_lesson['title']); ?></h1>
                        <div class="lesson-meta">
                            <span><i class="fas fa-clock"></i> <?php echo $current_lesson['duration'] ?? '15 นาที'; ?></span>
                            <?php if (!empty($exercises)): ?>
                            <span><i class="fas fa-code"></i> <?php echo count($exercises); ?> แบบฝึกหัด</span>
                            <?php endif; ?>
                            
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT 
                                    COUNT(e.exercise_id) as total_exercises,
                                    COUNT(CASE WHEN s.status = 'passed' THEN 1 END) as completed_exercises
                                FROM exercises e
                                LEFT JOIN submissions s ON e.exercise_id = s.exercise_id AND s.user_id = ?
                                WHERE e.lesson_id = ?
                            ");
                            $stmt->execute([$user_id, $lesson_id]);
                            $exercise_stats = $stmt->fetch();
                            
                            $all_exercises_completed = 
                                $exercise_stats['total_exercises'] > 0 && 
                                $exercise_stats['completed_exercises'] == $exercise_stats['total_exercises'];
                            ?>
                            
                            <?php if ($current_lesson['progress_status'] === 'completed'): ?>
                                <span class="completed-status">
                                    <i class="fas fa-check-circle"></i> เรียนจบแล้ว
                                </span>
                            <?php elseif ($exercise_stats['total_exercises'] > 0 && !$all_exercises_completed): ?>
                                <span class="incomplete-status">
                                    <i class="fas fa-exclamation-circle"></i> 
                                    ต้องทำแบบฝึกหัดให้ครบก่อน (<?php echo $exercise_stats['completed_exercises']; ?>/<?php echo $exercise_stats['total_exercises']; ?>)
                                </span>
                            <?php elseif ($current_lesson['progress_status'] === 'in_progress'): ?>
                                <button id="completeBtn" class="complete-btn" onclick="markAsCompleted(<?php echo $current_lesson['lesson_id']; ?>)">
                                    <i class="fas fa-check"></i> ทำเครื่องหมายว่าเรียนจบ
                                </button>
                            <?php else: ?>
                                <button id="checkAttendanceBtn" class="attendance-btn" onclick="checkAttendance(<?php echo $current_lesson['lesson_id']; ?>)">
                                    <i class="fas fa-user-check"></i> เช็ค
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="content-wrapper">
                    <div class="lesson-text">
                        <?php echo $current_lesson['content']; ?>
                    </div>

                    <?php if (!empty($exercises)): ?>
                    <div class="exercises-section">
                        <h3><i class="fas fa-laptop-code"></i> แบบฝึกหัด</h3>
                        <div class="exercise-list">
                            <?php foreach ($exercises as $exercise): ?>
                            <a href="exercise.php?id=<?php echo urlencode(htmlspecialchars($exercise['exercise_id'])); ?>" class="exercise-card">
                                <div class="exercise-header">
                                    <h4><?php echo htmlspecialchars($exercise['title']); ?></h4>
                                    <div class="exercise-status">
                                        <?php if ($exercise['submission_status'] == 'passed'): ?>
                                            <span class="status-badge success">ผ่านแล้ว</span>
                                        <?php else: ?>
                                            <span class="status-badge pending">รอทำ</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <p><?php echo htmlspecialchars($exercise['description']); ?></p>
                                
                                <div class="start-exercise-btn">
                                    <i class="fas fa-chevron-right"></i>
                                    เริ่มทำแบบฝึกหัด
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="lesson-navigation">
                        <?php if ($prev_lesson): ?>
                        <a href="lesson.php?course_id=<?php echo urlencode(htmlspecialchars($course_id)); ?>&lesson_id=<?php echo urlencode(htmlspecialchars($prev_lesson['lesson_id'])); ?>" 
                           class="nav-card prev">
                            <i class="fas fa-arrow-left"></i>
                            <div class="nav-info">
                                <span>บทก่อนหน้า</span>
                                <strong><?php echo htmlspecialchars($prev_lesson['title']); ?></strong>
                            </div>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($next_lesson): ?>
                        <a href="lesson.php?course_id=<?php echo urlencode(htmlspecialchars($course_id)); ?>&lesson_id=<?php echo urlencode(htmlspecialchars($next_lesson['lesson_id'])); ?>" 
                           class="nav-card next">
                            <div class="nav-info">
                                <span>บทถัดไป</span>
                                <strong><?php echo htmlspecialchars($next_lesson['title']); ?></strong>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../assets/js/lesson.js"></script>
    <script>
    function runCode(exerciseId) {
        const code = document.getElementById('code-' + exerciseId).value;
        const outputElement = document.getElementById('output-' + exerciseId);
        
        outputElement.innerHTML = '<div class="py-loading">กำลังรันโค้ด...</div>';

        fetch('lesson.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'run_code',
                code: code
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                outputElement.innerHTML = `<pre class="py-terminal ${data.error ? 'error' : ''}">${data.output}</pre>`;
            } else {
                throw new Error(data.message || 'Failed to run code');
            }
        })
        .catch(error => {
            outputElement.innerHTML = `<div class="py-error">Error: ${error.message}</div>`;
        });
    }

    function submitExercise(exerciseId) {
        window.codeEditor.submitExercise(exerciseId);
    }

    async function checkAttendance(lessonId) {
        lessonId = encodeURIComponent(lessonId);
        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'check_attendance',
                    lesson_id: lessonId
                })
            });

            const data = await response.json();
            if (data.success) {
                document.getElementById('checkAttendanceBtn').outerHTML = `
                    <button id="completeBtn" class="complete-btn" onclick="markAsCompleted(${lessonId})">
                        <i class="fas fa-check"></i> ทำเครื่องหมายว่าเรียนจบ
                    </button>
                `;

                showNotification('success', data.message);
            } else {
                throw new Error(data.message || 'เกิดข้อผิดพลาดในการเช็คชื่อ');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('error', error.message);
        }
    }

    async function markAsCompleted(lessonId) {
        lessonId = encodeURIComponent(lessonId);
        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'mark_complete',
                    lesson_id: lessonId
                })
            });

            const data = await response.json();
            if (data.success) {
                const completeBtn = document.getElementById('completeBtn');
                completeBtn.outerHTML = `
                    <span class="completed-status">
                        <i class="fas fa-check-circle"></i> เรียนจบแล้ว
                    </span>
                `;

                showNotification('success', 'ทำเครื่องหมายว่าเรียนจบแล้ว');

                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'เกิดข้อผิดพลาดในการทำเครื่องหมายว่าเรียนจบ');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('error', error.message);
        }
    }

    function showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i> ${message}`;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    // ฟังก์ชันช่วย sanitize
    function sanitizeInput(input) {
        return encodeURIComponent(String(input).replace(/[^\w. -]/gi, ''));
    }
    </script>

</body>
</html>