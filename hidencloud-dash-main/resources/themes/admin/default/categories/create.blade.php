@extends(AdminTheme::wrapper(), ['title' => __('admin.categories'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}" />
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">
@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
        <div class="row">
            <div class="col-12 col-md-12 col-lg-4">

        <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

                <div class="card">
                    <div class="card-header">
                        <h4>{!! __('admin.upload_create_category_icon',
                        ['default' => 'Upload Category Icon']) !!}</h4>
                    </div>
                    <div class="card-body">
                        <input type="file" name="icon" class="drop-zone-md" accept="image/*"/>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-12 col-lg-8">
                <div class="card">
                        <div class="card-header">
                            <h4>{!! __('admin.create_category') !!}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-12 col-12">
                                    <label for="status">Category Status</label>
                                    <select class="form-control select2 select2-hidden-accessible" name="status"
                                        tabindex="-1" aria-hidden="true">
                                        <option value="active">
                                            Active
                                        </option>
                                        <option value="unlisted">
                                            Unlisted (only users with direct link can view)
                                        </option>
                                        <option value="restricted">
                                            Admin Only (admins see it on /store; non-admins blocked)
                                        </option>
                                        <option value="admin_unlisted">
                                            Admin Only + Unlisted (admin-only access via direct URL, hidden from /store even for admins)
                                        </option>
                                        <option value="inactive">
                                            Retired / Inactive (package will not be shown to new customers)
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-md-12 col-12">
                                    <label for="parent_id">Parent group</label>
                                    <select class="form-control select2 select2-hidden-accessible" name="parent_id"
                                        tabindex="-1" aria-hidden="true">
                                        <option value="">— None (this is a top-level group) —</option>
                                        @foreach($parents as $parent)
                                            <option value="{{ $parent->id }}" @if(old('parent_id') == $parent->id) selected @endif>
                                                {{ $parent->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-12 col-12">
                                    <label for="name">{!! __('admin.name') !!}</label>
                                    <input type="text" name="name" id="name" oninput="updatePath()"
                                        class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                        required>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-12 col-12">
                                    <label for="link">{!! __('admin.link') !!}</label>
                                    <div class="input-group-prepend">
                                        <div>
                                            <div class="input-group-text">
                                                {{ route('store.service', '') }}/
                                            </div>
                                        </div>
                                        <input type="text" name="link" id="link" placeholder="web-hosting" class="form-control @error('link') is-invalid @enderror" value="{{ old('link') }}" required />
                                    @error('link')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                </div>

                                <div class="form-group col-md-12 col-12">
                                    <label for="description">{!! __('admin.description') !!}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
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
                var path = document.getElementById('link');
                var title = document.getElementById('name').value;
                path.value = title
                            .toLowerCase() // convert to lowercase
                            .trim() // remove leading and trailing whitespace
                            .replace(/[^\w\s-]/g, '') // remove non-word characters
                            .replace(/[\s_-]+/g, '-') // replace spaces, underscores, and hyphens with a single hyphen
                            .replace(/^-+|-+$/g, ''); // remove leading and trailing hyphens
            }
        </script>
@endsection
