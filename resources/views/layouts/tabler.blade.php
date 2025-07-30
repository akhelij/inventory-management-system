@php
    use App\Enums\PermissionEnum;
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Alami Gestion</title>

    <!-- CSS files -->
    <link href="{{ asset('dist/css/tabler.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('dist/css/tabler-flags.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('dist/css/tabler-payments.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('dist/css/tabler-vendors.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('dist/css/demo.min.css') }}" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        @import url('https://rsms.me/inter/inter.css');

        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --sidebar-active: #0ea5e9;
            --topbar-bg: #ffffff;
            --topbar-color: #334155;
        }

        [data-bs-theme="dark"] {
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #0284c7;
            --topbar-bg: #1e293b;
            --topbar-color: #f1f5f9;
        }

        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }

        .form-control:focus {
            box-shadow: none;
        }
        
        /* Modern sidebar styling */
        .navbar-vertical {
            background-color: var(--sidebar-bg);
            border-right: none;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        
        .navbar-vertical .navbar-brand {
            padding: 1rem;
            margin: 0;
            height: 65px;
        }
        
        .navbar-vertical .navbar-brand span {
            color: #ffffff;
            font-weight: 600;
            margin-left: 8px;
            letter-spacing: 0.5px;
        }
        
        .navbar-vertical.navbar-dark .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 0.375rem;
            margin: 0.25rem 0.8rem;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }
        
        .navbar-vertical.navbar-dark .navbar-nav .nav-link:hover,
        .navbar-vertical.navbar-dark .navbar-nav .nav-link:focus {
            color: #ffffff;
            background-color: var(--sidebar-hover);
        }
        
        .navbar-vertical.navbar-dark .navbar-nav .nav-item.active .nav-link {
            color: #ffffff;
            font-weight: 500;
            background-color: var(--sidebar-active);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .navbar-vertical.navbar-dark .navbar-nav .nav-link-icon {
            color: rgba(255, 255, 255, 0.8);
            margin-right: 12px;
        }
        
        .navbar-vertical.navbar-dark .navbar-nav .nav-item.active .nav-link-icon {
            color: #ffffff;
        }
        
        /* Top bar styling */
        .navbar-expand-md {
            background-color: var(--topbar-bg);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            height: 65px;
        }
        
        .page-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--topbar-color);
            margin-bottom: 0;
        }
        
        /* Dropdown styling */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-radius: 0.5rem;
        }
        
        .dropdown-item {
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
        }
        
        .dropdown-item:hover, 
        .dropdown-item:focus {
            background-color: rgba(14, 165, 233, 0.1);
            color: #0ea5e9;
        }
        
        /* Avatar styling */
        .avatar {
            border-radius: 0.5rem;
        }
        
        /* Make the sidebar menu scrollable */
        .navbar-collapse {
            max-height: calc(100vh - 65px);
            overflow-y: auto;
        }

        /* Dark mode adaptations */
        [data-bs-theme="dark"] .navbar-expand-md {
            color: #f1f5f9;
        }
        
        [data-bs-theme="dark"] .dropdown-menu {
            background-color: #1e293b;
            color: #f1f5f9;
        }
        
        [data-bs-theme="dark"] .dropdown-item {
            color: #f1f5f9;
        }
        
        [data-bs-theme="dark"] .form-control {
            background-color: #334155;
            border-color: #475569;
            color: #f1f5f9;
        }

        [data-bs-theme="dark"] .form-control::placeholder {
            color: #94a3b8;
        }
        
        [data-bs-theme="dark"] .input-icon-addon {
            color: #94a3b8;
        }
    </style>

    {{-- - Page Styles - --}}
    @stack('page-styles')
    @livewireStyles
</head>

<body>
<script src="{{ asset('dist/js/demo-theme.min.js') }}"></script>

