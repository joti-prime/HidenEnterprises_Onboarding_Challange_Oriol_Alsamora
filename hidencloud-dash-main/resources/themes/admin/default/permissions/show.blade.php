@extends(AdminTheme::wrapper(), ['title' => __('admin.permissions'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="card">
        <div class="card-header">
            <h4>{!! __('admin.list_permission', ['default' => 'List of groups that use perms']) !!} {{ $permission->name }}</h4>
        </div>
        <div class="card-body">
            <ul class="list-group">
                @foreach ($permission->groups()->get() as $group)
                    <a href="{{ route('groups.edit', ['group' => $group->id]) }}">
                        <li class="list-group-item">{{ $group->name }}</li>
                    </a>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
