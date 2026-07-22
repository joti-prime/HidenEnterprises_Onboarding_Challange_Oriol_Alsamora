@extends(AdminTheme::wrapper(), ['title' => __('admin.oauth'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
<link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}" />
<link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">

@endsection

@section('js_libraries')
<script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
<script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
    <div class="row">

      <div class="col-md-6 col-12">
        <div class="card">
          <div class="card-header justify-content-center">
            <div class="oauth-icon"><i class="fab fa-discord icon-32px"></i></div>
          </div>
          <form action="{{ route('admin.settings.store') }}" method="POST">
            @csrf
          <div class="card-body">
            <div class="row">
              <div class="form-group col-12">
                <label>{!! __('admin.discord_client_id', ['default' => 'Discord Client ID']) !!}</label>
                <input type="text" name="encrypted::oauth::discord[client_id]"
                       value="{{ Settings::getJson('encrypted::oauth::discord', 'client_id') }}"
                       placeholder="{!! __('admin.discord_client_id', ['default' => 'Discord Client ID']) !!}" class="form-control">
              </div>

              <div class="form-group col-12">
                <label>{!! __('admin.discord_client_secret', ['default' => 'Discord Client Secret']) !!}</label>
                <input type="password" name="encrypted::oauth::discord[client_secret]"
                       value="{{ Settings::getJson('encrypted::oauth::discord', 'client_secret') }}"
                       placeholder="{!! __('admin.discord_client_secret', ['default' => 'Discord Client Secret']) !!}" class="form-control">
              </div>

              <div class="form-group col-12">
                <label>{!! __('admin.discord_redirect', ['default' => 'Discord Redirect']) !!}</label>
                <input type="text" value="{{ config('app.url') . '/oauth/discord/redirect' }}" class="form-control" disabled>
              </div>

              <div class="form-group col-6">
                <div class="control-label">{!! __('admin.enable_driver', ['default' => 'Enable Driver']) !!}</div>
                <label class="custom-switch mt-2">
                  <input type="checkbox" name="encrypted::oauth::discord[is_enabled]" value="1" class="custom-switch-input"
                         @if(Settings::getJson('encrypted::oauth::discord', 'is_enabled', false)) checked @endif>
                  <span class="custom-switch-indicator"></span>
                  <span class="custom-switch-description">
                      {!! __('admin.allow_connect_using', ['name' => 'Discord','default' => 'Allow users to connect :name']) !!}
                  </span>
                </label>
              </div>
                <div class="form-group col-6">
                    <div class="control-label">{!! __('admin.allow_login') !!}</div>
                    <label class="custom-switch mt-2">
                        <input type="hidden" name="oauth::discord[allow_login]" value="0">
                        <input type="checkbox" name="oauth::discord[allow_login]" value="1" class="custom-switch-input"
                               @if(Settings::getJson('oauth::discord', 'allow_login', false)) checked @endif>
                        <span class="custom-switch-indicator"></span>
                        <span class="custom-switch-description">
                      {!! __('admin.allow_login_using', ['name' => 'Discord']) !!}
                  </span>
                    </label>
                </div>

              <div class="col-12" style="display: flex;justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">{!! __('admin.update_driver', ['default' => 'Update Driver']) !!}</button>
              </div>
            </div>
          </div>
        </form>
        </div>
      </div>

      <div class="col-md-6 col-12">
        <div class="card">
          <div class="card-header justify-content-center">
            <div class="oauth-icon"><i class="fab fa-github icon-32px"></i></div>
          </div>
          <form action="{{ route('admin.settings.store') }}" method="POST">
            @csrf
          <div class="card-body">
            <div class="row">
              <div class="form-group col-12">
                <label>{!! __('admin.github_client_id', ['default' => 'Github Client ID']) !!}</label>
                <input type="text" name="encrypted::oauth::github[client_id]"
                       value="{{ Settings::getJson('encrypted::oauth::github', 'client_id') }}"
                       placeholder="{!! __('admin.github_client_id', ['default' => 'Github Client ID']) !!}" class="form-control">
              </div>

              <div class="form-group col-12">
                <label>{!! __('admin.github_client_secret', ['default' => 'Github Client Secret']) !!}</label>
                <input type="password" name="encrypted::oauth::github[client_secret]"
                       value="{{ Settings::getJson('encrypted::oauth::github', 'client_secret') }}"
                       placeholder="{!! __('admin.github_client_secret', ['default' => 'Github Client Secret']) !!}" class="form-control">
              </div>

              <div class="form-group col-12">
                <label>{!! __('admin.github_redirect', ['default' => 'Github Redirect']) !!}</label>
                <input type="text" value="{{ config('app.url') . '/oauth/github/redirect' }}" class="form-control" disabled>
              </div>

              <div class="form-group col-6">
                <div class="control-label">{!! __('admin.enable_driver', ['default' => 'Enable Driver']) !!}</div>
                <label class="custom-switch mt-2">
                  <input type="checkbox" name="encrypted::oauth::github[is_enabled]" value="1" class="custom-switch-input"
                         @if(Settings::getJson('encrypted::oauth::github', 'is_enabled', false)) checked @endif>
                  <span class="custom-switch-indicator"></span>
                  <span class="custom-switch-description">
                      {!! __('admin.allow_connect_using', ['name' => 'Github','default' => 'Allow users to connect :name']) !!}
                  </span>
                </label>
              </div>
                <div class="form-group col-6">
                    <div class="control-label">{!! __('admin.allow_login') !!}</div>
                    <label class="custom-switch mt-2">
                        <input type="hidden" name="oauth::github[allow_login]" value="0">
                        <input type="checkbox" name="oauth::github[allow_login]" value="1" class="custom-switch-input"
                               @if(Settings::getJson('oauth::github', 'allow_login', false)) checked @endif>
                        <span class="custom-switch-indicator"></span>
                        <span class="custom-switch-description">
                      {!! __('admin.allow_login_using', ['name' => 'Github']) !!}
                  </span>
                    </label>
                </div>

              <div class="col-12" style="display: flex;justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">{!! __('admin.update_driver', ['default' => 'Update Driver']) !!}</button>
              </div>
            </div>
          </div>
        </form>
        </div>
      </div>

      <div class="col-md-6 col-12">
        <div class="card">
          <div class="card-header justify-content-center">
            <div class="oauth-icon"><i class="fab fa-google icon-32px"></i></div>
          </div>
          <form action="{{ route('admin.settings.store') }}" method="POST">
            @csrf
          <div class="card-body">
            <div class="row">
              <div class="form-group col-12">
                <label>{!! __('admin.google_client_id', ['default' => 'Google Client ID']) !!}</label>
                <input type="text" name="encrypted::oauth::google[client_id]"
                       value="{{ Settings::getJson('encrypted::oauth::google', 'client_id') }}"
                       placeholder="{!! __('admin.google_client_id', ['default' => 'Google Client ID']) !!}" class="form-control">
              </div>

              <div class="form-group col-12">
                <label>{!! __('admin.google_client_secret', ['default' => 'Google Client Secret']) !!}</label>
                <input type="password" name="encrypted::oauth::google[client_secret]"
                       value="{{ Settings::getJson('encrypted::oauth::google', 'client_secret') }}"
                       placeholder="{!! __('admin.google_client_secret', ['default' => 'Google Client Secret']) !!}" class="form-control">
              </div>

              <div class="form-group col-12">
                <label>{!! __('admin.google_redirect', ['default' => 'Google Redirect']) !!}</label>
                <input type="text" value="{{ config('app.url') . '/oauth/google/redirect' }}" class="form-control" disabled>
              </div>

              <div class="form-group col-6">
                <div class="control-label">{!! __('admin.enable_driver', ['default' => 'Enable Driver']) !!}</div>
                <label class="custom-switch mt-2">
                  <input type="checkbox" name="encrypted::oauth::google[is_enabled]" value="1" class="custom-switch-input"
                         @if(Settings::getJson('encrypted::oauth::google', 'is_enabled', false)) checked @endif>
                  <span class="custom-switch-indicator"></span>
                  <span class="custom-switch-description">
                      {!! __('admin.allow_connect_using', ['name' => 'Google','default' => 'Allow users to connect :name']) !!}
                  </span>
                </label>
              </div>
                <div class="form-group col-6">
                    <div class="control-label">{!! __('admin.allow_login') !!}</div>
                    <label class="custom-switch mt-2">
                        <input type="hidden" name="oauth::google[allow_login]" value="0">
                        <input type="checkbox" name="oauth::google[allow_login]" value="1" class="custom-switch-input"
                               @if(Settings::getJson('oauth::google', 'allow_login', false)) checked @endif>
                        <span class="custom-switch-indicator"></span>
                        <span class="custom-switch-description">
                      {!! __('admin.allow_login_using', ['name' => 'Google']) !!}
                  </span>
                    </label>
                </div>


              <div class="col-12" style="display: flex;justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">{!! __('admin.update_driver', ['default' => 'Update Driver']) !!}</button>
              </div>
            </div>
          </div>
        </form>
        </div>
      </div>

    </div>

<style>
  .oauth-icon {
    width: 64px;
    height: 64px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(195deg,#42424a 0%,#191919 100%);
    border-radius: 10px;
    font-size: 32px;
  }

  .icon-32px {
    font-size: 32px;
  }
</style>
@endsection