<div class="page">
    <!-- Sidebar -->
    <aside class="navbar navbar-vertical navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <!-- Brand -->
            <h1 class="navbar-brand navbar-brand-autodark">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('static/logo.svg') }}" width="32" height="32" alt="Alami" class="navbar-brand-image">
                    <span>ALAMI GESTION</span>
                </a>
            </h1>
            
            <!-- Mobile toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Sidebar Menu -->
            <div class="collapse navbar-collapse" id="sidebar-menu">
                <ul class="navbar-nav pt-lg-3">
                    <!-- Dashboard -->
                    <li class="nav-item {{ request()->is('dashboard*') ? 'active' : null }}">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-chart-line"></i>
                            </span>
                            <span class="nav-link-title">{{ __('Dashboard') }}</span>
                        </a>
                    </li>
                    
                    @can(PermissionEnum::READ_ORDERS)
                    <!-- Orders -->
                    <li class="nav-item {{ request()->is('orders*') ? 'active' : null }}">
                        <a class="nav-link" href="{{ route('orders.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-shipping-fast"></i>
                            </span>
                            <span class="nav-link-title">{{ __('Orders') }}</span>
                        </a>
                    </li>
                    @endcan
                    
                    @can(PermissionEnum::READ_PRODUCTS)
                    <!-- Products -->
                    <li class="nav-item {{ request()->is('products*') ? 'active' : null }}">
                        <a class="nav-link" href="{{ route('products.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-boxes"></i>
                            </span>
                            <span class="nav-link-title">{{ __('Products') }}</span>
                        </a>
                    </li>
                    @endcan
                    
                    @can(PermissionEnum::READ_CUSTOMERS)
                    <!-- Customers -->
                    <li class="nav-item {{ request()->is('customers*') ? 'active' : null }}">
                        <a class="nav-link" href="{{ route('customers.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-users"></i>
                            </span>
                            <span class="nav-link-title">{{ __('Customers') }}</span>
                        </a>
                    </li>
                    @endcan
                    
                    <!-- Warehouses -->
                    <li class="nav-item {{ request()->is('warehouses*') ? 'active' : null }}">
                        <a class="nav-link" href="{{ route('warehouses.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-warehouse"></i>
                            </span>
                            <span class="nav-link-title">{{ __('Warehouses') }}</span>
                        </a>
                    </li>
                    
                    <!-- Drivers -->
                    <li class="nav-item {{ request()->is('drivers*') ? 'active' : null }}">
                        <a class="nav-link" href="{{ route('drivers.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-truck"></i>
                            </span>
                            <span class="nav-link-title">{{ __('Drivers') }}</span>
                        </a>
                    </li>
                    
                    @can(PermissionEnum::READ_REPAIRS)
                    <!-- Repair Tickets -->
                    <li class="nav-item {{ request()->is('repair-tickets*') ? 'active' : null }}">
                        <a class="nav-link" href="{{ route('repair-tickets.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-tools"></i>
                            </span>
                            <span class="nav-link-title">{{ __('Repair Tickets') }}</span>
                        </a>
                    </li>
                    @endcan
                    
                    <!-- Settings links as individual items -->
                    @can(PermissionEnum::READ_USERS)
                    <li class="nav-item {{ request()->is('users*') ? 'active' : null }}">
                        <a class="nav-link" href="{{ route('users.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-user-cog"></i>
                            </span>
                            <span class="nav-link-title">{{ __('Users') }}</span>
                        </a>
                    </li>
                    @endcan
                    
                    @can(PermissionEnum::READ_ROLES_PERMISSIONS)
                    <li class="nav-item {{ request()->is('roles*') ? 'active' : null }}">
                        <a class="nav-link" href="{{ route('roles.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-user-shield"></i>
                            </span>
                            <span class="nav-link-title">{{ __('Roles & Permissions') }}</span>
                        </a>
                    </li>
                    @endcan
                    
                    @can(PermissionEnum::ACTIVITY_LOGS)
                    <li class="nav-item {{ request()->is('activity-logs') ? 'active' : null }}">
                        <a class="nav-link" href="{{ route('activity-logs') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="fas fa-history"></i>
                            </span>
                            <span class="nav-link-title">{{ __('Activity logs') }}</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="page-wrapper">
        <!-- Top Bar -->
        <header class="navbar navbar-expand-md d-print-none">
            <div class="container-xl">
                <!-- Page Title -->
                <div class="d-flex align-items-center">
                    <h2 class="page-title">
                        @php
                            $routeName = Route::currentRouteName();
                            if ($routeName) {
                                $routeParts = explode('.', $routeName);
                                $baseRoute = $routeParts[0];
                                echo __(ucfirst(str_replace('-', ' ', $baseRoute)));
                            }
                        @endphp
                    </h2>
                </div>
                
                <!-- Search Form -->
                <div class="my-2 my-md-0 flex-grow-1 flex-md-grow-0 order-first order-md-last">
                    <form action="./" method="get" autocomplete="off" novalidate>
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search" id="search" class="form-control" placeholder="{{ __('Search') }}..." aria-label="{{ __('Search in website') }}">
                        </div>
                    </form>
                </div>
                
                <!-- Right Side Items -->
                <div class="navbar-nav flex-row order-md-last">
                    <!-- Theme Toggle -->
                    <div class="d-none d-md-flex">
                        <a href="?theme=dark" class="nav-link px-0 hide-theme-dark" title="Enable dark mode" data-bs-toggle="tooltip" data-bs-placement="bottom">
                            <i class="fas fa-moon"></i>
                        </a>
                        <a href="?theme=light" class="nav-link px-0 hide-theme-light" title="Enable light mode" data-bs-toggle="tooltip" data-bs-placement="bottom">
                            <i class="fas fa-sun"></i>
                        </a>
                        
                        <!-- Language Dropdown -->
                        <div class="nav-item dropdown d-none d-md-flex me-3">
                            <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Language options">
                                <i class="fas fa-globe"></i>
                                @if (auth()->user()->unreadNotifications->count() !== 0)
                                    <span class="badge bg-red"></span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
                                @if(app()->getLocale() == 'en')
                                    <a class="dropdown-item" href="{{ route('lang.switch', 'fr') }}">
                                        <i class="flag flag-fr me-2"></i>Francais
                                    </a>
                                @else
                                    <a class="dropdown-item" href="{{ route('lang.switch', 'en') }}">
                                        <i class="flag flag-gb me-2"></i>English
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                            <span class="avatar avatar-sm shadow-none" style="background-image: url({{ Avatar::create(Auth::user()->name)->toBase64() }})"></span>
                            <div class="d-none d-xl-block ps-2">
                                <div>{{ Auth::user()->name }}</div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i class="fas fa-user-circle me-2"></i>{{ __('Account') }}
                            </a>
                            @can('manage-progress-items')
                            <a href="{{ route('progress-items.index') }}" class="dropdown-item">
                                <i class="fas fa-file-invoice-dollar me-2"></i>{{ __('Dev Progress') }}
                            </a>
                            @endcan
                            <form action="{{ route('logout') }}" method="post">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i>{{ __('Logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Body -->
        <div class="page-body">
            <x-notification />
            <div>
                @yield('content')
            </div>
        </div>
    </div>
</div>

<!-- Libs JS -->
@stack('page-libraries')
<!-- Tabler Core -->
<script src="{{ asset('dist/js/tabler.min.js') }}" defer></script>
<script src="{{ asset('dist/js/demo.min.js') }}" defer></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<style>
    /* Add to your CSS */
    .form-select.bg-success-lt {
        background-color: rgba(74, 222, 128, 0.1);
    }
    .form-select.bg-danger-lt {
        background-color: rgba(248, 113, 113, 0.1);
    }
    .form-select.bg-warning-lt {
        background-color: rgba(250, 204, 21, 0.1);
    }
    .form-select.bg-info-lt {
        background-color: rgba(147, 197, 253, 0.1);
    }
    .form-select.bg-primary-lt {
        background-color: rgba(59, 130, 246, 0.1);
    }
</style>
{{-- - Page Scripts - --}}
@stack('page-scripts')


@livewireScripts

<!-- Toast container for notifications -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    @if (session('success'))
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">{{ __('Success') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session('success') }}
            </div>
        </div>
    @endif
    
    @if (session('error'))
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">{{ __('Error') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session('error') }}
            </div>
        </div>
    @endif
</div>

<!-- Initialize toasts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto hide toasts after 5 seconds
        setTimeout(function() {
            const toasts = document.querySelectorAll('.toast.show');
            toasts.forEach(toast => {
                const bsToast = new bootstrap.Toast(toast, {
                    autohide: true,
                    delay: 5000
                });
                // The toast is already shown, so this just sets up the autohide
            });
        }, 500);
    });
</script>

<!-- Toast Component -->
<x-toast />

<!-- JavaScript -->
<!-- Adding the script tag for our toast.js -->
<script src="{{ asset('js/toast.js') }}"></script>
</body>
</html>
