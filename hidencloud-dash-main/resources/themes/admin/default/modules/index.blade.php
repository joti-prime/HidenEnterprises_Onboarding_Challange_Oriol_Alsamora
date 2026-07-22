@extends(AdminTheme::wrapper(), ['title' => __('admin.modules', ['default' => 'Modules']), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{!! __('admin.modules') !!}</div>

                <div class="card-body">
                    <hr>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{!! __('admin.name') !!}</th>
                                <th>{!! __('admin.version') !!}</th>
                                <th>{!! __('admin.status') !!}</th>
                                <th class="text-right">{!! __('admin.actions') !!}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach (Module::all() as $module)
                                @if(strpos($module->getPath(), '/Modules'))
                                    <tr>
                                        <td>{{ $module->getName() }}</td>
                                        <td>{{ config($module->getLowerName() . '.version', 'N/A') ?? 'N/A'}}</td>
                                        <td>
                                            @if ($module->isEnabled())
                                                <span class="badge badge-success">{!! __('admin.enabled') !!}</span>
                                            @else
                                                <span class="badge badge-danger">{!! __('admin.disabled') !!}</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if ($module->isEnabled())
                                                <a href="{{ route('modules.toggle', ['module' => $module->getName()]) }}"
                                                   class="btn btn-sm btn-warning">{!! __('admin.disable') !!}</a>
                                            @else
                                                <a href="{{ route('modules.toggle', ['module' => $module->getName()]) }}"
                                                   class="btn btn-sm btn-success">{!! __('admin.enable') !!}</a>
                                            @endif
                                            <button
                                                onclick="if (confirm('{{__('client.sure_you_want_delete')}}')) {window.location.href = '{{ route('modules.delete', ['module' => $module->getName()]) }}'}"
                                                class="btn btn-sm btn-danger m-2">{!! __('admin.delete') !!}</button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @includeIf(AdminTheme::path('marketplace.resources-card'))
@endsection
