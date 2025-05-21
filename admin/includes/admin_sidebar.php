<div class="sidebar">
    <div class="sidebar-header">
        <a href="/admin" class="sidebar-logo">
            <i class="fas fa-code"></i>
            <span>CODLY ADMIN</span>
        </a>
    </div>

    <!-- User Profile -->
    <div class="user-profile">
        <div class="profile-avatar">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="profile-info">
            <div class="profile-name"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin'; ?></div>
            <div class="profile-role">ผู้ดูแลระบบ</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="admin-nav">
        <div class="nav-section">
            <span class="nav-section-title">เมนูหลัก</span>
            <a href="/admin" class="nav-link <?php echo !isset($_GET['page']) || $_GET['page'] === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>แดชบอร์ด</span>
            </a>
        </div>

        <div class="nav-section">
            <span class="nav-section-title">จัดการระบบ</span>
            <a href="?page=users" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'users' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>จัดการผู้ใช้</span>
                <span class="badge">5</span>
            </a>
            <a href="?page=courses" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'courses' ? 'active' : ''; ?>">
                <i class="fas fa-book"></i>
                <span>จัดการคอร์ส</span>
            </a>
            <a href="?page=lessons" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'lessons' ? 'active' : ''; ?>">
                <i class="fas fa-book-reader"></i>
                <span>จัดการบทเรียน</span>
            </a>
            <a href="?page=exercises" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'exercises' ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i>
                <span>จัดการแบบฝึกหัด</span>
            </a>
            <a href="?page=hints" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'hints' ? 'active' : ''; ?>">
                <i class="fas fa-lightbulb"></i>
                <span>จัดการคำใบ้</span>
            </a>
        </div>

        <div class="nav-section">
            <span class="nav-section-title">การตรวจสอบ</span>
            <a href="?page=submissions" class="nav-link <?php echo isset($_GET['page']) && $_GET['page'] === 'submissions' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-check"></i>
                <span>ตรวจสอบการส่งงาน</span>
                <span class="badge badge-warning">3</span>
            </a>
        </div>

        <!-- Bottom Section -->
        <div class="nav-section mt-auto">
            <a href="/logout.php" class="nav-link nav-link-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>ออกจากระบบ</span>
            </a>
        </div>
    </nav>
</div>
