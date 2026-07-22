<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="HCTestDash Billing System">
    <meta name="keywords"
          content="HCTestDash Panel, Billing Panel, @isset($keywords){{ $keywords }}@endisset">
    <meta name="author" content="HCTestDash">
    <title>{!! __('admin.admin') !!} | @isset($title)
            {{ $title }}
        @endisset - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="@settings('favicon', '/assets/core/img/favicon.png')">

    <!-- General CSS Files -->
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/bootstrap/css/bootstrap.min.css')) }}"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />


    <!-- CSS Libraries -->
    @yield('css_libraries')
    <style>

        .sidebar-dropdown::before {
            content: "→";
            display: inline-block;
            left: -14px;
            position: relative;
        }

        .active-nav {
            color: #4f46e5 !important;
            font-weight: 600 !important;
        }

    </style>

    <!-- Template CSS -->
    @if(Cache::get('admin_theme_mode_'.auth()->user()->id, 'light') == 'dark')
        <link rel="stylesheet" href="{{ asset(AdminTheme::assets('css/dark-style.css')) }}"/>
    @else
        <link rel="stylesheet" href="{{ asset(AdminTheme::assets('css/style.css')) }}"/>
    @endif

    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('css/custom.css')) }}"/>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('css/components.css')) }}"/>

</head>

