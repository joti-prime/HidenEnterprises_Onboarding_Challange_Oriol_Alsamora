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
        <form action="{{ route('pages.update', ['page' => $page->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{!! __('admin.edit_page', ['default' => 'Edite Page']) !!}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="form-group col-md-4 col-12">
                                <div
                                    class="control-label">{!! __('admin.is_enabled', ['default' => 'Is Enabled']) !!}</div>
                                <label class="custom-switch mt-2">
                                    <input type="checkbox" name="is_enabled" class="custom-switch-input" value="1"
                                           @if($page->is_enabled) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        {!! __('admin.page_is_enabled_desc', ['default' => 'Determine whether the page is enabled or disabled']) !!}
                                    </span>
                                </label>
                            </div>

                            <div class="form-group col-md-4 col-12">
                                <div
                                    class="control-label">{!! __('admin.allow_guests', ['default' => 'Allow Guests']) !!}</div>
                                <label class="custom-switch mt-2">
                                    <input type="checkbox" name="allow_guests" class="custom-switch-input" value="1"
                                           @if($page->allow_guests) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        {!! __('admin.page_allow_guests_desc', ['default' => 'Determine whether the page is viewable for guests']) !!}
                                    </span>
                                </label>
                            </div>

                            <div class="form-group col-md-4 col-12">
                                <div  class="control-label">{!! __('admin.basic_page', ['default' => 'Basic Page']) !!}</div>
                                <label class="custom-switch mt-2">
                                    <input type="checkbox" name="basic_page" class="custom-switch-input" value="1" @if($page->basic_page) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        {!! __('admin.basic_page_desc', ['default' => 'Basic pages are blank pages without any HTML wrapper. You can write custom HTML/Tailwind code to fill the page']) !!}
                                    </span>
                                </label>
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label for="title">{!! __('admin.title', ['default' => 'Title']) !!}</label>
                                <input type="text" name="title" id="title"
                                       class="form-control @error('title') is-invalid @enderror"
                                       value="{{ $page->title }}"
                                       oninput="updatePath()"
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
                                    <input type="text" name="path" id="path" placeholder="web-hosting"
                                           class="form-control @error('link') is-invalid @enderror"
                                           value="{{ $page->path }}" required/>
                                    @error('path')
                                    <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                    @enderror
                                </div>
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

                            <div class="form-group col-md-12 col-12">
                                <label for="name">{!! __('admin.button_name', ['default' => 'Button Name']) !!}</label>
                                <input type="text" name="name" id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ $page->name }}"
                                       required>
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label for="icon">{!! __('admin.icon') !!}</label>
                                <input type="text" name="icon" id="icon"
                                       class="form-control @error('icon') is-invalid @enderror"
                                       value="{{ $page->icon }}"
                                       required>
                                <small class="form-text text-muted">
                                    {!! __('admin.page_icon_desc', ['default' => 'You can get icons from: <a href="https://boxicons.com/" target="_blank">Boxicons</a> or set this value to a custom svg code']) !!}
                                </small>
                                @error('icon')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label>{!! __('admin.visible_placement', ['default' => 'Visible placement']) !!}</label>
                                <select
                                    class="form-control select2 select2-hidden-accessible  @error('placement') is-invalid @enderror"
                                    name="placement[]" multiple="" tabindex="-1" aria-hidden="true">
                                    <option value="portal" @if(in_array( 'portal', $page->placement)) selected @endif>
                                        {!! __('admin.portal_navbar', ['default' => 'Portal Navbar']) !!}
                                    </option>
                                    <option value="navbar" @if(in_array( 'navbar', $page->placement)) selected @endif>
                                        {!! __('admin.navbar') !!}
                                    </option>
                                    <option value="footer_help_center"
                                            @if(in_array( 'footer_help_center', $page->placement)) selected @endif>
                                        {!! __('admin.footer_help_center', ['default' => 'Help Center (footer)']) !!}
                                    </option>
                                    <option value="footer_resources"
                                            @if(in_array( 'footer_resources', $page->placement)) selected @endif>
                                        {!! __('admin.footer_resources', ['default' => 'Resources (footer)']) !!}
                                    </option>
                                    <option value="footer_legal"
                                            @if(in_array( 'footer_legal', $page->placement)) selected @endif>
                                        {!! __('admin.footer_legal', ['default' => 'Legal (footer)']) !!}
                                    </option>
                                </select>
                                <small class="form-text text-muted">
                                    {!! __('admin.choose_locations_links', ['default' => 'Choose the locations to show the page links']) !!}
                                </small>
                                @error('placement')
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-2 col-12">
                                <div class="control-label">{!! __('admin.new_tab', ['default' => 'New Tab']) !!}</div>
                                <label class="custom-switch mt-2">
                                    <input type="checkbox" name="new_tab" class="custom-switch-input" value="1"
                                           @if($page->new_tab) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        {!! __('admin.new_tab_redirect', ['default' => 'Redirect in a new tab']) !!}
                                    </span>
                                </label>
                            </div>

                            <div class="form-group col-md-10 col-12">
                                <label for="redirect">{!! __('admin.redirect_url', ['default' => 'Redirect URL']) !!}</label>
                                <input type="text" name="redirect" id="redirect"
                                       class="form-control @error('redirect') is-invalid @enderror"
                                       value="{{ $page->redirect_url }}"
                                       placeholder="Leave empty not to redirect">
                                <small class="form-text text-muted">
                                    {!! __('admin.redirect_url_page_desc', ['default' => 'If you wish to redirect the user, fill in the URL of the page you want them to redirect to. Leave this field empty to disable redirect']) !!}
                                </small>

                                @error('redirect')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
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
