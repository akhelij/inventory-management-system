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

    <style>
        @import url('https://rsms.me/inter/inter.css');

        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }

        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }

        .form-control:focus {
            box-shadow: none;
        }
    </style>

    {{-- - Page Styles - --}}
    @stack('page-styles')
    @livewireStyles
</head>

<body>
<script src="{{ asset('dist/js/demo-theme.min.js') }}"></script>

<div class="page">
    <header class="navbar navbar-expand-md d-print-none">
        <div class="container-xl">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
                    aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('static/logo.svg') }}" width="110" height="32" alt="Tabler"
                         class="navbar-brand-image">
                    <span>ALAMI GESTION</span>
                </a>
            </h1>
            <div class="navbar-nav flex-row order-md-last">
                <div class="d-none d-md-flex">
                    <a href="?theme=dark" class="nav-link px-0 hide-theme-dark" title="Enable dark mode"
                       data-bs-toggle="tooltip"
                       data-bs-placement="bottom">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                             stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                             stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"/>
                        </svg>
                    </a>
                    <a href="?theme=light" class="nav-link px-0 hide-theme-light" title="Enable light mode"
                       data-bs-toggle="tooltip"
                       data-bs-placement="bottom">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                             stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                             stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/>
                            <path
                                    d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7"/>
                        </svg>
                    </a>

                    <div class="nav-item dropdown d-none d-md-flex me-3">
                        <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1"
                           aria-label="Show notifications">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
                                 viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802"/>
                            </svg>

                            @if (auth()->user()->unreadNotifications->count() !== 0)
                                <span class="badge bg-red"></span>
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
                            @if(app()->getLocale() == 'en')
                                <a class="dropdown-item" href="{{ route('lang.switch', 'fr') }}">
                                    Francais
                                </a>
                            @else
                                <a class="dropdown-item" href="{{ route('lang.switch', 'en') }}">
                                    English
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown"
                       aria-label="Open user menu">
                            <span class="avatar avatar-sm shadow-none"
                                  style="background-image: url({{ Avatar::create(Auth::user()->name)->toBase64() }})">
                            </span>

                        <div class="d-none d-xl-block ps-2">
                            <div>{{ Auth::user()->name }}</div>
                            {{--                                    <div class="mt-1 small text-muted">UI Designer</div> --}}
                        </div>
                    </a>
                    <div class="dropdown-menu">
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="icon dropdown-item-icon icon-tabler icon-tabler-settings" width="24"
                                 height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                 fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path
                                        d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z">
                                </path>
                                <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path>
                            </svg>
                            {{ __('Account') }}
                        </a>
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="icon dropdown-item-icon icon-tabler icon-tabler-logout" width="24"
                                     height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                     fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path
                                            d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/>
                                    <path d="M9 12h12l-3 -3"/>
                                    <path d="M18 15l3 -3"/>
                                </svg>
                                {{ __('Logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <header class="navbar-expand-md">
        <div class="collapse navbar-collapse" id="navbar-menu">
            <div class="navbar">
                <div class="container-xl">
                    <ul class="navbar-nav">
                        <li class="nav-item {{ request()->is('dashboard*') ? 'active' : null }}">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                    <span
                                            class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/home -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                             height="24" viewBox="0 0 24 24" stroke-width="2"
                                             stroke="currentColor" fill="none" stroke-linecap="round"
                                             stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M5 12l-2 0l9 -9l9 9l-2 0"/>
                                            <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/>
                                            <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/>
                                        </svg>
                                    </span>
                                <span class="nav-link-title">
                                        {{ __('Dashboard') }}
                                    </span>
                            </a>
                        </li>
                        @can(PermissionEnum::READ_ORDERS)
                        <li class="nav-item dropdown {{ request()->is('orders*') ? 'active' : null }}">
                            <a class="nav-link" href="{{ route('orders.index') }}"
                               data-bs-auto-close="outside" role="button" aria-expanded="false">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="icon icon-tabler icon-tabler-package-export" width="24"
                                             height="24" viewBox="0 0 24 24" stroke-width="2"
                                             stroke="currentColor" fill="none" stroke-linecap="round"
                                             stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M12 21l-8 -4.5v-9l8 -4.5l8 4.5v4.5"/>
                                            <path d="M12 12l8 -4.5"/>
                                            <path d="M12 12v9"/>
                                            <path d="M12 12l-8 -4.5"/>
                                            <path d="M15 18h7"/>
                                            <path d="M19 15l3 3l-3 3"/>
                                        </svg>
                                    </span>
                                <span class="nav-link-title">
                                    {{ __('Orders') }}
                                </span>
                            </a>
                        </li>
                        @endcan
                        @can(PermissionEnum::READ_PRODUCTS)
                        <li class="nav-item {{ request()->is('products*') ? 'active' : null }}">
                            <a class="nav-link" href="{{ route('products.index') }}">
                                    <span
                                            class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/home -->
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="icon icon-tabler icon-tabler-packages" width="24"
                                             height="24" viewBox="0 0 24 24" stroke-width="2"
                                             stroke="currentColor" fill="none" stroke-linecap="round"
                                             stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M7 16.5l-5 -3l5 -3l5 3v5.5l-5 3z"/>
                                            <path d="M2 13.5v5.5l5 3"/>
                                            <path d="M7 16.545l5 -3.03"/>
                                            <path d="M17 16.5l-5 -3l5 -3l5 3v5.5l-5 3z"/>
                                            <path d="M12 19l5 3"/>
                                            <path d="M17 16.5l5 -3"/>
                                            <path d="M12 13.5v-5.5l-5 -3l5 -3l5 3v5.5"/>
                                            <path d="M7 5.03v5.455"/>
                                            <path d="M12 8l5 -3"/>
                                        </svg>
                                    </span>
                                <span class="nav-link-title">
                                        {{ __('Products') }}
                                    </span>
                            </a>
                        </li>
                        @endcan
                        @can(PermissionEnum::READ_CUSTOMERS)
                            <li class="nav-item {{ request()->is('customers*') ? 'active' : null }}">
                                <a class="nav-link" href="{{ route('customers.index') }}">
                                    <span
                                        class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/home -->

                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                             stroke-width="1.5" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                        </svg>

                                    </span>
                                    <span class="nav-link-title">
                                        {{ __('Customers') }}
                                    </span>
                                </a>
                            </li>
                        @endcan
{{--                        @can(PermissionEnum::READ_CATEGORIES)--}}
{{--                        <li class="nav-item {{ request()->is('categories*') ? 'active' : null }}">--}}
{{--                            <a class="nav-link" href="{{ route('categories.index') }}">--}}
{{--                                    <span--}}
{{--                                            class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/home -->--}}

{{--                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"--}}
{{--                                             stroke-width="1.5" stroke="currentColor">--}}
{{--                                          <path stroke-linecap="round" stroke-linejoin="round"--}}
{{--                                                d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/>--}}
{{--                                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>--}}
{{--                                        </svg>--}}
{{--                                    </span>--}}
{{--                                <span class="nav-link-title">--}}
{{--                                        {{ __('Categories') }}--}}
{{--                                    </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}

                            <li class="nav-item {{ request()->is('warehouses*') ? 'active' : null }}">
                                <a class="nav-link" href="{{ route('warehouses.index') }}">
                                    <span
                                        class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/home -->

                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                                        </svg>

                                    </span>
                                    <span class="nav-link-title">
                                        {{ __('Warehouses') }}
                                    </span>
                                </a>
                            </li>
{{--                        @endcan--}}
                        @can(PermissionEnum::READ_REPAIRS)
                            <li class="nav-item {{ request()->is('repair-tickets*') ? 'active' : null }}">
                                <a class="nav-link" href="{{ route('repair-tickets.index') }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="icon icon-tabler"
                                             width="24"
                                             height="24"
                                             viewBox="0 0 24 24"
                                             stroke-width="2"
                                             stroke="currentColor"
                                             fill="none"
                                             stroke-linecap="round"
                                             stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M7 10h3v-3l-3.5 -3.5a6 6 0 0 1 8 8l6 6a2 2 0 0 1 -3 3l-6 -6a6 6 0 0 1 -8 -8l3.5 3.5" />
                                        </svg>
                                    </span>
                                                                <span class="nav-link-title">
                                        {{ __('Repair Tickets') }}
                                    </span>
                                </a>
                            </li>
                        @endcan
                        <li class="nav-item dropdown {{ request()->is('activity-logs', 'users*', 'units*', 'roles*') ? 'active' : null }}">
                            <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown"
                               data-bs-auto-close="outside" role="button" aria-expanded="false">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="icon icon-tabler icon-tabler-settings" width="24"
                                             height="24" viewBox="0 0 24 24" stroke-width="2"
                                             stroke="currentColor" fill="none" stroke-linecap="round"
                                             stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path
                                                    d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/>
                                            <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/>
                                        </svg>
                                    </span>
                                <span class="nav-link-title">
                                        {{ __('Settings') }}
                                    </span>
                            </a>
                            <div class="dropdown-menu">
                                <div class="dropdown-menu-columns">
                                    <div class="dropdown-menu-column">
                                        @can(PermissionEnum::READ_USERS)
                                            <a class="dropdown-item" href="{{ route('users.index') }}">
                                                {{ __('Users') }}
                                            </a>
                                        @endcan
                                        @can(PermissionEnum::READ_ROLES_PERMISSIONS)
                                            <a class="dropdown-item" href="{{ route('roles.index') }}">
                                                {{ __('Roles & Permissions') }}
                                            </a>
                                        @endcan
                                        @can(PermissionEnum::ACTIVITY_LOGS)
                                            <a class="dropdown-item" href="{{ route('activity-logs') }}">
                                                {{ __('Activity logs') }}
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <div class="my-2 my-md-0 flex-grow-1 flex-md-grow-0 order-first order-md-last">
                        <form action="./" method="get" autocomplete="off" novalidate>
                            <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <!-- Download SVG icon from http://tabler-icons.io/i/search -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                             height="24" viewBox="0 0 24 24" stroke-width="2"
                                             stroke="currentColor" fill="none" stroke-linecap="round"
                                             stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/>
                                            <path d="M21 21l-6 -6"/>
                                        </svg>
                                    </span>
                                <input type="text" name="search" id="search" value=""
                                       class="form-control" placeholder="{{ __('Search') }}..."
                                       aria-label="{{ __('Search in website') }}">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">{{ config('app.name') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body"></div>
            </div>
        </div>
        <x-notification />
        <div>
            @yield('content')
        </div>

    </div>
</div>

<!-- Libs JS -->
@stack('page-libraries')
<!-- Tabler Core -->
<script src="{{ asset('dist/js/tabler.min.js') }}" defer></script>
<script src="{{ asset('dist/js/demo.min.js') }}" defer></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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

@push('page-scripts')
    <script>
        // Listen for notify events
        Livewire.on('notify', params => {
            // You can use any notification library here
            // Example with Tabler's built-in notifications:
            var color = params.type === 'success' ? 'success' : 'danger';
            var message = params.message;

            // Show notification
            var notify = document.querySelector('.toast');
            if (notify) {
                notify.querySelector('.toast-body').textContent = message;
                notify.classList.add('bg-' + color);
                var toast = new bootstrap.Toast(notify);
                toast.show();
            }
        });
    </script>
@endpush
@livewireScripts
</body>

</html>
