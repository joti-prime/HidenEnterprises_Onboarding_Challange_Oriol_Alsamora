@extends(AdminTheme::wrapper(), ['title' => __('admin.themes'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

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
        @if (View::exists(Theme::path('admin-settings')))
            @includeIf(Theme::path('admin-settings'))
        @else
            <div class="alert alert-warning mt-3">
            <div class="alert-title">{!! __('admin.warning') !!}</div>
                {{ Theme::active()->name }} {!! __('admin.theme_warning', ['default' => 'theme does not have configurable settings.']) !!}
            </div>
        @endif
    </div>
</div>
<style>
    span.select2.select2-container.select2-container--default {
        width: 100% !important;
    }
</style>
@endsection