<body>
<div id="app">
    <div class="main-wrapper main-wrapper-1">
        <div class="navbar-bg primary-bg"></div>
        <nav class="navbar navbar-expand-lg main-navbar">
            <form class="form-inline mr-auto">
                <ul class="navbar-nav mr-3">
                    <li>
                        <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i
                                class="fas fa-bars"></i></a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard') }}" target="_blank" class="nav-link nav-link-lg"><i
                                class="fas fa-home"></i></a>
                    </li>
                    <li>
                        <a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i
                                class="fas fa-search"></i></a>
                    </li>
                </ul>
            </form>
            @if (auth()->check())
                <ul class="navbar-nav navbar-right">
                    @foreach (Module::allEnabled() as $module)
                        @includeIf(AdminTheme::moduleView($module->getLowerName(), 'elements.navbar-dropdown-right'))
                    @endforeach
                    <li>
                        <a href="{{ route('admin.toggle-mode') }}" class="nav-link nav-link-lg"><i
                                class="fas fa-adjust"></i></a>
                    </li>
                    <li class="dropdown dropdown-list-toggle">
                        <a href="#" data-toggle="dropdown" class="nav-link nav-link-lg message-toggle">
                            <i class="far fa-envelope"></i></a>
                        <div class="dropdown-menu dropdown-list dropdown-menu-right">
                            <div class="dropdown-header">
                                {!!  __('admin.email_history', ['default' => 'Email History']) !!}
                            </div>
                            <div class="dropdown-list-content dropdown-list-message">

                                @foreach (EmailHistory::where('user_id', Auth::user()->id)->latest()->paginate(10) as $email)
                                    <a href="#" class="dropdown-item dropdown-item-unread">
                                        <div class="dropdown-item-avatar">
                                            <img alt="image"
                                                 src="https://upload.wikimedia.org/wikipedia/commons/5/5f/Gravatar-default-logo.jpg"
                                                 class="rounded-circle">
                                            <div class="is-online"></div>
                                        </div>
                                        <div class="dropdown-item-desc">
                                            <b>{{ $email->receiver }}</b>
                                            <p>{{ $email->subject }}</p>
                                            <div class="time">{{ $email->created_at->diffForHumans() }}</div>
                                        </div>
                                    </a>
                                @endforeach

                            </div>
                            <div class="dropdown-footer text-center">
                                <a href="{{ route('email.history') }}">
                                    {!!  __('admin.view_all', ['default' => 'View All']) !!}
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </li>

                    <li class="dropdown dropdown-list-toggle">
                        <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg
                            @if (Notification::where('user_id', Auth::user()->id)->where('read_at', '=', null)->exists()) beep @endif">
                            <i class="far fa-bell"></i>
                        </a>
                        <div class="dropdown-menu dropdown-list dropdown-menu-right">
                            <div class="dropdown-header">
                                {!!  __('admin.notifications', ['default' => 'Notifications']) !!}
                                <div class="float-right">
                                    <a href="{{ route('notifications.mark-as-read') }}">
                                        {!!  __('admin.mark_as_read', ['default' => 'Mark All As Read']) !!}
                                    </a>
                                </div>
                            </div>
                            <div class="dropdown-list-content dropdown-list-icons">

                                @foreach (Notification::where('user_id', Auth::user()->id)->latest()->paginate(10) as $notificaiton)
                                    <a href="#" class="dropdown-item dropdown-item-unread">
                                        <div class="dropdown-item-desc">
                                            {{ $notificaiton->message }}
                                            <div
                                                class="time text-primary">{{ $notificaiton->created_at->diffForHumans() }}</div>
                                        </div>
                                    </a>
                                @endforeach

                            </div>
                        </div>
                    </li>

                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <img alt="image" src="{{ Auth::user()->avatar() }}" class="rounded-circle mr-1">
                            <div class="d-sm-none d-lg-inline-block">{{ Auth::user()->first_name }}
                                {{ Auth::user()->last_name }}
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            @if (Auth::user()->hasPerm('admin.view'))
                                <a href="{{ route('admin.view') }}" class="dropdown-item has-icon">
                                    <i class="fas fa-solid fa-toolbox"></i> {!! __('admin.admin_panel') !!}
                                </a>
                            @endif
                            <a href="{{ route('user.settings') }}" class="dropdown-item has-icon">
                                <i class="fas fa-cog"></i> {!!  __('admin.settings', ['default' => 'Settings']) !!}
                            </a>
                            @foreach (Module::allEnabled() as $module)
                                @if(config($module->getLowerName() . '.elements.user_dropdown'))
                                    @foreach (config($module->getLowerName() . '.elements.user_dropdown') as $key => $menu)
                                        <a href="{{ $menu['href'] }}" class="dropdown-item has-icon"
                                           style="{{ $menu['style'] }}">
                                            {!! $menu['icon'] !!} {!! __($menu['name']) !!}
                                        </a>
                                    @endforeach
                                @endif
                            @endforeach
                            <div class="dropdown-divider"></div>
                            <a href="/auth/logout" class="dropdown-item has-icon text-danger">
                                <i class="fas fa-sign-out-alt"></i> {!!  __('admin.logout', ['default' => 'Logout']) !!}
                            </a>
                        </div>
                    </li>
                </ul>
            @endif
        </nav>

        <div class="main-sidebar sidebar-style-2">
            <aside id="sidebar-wrapper">
                <div class="sidebar-brand">
                    <a href="{{ route('admin.view') }}">{!! __('admin.admin_panel') !!}</a>
                </div>
                <div class="sidebar-brand sidebar-brand-sm">
                    <a href="{{ route('admin.view') }}">{!!  __('admin.panel', ['default' => 'PANEL']) !!}</a>
                </div>
                <ul class="sidebar-menu">
                    <li class="menu-header">{!!  __('admin.dashboard', ['default' => 'Dashboard']) !!}</li>
                    <li>
                        <a class="nav-link {{ nav_active('admin.view') }}" href="{{ route('admin.view') }}"><i
                                class="fas fa-fire"></i>
                            <span>{!! __('admin.overview') !!}</span></a>
                    </li>

                    <li class="menu-header">{!!  __('admin.client_management', ['default' => 'Client Management']) !!}</li>
                    <li class="dropdown {{ nav_active(['users.index', 'groups.index']) }}">
                        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                                class="fas fa-user"></i> <span>{!! __('admin.customers') !!}</span></a>
                        <ul class="dropdown-menu">
                            <li><a class="nav-link {{ nav_active('users.index') }}"
                                   href="{{ route('users.index') }}">{!! __('admin.clients') !!}</a>
                            </li>
                            <li><a class="nav-link {{ nav_active('groups.index') }}"
                                   href="{{ route('groups.index') }}">{!! __('admin.groups') !!}</a>
                            </li>
                        </ul>
                    </li>

                    <li class="dropdown {{ nav_active(['admin.bans.index', 'admin.warnings.index']) }}">
                        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                                class="fas fa-gavel"></i> <span>{!! __('admin.moderation') !!}</span></a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="nav-link {{ nav_active('admin.bans.index') }}"
                                   href="{{ route('admin.bans.index') }}">{{ __('admin.bans') }}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('admin.warnings.index') }}"
                                   href="{{ route('admin.warnings.index') }}">{{ __('admin.warnings') }}</a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a class="nav-link {{ nav_active('payments.index') }}"
                           href="{{ route('payments.index', ['status' => 'paid']) }}"><i
                                class="fas fa-solid fa-coins"></i>
                            <span>{!!  __('admin.payments', ['default' => 'Payments']) !!}</span></a>
                    </li>

                    <li>
                        <a class="nav-link {{ nav_active('payments.subscriptions') }}"
                           href="{{ route('payments.subscriptions', ['status' => 'paid']) }}">
                            <i class="fas fa-hand-holding-usd"></i>
                            <span>{!!  __('client.subscription') !!}</span></a>
                    </li>

                    <li>
                        <a class="nav-link {{ nav_active('orders.index') }}"
                           href="{{ route('orders.index', ['status' => 'active']) }}"><i
                                class="fas fa-solid fa-server"></i>
                            <span>{!!  __('admin.orders', ['default' => 'Orders']) !!}</span></a>
                    </li>

                    <li class="menu-header">{!!  __('admin.settings', ['default' => 'Settings']) !!}</li>
                    <li class="dropdown {{ nav_active('admin/settings', prefix: true) }} {{ nav_active('admin/logs', prefix: true) }}">
                        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                                class="fas fa-cog"></i>
                            <span>{!!  __('admin.configuration', ['default' => 'Configuration']) !!}</span></a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="nav-link {{ nav_active('admin.settings') }}"
                                   href="{{ route('admin.settings') }}">{!!  __('admin.settings', ['default' => 'Settings']) !!}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('admin.config') }}"
                                   href="{{ route('admin.config') }}">{!!  __('admin.config', ['default' => 'Config']) !!}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('admin.seo') }}"
                                   href="{{ route('admin.seo') }}">{!!  __('admin.seo', ['default' => 'SEO']) !!}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('admin.taxes') }}"
                                   href="{{ route('admin.taxes') }}">{{ __('admin.taxes') }}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('admin.registrations') }}"
                                   href="{{ route('admin.registrations') }}">{!!  __('admin.registrations', ['default' => 'Registrations']) !!}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('admin.oauth') }}"
                                   href="{{ route('admin.oauth') }}">{!!  __('admin.oauth', ['default' => 'Oauth']) !!}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('admin.captcha') }}"
                                   href="{{ route('admin.captcha') }}">{!!  __('admin.captcha', ['default' => 'Captcha']) !!}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('admin.maintenance') }}"
                                   href="{{ route('admin.maintenance') }}">{!!  __('admin.maintenance', ['default' => 'Maintenance']) !!}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('admin.settings.theme') }}"
                                   href="{{ route('admin.settings.theme') }}">{!!  __('admin.theme_settings', ['default' => 'Theme Settings']) !!}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('admin.settings.portal') }}"
                                   href="{{ route('admin.settings.portal') }}">{!!  __('admin.portals', ['default' => 'Portals']) !!}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('logs.index') }}"
                                   href="{{ route('logs.index') }}">{!!  __('admin.logs', ['default' => 'Logs']) !!}</a>
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown {{ nav_active('admin/emails', prefix: true) }}">
                        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                                class="fas fa-solid fa-envelope"></i>
                            <span>{!!  __('admin.emails', ['default' => 'Emails']) !!}</span></a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="nav-link {{ nav_active('emails.history') }}"
                                   href="{{ route('emails.history') }}">{!!  __('admin.history', ['default' => 'History']) !!}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('emails.configure') }}"
                                   href="{{ route('emails.configure') }}">{!!  __('admin.configure', ['default' => 'Configure']) !!}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('emails.messages') }}"
                                   href="{{ route('emails.messages') }}">{!!  __('admin.messages', ['default' => 'Messages']) !!}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('emails.templates') }}"
                                   href="{{ route('emails.templates') }}">{!!  __('admin.templates', ['default' => 'Templates']) !!}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ nav_active('emails.mass-mailer') }}"
                                   href="{{ route('emails.mass-mailer') }}">Mass Mailer</a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a class="nav-link {{ nav_active('pages.index') }}" href="{{ route('pages.index') }}"><i
                                class="fas fa-solid fa-file"></i>
                            <span>{!!  __('admin.pages', ['default' => 'Pages']) !!}</span></a>
                    </li>

                    <li>
                        <a class="nav-link {{ nav_active('articles.index') }}" href="{{ route('articles.index') }}"><i
                                class="fas fa-solid fa-newspaper"></i>
                            <span>{{ __('admin.articles') }}</span></a>
                    </li>

                    <li>
                        <a class="nav-link {{ nav_active('updates.index') }}"
                           href="{{ route('updates.index') }}"><i class="fas fa-cloud-download-alt"></i>
                            <span>{{ __('admin.updates') }}</span></a>
                    </li>

                    <li class="menu-header">{!!  __('admin.store', ['default' => 'Store']) !!}</li>
                    <li class="dropdown {{ nav_active(['categories.index', 'packages.index', 'coupons.index']) }}">
                        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                                class="fas fa-solid fa-box"></i>
                            <span>{!!  __('admin.products_and_services', ['default' => 'Products & Services']) !!}</span></a>
                        <ul class="dropdown-menu">
                            <li><a class="nav-link {{ nav_active('categories.index') }}"
                                   href="{{ route('categories.index') }}">{!! __('admin.categories') !!}</a></li>
                            <li><a class="nav-link {{ nav_active('packages.index') }}"
                                   href="{{ route('packages.index') }}">{!!  __('admin.packages', ['default' => 'Packages']) !!}</a>
                            <li><a class="nav-link {{ nav_active('coupons.index') }}"
                                   href="{{ route('coupons.index') }}">{!!  __('admin.coupons', ['default' => 'Coupons']) !!}</a>
                            </li>

                        </ul>
                    </li>


                    <li>
                        <a class="nav-link {{ nav_active('gateways.index') }}" href="{{ route('gateways.index') }}"><i
                                class="fas fa-solid fa-credit-card"></i>
                            <span>{!! __('admin.gateways') !!}</span></a>
                    </li>

                    <li class="menu-header">
                        {!!  __('admin.design_and_compatibility', ['default' => 'Design & Compatibility']) !!}</li>

