<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ThemVet Admin')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1a1f36 0%, #252b42 100%);
            position: fixed;
            width: 240px;
            z-index: 100;
            overflow-y: auto;
        }
        .sidebar .logo {
            padding: 20px;
            color: #fff;
            font-size: 1.3rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            letter-spacing: 0.5px;
        }
        .sidebar .logo i { color: #6366f1; margin-right: 8px; }
        .sidebar nav { padding: 12px 0; }
        .sidebar nav a {
            color: #94a3b8;
            text-decoration: none;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.15s;
            border-left: 3px solid transparent;
        }
        .sidebar nav a i { width: 18px; text-align: center; font-size: 0.9rem; }
        .sidebar nav a:hover {
            color: #e2e8f0;
            background-color: rgba(255,255,255,0.05);
        }
        .sidebar nav a.active {
            color: #fff;
            background-color: rgba(99,102,241,0.15);
            border-left-color: #6366f1;
        }
        .sidebar .nav-section {
            padding: 8px 20px 4px;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 600;
        }
        .main-content {
            margin-left: 240px;
            padding: 24px;
            min-height: 100vh;
        }
        .page-header {
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        .page-header h2 { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0; }
        .page-header p { font-size: 0.875rem; color: #64748b; margin: 4px 0 0; }
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            transition: transform 0.15s;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-card .card-body { padding: 20px; }
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .stat-card .stat-value { font-size: 1.8rem; font-weight: 700; color: #1e293b; }
        .stat-card .stat-label { font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .table { margin: 0; }
        .table thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: 600;
            padding: 10px 16px;
        }
        .table tbody td {
            padding: 12px 16px;
            vertical-align: middle;
            border-color: #f1f5f9;
            font-size: 0.875rem;
        }
        .badge { font-weight: 500; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; }
        .btn { border-radius: 8px; font-weight: 500; font-size: 0.875rem; }
        .btn-sm { padding: 4px 12px; }
        .form-control, .form-select { border-radius: 8px; border-color: #e2e8f0; }
        .form-control:focus, .form-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
        .modal-content { border-radius: 12px; border: none; }
        .modal-header { border-bottom: 1px solid #f1f5f9; }
        .modal-footer { border-top: 1px solid #f1f5f9; }
        .flash-message {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .content-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
        }
        .content-card .card-header {
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            padding: 14px 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .content-card .card-body { padding: 20px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i> ThemVet
        </div>
        <nav>
            {{-- ADMIN SIDEBAR --}}
            @if(Auth::user()->isAdmin())
                <div class="nav-section">Main</div>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Users
                </a>

                <div class="nav-section">Content</div>
                <a href="{{ route('admin.exams.index') }}" class="{{ request()->routeIs('admin.exams*') ? 'active' : '' }}">
                    <i class="fas fa-book"></i> Exams
                </a>
                <a href="{{ route('admin.subjects.index') }}" class="{{ request()->routeIs('admin.subjects*') ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i> Subjects
                </a>
                <a href="{{ route('admin.topics.index') }}" class="{{ request()->routeIs('admin.topics*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i> Topics
                </a>
                <a href="{{ route('admin.questions.index') }}" class="{{ request()->routeIs('admin.questions*') ? 'active' : '' }}">
                    <i class="fas fa-question-circle"></i> Questions
                </a>

                <div class="nav-section">Tests</div>
                <a href="{{ route('admin.mock-tests.index') }}" class="{{ request()->routeIs('admin.mock-tests*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i> Mock Tests
                </a>
                <a href="{{ route('admin.review-queue') }}" class="{{ request()->routeIs('admin.review-queue') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check"></i> Review Queue
                </a>

                <div class="nav-section">Management</div>
                <a href="{{ route('admin.reviewer-assignments.index') }}" class="{{ request()->routeIs('admin.reviewer-assignments.*') ? 'active' : '' }}">
                    <i class="fas fa-user-tag"></i> Reviewers
                </a>
                <a href="{{ route('admin.analytics') }}" class="{{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
                <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles*') ? 'active' : '' }}">
                    <i class="fas fa-user-shield"></i> Roles
                </a>
                <a href="{{ route('admin.notifications.index') }}" class="{{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i> Notifications
                </a>
                <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> Settings
                </a>

            {{-- TEACHER SIDEBAR --}}
            @elseif(Auth::user()->isTeacher())
                <div class="nav-section">Main</div>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>

                <div class="nav-section">Content</div>
                <a href="{{ route('admin.questions.index') }}" class="{{ request()->routeIs('admin.questions*') ? 'active' : '' }}">
                    <i class="fas fa-question-circle"></i> Questions
                </a>

                <div class="nav-section">Tests</div>
                <a href="{{ route('admin.mock-tests.index') }}" class="{{ request()->routeIs('admin.mock-tests*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i> Mock Tests
                </a>
                <a href="{{ route('admin.review-queue') }}" class="{{ request()->routeIs('admin.review-queue') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check"></i> Review Queue
                </a>

            {{-- STUDENT SIDEBAR --}}
            @else
                <div class="nav-section">Main</div>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>

                <div class="nav-section">Content</div>
                <a href="{{ route('admin.exams.index') }}" class="{{ request()->routeIs('admin.exams*') ? 'active' : '' }}">
                    <i class="fas fa-book"></i> Exams
                </a>
                <a href="{{ route('admin.mock-tests.index') }}" class="{{ request()->routeIs('admin.mock-tests*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i> Mock Tests
                </a>
            @endif

            <a href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="margin-top: 8px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 16px;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>

    <div class="main-content">
        @if(session('success'))
            <div class="flash-message alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="flash-message alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
