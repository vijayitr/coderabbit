<!-- Toggle Sidebar Control (Hidden Checkbox) -->
<input type="checkbox" id="sidebarToggle">

<!-- Sidebar -->
<div class="sidebar">
    <div class="d-flex flex-column p-2">
        <div class="d-flex gap-1 justify-content-between align-items-center p-2">
            <a href="/" class="navbar-brand text-white flex-grow-1">
                <img src="{{url('/images/logo_white.png')}}" alt="Expanded Logo" class="logo-expanded">
                <img src="{{url('/images/White-initial-logo.png')}}" alt="Collapsed Logo" class="logo-collapsed">
            </a>
            <label for="sidebarToggle" class="toggle-label">
                <i class="bi bi-chevron-left"></i>
                <i class="bi bi-chevron-right"></i>
            </label>
        </div>
        <ul class="nav flex-column py-3">
            <li class="nav-item">
                <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }} w-100">
                    <img src="{{ url('/images/sidebar-icons/dashboard.png') }}" alt="dashboard">
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            @if ($authUser->can('time_entry.create') || $authUser->can('time_entry.view') ||  $authUser->can('time_entry.edit'))
            <li class="nav-item">
                <a href="{{ url('/time-entry') }}" class="nav-link {{ request()->is('time-entry*') ? 'active' : '' }} w-100">
                    <img src="{{ url('/images/sidebar-icons/time-entry.png') }}" alt="time-entry">
                    <span class="menu-text">Time Entry</span>
                </a>
            </li>
            @endif
            @if ($authUser->can('role.create') || $authUser->can('role.view') ||  $authUser->can('role.edit') ||  $authUser->can('role.delete'))
            <li class="nav-item">
                <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles*') ? 'active' : '' }} w-100">
                    <i class="bi bi-shield-lock"></i>
                    <span class="menu-text">Roles & Permissions</span>
                </a>
            </li>
            @endif
            @if ($authUser->can('case_allocation.view') || $authUser->can('case_allocation.view_all_allocations') ||  $authUser->can('case_allocation.add') ||  $authUser->can('case_allocation.remove'))
            <li class="nav-item">
                <a href="{{ url('/case-allocations') }}" class="nav-link {{ request()->is('case-allocations*') ? 'active' : '' }} w-100">
                    <img src="{{ url('/images/sidebar-icons/cash-allocation.png') }}" alt="case-allocation">
                    <span class="menu-text">Case Allocation</span>
                </a>
            </li>
            @endif
            @if ($authUser->can('user.create') || $authUser->can('user.view') ||  $authUser->can('user.edit') ||  $authUser->can('user.delete'))
            <li class="nav-item">
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users*') ? 'active' : '' }} w-100">
                    <img src="{{ url('/images/sidebar-icons/manager-management.png') }}" alt="manager-management">
                    <span class="menu-text">User Management</span>
                </a>
            </li>
            @endif

            @if ($authUser->can('client.create') || $authUser->can('client.view') ||  $authUser->can('client.edit') ||  $authUser->can('client.delete'))
            <li class="nav-item">
                <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients*') ? 'active' : '' }} w-100">
                    <img src="{{ url('/images/sidebar-icons/manager-management.png') }}" alt="manager-management">
                    <span class="menu-text">Clients</span>
                </a>
            </li>
            @endif

            @if ($authUser->can('workflow.view'))
            <li class="nav-item">
                <a href="{{ route('workflows.index') }}" class="nav-link {{ request()->routeIs('workflows*') ? 'active' : '' }} w-100">
                    <img src="{{ url('/images/sidebar-icons/manager-management.png') }}" alt="manager-management">
                    <span class="menu-text">Workflows</span>
                </a>
            </li>
            @endif

            @if ($authUser->can('report.view'))
            <li class="nav-item">
                <a href="{{ url('/reports') }}" class="nav-link {{ request()->is('reports*') ? 'active' : '' }} w-100">
                    <img src="{{ url('/images/sidebar-icons/reporting.png') }}" alt="reporting">
                    <span class="menu-text">Reports</span>
                </a>
            </li>
            @endif
            @if ($authUser->can('ticket.view'))
            <li class="nav-item">
                <a href="{{ route('tickets.index') }}" class="nav-link {{ request()->routeIs('tickets*') ? 'active' : '' }} w-100">
                    <i class="bi bi-chat-dots"></i>
                    <span class="menu-text">Ticket System</span>
                </a>
            </li>
            @endif

            @if ($authUser->can('report.view')) 
                <li class="nav-item">
                    <a href="{{ route('qualityAssurance.index') }}" class="nav-link {{ request()->routeIs('qualityAssurance*') ? 'active' : '' }} w-100">
                        <i class="bi bi-chat-dots"></i>
                        <span class="menu-text">Call Allocations</span>
                    </a>
                </li>
            @endif

            <!-- <li class="nav-item d-block">
                <a class="nav-link d-flex align-items-center justify-content-between {{ request()->is('qc-manager*') ? 'active' : '' }} w-100" 
                   data-bs-toggle="collapse" 
                   href="#qcManagerSubmenu" 
                   role="button" 
                   aria-expanded="{{ request()->is('qc-manager*') ? 'true' : 'false' }}" 
                   aria-controls="qcManagerSubmenu">
                    <div>
                        <img src="{{ url('/images/sidebar-icons/qc-manager.png') }}" alt="qc-manager" class="me-2">
                        <span class="menu-text">QC Manager</span>
                    </div>
                    <i class="bi bi-chevron-down"></i> {{-- Bootstrap Icons optional --}}
                </a>

                <div class="collapse {{ request()->is('qc-manager*') ? 'show' : '' }} w-100 mt-1" id="qcManagerSubmenu">
                    <ul class="nav flex-column ms-4">
                        <li class="nav-item">
                            <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboards*') ? 'active' : '' }} w-100">
                                <img src="{{ url('/images/sidebar-icons/dashboard.png') }}" alt="dashboard" class="me-2">
                                <span class="menu-text">Dashboard</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }} w-100">
                                <img src="{{ url('/images/sidebar-icons/dashboard.png') }}" alt="dashboard" class="me-2">
                                <span class="menu-text">Dashboard</span>
                            </a>
                        </li>
                        {{-- Add more submenu items here --}}
                    </ul>
                </div>
            </li> -->


          <!--   <li class="nav-item">
                <a href="{{ url('/qc-manager') }}" class="nav-link {{ request()->is('qc-manager*') ? 'active' : '' }} w-100">
                    <img src="{{ url('/images/sidebar-icons/qc-manager.png') }}" alt="qc-manager">
                    <span class="menu-text">QC Manager</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/qc-agent') }}" class="nav-link {{ request()->is('qc-agent*') ? 'active' : '' }} w-100">
                    <img src="{{ url('/images/sidebar-icons/qc-agent.png') }}" alt="qc-agent">
                    <span class="menu-text">QC Agent</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/notifications') }}" class="nav-link {{ request()->is('notifications*') ? 'active' : '' }} w-100">
                    <img src="{{ url('/images/sidebar-icons/notification.png') }}" alt="notification">
                    <span class="menu-text">Notifications & Alerts</span>
                </a>
            </li> -->
            <li class="nav-item position-absolute bottom-0 py-3">
                <a href="{{ url('/logout') }}" class="nav-link {{ request()->is('logout*') ? 'active' : '' }} w-100">
                    <img src="{{ url('/images/sidebar-icons/logout.png') }}" alt="logout">
                    <span class="menu-text">Logout</span>
                </a>
            </li>
        </ul>

    </div>
</div>