{{--                    <li>--}}
{{--                        <a class="nav-link {{ nav_active('admin.marketplace') }}"--}}
{{--                           href="{{ route('admin.marketplace') }}"><i class="fas fa-store"></i>--}}
{{--                            <span>{!! __('admin.marketplace') !!}</span></a>--}}
{{--                    </li>--}}

                    <li class="dropdown {{ nav_active('themes', dropdown: true) }}">
                        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                                class="fas fa-sharp fa-solid fa-palette"></i>
                            <span>{!! __('admin.themes') !!}</span></a>
                        <ul class="dropdown-menu">
                            <li><a class="nav-link {{ nav_active('admin.themes') }}"
                                   href="{{ route('admin.themes') }}">{!! __('admin.client_themes') !!}</a></li>
                            <li><a class="nav-link {{ nav_active('admin.admin_themes') }}"
                                   href="{{ route('admin.admin_themes') }}">{!! __('admin.admin_themes') !!}</a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a class="nav-link {{ nav_active('modules.view') }}" href="{{ route('modules.view') }}"><i
                                class="fas fa-solid fa-plug"></i>
                            <span>{!! __('admin.modules') !!}</span></a>
                    </li>

                    <li>
                        <a class="nav-link {{ nav_active('services.view') }}" href="{{ route('services.view') }}"><i
                                class="fas fa-solid fa-robot"></i>
                            <span>{!!  __('admin.services', ['default' => 'Services']) !!}</span></a>
                    </li>

                    <li>
                        <a class="nav-link {{ nav_active('widgets.index') }}" href="{{ route('widgets.index') }}"><i class="fa-solid fa-square-poll-horizontal"></i>
                            <span>{!!  __('admin.widgets', ['default' => 'Widgets']) !!}</span></a>
                    </li>

                    <li>
                        <a class="nav-link {{ nav_active('api-v1.index') }}" href="{{ route('api-v1.index') }}"><i
                                class="fas fa-solid fa-code"></i>
                            <span>{!!  __('admin.api_tokens', ['default' => 'API Tokens']) !!}</span></a>
                    </li>

                    <li class="menu-header">{!! __('admin.modules') !!}</li>

                    @foreach (Module::allEnabled() as $module)
                        @if(config($module->getLowerName() . '.elements.admin_menu'))
                            @foreach (config($module->getLowerName() . '.elements.admin_menu') as $key => $menu)

                                @if(isset($menu['type']) AND $menu['type'] == 'dropdown')
                                    <li class="dropdown  {{ nav_active($module->getLowerName(), dropdown: true) }}">
                                        <a href="#" class="nav-link has-dropdown"
                                           data-toggle="dropdown">{!! $menu['icon'] !!}
                                            <span>{{__($menu['name']) }}</span></a>
                                        <ul class="dropdown-menu">

                                            @foreach($menu['items'] as $item)
                                                <li>
                                                    <a class="nav-link {{ nav_active($item['href'], href: true) }}"
                                                       href="{{ $item['href'] }}">
                                                        {{ __($item['name']) }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                    @continue
                                @endif


                                <li>
                                    <a class="nav-link {{ nav_active($module->getLowerName(), true) }}"
                                       style="{{ $menu['style'] }}"
                                       href="{{ $menu['href'] }}">{!! $menu['icon'] !!}
                                        <span>{!! __($menu['name']) !!}</span>
                                    </a>
                                </li>
                            @endforeach
                        @endif
                    @endforeach
                </ul>
                <div id="nav-footer" style="min-height: 20px;"></div>
                <hr>
            </aside>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    setTimeout(function() {
                        let activeItem = document.querySelector('.sidebar-menu .active-nav');
                        if (activeItem) {
                            activeItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }, 300);
                });
            </script>
        </div>

        <!-- Main Content -->
        <div class="main-content" style="min-height: 842px;">
            {{-- alerts --}}
            @if(count($errors) > 0)
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger">
                        <a href="#" style="background: #0000003d;" class="badge badge-danger">
                            <i class="fas fa-solid fa-bell" style="margin-left: 0px"></i></a> {!! $error !!}
                    </div>
                @endforeach
            @endif

            @if (Session::has('success'))
                <div class="alert alert-success">
                    <a href="#" style="background: #0000003d;" class="badge badge-success">
                        <i class="fas fa-solid fa-bell" style="margin-left: 0px"></i></a> {!! session('success') !!}
                </div>
            @endif

            @if (Session::has('error'))
                <div class="alert alert-danger">
                    <a href="#" style="background: #0000003d;" class="badge badge-danger">
                        <i class="fas fa-solid fa-bell" style="margin-left: 0px"></i></a> {!! session('error') !!}
                </div>
            @endif

            @if (Session::has('warning'))
                <div class="alert alert-warning">
                    <a href="#" style="background: #0000003d;" class="badge badge-warning">
                        <i class="fas fa-solid fa-bell" style="margin-left: 0px"></i></a> {!! session('warning') !!}
                </div>
            @endif

            @if(Settings::get('maintenance') == 'true' && Auth::user()->is_admin())
                <div class="alert alert-warning">
                    <div class="alert-title">{!!  __('admin.maintenance', ['default' => 'Maintenance']) !!}</div>
                    {!!  __('admin.maintenance_mode_desc', ['default' => 'Maintenance mode is active, you are currently bypassing maintenance mode']) !!}
                    <a href="/admin/settings/store?maintenance=false" class="btn btn-icon icon-left btn-primary ml-2"><i
                            class="fas fa-exclamation-triangle"></i> {!!  __('admin.maintenance_disable_button', ['default' => 'Disable Maintenance Mode']) !!}
                    </a>
                </div>
            @endif

            @if(version_compare(PHP_VERSION, '8.2', '<'))
            <div class="alert alert-danger" role="alert">
                You are running an unsupported version of PHP: <code>{{ PHP_VERSION }}</code>. Please upgrade to PHP <code>8.2</code> or higher as soon as possible. <br><br>
                <a href="https://docs.wemx.net/en/project/upgrade-php-83" target="_blank" class="btn btn-primary btn-sm">Upgrade PHP Docs</a>
            </div>
            @endif

            @if(!config('laravelcloudflare.enabled') AND request()->header('cf-ipcountry'))
            <div class="alert alert-danger" role="alert">
                {!! __('admin.enable_cloudflare_proxy_integration') !!}
            </div>
            @endif

            @if(!Cache::has('cron_active'))
                <div class="alert alert-danger" role="alert">
                    {!! __('admin.cronjobs_are_not_running_add_php_artisan_scheduler', ['base_path' => base_path()]) !!}
                </div>
            @endif

            @if(!Cache::has('queue_active'))
                <div class="alert alert-danger" role="alert">
                    {!! __('admin.queue_worker_not_setup') !!}
                </div>
            @endif

            @if(config('app.debug') AND config('app.version') != 'dev')
                <div class="alert alert-warning" role="alert">
                    {!! __('admin.disable_debug_mode_immediately_if_your_application', ['base_path' => base_path('.env')]) !!}
                </div>
            @endif
            {{-- end alerts --}}

            @yield('container')
        </div>

        <footer class="main-footer">
            <div class="footer-left">
                {{ __('admin.copyright') }} &copy; {{ date('Y') }}
                <div class="bullet"></div>
                {{ __('admin.desing_by') }} <a href="">HCTestDash</a>
            </div>
            <div class="footer-right"></div>
        </footer>
    </div>
