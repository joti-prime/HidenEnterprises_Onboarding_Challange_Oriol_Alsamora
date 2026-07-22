@extends(AdminTheme::wrapper(), ['title' => __('admin.logs'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}"/>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">

@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mb-6">
                <div class="card-body">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link @if(request()->input('severity', 'CRITICAL') == 'CRITICAL') active @endif"
                               href="{{ route('logs.index', ['severity' => 'CRITICAL']) }}">{!! __('admin.critical') !!}
                                <span
                                    class="badge @if(request()->input('severity', 'CRITICAL') == 'CRITICAL') badge-white @else badge-primary @endif ">
                                    {{ ErrorLog::where('severity', 'CRITICAL')->count() }}</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if(request()->input('severity') == 'ERROR') active @endif"
                               href="{{ route('logs.index', ['severity' => 'ERROR']) }}">{!! __('admin.error') !!} <span
                                    class="badge  badge-primary @if(request()->input('severity') == 'ERROR') badge-white @else badge-primary @endif ">
                                    {{ ErrorLog::where('severity', 'ERROR')->count() }}</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if(request()->input('severity') == 'WARNING') active @endif"
                               href="{{ route('logs.index', ['severity' => 'WARNING']) }}">{!! __('admin.warning') !!}
                                <span
                                    class="badge  @if(request()->input('severity') == 'WARNING') badge-white @else badge-primary @endif">
                                    {{ ErrorLog::where('severity', 'WARNING')->count() }}</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if(request()->input('severity') == 'INFO') active @endif"
                               href="{{ route('logs.index', ['severity' => 'INFO']) }}">{!! __('admin.info') !!} <span
                                    class="badge  @if(request()->input('severity') == 'INFO') badge-white @else badge-primary @endif ">
                                    {{ ErrorLog::where('severity', 'INFO')->count() }}</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if(request()->input('severity') == 'RESOLVED') active @endif"
                               href="{{ route('logs.index', ['severity' => 'RESOLVED']) }}">{!! __('admin.resolved') !!}
                                <span
                                    class="badge  @if(request()->input('severity') == 'RESOLVED') badge-white @else badge-primary @endif ">
                                    {{ ErrorLog::where('severity', 'RESOLVED')->count() }}</span></a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    @if(ErrorLog::count() == 0)
                        @include(AdminTheme::path('empty-state'), ['title' => 'We couldn\'t find any logs', 'description' => 'New logs will appear here'])
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>{!! __('admin.severity') !!}</th>
                                    <th>{!! __('admin.source') !!}</th>
                                    <th>{!! __('admin.message') !!}</th>
                                    <th>{!! __('admin.date') !!}</th>
                                    <th class="text-right">{!! __('admin.actions') !!}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td><span class="badge badge-secondary">{{ $log->severity }}</span></td>
                                        <td>{{ $log->source }}</td>
                                        <td>
                                            {!! Str::limit($log->message, 50) !!}
                                            <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1"
                                                 role="dialog"
                                                 aria-labelledby="logModalLabel{{ $log->id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title" id="logModalLabel{{ $log->id }}">
                                                                {{ $log->source }}
                                                            </h4>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="{{ __('admin.close') }}">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>

                                                        <div class="modal-body">
                                                            <strong> {!! nl2br($log->message)  !!} </strong>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                    data-dismiss="modal">{!! __('admin.close') !!}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{$log->created_at->format('d M Y') }}
                                            ({{ $log->created_at->diffForHumans() }})
                                        </td>
                                        <td class="text-right">
                                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                                    data-target="#logModal{{ $log->id }}">
                                                {!! __('admin.view') !!}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        {{ $logs->links(AdminTheme::pagination()) }}
                </div>
            </div>
        </div>
    </div>
@endsection
