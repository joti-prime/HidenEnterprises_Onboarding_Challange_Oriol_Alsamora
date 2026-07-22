@extends(AdminTheme::wrapper(), ['title' => __('admin.groups', ['default' => 'Groups']), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{!! __('admin.groups') !!}</div>

                <div class="card-body">
                    <a href="{{ route('groups.create') }}" class="btn btn-primary">{!! __('admin.create_group') !!}</a>
                    <a href="{{ route('permissions.index') }}"
                       class="btn btn-primary">{!! __('admin.permissions') !!}</a>
                    <hr>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{!! __('admin.id') !!}</th>
                                <th>{!! __('admin.name') !!}</th>
                                <th class="text-right">{!! __('admin.actions') !!}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($groups as $group)
                                <tr>
                                    <td>{{ $group->id }}</td>
                                    <td>{{ $group->name }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('groups.users', $group->id) }}"
                                           class="btn btn-primary">{!! __('admin.users') !!}</a>
                                        <a href="{{ route('groups.edit', $group->id) }}"
                                           class="btn btn-primary">{!! __('admin.edit') !!}</a>

                                        <form action="{{ route('groups.destroy', $group->id) }}" method="POST"
                                              style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="deleteItem(event)" type="submit"
                                                    class="btn btn-danger">{!! __('admin.delete') !!}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
