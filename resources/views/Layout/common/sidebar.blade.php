<div class="sidebar" id="sidebar">

    <!-- Start Logo -->
    <div class="sidebar-logo">
        <div>
            <!-- Logo Normal -->
            <a href="{{ route('dashboard') }}" class="logo logo-normal">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Logo">
            </a>

            <!-- Logo Small -->
            <a href="{{ route('dashboard') }}" class="logo-small">
                <img src="{{ asset('assets/img/logo-small.svg') }}" alt="Logo">
            </a>

            <!-- Logo Dark -->
            <a href="{{ route('dashboard') }}" class="dark-logo">
                <img src="{{ asset('assets/img/logo-white.svg') }}" alt="Logo">
            </a>
        </div>
        <button class="sidenav-toggle-btn btn border-0 p-0 active" id="toggle_btn">
            <i class="ti ti-arrow-bar-to-left"></i>
        </button>

        <!-- Sidebar Menu Close -->
        <button class="sidebar-close">
            <i class="ti ti-x align-middle"></i>
        </button>
    </div>
    <!-- End Logo -->

    <!-- Sidenav Menu -->
    <div class="sidebar-inner" data-simplebar>
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="menu-title"><span>Main Menu</span></li>
                <li>
                    <ul>
                        <li>
                            <a href="{{ route('dashboard') }}"
                                class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="ti ti-dashboard"></i><span>Dashboard</span>
                            </a>
                        </li>
                        {{-- <li class="submenu">
                            <a href="javascript:void(0);" class="active subdrop">
                                <i class="ti ti-dashboard"></i><span>Dashboard</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}" class="active">Deals Dashboard</a></li>
                                <li><a href="{{ route('dashboard') }}">Leads Dashboard</a></li>
                                <li><a href="{{ route('dashboard') }}">Project Dashboard</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i
                                    class="ti ti-brand-airtable"></i><span>Applications</span><span
                                    class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Chat</a></li>
                                <li class="submenu submenu-two">
                                    <a href="javascript:void(0);">Call<span
                                            class="menu-arrow inside-submenu"></span></a>
                                    <ul>
                                        <li><a href="{{ route('dashboard') }}">Video Call</a></li>
                                        <li><a href="{{ route('dashboard') }}">Audio Call</a></li>
                                        <li><a href="{{ route('dashboard') }}">Call History</a></li>
                                    </ul>
                                </li>
                                <li><a href="{{ route('dashboard') }}">Calendar</a></li>
                                <li><a href="{{ route('dashboard') }}">Email</a></li>
                                <li><a href="{{ route('dashboard') }}">To Do</a></li>
                                <li><a href="{{ route('dashboard') }}">Notes</a></li>
                                <li><a href="{{ route('dashboard') }}">File Manager</a></li>
                                <li><a href="{{ route('dashboard') }}">Social Feed</a></li>
                                <li><a href="{{ route('dashboard') }}">Kanban</a></li>
                                <li><a href="{{ route('dashboard') }}">Invoices</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="#">
                                <i class="ti ti-user-star"></i><span>Super Admin</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li><a href="{{ route('dashboard') }}">Companies</a></li>
                                <li><a href="{{ route('dashboard') }}">Subscriptions</a></li>
                                <li><a href="{{ route('dashboard') }}">Packages</a></li>
                                <li><a href="{{ route('dashboard') }}">Domain</a></li>
                                <li><a href="{{ route('dashboard') }}">Purchase Transaction</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="#">
                                <i class="ti ti-layout-grid"></i><span>Layouts</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Mini</a></li>
                                <li><a href="{{ route('dashboard') }}">Hover View</a></li>
                                <li><a href="{{ route('dashboard') }}">Hidden</a></li>
                                <li><a href="{{ route('dashboard') }}">Full Width</a></li>
                                <li><a href="{{ route('dashboard') }}">RTL</a></li>
                                <li><a href="{{ route('dashboard') }}">Dark</a></li>
                            </ul>
                        </li> --}}
                    </ul>
                </li>
                @if (
                    \App\Helpers\PermissionHelper::canView('location') ||
                        \App\Helpers\PermissionHelper::canView('company') ||
                        \App\Helpers\PermissionHelper::canView('area'))
                    <li class="menu-title"><span>Masters</span></li>
                    <li>
                        <ul>
                            @if (\App\Helpers\PermissionHelper::canView('location'))
                                <li>
                                    <a href="{{ route('location.index') }}"
                                        class="{{ request()->routeIs('location.*') ? 'active' : '' }}"><i
                                            class="ti ti-map-pin-pin"></i><span>Locations</span></a>
                                </li>
                            @endif
                            @if (\App\Helpers\PermissionHelper::canView('company'))
                                <li>
                                    <a href="{{ route('company.index') }}"
                                        class="{{ request()->routeIs('company.*') ? 'active' : '' }}"><i
                                            class="ti ti-building-community"></i><span>Companies</span></a>
                                </li>
                            @endif
                            @if (\App\Helpers\PermissionHelper::canView('area'))
                                <li>
                                    <a href="{{ route('area.index') }}"
                                        class="{{ request()->routeIs('area.*') ? 'active' : '' }}"><i
                                            class="ti ti-building-factory"></i><span>Areas</span></a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (\App\Helpers\PermissionHelper::canView('user') || \App\Helpers\PermissionHelper::canView('role_permission'))
                    <li class="menu-title"><span>Users</span></li>
                    <li>
                        <ul>
                            @if (\App\Helpers\PermissionHelper::canView('role_permission'))
                                <li class="submenu">
                                    <a href="javascript:void(0);"
                                        class="{{ request()->routeIs('role-permission.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'active subdrop' : '' }}">
                                        <i class="ti ti-user-shield"></i><span>Roles & Permissions</span><span
                                            class="menu-arrow"></span>
                                    </a>
                                    <ul>
                                        <li><a href="{{ route('role-permission.index') }}"
                                                class="{{ request()->routeIs('role-permission.index') ? 'active' : '' }}">Manage
                                                Roles</a></li>
                                        <li><a href="{{ route('permissions.index') }}"
                                                class="{{ request()->routeIs('permissions.index') ? 'active' : '' }}">Manage
                                                Permissions</a></li>
                                    </ul>
                                </li>
                            @endif
                            @if (\App\Helpers\PermissionHelper::canView('user'))
                                <li>
                                    <a href="{{ route('user.index') }}"
                                        class="{{ request()->routeIs('user.*') ? 'active' : '' }}"><i
                                            class="ti ti-user"></i><span>Users</span></a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (\App\Helpers\PermissionHelper::canView('device') || \App\Helpers\PermissionHelper::canView('schedule'))
                    <li class="menu-title"><span>Devices</span></li>
                    <li>
                        <ul>
                            @if (\App\Helpers\PermissionHelper::canView('device'))
                                <li>
                                    <a href="{{ route('device.index') }}"
                                        class="{{ request()->routeIs('device.*') ? 'active' : '' }}"><i
                                            class="ti ti-device-tablet-plus"></i><span>Devices</span></a>
                                </li>
                            @endif
                            @if (\App\Helpers\PermissionHelper::canView('schedule'))
                                <li>
                                    <a href="{{ route('schedule.index') }}"
                                        class="{{ request()->routeIs('schedule.*') ? 'active' : '' }}"><i
                                            class="ti ti-calendar-event"></i><span>Schedules</span></a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- <li class="menu-title"><span>Reports</span></li>
                <li>
                    <ul>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-report-analytics"></i><span>Reports</span><span
                                    class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Lead Reports</a></li>
                                <li><a href="{{ route('dashboard') }}">Deal Reports</a></li>
                                <li><a href="{{ route('dashboard') }}">Contact Reports</a></li>
                                <li><a href="{{ route('dashboard') }}">Company Reports</a></li>
                                <li><a href="{{ route('dashboard') }}">Project Reports</a></li>
                                <li><a href="{{ route('dashboard') }}">Task Reports</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>CRM Settings</span></li>
                <li>
                    <ul>
                        <li><a href="{{ route('dashboard') }}"><i class="ti ti-artboard"></i><span>Sources</span></a>
                        </li>
                        <li><a href="{{ route('dashboard') }}"><i class="ti ti-message-exclamation"></i><span>Lost
                                    Reason</span></a></li>
                        <li><a href="{{ route('dashboard') }}"><i class="ti ti-steam"></i><span>Contact
                                    Stage</span></a></li>
                        <li><a href="{{ route('dashboard') }}"><i
                                    class="ti ti-building-factory"></i><span>Industry</span></a></li>
                        <li><a href="{{ route('dashboard') }}"><i class="ti ti-phone-check"></i><span>Calls</span></a>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>User Management</span></li>
                <li>
                    <ul>
                        <li><a href="{{ route('user.index') }}" class="{{ request()->routeIs('user.*') ? 'active' : '' }}"><i class="ti ti-users"></i><span>Manage Users</span></a>
                        </li>
                        <li><a href="{{ route('role-permission.index') }}" class="{{ request()->routeIs('role-permission.*') || request()->routeIs('roles.*') ? 'active' : '' }}"><i class="ti ti-user-shield"></i><span>Roles & Permissions</span></a></li>
                        <li><a href="{{ route('dashboard') }}"><i class="ti ti-flag-question"></i><span>Delete
                                    Request</span></a></li>
                    </ul>
                </li>
                <li class="menu-title"><span>Membership</span></li>
                <li>
                    <ul>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-brand-apple-podcast"></i><span>Membership</span><span
                                    class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Membership Plans</a></li>
                                <li><a href="{{ route('dashboard') }}">Membership Addons</a></li>
                                <li><a href="{{ route('dashboard') }}">Transactions</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>Content</span></li>
                <li>
                    <ul>
                        <li><a href="{{ route('dashboard') }}"><i class="ti ti-page-break"></i><span>Pages</span></a>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-brand-blogger"></i><span>Blog</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">All Blogs</a></li>
                                <li><a href="{{ route('dashboard') }}">Blog Categories</a></li>
                                <li><a href="{{ route('dashboard') }}">Blog Comments</a></li>
                                <li><a href="{{ route('dashboard') }}">Blog Tags</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-map-pin-pin"></i><span>Location</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Countries</a></li>
                                <li><a href="{{ route('dashboard') }}">States</a></li>
                                <li><a href="{{ route('dashboard') }}">Cities</a></li>
                            </ul>
                        </li>
                        <li><a href="{{ route('dashboard') }}"><i
                                    class="ti ti-quote"></i><span>Testimonials</span></a>
                        </li>
                        <li><a href="{{ route('dashboard') }}"><i
                                    class="ti ti-question-mark"></i><span>FAQ</span></a></li>
                    </ul>
                </li>
                <li class="menu-title"><span>Support</span></li>
                <li>
                    <ul>
                        <li><a href="{{ route('dashboard') }}"><i class="ti ti-message-check"></i><span>Contact
                                    Messages</span></a></li>
                        <li><a href="{{ route('dashboard') }}"><i class="ti ti-ticket"></i><span>Tickets</span></a>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>Settings</span></li>
                <li>
                    <ul>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-settings-cog"></i><span>General Settings</span><span
                                    class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Profile</a></li>
                                <li><a href="{{ route('dashboard') }}">Security</a></li>
                                <li><a href="{{ route('dashboard') }}">Notifications</a></li>
                                <li><a href="{{ route('dashboard') }}">Connected Apps</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-world-cog"></i><span>Website Settings</span><span
                                    class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Company Settings</a></li>
                                <li><a href="{{ route('dashboard') }}">Localization</a></li>
                                <li><a href="{{ route('dashboard') }}">Prefixes</a></li>
                                <li><a href="{{ route('dashboard') }}">Preference</a></li>
                                <li><a href="{{ route('dashboard') }}">Appearance</a></li>
                                <li><a href="{{ route('dashboard') }}">Language</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-apps"></i><span>App Settings</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Invoice Settings</a></li>
                                <li><a href="{{ route('dashboard') }}">Printers</a></li>
                                <li><a href="{{ route('dashboard') }}">Custom Fields</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-device-laptop"></i><span>System Settings</span><span
                                    class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Email Settings</a></li>
                                <li><a href="{{ route('dashboard') }}">SMS Gateways</a></li>
                                <li><a href="{{ route('dashboard') }}">GDPR Cookies</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-moneybag"></i><span>Financial Settings</span><span
                                    class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Payment Gateways</a></li>
                                <li><a href="{{ route('dashboard') }}">Bank Accounts</a></li>
                                <li><a href="{{ route('dashboard') }}">Tax Rates</a></li>
                                <li><a href="{{ route('dashboard') }}">Currencies</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-settings-2"></i><span>Other Settings</span><span
                                    class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Sitemap</a></li>
                                <li><a href="{{ route('dashboard') }}">Clear Cache</a></li>
                                <li><a href="{{ route('dashboard') }}">Storage</a></li>
                                <li><a href="{{ route('dashboard') }}">Cronjob</a></li>
                                <li><a href="{{ route('dashboard') }}">Ban IP Address</a></li>
                                <li><a href="{{ route('dashboard') }}">System Backup</a></li>
                                <li><a href="{{ route('dashboard') }}">Database Backup</a></li>
                                <li><a href="{{ route('dashboard') }}">System Update</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>Pages</span></li>
                <li>
                    <ul>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-lock-square-rounded"></i><span>Authentication</span><span
                                    class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('login') }}">Login</a></li>
                                <li><a href="{{ route('register') }}">Register</a></li>
                                <li><a href="{{ route('dashboard') }}">Forgot Password</a></li>
                                <li><a href="{{ route('dashboard') }}">Reset Password</a></li>
                                <li><a href="{{ route('dashboard') }}">Email Verification</a></li>
                                <li><a href="{{ route('dashboard') }}">2 Step Verification</a></li>
                                <li><a href="{{ route('dashboard') }}">Lock Screen</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-error-404"></i><span>Error Pages</span><span
                                    class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">404 Error</a></li>
                                <li><a href="{{ route('dashboard') }}">500 Error</a></li>
                            </ul>
                        </li>
                        <li><a href="{{ route('dashboard') }}"><i class="ti ti-file"></i><span>Blank Page</span></a>
                        </li>
                        <li><a href="{{ route('dashboard') }}"><i
                                    class="ti ti-inner-shadow-top-right"></i><span>Coming
                                    Soon</span></a></li>
                        <li><a href="{{ route('dashboard') }}"><i class="ti ti-info-triangle"></i><span>Under
                                    Maintenance</span></a></li>
                    </ul>
                </li>
                <li class="menu-title"><span>UI Interface</span></li>
                <li>
                    <ul>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-hierarchy"></i><span>Base UI</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Accordion</a></li>
                                <li><a href="{{ route('dashboard') }}">Alerts</a></li>
                                <li><a href="{{ route('dashboard') }}">Avatar</a></li>
                                <li><a href="{{ route('dashboard') }}">Badges</a></li>
                                <li><a href="{{ route('dashboard') }}">Breadcrumb</a></li>
                                <li><a href="{{ route('dashboard') }}">Buttons</a></li>
                                <li><a href="{{ route('dashboard') }}">Button Group</a></li>
                                <li><a href="{{ route('dashboard') }}">Card</a></li>
                                <li><a href="{{ route('dashboard') }}">Carousel</a></li>
                                <li><a href="{{ route('dashboard') }}">Collapse</a></li>
                                <li><a href="{{ route('dashboard') }}">Dropdowns</a></li>
                                <li><a href="{{ route('dashboard') }}">Ratio</a></li>
                                <li><a href="{{ route('dashboard') }}">Grid</a></li>
                                <li><a href="{{ route('dashboard') }}">Images</a></li>
                                <li><a href="{{ route('dashboard') }}">Links</a></li>
                                <li><a href="{{ route('dashboard') }}">List Group</a></li>
                                <li><a href="{{ route('dashboard') }}">Modals</a></li>
                                <li><a href="{{ route('dashboard') }}">Offcanvas</a></li>
                                <li><a href="{{ route('dashboard') }}">Pagination</a></li>
                                <li><a href="{{ route('dashboard') }}">Placeholders</a></li>
                                <li><a href="{{ route('dashboard') }}">Popovers</a></li>
                                <li><a href="{{ route('dashboard') }}">Progress</a></li>
                                <li><a href="{{ route('dashboard') }}">Scrollspy</a></li>
                                <li><a href="{{ route('dashboard') }}">Spinner</a></li>
                                <li><a href="{{ route('dashboard') }}">Tabs</a></li>
                                <li><a href="{{ route('dashboard') }}">Toasts</a></li>
                                <li><a href="{{ route('dashboard') }}">Tooltips</a></li>
                                <li><a href="{{ route('dashboard') }}">Typography</a></li>
                                <li><a href="{{ route('dashboard') }}">Utilities</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-whirl"></i><span>Advanced UI</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Dragula</a></li>
                                <li><a href="{{ route('dashboard') }}">Clipboard</a></li>
                                <li><a href="{{ route('dashboard') }}">Range Slider</a></li>
                                <li><a href="{{ route('dashboard') }}">Sweet Alerts</a></li>
                                <li><a href="{{ route('dashboard') }}">Lightbox</a></li>
                                <li><a href="{{ route('dashboard') }}">Rating</a></li>
                                <li><a href="{{ route('dashboard') }}">Scrollbar</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-forms"></i><span>Forms</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li class="submenu submenu-two">
                                    <a href="javascript:void(0);">Form Elements<span
                                            class="menu-arrow inside-submenu"></span></a>
                                    <ul>
                                        <li><a href="{{ route('dashboard') }}">Basic Inputs</a></li>
                                        <li><a href="{{ route('dashboard') }}">Checkbox & Radios</a></li>
                                        <li><a href="{{ route('dashboard') }}">Input Groups</a></li>
                                        <li><a href="{{ route('dashboard') }}">Grid & Gutters</a></li>
                                        <li><a href="{{ route('dashboard') }}">Input Masks</a></li>
                                        <li><a href="{{ route('dashboard') }}">File Uploads</a></li>
                                    </ul>
                                </li>
                                <li class="submenu submenu-two">
                                    <a href="javascript:void(0);">Layouts<span
                                            class="menu-arrow inside-submenu"></span></a>
                                    <ul>
                                        <li><a href="{{ route('dashboard') }}">Horizontal Form</a></li>
                                        <li><a href="{{ route('dashboard') }}">Vertical Form</a></li>
                                        <li><a href="{{ route('dashboard') }}">Floating Labels</a></li>
                                    </ul>
                                </li>
                                <li><a href="{{ route('dashboard') }}">Form Validation</a></li>
                                <li><a href="{{ route('dashboard') }}">Form Select</a></li>
                                <li><a href="{{ route('dashboard') }}">Form Wizard</a></li>
                                <li><a href="{{ route('dashboard') }}">Form Picker</a></li>
                                <li><a href="{{ route('dashboard') }}">Form Editors</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-table"></i><span>Tables</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Basic Tables </a></li>
                                <li><a href="{{ route('dashboard') }}">Data Table </a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-chart-pie-3"></i><span>Charts</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Apex Charts</a></li>
                                <li><a href="{{ route('dashboard') }}">Chart C3</a></li>
                                <li><a href="{{ route('dashboard') }}">Chart Js</a></li>
                                <li><a href="{{ route('dashboard') }}">Morris Charts</a></li>
                                <li><a href="{{ route('dashboard') }}">Flot Charts</a></li>
                                <li><a href="{{ route('dashboard') }}">Peity Charts</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);">
                                <i class="ti ti-icons"></i><span>Icons</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('dashboard') }}">Fontawesome Icons</a></li>
                                <li><a href="{{ route('dashboard') }}">Tabler Icons</a></li>
                                <li><a href="{{ route('dashboard') }}">Bootstrap Icons</a></li>
                                <li><a href="{{ route('dashboard') }}">Remix Icons</a></li>
                                <li><a href="{{ route('dashboard') }}">Feather Icons</a></li>
                                <li><a href="{{ route('dashboard') }}">Ionic Icons</a></li>
                                <li><a href="{{ route('dashboard') }}">Material Icons</a></li>
                                <li><a href="{{ route('dashboard') }}">Pe7 Icons</a></li>
                                <li><a href="{{ route('dashboard') }}">Simpleline Icons</a></li>
                                <li><a href="{{ route('dashboard') }}">Themify Icons</a></li>
                                <li><a href="{{ route('dashboard') }}">Weather Icons</a></li>
                                <li><a href="{{ route('dashboard') }}">Typicon Icons</a></li>
                                <li><a href="{{ route('dashboard') }}">Flag Icons</a></li>
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-map-star"></i><span>Maps</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a href="{{ route('dashboard') }}">Vector</a>
                                </li>
                                <li>
                                    <a href="{{ route('dashboard') }}">Leaflet</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>Help</span></li>
                <li>
                    <ul>
                        <li><a href="javascript:void(0);"><i
                                    class="ti ti-file-stack"></i><span>Documentation</span></a>
                        </li>
                        <li><a href="javascript:void(0);"><i class="ti ti-arrow-capsule"></i><span>Changelog
                                    v2.2.7</span></a></li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-menu-deep"></i><span>Multi
                                    Level</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="javascript:void(0);">Level 1.1</a></li>
                                <li class="submenu submenu-two"><a href="javascript:void(0);">Level 1.2<span
                                            class="menu-arrow inside-submenu"></span></a>
                                    <ul>
                                        <li><a href="javascript:void(0);">Level 2.1</a></li>
                                        <li class="submenu submenu-two submenu-three"><a
                                                href="javascript:void(0);">Level 2.2<span
                                                    class="menu-arrow inside-submenu inside-submenu-two"></span></a>
                                            <ul>
                                                <li><a href="javascript:void(0);">Level 3.1</a></li>
                                                <li><a href="javascript:void(0);">Level 3.2</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li> --}}
            </ul>
        </div>
    </div>

</div>
