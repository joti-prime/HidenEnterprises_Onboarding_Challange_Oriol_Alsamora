@extends(AdminTheme::wrapper(), ['title' => __('admin.registrations'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

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
                        <h4>{!! __('admin.registration_settings', ['default' => 'Registration Settings']) !!}</h4>
                    </div>
                    <div class="card-body">
                        @csrf
                        <div class="row">

                            <div class="form-group col-6">
                                <label for="registrations">{!! __('admin.registration') !!}</label>
                                <select class="form-control select2 select2-hidden-accessible" name="registrations"
                                        tabindex="-1" aria-hidden="true">
                                    <option value="true"
                                            @if(Settings::get('registrations', 'true') == 'true') selected @endif>{!! __('admin.enabled') !!}</option>
                                    <option value="false"
                                            @if(Settings::get('registrations', 'true') == 'false') selected @endif>{!! __('admin.disabled') !!}</option>
                                </select>
                                <small class="form-text text-muted">
                                    {!! __('admin.registrations_enable_desc', ['default' => 'Do you want to enable user registrations']) !!}
                                </small>
                            </div>

                            <div class="form-group col-6">
                                <label
                                    for="registration_activation">{!! __('admin.account_activation', ['default' => 'Account Activation']) !!}</label>
                                <select class="form-control select2 select2-hidden-accessible"
                                        name="registration_activation" tabindex="-1" aria-hidden="true">
                                    <option value="1"
                                            @if(Settings::get('registration_activation') == '1') selected @endif>
                                        {!! __('admin.no_verification', ['default' => 'No verification']) !!}</option>
                                    <option value="2"
                                            @if(Settings::get('registration_activation') == '2') selected @endif>
                                        {!! __('admin.email_verification', ['default' => 'Email verification']) !!}</option>
                                    <option value="3"
                                            @if(Settings::get('registration_activation') == '3') selected @endif>
                                        {!! __('admin.email_verification_approval', ['default' => 'Email verification and Admin Approval']) !!}</option>
                                </select>
                                <small class="form-text text-muted">
                                    {!! __('admin.registrations_Select_method', ['default' => 'Select the activation method for new registrations']) !!}
                                </small>
                            </div>

                            <div class="form-group col-12">
                                <div class="control-label">
                                    {!! __('admin.require_address_information', ['default' => 'Require Address Information']) !!}
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('require_address', false)) /admin/settings/store?require_address=0 @else /admin/settings/store?require_address=1 @endif';">
                                    <input type="checkbox" name="require_address" value="1" class="custom-switch-input"
                                           @if(settings('require_address', false)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                    {!! __('admin.require_address_information_desc', ['default' => 'After login in, users will be forced to complete their address information.']) !!}
                                    </span>
                                </label>
                            </div>

                            <div class="form-group col-12">
                                <div class="control-label">
                                    {!! __('admin.require_phone_number', ['default' => 'Require user to setup a phone number']) !!}
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('require_phone_number', false)) /admin/settings/store?require_phone_number=0 @else /admin/settings/store?require_phone_number=1 @endif';">
                                    <input type="checkbox" name="require_phone_number" value="1" class="custom-switch-input"
                                           @if(settings('require_phone_number', false)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                    {!! __('admin.require_phone_number_desc') !!}
                                    </span>
                                </label>
                            </div>

                            <div class="form-group col-12">
                                <div class="control-label">
                                    {!! __('admin.force_staff_2fa') !!}
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('force_staff_2fa', false)) /admin/settings/store?force_staff_2fa=0 @else /admin/settings/store?force_staff_2fa=1 @endif';">
                                    <input type="checkbox" name="force_staff_2fa" value="1" class="custom-switch-input"
                                           @if(settings('force_staff_2fa', false)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                    {!! __('admin.force_staff_2fa_description') !!}
                                    </span>
                                </label>
                            </div>

                            <div class="form-group col-12">
                                <div class="control-label">
                                    {!! __('admin.allow_staff_sso_logins') !!}
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('staff_sso_login', false)) /admin/settings/store?staff_sso_login=0 @else /admin/settings/store?staff_sso_login=1 @endif';">
                                    <input type="checkbox" name="staff_sso_login" value="1" class="custom-switch-input"
                                           @if(settings('staff_sso_login', false)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                    {!! __('admin.allow_staff_sso_logins_description') !!}
                                    </span>
                                </label>
                            </div>

                            <div class="form-group col-12">
                                <div class="control-label">
                                    {!! __('admin.allow_custom_avatars') !!}
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('allow_custom_avatars', true)) /admin/settings/store?allow_custom_avatars=0 @else /admin/settings/store?allow_custom_avatars=1 @endif';">
                                    <input type="checkbox" name="allow_custom_avatars" value="1" class="custom-switch-input"
                                           @if(settings('allow_custom_avatars', true)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                    {!! __('admin.allow_custom_avatars_description') !!}
                                    </span>
                                </label>
                            </div>

                            <div class="form-group col-12">
                                <label>{{ __('admin.default_avatar') }}</label>
                                <input type="url" name="default_avatar" value="@settings('default_avatar', '/assets/core/img/logo.png')" class="form-control">
                                <div class="gallery gallary mt-3">
                                    <div class="gallery-item" data-image="@settings('default_avatar', '/assets/core/img/logo.png')" data-title="Image 1" href="@settings('default_avatar', '/assets/core/img/logo.png')" title="Image 1" style="background-image: url('@settings('default_avatar', '/assets/core/img/logo.png')');"></div>
                                  </div>
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label for="registration_activation_message">
                                    {!! __('admin.registration_activation_message', ['default' => 'Registration Activation Message']) !!}
                                </label>
                                <textarea class="summernote form-control" name="registration_activation_message"
                                          id="registration_activation_message" style="display: none;">
                                    @settings('registration_activation_message',
                                    'Your account has been placed in a queue and requires manual approval by an administrator.')
                                </textarea>
                            </div>

                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">{!! __('admin.submit') !!}</button>
                    </div>
            </div>
            </form>
        </div>
    </div>
    <style>
        span.select2.select2-container.select2-container--default {
            width: 100% !important;
        }
    </style>
@endsection
