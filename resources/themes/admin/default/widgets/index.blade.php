@extends(AdminTheme::wrapper(), ['title' => __('admin.settings'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}"/>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">

@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <form action="{{ route('admin.settings.store') }}" method="POST">
                    <div class="card-header">
                        <h4>{!! __('admin.widgets', ['default' => 'Widgets']) !!}</h4>
                    </div>
                    <div class="card-body">
                        @csrf
                        <div class="row">

                            <div class="form-group col-12">
                                <div class="control-label">
                                    2FA Dashboard Widget
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('widget:dashboard:2fa', false)) /admin/settings/store?widget:dashboard:2fa=0 @else /admin/settings/store?widget:dashboard:2fa=1 @endif';">
                                    <input type="checkbox" name="widget:dashboard:2fa" value="1"
                                           class="custom-switch-input"
                                           @if(settings('widget:dashboard:2fa', false)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        Enable two factor authentication widget on the dashboard page
                                    </span>
                                </label>
                            </div>

                            <div class="form-group col-12">
                                <div class="control-label">
                                    Social Accounts Dashboard Widget
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('widget:dashboard:social_accounts', false)) /admin/settings/store?widget:dashboard:social_accounts=0 @else /admin/settings/store?widget:dashboard:social_accounts=1 @endif';">
                                    <input type="checkbox" name="widget:dashboard:social_accounts" value="1"
                                           class="custom-switch-input"
                                           @if(settings('widget:dashboard:social_accounts', false)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        Enable social accounts widget on the dashboard page
                                    </span>
                                </label>
                            </div>

                            @foreach (enabledModules() as $module)
                            @if(View::exists(Theme::moduleView($module->getLowerName(), 'widgets.dashboard-widget')))
                            <div class="form-group col-12">
                                <div class="control-label">
                                    {{ $module->getName() }} Dashboard Widget
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('widget:dashboard:'. $module->getLowerName(), false)) /admin/settings/store?widget:dashboard:{{ $module->getLowerName() }}=0 @else /admin/settings/store?widget:dashboard:{{ $module->getLowerName() }}=1 @endif';">
                                    <input type="checkbox" name="widget:dashboard:{{ $module->getLowerName() }}" value="1"
                                           class="custom-switch-input"
                                           @if(settings('widget:dashboard:'. $module->getLowerName(), false)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        Enable {{ $module->getName() }} widget on the dashboard page
                                    </span>
                                </label>
                            </div>
                            @endif
                            @endforeach

                            @foreach (enabledModules() as $module)
                            @if(View::exists(Theme::moduleView($module->getLowerName(), 'widgets.dashboard-sidebar-widget')))
                            <div class="form-group col-12">
                                <div class="control-label">
                                    {{ $module->getName() }} Dashboard Sidebar Widget
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('widget:dashboard-sidebar:'. $module->getLowerName(), false)) /admin/settings/store?widget:dashboard-sidebar:{{ $module->getLowerName() }}=0 @else /admin/settings/store?widget:dashboard-sidebar:{{ $module->getLowerName() }}=1 @endif';">
                                    <input type="checkbox" name="widget:dashboard-sidebar:{{ $module->getLowerName() }}" value="1"
                                           class="custom-switch-input"
                                           @if(settings('widget:dashboard-sidebar:'. $module->getLowerName(), false)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        Enable {{ $module->getName() }} widget on the dashboard sidebar
                                    </span>
                                </label>
                            </div>
                            @endif
                            @endforeach

                            @foreach (enabledModules() as $module)
                            @if(View::exists(Theme::moduleView($module->getLowerName(), 'widgets.dashboard-sidebar-widget')))
                            <div class="form-group col-12">
                                <div class="control-label">
                                    {{ $module->getName() }} Dashboard Sidebar Widget
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('widget:dashboard-sidebar:'. $module->getLowerName(), false)) /admin/settings/store?widget:dashboard-sidebar:{{ $module->getLowerName() }}=0 @else /admin/settings/store?widget:dashboard-sidebar:{{ $module->getLowerName() }}=1 @endif';">
                                    <input type="checkbox" name="widget:dashboard-sidebar:{{ $module->getLowerName() }}" value="1"
                                           class="custom-switch-input"
                                           @if(settings('widget:dashboard-sidebar:'. $module->getLowerName(), false)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        Enable {{ $module->getName() }} widget on the dashboard sidebar
                                    </span>
                                </label>
                            </div>
                            @endif
                            @endforeach

                            @foreach (enabledModules() as $module)
                            @if(View::exists(Theme::moduleView($module->getLowerName(), 'widgets.order-manage-top-widget')))
                            <div class="form-group col-12">
                                <div class="control-label">
                                    {{ $module->getName() }} Order Manage Top Widget
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('widget:order-manage-top:'. $module->getLowerName(), false)) /admin/settings/store?widget:order-manage-top:{{ $module->getLowerName() }}=0 @else /admin/settings/store?widget:order-manage-top:{{ $module->getLowerName() }}=1 @endif';">
                                    <input type="checkbox" name="widget:order-manage-top:{{ $module->getLowerName() }}" value="1"
                                           class="custom-switch-input"
                                           @if(settings('widget:order-manage-top:'. $module->getLowerName(), false)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        Enable {{ $module->getName() }} widget on the order manage top
                                    </span>
                                </label>
                            </div>
                            @endif
                            @endforeach

                            @foreach (enabledModules() as $module)
                            @if(View::exists(Theme::moduleView($module->getLowerName(), 'widgets.order-manage-bottom-widget')))
                            <div class="form-group col-12">
                                <div class="control-label">
                                    {{ $module->getName() }} Order Manage bottom Widget
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('widget:order-manage-bottom:'. $module->getLowerName(), false)) /admin/settings/store?widget:order-manage-bottom:{{ $module->getLowerName() }}=0 @else /admin/settings/store?widget:order-manage-bottom:{{ $module->getLowerName() }}=1 @endif';">
                                    <input type="checkbox" name="widget:order-manage-bottom:{{ $module->getLowerName() }}" value="1"
                                           class="custom-switch-input"
                                           @if(settings('widget:order-manage-bottom:'. $module->getLowerName(), false)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        Enable {{ $module->getName() }} widget on the order manage bottom
                                    </span>
                                </label>
                            </div>
                            @endif
                            @endforeach

                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">{!! __('admin.submit') !!}</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
    <style>
        span.select2.select2-container.select2-container--default {
            width: 100% !important;
        }
    </style>
@endsection
