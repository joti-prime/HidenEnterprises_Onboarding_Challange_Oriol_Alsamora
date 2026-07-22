@extends(AdminTheme::wrapper(), ['title' => 'Create Api Key', 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

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
            <form action="{{ route('api-v1.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card">
                        <div class="card-header">
                            <h4>Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="description">{!! __('admin.description') !!}</label>
                                <input type="text" name="description" id="description"
                                    class="form-control" value="{{ old('description') }}" placeholder="Description"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="description">Expires At</label>
                                <input type="date" name="expires_at" id="expires_at"
                                    class="form-control" value="{{ old('expires_at') }}">
                                <small class="mt-1">Leave empty if you don't wish for the token to expire</small>
                            </div>
                        </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Permissions</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="full_access">Full Access</label>
                            <select class="form-control select2 select2-hidden-accessible" name="full_access" onchange="toggleFullAccess(this.value)"
                                tabindex="-1" aria-hidden="true">
                                <option value="0">
                                    No, only allow access to the selected permissions
                                </option>
                                <option value="1">
                                    Yes, allow access to all permissions
                                </option>
                            </select>
                        </div>

                        <div class="row">
                            @foreach($apiRoutes as $endpoint)
                                <div class="col-4">
                                    <div class="custom-switches-stacked border p-2 mb-2">
                                        <label class="custom-switch">
                                            <input type="checkbox" role="switch" name="permissions[]" value="{{ $endpoint['identifier'] }}" class="custom-switch-input">
                                            <span class="custom-switch-indicator"></span>
                                            <span class="custom-switch-description"> <span class="mr-1">{{ $endpoint['identifier'] }}</span>
                                                @if($endpoint['method'] == 'GET')
                                                    <span class="badge badge-primary">{{ $endpoint['method'] }}</span>
                                                @elseif($endpoint['method'] == 'POST')
                                                    <span class="badge badge-success">{{ $endpoint['method'] }}</span>
                                                @elseif($endpoint['method'] == 'PUT' OR $endpoint['method'] == 'PATCH')
                                                    <span class="badge badge-warning">{{ $endpoint['method'] }}</span>
                                                @elseif($endpoint['method'] == 'DELETE')
                                                    <span class="badge badge-danger">{{ $endpoint['method'] }}</span>
                                                @endif
                                            </span>

                                        </label>
                                        <small class="text-truncate">{{ $endpoint['uri'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>IP connections</h4>
                    </div>
                    <div class="card-body">
                        <p>{{ __('admin.allowed_ips_desc') }}</p>
                        <div class="form-group">
                            <label for="ips[]">IP Address</label>
                            <input type="text" name="ips[]" id="ips[]"
                                class="form-control mb-3" value="" placeholder="127.0.0.1">
                            <div id="IP-inputs"></div>

                            <a style="cursor: pointer" class="text-primary" onclick="addIpInput()">Add IP Address</a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-footer text-right">
                        <button class="btn btn-success" type="submit">{!! __('admin.create') !!}</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
<script>
    function toggleFullAccess(value) {
        if (value == 1) {
            document.querySelectorAll('input[name="permissions[]"]').forEach(function (el) {
                el.checked = true;
                el.disabled = true;
            });
        } else {
            document.querySelectorAll('input[name="permissions[]"]').forEach(function (el) {
                el.checked = false;
                el.disabled = false;
            });
        }
    }
    
    function addIpInput() {
        var input = document.createElement("input");
        input.type = "text";
        input.name = "ips[]";
        input.className = "form-control mb-3";
        input.placeholder = "127.0.0.1";
        document.getElementById("IP-inputs").appendChild(input);
    }
</script>
@endsection
