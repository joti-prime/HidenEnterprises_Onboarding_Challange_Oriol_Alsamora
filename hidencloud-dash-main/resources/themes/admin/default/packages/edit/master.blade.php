@extends(AdminTheme::wrapper(), ['title' => $title ?? 'Package', 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}"/>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">
@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('admin.edit_package') }}</h4>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-4">
                        <li class="nav-item">
                            <a href="{{ route('packages.edit', $package->id) }}" class="nav-link nav-link-tab @if($tab == 'index') active @endif">{{ __('admin.package') }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('packages.features', $package->id) }}" class="nav-link nav-link-tab @if($tab == 'features') active @endif">{{ __('admin.features') }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('packages.prices', $package->id) }}" class="nav-link nav-link-tab @if($tab == 'prices') active @endif">{{ __('admin.prices') }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('packages.service', $package->id) }}" class="nav-link nav-link-tab @if($tab == 'service') active @endif">{{ __('admin.service') }} ({{ ucfirst($package->service) }})</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('packages.config-options', $package->id) }}" class="nav-link nav-link-tab @if($tab == 'config_options') active @endif">Configurable Options (BETA)</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('packages.emails', $package->id) }}" class="nav-link nav-link-tab @if($tab == 'emails') active @endif">
                                {{ __('admin.emails') }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('packages.webhooks', $package->id) }}" class="nav-link nav-link-tab @if($tab == 'webhooks') active @endif">
                                {{ __('admin.webhooks') }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('packages.links', $package->id) }}" class="nav-link nav-link-tab @if($tab == 'links') active @endif">{{ __('admin.links') }}</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        @yield('content')
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        span.select2.select2-container.select2-container--default {
            width: 100% !important;
        }
    </style>
@endsection
