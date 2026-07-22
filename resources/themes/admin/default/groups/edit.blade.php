@extends(AdminTheme::wrapper(), ['title' => __('admin.groups', ['default' => 'Groups']), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{!! __('admin.edit_group') !!}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('groups.update', $group->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">{!! __('admin.name') !!}</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror" value="{{ $group->name }}"
                                required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <div class="control-label">{!! __('admin.group_permissions') !!}</div>


                            @php
                                $groupedPermissions = $permissions->groupBy(function ($permission) {
                                    return explode('.', $permission->name)[0];
                                });
                            @endphp


                            <div class="row">
                                @foreach ($groupedPermissions as $groupName => $permsInGroup)
                                    <div class="col-md-12">
                                        <div class="card mt-2">
                                            <div class="card-header">
                                                <h3>{{ ucwords(str_replace('_', ' ', $groupName)) }}</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach ($permsInGroup->chunk(ceil($permsInGroup->count() / 3)) as $chunk)
                                                        <div class="col-md-4">
                                                            @foreach ($chunk as $perms)
                                                                @php
                                                                    $checked = '';
                                                                    if ($group->permissions()->find($perms->id)) {
                                                                        $checked = 'checked';
                                                                    }
                                                                @endphp
                                                                <div class="custom-switches-stacked border p-2 mb-2">
                                                                    <label class="custom-switch">
                                                                        <input type="checkbox" role="switch" name="perms[]"
                                                                            value="{{ $perms->id }}"
                                                                            class="custom-switch-input" {{ $checked }}>
                                                                        <span class="custom-switch-indicator"></span>
                                                                        <span
                                                                            class="custom-switch-description">{{ $perms->name }}</span>

                                                                    </label>
                                                                    <small
                                                                        class="text-truncate">{{ $perms->descriptions }}</small>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>



                        </div>


                        <button type="submit" class="btn btn-primary">{!! __('admin.update') !!}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
