<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title') - Data Management System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" crossorigin="anonymous">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" crossorigin="anonymous">
    
    <style>
        /* Reset dan base styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        html, body {
            overflow-x: hidden !important;
            max-width: 100% !important;
            width: 100% !important;
            position: relative;
        }
        
        /* Layout utama */
        .app-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            min-width: 250px;
            background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
            color: white;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform 0.3s ease;
        }
        
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.mobile-open {
                transform: translateX(0);
            }
        }
        
        /* Main content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            width: calc(100% - 250px);
            max-width: calc(100% - 250px);
            overflow-x: hidden;
            transition: margin-left 0.3s ease;
            padding-top: 56px;
        }
        
        .main-content.expanded {
            margin-left: 0;
            width: 100%;
            max-width: 100%;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                max-width: 100%;
                padding-top: 56px;
            }
        }
        
        /* Navbar */
        .navbar {
            background: #343a40 !important;
            border-bottom: 1px solid #495057;
            padding: 0.5rem 1rem;
            position: fixed !important;
            top: 0;
            left: 250px;
            width: calc(100% - 250px);
            z-index: 1030;
            transition: left 0.3s ease;
            overflow: visible !important;
        }
        
        .navbar.expanded {
            left: 0;
            width: 100%;
        }
        
        @media (max-width: 768px) {
            .navbar {
                left: 0;
                width: 100%;
            }
        }
        
        /* Content wrapper */
        .content-wrapper {
            padding: 20px;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Card styles */
        .card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }
        
        .card-body {
            padding: 1.25rem;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Table container - FIX untuk tidak ada scroll horizontal */
        .table-responsive-container {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* DataTables customization */
        .dataTables_wrapper {
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
        }
        
        .dataTables_scroll {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        .dataTables_scrollBody {
            overflow-x: hidden !important;
            max-width: 100% !important;
        }
        
        table.dataTable {
            width: 100% !important;
            max-width: 100% !important;
            table-layout: fixed !important;
        }
        
        table.dataTable th,
        table.dataTable td {
            min-width: 100px;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Mobile sidebar toggle */
        .mobile-sidebar-toggle {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1001;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #2c3e50;
            color: white;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        @media (max-width: 768px) {
            .mobile-sidebar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }
        
        /* Form controls */
        .form-control, .form-select, .btn {
            max-width: 100%;
        }
        
        /* Utility classes */
        .no-scroll-x {
            overflow-x: hidden !important;
            max-width: 100% !important;
        }
        
        .full-width {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        .prevent-overflow {
            overflow: hidden !important;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Print styles */
        @media print {
            .sidebar, .navbar, .btn {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }
        
        /* Navbar dropdown fixes */
        .navbar .dropdown {
            position: relative;
        }

        .navbar .dropdown-menu {
            z-index: 1050 !important;
            position: fixed !important;
            min-width: 200px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 0.375rem;
            background-color: white;
            display: none;
            top: 56px !important; /* Height of navbar */
            right: 1rem !important; /* Right margin */
        }

        .navbar .dropdown-menu.show {
            display: block !important;
        }
        
        .dropdown-item {
            padding: 0.5rem 1rem;
            color: #212529;
            text-decoration: none;
            transition: background-color 0.15s ease-in-out, color 0.15s ease-in-out;
            display: block;
            clear: both;
            font-weight: 400;
            white-space: nowrap;
            border: 0;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #1e2125;
        }
        
        .dropdown-item:focus {
            background-color: #f8f9fa;
            color: #1e2125;
            outline: 0;
        }
    </style>
    
    @yield('styles')
</head>
<body class="no-scroll-x">
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="p-3">
                <h4 class="text-center mb-4">
                    <i class="fas fa-database"></i> Data System
                </h4>
                
                <div class="list-group list-group-flush">
                    <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action bg-transparent text-white border-0 mb-1">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    
                    <a href="{{ route('datasets.index') }}" class="list-group-item list-group-item-action bg-transparent text-white border-0 mb-1">
                        <i class="fas fa-table me-2"></i> Datasets
                    </a>
                    
                    <a href="{{ route('imports.index') }}" class="list-group-item list-group-item-action bg-transparent text-white border-0 mb-1">
                        <i class="fas fa-file-import me-2"></i> Import Excel
                    </a>
                    

                    
                    <a href="{{ route('dashboard.analytics') }}" class="list-group-item list-group-item-action bg-transparent text-white border-0 mb-1">
                        <i class="fas fa-chart-bar me-2"></i> Analisis
                    </a>
                    
                    @if(auth()->check() && auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action bg-transparent text-white border-0 mb-1">
                        <i class="fas fa-cog me-2"></i> Admin Panel
                    </a>
                    @endif
                    
                    <hr class="bg-light my-3">
                    
                    <div class="list-group-item bg-transparent text-white border-0">
                        <small>User: {{ auth()->user()->name ?? 'Guest' }}</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-dark">
                <div class="container-fluid">
                    <button class="btn btn-outline-light me-2" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <a class="navbar-brand" href="{{ route('dashboard') }}">
                        <i class="fas fa-database me-2"></i> Data Management System
                    </a>
                    
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user me-1"></i> {{ Auth::user()->name ?? 'Guest' }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                            <i class="fas fa-user-cog me-2"></i> Profile
                                        </a>
                                    </li>
                                    @if(Auth::check() && Auth::user()->isAdmin())
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                            <i class="fas fa-cog me-2"></i> Admin Panel
                                        </a>
                                    </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            
            <!-- Content Wrapper -->
            <div class="content-wrapper no-scroll-x">
                <!-- Notifications -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> 
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <!-- Main Content -->
                @yield('content')
            </div>
        </div>
        
        <!-- Mobile Sidebar Toggle Button -->
        <button class="mobile-sidebar-toggle" id="mobileSidebarToggle" onclick="toggleMobileSidebar()">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" crossorigin="anonymous"></script>
    
    <script>
        // Sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const navbar = document.querySelector('.navbar');
            
            if (window.innerWidth <= 768) {
                // Mobile view
                sidebar.classList.toggle('mobile-open');
            } else {
                // Desktop view
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                navbar.classList.toggle('expanded');
            }
            
            // Force reflow to prevent horizontal scroll
            document.body.style.overflowX = 'hidden';
        }
        
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('mobile-open');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.getElementById('mobileSidebarToggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !mobileToggle.contains(event.target) &&
                sidebar.classList.contains('mobile-open')) {
                sidebar.classList.remove('mobile-open');
            }
        });
        
        // Prevent horizontal scroll on window resize
        window.addEventListener('resize', function() {
            document.body.style.overflowX = 'hidden';
            document.documentElement.style.overflowX = 'hidden';
        });
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Force no horizontal scroll
            document.body.style.overflowX = 'hidden';
            document.documentElement.style.overflowX = 'hidden';

            // Initialize Bootstrap dropdowns
            const dropdownElements = document.querySelectorAll('[data-bs-toggle="dropdown"]');
            dropdownElements.forEach(function(element) {
                new bootstrap.Dropdown(element);
            });

            // Fix DataTables width issues
            if ($.fn.dataTable.isDataTable('table')) {
                $('table').DataTable().columns.adjust().responsive.recalc();
            }

            // Adjust table widths
            $('table').each(function() {
                $(this).css('width', '100%');
                $(this).parent().css('overflow-x', 'hidden');
            });

            // Make all containers full width
            $('.container, .container-fluid').addClass('no-scroll-x full-width');
        });
        
        // Global function to fix overflow issues
        function fixOverflowIssues() {
            // Fix body overflow
            document.body.style.overflowX = 'hidden';
            document.documentElement.style.overflowX = 'hidden';
            
            // Fix all container overflows
            $('.container, .container-fluid, .row, .col, [class*="col-"]').css({
                'overflow-x': 'hidden',
                'max-width': '100%'
            });
            
            // Fix tables
            $('table').each(function() {
                const $table = $(this);
                $table.css('width', '100%');
                
                // Wrap table in responsive container
                if (!$table.parent().hasClass('table-responsive-container')) {
                    $table.wrap('<div class="table-responsive-container"></div>');
                }
            });
            
            // Fix modals
            $('.modal').css('overflow-x', 'hidden');
            $('.modal-content').css('max-width', '100%');
        }
        
        // Call overflow fix on various events
        $(document).on('shown.bs.modal', fixOverflowIssues);
        $(window).on('load resize orientationchange', fixOverflowIssues);
        setInterval(fixOverflowIssues, 1000); // Safety check every second
    </script>
    
    @yield('scripts')
</body>
</html>