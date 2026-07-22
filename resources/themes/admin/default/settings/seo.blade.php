@extends(AdminTheme::wrapper(), ['title' => __('admin.settings'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

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
              <h4>Search Engine Optimization</h4>
            </div>
            <div class="card-body">
                @csrf
              <div class="row">
                <div class="form-group col-12">
                    <label>Title</label>
                    <input type="text" name="seo::title" value="@settings('seo::title', settings('app_name'))" class="form-control">
                </div>
                <div class="form-group col-12">
                    <label for="seo::description">{!! __('admin.description') !!}</label>
                    <textarea class="form-control" name="seo::description" id="seo::description" placeholder="Write a short description about your application">@settings('seo::description')</textarea>
                </div>
                <div class="form-group col-12">
                    <label for="seo::keywords">{!! __('admin.keywords') !!}</label>
                    <textarea class="form-control" name="seo::keywords" id="seo::keywords" placeholder="keywords1, keywords2, keywords3">@settings('seo::keywords')</textarea>
                    <small class="form-text text-muted">
                        Help search engines by providing list of keywords, separate each keyword with a comma (",")
                    </small>
                </div>
                <div class="form-group col-12">
                    <label>Robots</label>
                    <input type="text" name="seo::robots" value="@settings('seo::robots', 'index, follow')" class="form-control">
                    <small class="form-text text-muted">
                        This is a more advanced setting, leave this as default to "index, follow" if you are not sure
                    </small>
                </div>
                <div class="form-group col-12">
                    <label>Color</label>
                    <input type="color" name="seo::color" value="@settings('seo::color', '#4f46e5')" class="form-control">
                    <small class="form-text text-muted">
                        Default color of the application
                    </small>
                </div>
                <div class="form-group col-12">
                    <label>Logo / Image</label>
                    <input type="text" name="seo::image" value="@settings('seo::image', settings('logo', '/assets/core/img/logo.png'))" class="form-control">
                    <div class="gallery gallary mt-3">
                        <div class="gallery-item" data-image="@settings('seo::image', settings('logo', '/assets/core/img/logo.png'))" data-title="Image 1" href="@settings('seo::image', settings('logo', '/assets/core/img/logo.png'))" title="Image 1" style="background-image: url('@settings('seo::image', settings('logo', '/assets/core/img/logo.png'))')');"></div>
                      </div>
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