</div>


<!-- General JS Scripts -->
<script src="{{ asset(AdminTheme::assets('modules/jquery.min.js')) }}"></script>
<script src="{{ asset(AdminTheme::assets('modules/popper.js')) }}"></script>
<script src="{{ asset(AdminTheme::assets('modules/tooltip.js')) }}"></script>
<script src="{{ asset(AdminTheme::assets('modules/bootstrap/js/bootstrap.min.js')) }}"></script>
<script src="{{ asset(AdminTheme::assets('modules/nicescroll/jquery.nicescroll.min.js')) }}"></script>
<script src="{{ asset(AdminTheme::assets('modules/moment.min.js')) }}"></script>
<script src="{{ asset(AdminTheme::assets('js/stisla.js')) }}"></script>

<!-- JS Libraies -->
@yield('js_libraries')

<!-- Page Specific JS File -->
{{-- <script src="{{ asset(AdminTheme::assets('js/page/index.js')) }}"></script> --}}

<!-- Template JS File -->
<script src="{{ asset(AdminTheme::assets('js/scripts.js')) }}"></script>
<script src="{{ asset(AdminTheme::assets('js/custom.js')) }}"></script>
<script>

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    function deleteItem(event) {
        if (window.confirm('{!! __('admin.are_you_sure') !!}')) {
            // Delete item code here
        } else {
            event.preventDefault();
        }
    }

    function confirmAction(event, message) {
        if (window.confirm(message)) {
            // Delete item code here
        } else {
            event.preventDefault();
        }
    }
</script>
</body>

</html>
