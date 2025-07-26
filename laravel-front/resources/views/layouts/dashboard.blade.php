<!-- resources/views/layouts/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard')</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Custom styles for overlay and body overflow */
        .overflow-hidden-mobile {
            overflow: hidden; /* Prevents scrolling on body when sidebar is open */
        }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Overlay for when sidebar is open on mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

    @php
        // Define menu items for different roles
        $adminMenuItems = [
            ['name' => 'Admin Home', 'url' => route('admin.dashboard'), 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>', 'route_name' => 'dashboard'],
            ['name' => 'Manage Users', 'url' => '#', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.107-1.282-.303-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.107-1.282.303-1.857M11 15V6m0 0L8 9m3-3l3 3"></path></svg>', 'route_name' => 'admin.users.*'],
            ['name' => 'System Settings', 'url' => '#', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>', 'route_name' => 'admin.settings.*'],
        ];

        $facultyMenuItems = [
            ['name' => 'Faculty Dashboard', 'url' => route('faculty.dashboard'), 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>', 'route_name' => 'dashboard'],
            ['name' => 'My Courses', 'url' => '#', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>', 'route_name' => 'faculty.courses.*'],
            ['name' => 'Student Grades', 'url' => '#', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>', 'route_name' => 'faculty.grades.*'],
        ];

        $studentMenuItems = [
            ['name' => 'Student Home', 'url' => route('student.dashboard'), 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>', 'route_name' => 'dashboard'],
            ['name' => 'My Schedule', 'url' => '#', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>', 'route_name' => 'student.schedule.*'],
            ['name' => 'My Grades', 'url' => '#', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>', 'route_name' => 'student.grades.*'],
        ];

        // Define a common logout item with 'type' for special handling
        $logoutItem = [
            'name' => 'Logout',
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 8H7a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2z"></path></svg>',
            'type' => 'logout', // Special type to indicate this is a logout action
            'url' => route('logout') // The actual logout URL
        ];

        // Determine which menu to use based on the authenticated user's role
        $currentMenuItems = [];
        if (Auth::check()) { // Check if a user is logged in
            switch (auth()->user()->role) {
                case 'admin':
                    $currentMenuItems = array_merge($adminMenuItems, [$logoutItem]);
                    break;
                case 'faculty':
                    $currentMenuItems = array_merge($facultyMenuItems, [$logoutItem]);
                    break;
                case 'student':
                    $currentMenuItems = array_merge($studentMenuItems, [$logoutItem]);
                    break;
                default:
                    // Fallback for unknown roles or guests (though guests shouldn't reach this layout)
                    $currentMenuItems = [];
                    break;
            }
        }
    @endphp

    <!-- Sidebar Component -->
    <x-sidebar :menuItems="$currentMenuItems">
        {{-- You can pass additional content here if needed --}}
    </x-sidebar>

    <div class="lg:flex flex-1">
        <!-- Burger Icon for Mobile -->
        <button id="sidebar-toggle" class="lg:hidden fixed top-4 left-4 z-50 p-2 bg-indigo-600 text-white rounded-md shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <!-- Main content area -->
        <div id="main-content" class="flex-1 p-8 transition-all duration-300 ease-in-out lg:ml-64">
            @yield('content')
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const mainContent = document.getElementById('main-content');
            const body = document.body;

            // Function to open sidebar
            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                sidebarOverlay.classList.remove('hidden');
                body.classList.add('overflow-hidden-mobile'); // Prevent body scroll
                // Adjust main content margin for desktop view only
                if (window.innerWidth >= 1024) { // Tailwind's 'lg' breakpoint
                    mainContent.classList.add('lg:ml-64');
                }
            }

            // Function to close sidebar
            function closeSidebar() {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
                body.classList.remove('overflow-hidden-mobile'); // Allow body scroll
                // Reset main content margin for desktop view only
                if (window.innerWidth >= 1024) { // Tailwind's 'lg' breakpoint
                    mainContent.classList.remove('lg:ml-64');
                }
            }

            // Toggle sidebar on button click
            sidebarToggle.addEventListener('click', function () {
                if (sidebar.classList.contains('-translate-x-full')) {
                    openSidebar();
                } else {
                    closeSidebar();
                }
            });

            // Close sidebar when clicking outside (on overlay)
            sidebarOverlay.addEventListener('click', closeSidebar);

            // Close sidebar when a link inside it is clicked (optional, but good UX)
            sidebar.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    // Only close on mobile, as on desktop it's static
                    if (window.innerWidth < 1024) {
                        closeSidebar();
                    }
                });
            });

            // Handle initial state and resize for responsiveness
            function handleResize() {
                if (window.innerWidth >= 1024) { // Desktop view
                    sidebar.classList.remove('-translate-x-full', 'fixed', 'h-screen', 'z-40');
                    sidebar.classList.add('translate-x-0', 'static', 'h-auto', 'shadow-none');
                    sidebarOverlay.classList.add('hidden');
                    body.classList.remove('overflow-hidden-mobile');
                    mainContent.classList.add('lg:ml-64'); // Ensure content shifts
                } else { // Mobile view
                    sidebar.classList.remove('translate-x-0', 'static', 'h-auto', 'shadow-none');
                    sidebar.classList.add('-translate-x-full', 'fixed', 'h-screen', 'z-40');
                    // Do not hide overlay if sidebar is open
                    if (!sidebar.classList.contains('translate-x-0')) {
                        sidebarOverlay.classList.add('hidden');
                    }
                    mainContent.classList.remove('lg:ml-64'); // Remove desktop margin
                }
            }

            // Initial call
            handleResize();

            // Listen for window resize events
            window.addEventListener('resize', handleResize);
        });
    </script>
</body>
</html>
