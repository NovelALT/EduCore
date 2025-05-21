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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        try {
            $stmt = $pdo->prepare("
                UPDATE users SET 
                firstname = ?,
                username = ?,
                email = ?
                WHERE user_id = ?
            ");
            $stmt->execute([
                $_POST['display_name'],
                $_POST['username'],
                $_POST['email'],
                $user_id
            ]);
            $success_message = "อัพเดทข้อมูลสำเร็จ";
        } catch (PDOException $e) {
            $error_message = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }

    if (isset($_POST['update_password'])) {
        if (password_verify($_POST['current_password'], $user['password_hash'])) {
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                $new_password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $stmt->execute([$new_password_hash, $user_id]);
                $success_message = "เปลี่ยนรหัสผ่านสำเร็จ";
            } else {
                $error_message = "รหัสผ่านใหม่ไม่ตรงกัน";
            }
        } else {
            $error_message = "รหัสผ่านปัจจุบันไม่ถูกต้อง";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codly - Settings</title>
    <link rel="stylesheet" href="../assets/main.css">
    <link rel="stylesheet" href="../assets/css/learn.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/preload.css">
    <link rel="stylesheet" href="../assets/css/settings.css">
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
            <div class="settings-container">
                <?php if (isset($success_message)): ?>
                    <div class="alert success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if (isset($error_message)): ?>
                    <div class="alert error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="settings-section">
                        <h2><i class="fas fa-user-circle"></i> ข้อมูลโปรไฟล์</h2>
                        <div class="profile-picture-upload">
                            <div class="profile-picture">
                                <img src="<?php echo $user['profile_image'] ?? '../assets/images/default-avatar.png'; ?>" 
                                     alt="Profile Picture" id="profile-preview">
                                <div class="upload-overlay">
                                    <i class="fas fa-camera"></i>
                                </div>
                            </div>
                            <input type="file" name="profile_image" id="profile-upload" accept="image/*" hidden>
                            <button type="button" class="upload-btn" onclick="document.getElementById('profile-upload').click()">
                                <i class="fas fa-upload"></i> อัพโหลดรูปโปรไฟล์
                            </button>
                        </div>

                        <div class="form-group">
                            <label>ชื่อที่แสดง</label>
                            <input type="text" name="display_name" value="<?php echo htmlspecialchars($user['firstname']); ?>">
                        </div>
                        <div class="form-group">
                            <label>ชื่อผู้ใช้</label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                        </div>
                        <div class="form-group">
                            <label>อีเมล</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        <button type="submit" name="update_profile" class="save-button">
                            <i class="fas fa-save"></i> บันทึกข้อมูล
                        </button>
                    </div>
                </form>

                <form method="POST" class="settings-section">
                    <h2><i class="fas fa-lock"></i> เปลี่ยนรหัสผ่าน</h2>
                    <div class="form-group">
                        <label>รหัสผ่านปัจจุบัน</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>รหัสผ่านใหม่</label>
                        <input type="password" name="new_password" required 
                               pattern=".{8,}" title="รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร">
                    </div>
                    <div class="form-group">
                        <label>ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="update_password" class="save-button">
                        <i class="fas fa-key"></i> เปลี่ยนรหัสผ่าน
                    </button>
                </form>
            </div>
        </main>
        
        <!-- Add Notification Toast -->
        <div class="toast" id="settings-toast"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function loadNavigation() {
                const apiEndpoint = window.innerWidth <= 768 ? 'api/navbar-mobile.php' : 'api/navbar-desktop.php';
                fetch(apiEndpoint + '?page=settings')
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

            // Mobile menu functionality
            const mobileToggle = document.querySelector('.mobile-toggle');
            const sidebar = document.querySelector('.sidebar');

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

            // Add profile picture preview
            document.getElementById('profile-upload').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('profile-preview').src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
        });

        // Settings save function
        function saveSettings() {
            const toast = document.getElementById('settings-toast');
            toast.textContent = 'บันทึกการตั้งค่าเรียบร้อย';
            toast.className = 'toast show success';
            setTimeout(() => toast.className = 'toast', 3000);
        }
    </script>
    <script src="../assets/js/preload.js"></script>
</body>
</html>
