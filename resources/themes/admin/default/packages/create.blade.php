@extends(AdminTheme::wrapper(), ['title' => __('admin.packages'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}"/>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">

@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4>{!!  __('admin.create_package', ['default' => 'Create Package']) !!}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('packages.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row mt-4">
                            <div class="form-group col-md-12 col-12">
                                <label for="name">{!!  __('admin.package_name', ['default' => 'Package Name']) !!}</label>
                                <input type="text" name="name" id="name" placeholder="{!!  __('admin.package_name', ['default' => 'Package Name']) !!}" class="form-control"
                                       value="" required=""/>
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label for="category">{!!  __('admin.category') !!}</label>
                                <select class="form-control select2 select2-hidden-accessible" name="category"
                                        tabindex="-1" aria-hidden="true">
                                    @foreach (Categories::get() as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label for="service">{!!  __('admin.service_provider', ['default' => 'Service Provider']) !!}</label>
                                <select class="form-control select2 select2-hidden-accessible" name="service"
                                        tabindex="-1" aria-hidden="true">
                                    @foreach (Service::allEnabled() as $service)
                                        <option value="{{ $service->module()->getLowerName() }}">{{ $service->about()->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label for="status">{!!  __('admin.package_status', ['default' => 'Package Status']) !!}</label>
                                <select class="form-control select2 select2-hidden-accessible" name="status"
                                        tabindex="-1" aria-hidden="true">
                                    <option value="active">{!!  __('admin.active') !!}</option>
                                    <option value="unlisted">
                                        {!!  __('admin.package_status_unlisted', ['default' => 'Unlisted (only users with direct link can view)']) !!}
                                    </option>
                                    <option value="restricted" selected>
                                        {!!  __('admin.package_status_admin_only', ['default' => 'Admin Only (only administrators can view)']) !!}
                                    </option>
                                    <option value="inactive">
                                        {!!  __('admin.package_status_retired_inactive', ['default' => 'Retired / Inactive (package will not be shown to new customers)']) !!}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <button href="#" class="btn btn-dark" type="submit">{!!  __('admin.create') !!}</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <style>
        span.select2.select2-container.select2-container--default {
            width: 100% !important;
        }
    </style>
@endsection
