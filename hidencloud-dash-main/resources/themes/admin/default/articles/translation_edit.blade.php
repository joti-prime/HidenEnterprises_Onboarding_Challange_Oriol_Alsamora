@extends(AdminTheme::wrapper(), ['title' =>  __('admin.pages'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])
@section('css_libraries')
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}"/>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">

@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection
@section('container')
    <div class="">
        <form action="{{ route('articles.translation.store', ['id' => $page->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{!! __('admin.edit_page') !!}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="form-group col-md-12 col-12">
                                <label for="language">{!! __('admin.locations') !!}</label>
                                <select class="form-control select2 select2-hidden-accessible" name="locale" tabindex="-1" aria-hidden="true">
                                    @if(Module::isEnabled('locales'))
                                        @foreach(lang_module()->getInstalled() as $key => $lang)
                                            <option @if($locale == $key) selected @endif value="{{$key}}">{{$lang}}</option>
                                        @endforeach
                                    @else
                                        <option value="en">English</option>
                                    @endif
                                </select>
                            </div>


                            <div class="form-group col-md-12 col-12">
                                <label for="title">{!! __('admin.title', ['default' => 'Title']) !!}</label>
                                <input type="text" name="title" id="title"
                                       class="form-control @error('title') is-invalid @enderror"
                                       value="{{ $page->title }}"
                                       required>
                                @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>


                            <div class="form-group col-md-12 col-12">
                                <label for="content">{!! __('admin.content', ['default' => 'Content']) !!}</label>
                                <textarea class="summernote form-control" name="content" id="content"
                                          style="display: none;">
                                {!! $page->content !!}
                            </textarea>
                                <small class="form-text text-muted">
                                    {!! __('admin.page_content_desc', ['default' => 'This field is the custom content as shown on the page. You are free to use custom code if you wish for example <code>&lt;img src="path-to-image"&gt;</code>']) !!}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button class="btn btn-dark" type="submit">{!! __('admin.update') !!}</button>
                    </div>
                </div>
        </form>
    </div>
</div>

@endsection
