<div class="sidebar" id="sidebar">
    <div class="sidebar-header p-3 border-bottom">
        <div class="d-flex align-items-center">
            <div class="sidebar-logo me-2">
                <i class="fas fa-database fa-2x text-white"></i>
            </div>
            <div class="sidebar-title">
                <h6 class="mb-0 text-white">Navigation</h6>
                <small class="text-white-50">Data Management System</small>
            </div>
        </div>
    </div>
    
    <div class="sidebar-body">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <!-- Datasets -->
            <li class="nav-item has-submenu {{ request()->is('datasets*') ? 'open' : '' }}">
                <a class="nav-link {{ request()->is('datasets*') ? 'active' : '' }}" href="{{ route('datasets.index') }}">
                    <i class="fas fa-database"></i>
                    <span>Datasets</span>
                    <span class="badge bg-primary float-end">{{ Auth::user()->datasets()->count() }}</span>
                </a>
                <div class="submenu-toggle">
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="submenu {{ request()->is('datasets*') ? 'open' : '' }}">
                    <a class="nav-link {{ request()->is('datasets/create') ? 'active' : '' }}" href="{{ route('datasets.create') }}">
                        <i class="fas fa-plus-circle"></i>
                        <span>Create New</span>
                    </a>
                    <a class="nav-link {{ request()->is('datasets/*/analyze') ? 'active' : '' }}" href="#">
                        <i class="fas fa-chart-bar"></i>
                        <span>Analytics</span>
                    </a>
                </div>
            </li>
            
            <!-- Import -->
            <li class="nav-item">
                <a class="nav-link {{ request()->is('imports*') ? 'active' : '' }}" href="{{ route('imports.index') }}">
                    <i class="fas fa-file-import"></i>
                    <span>Import Excel</span>
                </a>
            </li>
            

            
            <!-- Analytics -->
            <li class="nav-item">
                <a class="nav-link {{ request()->is('analytics*') ? 'active' : '' }}" href="{{ route('dashboard.analytics') }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
            </li>
            
            <!-- Divider -->
            <li class="nav-item mt-3">
                <div class="nav-divider">
                    <small class="text-white-50">SYSTEM</small>
                </div>
            </li>
            
            <!-- Admin Panel (only for admins) -->
            @if(Auth::user()->isAdmin())
            <li class="nav-item has-submenu {{ request()->is('admin*') ? 'open' : '' }}">
                <a class="nav-link {{ request()->is('admin*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-cog text-danger"></i>
                    <span>Admin Panel</span>
                </a>
                <div class="submenu-toggle">
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="submenu {{ request()->is('admin*') ? 'open' : '' }}">
                    <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </a>
                    <a class="nav-link {{ request()->is('admin/system*') ? 'active' : '' }}" href="{{ route('admin.system') }}">
                        <i class="fas fa-server"></i>
                        <span>System Info</span>
                    </a>
                    <a class="nav-link {{ request()->is('admin/logs*') ? 'active' : '' }}" href="{{ route('admin.logs') }}">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Activity Logs</span>
                    </a>
                </div>
            </li>
            @endif
            
            <!-- Profile -->
            <li class="nav-item has-submenu {{ request()->is('profile*') ? 'open' : '' }}">
                <a class="nav-link {{ request()->is('profile*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
                <div class="submenu-toggle">
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="submenu {{ request()->is('profile*') ? 'open' : '' }}">
                    <a class="nav-link {{ request()->is('profile/edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                        <i class="fas fa-edit"></i>
                        <span>Edit Profile</span>
                    </a>
                    <a class="nav-link" href="#">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                    </a>
                    <a class="nav-link" href="#">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                </div>
            </li>
            
            <!-- Help -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#helpModal">
                    <i class="fas fa-question-circle"></i>
                    <span>Help & Support</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer p-3 border-top">
        <div class="d-flex justify-content-between align-items-center">
            <div class="sidebar-user">
                <small class="text-white-50">Logged in as:</small>
                <div class="text-white">{{ Auth::user()->name }}</div>
            </div>
            <button class="btn btn-sm btn-outline-light" id="sidebarCollapse" title="Toggle Sidebar">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle me-2"></i> Help & Support
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-book text-primary me-2"></i>
                        User Documentation
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-video text-success me-2"></i>
                        Video Tutorials
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-envelope text-warning me-2"></i>
                        Contact Support
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-bug text-danger me-2"></i>
                        Report a Bug
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>