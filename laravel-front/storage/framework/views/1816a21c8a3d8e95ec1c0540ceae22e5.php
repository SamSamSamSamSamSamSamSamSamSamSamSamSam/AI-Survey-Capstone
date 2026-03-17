<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DCISM Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --dark-bg: #1a1c23;
            --sidebar-bg: #2a3042;
            --sidebar-hover: #3a4052;
            --text-light: #f8f9fc;
            --text-muted: #b7b9cc;
            --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fc;
            color: #333;
            overflow-x: hidden;
        }
        
        .fixed-sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            overflow-y: auto;
            width: 250px;
            background: var(--sidebar-bg);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }
        
        .main-content {
            margin-left: 250px;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-brand {
            display: flex;
            align-items: center;
            color: var(--text-light);
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .sidebar-brand img {
            height: 36px;
            margin-right: 10px;
        }
        
        .nav-pills .nav-link {
            color: var(--text-muted);
            border-radius: 0.35rem;
            margin-bottom: 5px;
            padding: 12px 15px;
            transition: all 0.2s;
        }
        
        .nav-pills .nav-link:hover {
            color: var(--text-light);
            background-color: var(--sidebar-hover);
        }
        
        .nav-pills .nav-link.active {
            color: var(--text-light);
            background-color: var(--primary-color);
        }
        
        .nav-pills .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .sidebar-footer {
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-logout {
            width: 100%;
            background-color: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-muted);
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background-color: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.5);
            color: #fff;
        }
        
        .page-content {
            padding: 30px;
            min-height: calc(100vh - 70px);
        }
        
        footer {
            background-color: var(--dark-bg);
            color: var(--text-muted);
            padding: 15px 0;
            margin-left: 250px;
            transition: all 0.3s;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .fixed-sidebar {
                width: 70px;
                overflow: visible;
            }
            
            .fixed-sidebar .sidebar-brand span,
            .fixed-sidebar .nav-link span {
                display: none;
            }
            
            .fixed-sidebar .sidebar-brand {
                justify-content: center;
            }
            
            .fixed-sidebar .sidebar-brand img {
                margin-right: 0;
            }
            
            .main-content, footer {
                margin-left: 70px;
            }
            
            .nav-pills .nav-link {
                padding: 12px;
                text-align: center;
            }
            
            .nav-pills .nav-link i {
                margin-right: 0;
                font-size: 1.3rem;
            }
        }
        
        /* User info section */
        .user-info {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .user-name {
            color: var(--text-light);
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .user-role {
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        
        /* Animation for menu items */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .nav-item {
            animation: fadeIn 0.3s ease-out;
        }
        
        /* Scrollbar styling */
        .fixed-sidebar::-webkit-scrollbar {
            width: 5px;
        }
        
        .fixed-sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .fixed-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }
    </style>
</head>
<body class="bg-light">

    <div class="container-fluid p-0">
        <div class="row flex-nowrap">

            <!-- Sidebar -->
            <div class="fixed-sidebar">
                <div class="d-flex flex-column h-100">
                    <!-- Sidebar header with logo -->
                    <div class="sidebar-header">
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="sidebar-brand">
                            <img src="<?php echo e(asset('images/dcismicon.png')); ?>" alt="DCISM Icon">
                            <span>DCISM</span>
                        </a>
                    </div>
                    
                    <!-- User info section -->
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo e(strtoupper(substr(Auth::user()->name, 0, 1))); ?>

                        </div>
                        <div class="user-name"><?php echo e(Auth::user()->name); ?></div>
                        <div class="user-role">
                            <?php if(Auth::user()->hasRole('admin')): ?>
                                Administrator
                            <?php elseif(Auth::user()->hasRole('teacher')): ?>
                                Faculty
                            <?php else: ?>
                                Student
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Navigation menu -->
                    <div class="flex-grow-1 px-3 pt-3">
                        <ul class="nav nav-pills flex-column mb-auto" id="menu">
                            <?php if(Auth::user() && Auth::user()->hasRole('admin')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link text-white <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                                        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('admin.department')); ?>" class="nav-link text-white <?php echo e(request()->routeIs('admin.department') ? 'active' : ''); ?>">
                                        <i class="bi bi-building"></i> <span>Department</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('admin.users')); ?>" class="nav-link text-white <?php echo e(request()->routeIs('admin.users') ? 'active' : ''); ?>">
                                        <i class="bi bi-people"></i> <span>Users</span>
                                    </a>
                                </li>
                            <?php elseif(Auth::user() && Auth::user()->hasRole('teacher')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('teacher.dashboard')); ?>" class="nav-link text-white <?php echo e(request()->routeIs('teacher.dashboard') ? 'active' : ''); ?>">
                                        <i class="bi bi-journal-text"></i> <span>Dashboard</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('teacher.survey')); ?>" class="nav-link text-white <?php echo e(request()->routeIs('teacher.survey') ? 'active' : ''); ?>">
                                        <i class="bi bi-clipboard-check"></i> <span>Survey</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('teacher.reviews')); ?>" class="nav-link text-white <?php echo e(request()->routeIs('teacher.reviews') ? 'active' : ''); ?>">
                                        <i class="bi bi-bar-chart-line"></i> <span>Results</span>
                                    </a>
                                </li>
                            <?php elseif(Auth::user() && Auth::user()->hasRole('student')): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('student.dashboard')); ?>" class="nav-link text-white <?php echo e(request()->routeIs('student.dashboard') ? 'active' : ''); ?>">
                                        <i class="bi bi-house"></i> <span>Dashboard</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('student.survey')); ?>" class="nav-link text-white <?php echo e(request()->routeIs('student.survey') ? 'active' : ''); ?>">
                                        <i class="bi bi-clipboard-check"></i> <span>Surveys</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('student.results')); ?>" class="nav-link text-white <?php echo e(request()->routeIs('student.results') ? 'active' : ''); ?>">
                                        <i class="bi bi-bar-chart-line"></i> <span>Results</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <!-- Sidebar footer with logout -->
                    <div class="sidebar-footer mt-auto">
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                        class="btn btn-logout">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                        
                        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                            <?php echo csrf_field(); ?>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main content area -->
            <div class="col main-content">
                <div class="page-content">
                    <?php echo $__env->yieldPushContent('styles'); ?>
                    <?php echo $__env->yieldContent('content'); ?>
                    <?php echo $__env->yieldPushContent('scripts'); ?>
                </div>
            </div>

        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3">
        <p class="mb-0">&copy; 2025 DCISM Admin Portal. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add active class to current page in navigation
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('#menu .nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
            
            // Add smooth transitions
            const sidebar = document.querySelector('.fixed-sidebar');
            const mainContent = document.querySelector('.main-content');
            const footer = document.querySelector('footer');
            
            // Toggle sidebar on small screens
            const toggleSidebar = () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                footer.classList.toggle('expanded');
            };
            
            // You can add a button to toggle the sidebar if needed
            // For now, it's responsive based on screen size
        });
    </script>
</body>
</html><?php /**PATH C:\Users\arjoy\Desktop\DESKTOP\Capstone\AI-Survey-Capstone\laravel-front\resources\views/layouts/default.blade.php ENDPATH**/ ?>