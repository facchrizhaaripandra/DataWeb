<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
    <div class="container-fluid">
        <!-- Sidebar Toggle Button -->
        <button class="btn btn-dark me-2" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Brand -->
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <i class="fas fa-database me-2"></i>
            <strong>Data Management</strong>
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Quick Actions -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('datasets.create') }}">
                                <i class="fas fa-plus text-primary"></i> New Dataset
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('imports.create') }}">
                                <i class="fas fa-file-import text-success"></i> Import Data
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('dashboard.analytics') }}">
                                <i class="fas fa-chart-bar text-info"></i> Analytics
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger notification-badge">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 300px;">
                        <div class="dropdown-header">
                            <h6 class="mb-0">Notifications</h6>
                        </div>
                        <div class="dropdown-body" id="notificationList" style="max-height: 300px; overflow-y: auto;">
                            <!-- Notifications will be loaded here -->
                        </div>
                        <div class="dropdown-footer text-center">
                            <a href="#" class="text-decoration-none small">View All</a>
                        </div>
                    </div>
                </li>
                
                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        {{ Auth::user()->name }}
                        <span class="badge bg-{{ Auth::user()->isAdmin() ? 'danger' : 'primary' }} ms-1">
                            {{ Auth::user()->role }}
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user text-primary"></i> Profile
                            </a>
                        </li>
                        @if(Auth::user()->isAdmin())
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-cog text-danger"></i> Admin Panel
                            </a>
                        </li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="dropdown-item p-0">
                                @csrf
                                <button type="submit" class="btn btn-link text-decoration-none w-100 text-start">
                                    <i class="fas fa-sign-out-alt text-danger"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>