<?php
session_start();
require_once('../config/db_admin.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codly Admin - <?php echo ucfirst($page); ?></title>
    <link rel="stylesheet" href="../assets/css/learn.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <button class="mobile-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <?php include('includes/admin_sidebar.php'); ?>

        <div class="main-content">
            <?php
            switch($page) {
                case 'dashboard':
                    include('pages/dashboard.php');
                    break;
                case 'users':
                    include('pages/users.php');
                    break;
                case 'courses':
                    include('pages/courses.php');
                    break;
                case 'lessons':
                    include('pages/lessons.php');
                    break;
                case 'exercises':
                    include('pages/exercises.php');
                    break;
                case 'submissions':
                    include('pages/submissions.php');
                    break;
                case 'hints':
                    include('pages/hints.php');
                    break;
                default:
                    include('pages/dashboard.php');
            }
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script src="../assets/js/modal.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }

        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = '<?php echo $page; ?>';
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href').includes(currentPage)) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
