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
                        <h4>{!! __('admin.general_settings', ['default' => 'General Settings']) !!}</h4>
                    </div>
                    <div class="card-body">
                        @csrf
                        <div class="row">
                            <div class="form-group col-6">
                                <label>{!! __('admin.application_name', ['default' => 'Application Name']) !!}</label>
                                <input type="text" name="app_name" value="@settings('app_name', 'HCTestDash')"
                                       class="form-control">
                            </div>
                            <div class="form-group col-6">
                                <label>{!! __('admin.contact_email', ['default' => 'Contact Email']) !!}</label>
                                <input type="email" name="contact_email"
                                       value="@settings('contact_email', 'contact@example.com')" class="form-control">
                            </div>
                            <div class="form-group col-12">
                                <label>{!! __('admin.company_address', ['default' => 'Company Address']) !!}</label>
                                <input type="text" name="company_address"
                                       value="@settings('company_address', '291 N 4th St, San Jose, CA 95112, USA')"
                                       class="form-control">
                            </div>
                            <div class="form-group col-6">
                                <label for="currency">{!! __('admin.currency') !!}</label>
                                <select class="form-control select2 select2-hidden-accessible" name="currency"
                                        tabindex="-1" aria-hidden="true">
                                    @foreach(config('utils.currencies') as $key => $currency)
                                        <option value="{{ $key }}"
                                                @if(settings('currency') == $key) selected @endif>{{ $currency['name'] }}
                                            ({{ $currency['symbol'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-6">
                                <label
                                        for="language">{!! __('admin.default_language', ['default' => 'Default Language']) !!}</label>
                                <select class="form-control select2 select2-hidden-accessible" name="language"
                                        tabindex="-1" aria-hidden="true">
                                    @if(Module::isEnabled('locales'))
                                        @foreach(lang_module()->getInstalled() as $key => $lang)
                                            <option @if(settings('language') == $key) selected
                                                    @endif value="{{$key}}">{{$lang}}</option>
                                        @endforeach
                                    @else
                                        <option value="en">English</option>
                                    @endif
                                </select>
                            </div>
                            <div class="form-group col-6">
                                <label for="allow_toggle_mode">Allow toggle theme mode</label>
                                <select class="form-control select2 select2-hidden-accessible" name="theme::allow_toggle_mode" tabindex="-1" aria-hidden="true">
                                    <option value="1" @if(settings('theme::allow_toggle_mode', 1) == 1) selected @endif>{!! __('admin.yes') !!}</option>
                                    <option value="0" @if(settings('theme::allow_toggle_mode', 1) == 0) selected @endif>{!! __('admin.no') !!}</option>
                                </select>
                            </div>
                            <div class="form-group col-6">
                                <label for="allow_toggle_mode">Default theme mode</label>
                                <select class="form-control select2 select2-hidden-accessible" name="theme::default_mode" tabindex="-1" aria-hidden="true">
                                    <option value="dark" @if(settings('theme::default_mode', 'dark') == 'dark') selected @endif>Dark</option>
                                    <option value="light" @if(settings('theme::default_mode', 'dark') == 'light') selected @endif>Light</option>
                                </select>
                            </div>
                            <div class="form-group col-12">
                                <label>Google Analytics Code</label>
                                <input type="text" name="google::analytics_code"
                                       value="@settings('google::analytics_code')" class="form-control">
                                <small class="mt-1">Enter your google analytics code for tracking. For example
                                    G-XXXXXXXXXX - Leave empty to disable</small>
                            </div>
                            <div class="form-group col-6">
                                <div class="control-label">
                                    User Data Download
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('download_user_data', true)) /admin/settings/store?download_user_data=0 @else /admin/settings/store?download_user_data=1 @endif';">
                                    <input type="checkbox" name="download_user_data" value="1"
                                           class="custom-switch-input"
                                           @if(settings('download_user_data', true)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                            Do you want to give your users the option to download all data stored about the user from user settings.
                        </span>
                                </label>
                            </div>

                            <div class="form-group col-6">
                                <div class="control-label">
                                    Enable Account Deletion Requests
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('delete_user_account', true)) /admin/settings/store?delete_user_account=0 @else /admin/settings/store?delete_user_account=1 @endif';">
                                    <input type="checkbox" name="delete_user_account" value="1"
                                           class="custom-switch-input"
                                           @if(settings('delete_user_account', true)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                            Users will be given the option to delete their account and all data stored about the user from user settings.
                        </span>
                                </label>
                            </div>

                            <div class="form-group col-12">
                                <div class="control-label">
                                    Enable Cookie popup
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('cookie_popup_enabled', true)) /admin/settings/store?cookie_popup_enabled=0 @else /admin/settings/store?cookie_popup_enabled=1 @endif';">
                                    <input type="checkbox" name="cookie_popup_enabled" value="1"
                                           class="custom-switch-input"
                                           @if(settings('cookie_popup_enabled', true)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                            Do you want to enable the cookie alert popup message on the client area
                        </span>
                                </label>
                            </div>

                            <div class="form-group col-6">
                                <label>{!! __('admin.application_logo', ['default' => 'Application Logo']) !!}</label>
                                <input type="text" name="logo"
                                       value="@settings('logo', '/assets/core/img/logo.png')" class="form-control">
                                <div class="gallery gallary mt-3">
                                    <div class="gallery-item"
                                         data-image="@settings('logo', '/assets/core/img/logo.png')"
                                         data-title="Image 1" href="@settings('logo', '/assets/core/img/logo.png')"
                                         title="Image 1"
                                         style="background-image: url('@settings('logo', '/assets/core/img/logo.png')');"></div>
                                </div>
                            </div>

                            <div class="form-group col-6">
                                <label>{!! __('admin.favicon') !!}</label>
                                <input type="text" name="favicon"
                                       value="@settings('favicon', '/assets/core/img/favicon.png')"
                                       class="form-control">
                                <div class="gallery mt-3">
                                    <div class="gallery-item"
                                         data-image="@settings('favicon', '/assets/core/img/favicon.png')"
                                         data-title="Image 1"
                                         href="@settings('favicon', '/assets/core/img/favicon.png')" title="Image 1"
                                         style="background-image: url('@settings('favicon', '/assets/core/img/favicon.png')');"></div>
                                </div>
                            </div>

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
