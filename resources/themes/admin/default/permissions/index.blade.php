@extends(AdminTheme::wrapper(), ['title' => __('admin.permissions'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{!! __('admin.permissions') !!}</div>

                <div class="card-body">
                    <a href="{{ route('permissions.create') }}"
                       class="btn btn-primary">{!! __('admin.create_perms') !!}</a>
                    <a href="{{ route('permissions.import') }}"
                       class="btn btn-primary">{!! __('admin.import_perms') !!}</a>

                    <hr>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{!! __('admin.name') !!}</th>
                                <th>{!! __('admin.descriptions') !!}</th>
                                <th class="text-right">{!! __('admin.actions') !!}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->name }}</td>
                                    <td>{{ $permission->descriptions }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('permissions.show', $permission->id) }}"
                                           class="btn btn-success">{!! __('admin.show') !!}</a>
                                        <a href="{{ route('permissions.edit', $permission->id) }}"
                                           class="btn btn-primary">{!! __('admin.edit') !!}</a>
                                        <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST"
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
