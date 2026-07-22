@extends(AdminTheme::wrapper(), ['title' => __('admin.captcha'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

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
            <div class="captcha-icon"><i class="fab fa-cloudflare icon-32px"></i></div>
          </div>
          <form action="{{ route('admin.settings.store') }}" method="POST">
            @csrf
          <div class="card-body">
            <div class="row">
              <div class="form-group col-12">
                <label>{!! __('admin.cloudflare_site_key', ['default' => 'CloudFlare Site Key']) !!}</label>
                <input type="text" name="encrypted::captcha::cloudflare[site_key]"
                       value="{{ Settings::getJson('encrypted::captcha::cloudflare', 'site_key') }}" class="form-control">
              </div>

              <div class="form-group col-12">
                <label>{!! __('admin.cloudflare_secret_key', ['default' => 'CloudFlare Secret Key']) !!}</label>
                <input type="password" name="encrypted::captcha::cloudflare[secret_key]"
                       value="{{ Settings::getJson('encrypted::captcha::cloudflare', 'secret_key') }}" class="form-control">
              </div>

              <div class="form-group col-6">
                <div class="control-label">{!! __('admin.enable_captcha', ['default' => 'Enable Captcha']) !!}</div>
                <label class="custom-switch mt-2">
                  <input type="checkbox" name="encrypted::captcha::cloudflare[is_enabled]" value="1" class="custom-switch-input"
                         @if(Settings::getJson('encrypted::captcha::cloudflare', 'is_enabled', false)) checked @endif>
                  <span class="custom-switch-indicator"></span>
                  <span class="custom-switch-description">{!! __('admin.enable_captcha') !!}</span>
                </label>
              </div>

              <div class="form-group col-6">
                <div class="control-label">{!! __('admin.captcha_show_login', ['default' => 'Show on Login Page']) !!}</div>
                <label class="custom-switch mt-2">
                  <input type="checkbox" name="encrypted::captcha::cloudflare[page_login]" value="1" class="custom-switch-input"
                         @if(Settings::getJson('encrypted::captcha::cloudflare', 'page_login', false)) checked @endif>
                  <span class="custom-switch-indicator"></span>
                  <span class="custom-switch-description">
                      {!! __('admin.captcha_show_login_desc', ['default' => 'Display Captcha challenge on login page?']) !!}
                  </span>
                </label>
              </div>

              <div class="form-group col-6">
                <div class="control-label">{!! __('admin.captcha_show_register', ['default' => 'Show on Register Page']) !!}</div>
                <label class="custom-switch mt-2">
                  <input type="checkbox" name="encrypted::captcha::cloudflare[page_register]" value="1" class="custom-switch-input"
                         @if(Settings::getJson('encrypted::captcha::cloudflare', 'page_register', false)) checked @endif>
                  <span class="custom-switch-indicator"></span>
                  <span class="custom-switch-description">
                      {!! __('admin.captcha_show_register_desc', ['default' => 'Display Captcha challenge on login page?']) !!}
                  </span>
                </label>
              </div>

              <div class="form-group col-6">
                <div class="control-label">{!! __('admin.captcha_show_contact_us', ['default' => 'Show on Contact Us Page']) !!}</div>
                <label class="custom-switch mt-2">
                  <input type="checkbox" name="encrypted::captcha::cloudflare[page_contact_us]" value="1" class="custom-switch-input"
                         @if(Settings::getJson('encrypted::captcha::cloudflare', 'page_contact_us', false)) checked @endif>
                  <span class="custom-switch-indicator"></span>
                  <span class="custom-switch-description">
                      {!! __('admin.captcha_show_contact_us_desc', ['default' => 'Display Captcha challenge on login page?']) !!}
                  </span>
                </label>
              </div>

              <div class="col-12" style="display: flex;justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">{!! __('admin.update') !!}</button>
              </div>
            </div>
          </div>
        </form>
        </div>
      </div>

    </div>

<style>
  .captcha-icon {
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
