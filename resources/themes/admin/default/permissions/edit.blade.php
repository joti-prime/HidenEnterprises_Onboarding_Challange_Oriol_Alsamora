@extends(AdminTheme::wrapper(), ['title' => __('admin.permissions'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{!! __('admin.edit_permission', ['default' => 'Edit Permission']) !!}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('permissions.update', ['permission' => $permission->id]) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">{!! __('admin.name') !!}</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror" value="{{ $permission->name }}"
                                required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="descriptions">{!! __('admin.description') !!}</label>
                            <input type="text" name="descriptions" id="descriptions"
                                class="form-control @error('descriptions') is-invalid @enderror"
                                value="{{ $permission->descriptions }}">
                            @error('descriptions')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">{!! __('admin.update') !!}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
