<?php
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->query("SELECT COUNT(*) as count FROM courses");
    $total_courses = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->query("SELECT COUNT(*) as count FROM lessons");
    $total_lessons = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->query("SELECT COUNT(*) as count FROM submissions");
    $total_submissions = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->query("
        SELECT s.*, u.username, e.title as exercise_title 
        FROM submissions s 
        JOIN users u ON s.user_id = u.user_id 
        JOIN exercises e ON s.exercise_id = e.exercise_id 
        ORDER BY s.submitted_at DESC 
        LIMIT 5
    ");
    $recent_submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}
?>

<h1>แดชบอร์ด</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo $total_users; ?></div>
        <div class="stat-label">ผู้ใช้ทั้งหมด</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo $total_courses; ?></div>
        <div class="stat-label">คอร์สทั้งหมด</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <i class="fas fa-tasks"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo $total_lessons; ?></div>
        <div class="stat-label">บทเรียนทั้งหมด</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <i class="fas fa-code"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo $total_submissions; ?></div>
        <div class="stat-label">การส่งงานทั้งหมด</div>
    </div>
</div>

<h2>กิจกรรมล่าสุด</h2>
<div class="recent-activities">
    <?php foreach ($recent_submissions as $submission): ?>
    <div class="activity-item">
        <div class="activity-icon">
            <i class="fas fa-code"></i>
        </div>
        <div class="activity-details">
            <p><?php echo htmlspecialchars($submission['username']); ?> 
               ส่งแบบฝึกหัด <?php echo htmlspecialchars($submission['exercise_title']); ?></p>
            <span class="activity-time"><?php echo date('d/m/Y H:i', strtotime($submission['submitted_at'])); ?></span>
        </div>
        <div class="activity-status <?php echo $submission['status']; ?>">
            <?php echo $submission['status']; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
