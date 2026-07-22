@extends(AdminTheme::wrapper(), ['title' => __('admin.editing_file', ['default' => 'Editing File']), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
<link rel="stylesheet" href="{{ Theme::get('Default')->assets }}assets/modules/summernote/summernote-bs4.css">
<link rel="stylesheet" href="{{ Theme::get('Default')->assets }}assets/modules/codemirror/lib/codemirror.css">
<link rel="stylesheet" href="{{ Theme::get('Default')->assets }}assets/modules/codemirror/theme/duotone-dark.css">
<link rel="stylesheet" href="{{ Theme::get('Default')->assets }}assets/modules/jquery-selectric/selectric.css">
@endsection

@section('js_libraries')
<script src="{{ Theme::get('Default')->assets }}assets/modules/codemirror/lib/codemirror.js"></script>
<script src="{{ Theme::get('Default')->assets }}assets/modules/codemirror/mode/javascript/javascript.js"></script>
@endsection

@section('container')
<section class="section">
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>{!! __('admin.code_editor', ['default' => 'Code Editor']) !!}</h4>
            </div>
            <div class="card-body">
            <form method="post" action="{{ route('admin.theme.file.save') }}?file={{ $_GET['file']}}">
                    @csrf
                <div class="form-group row mb-4">
                    <div class="col-sm-12 col-md-12">
                        <textarea class="codeeditor" name="contents" style="display: none;">
{{ $contents }}
                        </textarea>
                    </div>
                </div>
                <div class="form-group row mb-4">
                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                    <div class="col-sm-12 col-md-12">
                        <button class="btn btn-icon icon-left btn-dark"><i class="far fa-file"></i> {!! __('admin.save_file', ['default' => 'Save File']) !!}</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>
</section>
<style>
    .CodeMirror.cm-s-duotone-dark {
        height: 600px !important;
    }
</style>
@endsection
