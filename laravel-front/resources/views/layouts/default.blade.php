<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Layout Preview</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <div class="container-fluid flex-grow-1">
        <div class="row flex-nowrap">

            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-dark">
                <div class="d-flex flex-column align-items-sm-start px-3 pt-2 text-white min-vh-100">
                    <a href="/" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bold">My App</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-sm-start" id="menu">
                        @if(Auth::user() && Auth::user()->role === 'admin')
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}" class="nav-link text-white">
                                    <i class="bi bi-speedometer2"></i> <span>Admin Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.users') }}" class="nav-link text-white">
                                    <i class="bi bi-person"></i> <span>Users</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.reports') }}" class="nav-link text-white">
                                    <i class="bi bi-bar-chart-line"></i> <span>Reports</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings') }}" class="nav-link text-white">
                                    <i class="bi bi-gear"></i> <span>Settings</span>
                                </a>
                            </li>
                            
                        @elseif(Auth::user() && Auth::user()->role === 'teacher')
                            <li class="nav-item">
                                <a href="{{ route('teacher.dashboard') }}" class="nav-link text-white">
                                    <i class="bi bi-journal-text"></i> <span>Teacher Dashboard</span>
                                </a>
                            </li>
                        @elseif(Auth::user() && Auth::user()->role === 'student')
                            <li class="nav-item">
                                <a href="{{ route('student.dashboard') }}" class="nav-link text-white">
                                    <i class="bi bi-house"></i> <span>Student Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('student.results') }}" class="nav-link text-white">
                                    <i class="bi bi-bar-chart-line"></i> <span>Results</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('student.surveys') }}" class="nav-link text-white">
                                    <i class="bi bi-clipboard-check"></i> <span>Surveys</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                    <hr class="text-white w-100">
                    <a href="{{ route('logout') }}" class="btn btn-outline-light btn-sm mt-auto"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col py-3">
                @yield('content')
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <p class="mb-0">&copy; 2025 My Laravel App. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>
