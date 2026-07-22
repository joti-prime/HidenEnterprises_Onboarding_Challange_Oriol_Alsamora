@extends(AdminTheme::wrapper(), ['title' => __('admin.pages'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])
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
        <form action="{{ route('articles.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('POST')
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('admin.create_article') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="form-group col-md-12 col-12">
                                <label>{{ __('admin.status') }}</label>
                                <select
                                    class="form-control select2 select2-hidden-accessible  @error('status') is-invalid @enderror"
                                    name="status" tabindex="-1" aria-hidden="true">
                                    <option value="draft" selected>{{ __('admin.draft') }}</option>
                                    <option value="published">{{ __('admin.published') }}</option>
                                    <option value="unlisted">{{ __('admin.unlisted') }}</option>
                                </select>
                                @error('placement')
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label for="title">{!! __('admin.title', ['default' => 'Title']) !!}</label>
                                <input type="text" oninput="updatePath()" name="title" id="title"
                                       class="form-control @error('title') is-invalid @enderror" value=""
                                       required>
                                @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label for="path">{!! __('admin.link') !!}</label>
                                <div class="input-group-prepend">
                                    <div>
                                        <div class="input-group-text">
                                            {{ route('page', '') }}/
                                        </div>
                                    </div>
                                    <input type="text" name="path" id="path" placeholder="terms-of-service"
                                           class="form-control @error('link') is-invalid @enderror" value="" required/>
                                    @error('path')
                                    <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label for="content">{!! __('admin.content', ['default' => 'Content']) !!}</label>
                                <textarea class="summernote form-control @error('description') is-invalid @enderror" name="content"
                                          id="content" style="height: 200px !important"></textarea>
                                @error('content')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label for="short_desc">Short Description</label>
                                <textarea class="form-control @error('short_desc') is-invalid @enderror" name="short_desc" placeholder="Write a short description about the article" id="short_desc"></textarea>
                                @error('short_desc')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label>{{ __('admin.labels') }}</label>
                                <select
                                    class="form-control select2 select2-hidden-accessible  @error('labels') is-invalid @enderror"
                                    name="labels[]" multiple="" tabindex="-1" aria-hidden="true">
                                    @foreach(config('article.labels') as $key => $label)
                                        <option value="{{ $key }}">{{ $label['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('placement')
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-4 col-12">
                                <div
                                    class="control-label">{!! __('admin.allow_guests', ['default' => 'Allow Guests']) !!}</div>
                                <label class="custom-switch mt-2">
                                    <input type="checkbox" name="allow_guests" class="custom-switch-input" value="1" checked>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        {!! __('admin.page_allow_guests_desc', ['default' => 'Determine whether the page is viewable for guests']) !!}
                                    </span>
                                </label>
                            </div>

                            <div class="form-group col-md-4 col-12">
                                <div
                                    class="control-label">{{ __('admin.show_author') }}</div>
                                <label class="custom-switch mt-2">
                                    <input type="checkbox" name="show_author" class="custom-switch-input" value="1"
                                           checked>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                    {{ __('admin.show_author_desc') }}
                              </span>
                                </label>
                            </div>

                            <div class="form-group col-md-4 col-12">
                                <div
                                    class="control-label">{{ __('admin.allow_comments') }}</div>
                                <label class="custom-switch mt-2">
                                    <input type="checkbox" name="allow_comments" class="custom-switch-input" value="1" checked="">
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        {{ __('admin.allow_comments_desc') }}
                                    </span>
                                </label>
                            </div>

                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button class="btn btn-dark" type="submit">{!! __('admin.create') !!}</button>
                    </div>
                </div>
        </form>
    </div>
    </div>
    <script>
        function updatePath() {
            var path = document.getElementById('path');
            var title = document.getElementById('title').value;
            path.value = title
                        .toLowerCase() // convert to lowercase
                        .trim() // remove leading and trailing whitespace
                        .replace(/[^\w\s-]/g, '') // remove non-word characters
                        .replace(/[\s_-]+/g, '-') // replace spaces, underscores, and hyphens with a single hyphen
                        .replace(/^-+|-+$/g, ''); // remove leading and trailing hyphens
        }
    </script>
@endsection
