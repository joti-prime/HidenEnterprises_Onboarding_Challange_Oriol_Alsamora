@extends(AdminTheme::wrapper(), ['title' => __('admin.maintenance'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

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
    <div class="col-12">
        <div class="card">
            <form action="{{ route('admin.settings.store') }}" method="POST">
            <div class="card-header">
              <h4>{!! __('admin.maintenance_settings', ['default' => 'Maintenance Settings']) !!}</h4>
            </div>
            <div class="card-body">
                @csrf
              <div class="row">

                <div class="form-group col-12">
                    <label for="maintenance">{!! __('admin.enable_maintenance_mode', ['default' => 'Enable Maintenance Mode']) !!}</label>
                    <select class="form-control select2 select2-hidden-accessible" name="maintenance" tabindex="-1" aria-hidden="true">
                        <option value="true" @if(Settings::get('maintenance', 'false') == 'true') selected @endif>{!! __('admin.enabled') !!}</option>
                        <option value="false" @if(Settings::get('maintenance', 'false') == 'false') selected @endif>{!! __('admin.disabled') !!}</option>
                    </select>
                    <small class="form-text text-muted">
                        {!! __('admin.enable_maintenance_mode_desc', ['default' => 'Do you want to enable maintenance mode']) !!}
                    </small>
                </div>

                <div class="form-group col-md-12 col-12">
                    <label for="maintenance_message">{!! __('admin.maintenance_mode_message', ['default' => 'Maintenance Mode Message']) !!}</label>
                    <textarea class="summernote form-control" name="maintenance_message" id="maintenance_message" style="display: none;">
                        @settings('maintenance_message', 'We are currently ongoing maintenance, please get back later.')
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
