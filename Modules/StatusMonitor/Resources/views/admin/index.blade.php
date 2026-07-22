@extends(AdminTheme::wrapper(), ['title' => 'Status Monitor', 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Status Monitor</span>
                    <a href="{{ route('admin.statusmonitor.create') }}" class="btn btn-primary">
                        + Add monitor
                    </a>
                </div>

                <div class="card-body">
                    @if ($monitors->isEmpty())
                        <p class="text-muted mb-0">
                            No monitors yet. Click "Add monitor" to start watching a website or host.
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Target</th>
                                    <th>Status</th>
                                    <th>Response time</th>
                                    <th>Last checked</th>
                                    <th>Enabled</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($monitors as $monitor)
                                    <tr>
                                        <td>{{ $monitor->name }}</td>
                                        <td>
                                            <span class="badge badge-secondary text-uppercase">{{ $monitor->check_type }}</span>
                                        </td>
                                        <td>
                                            {{ $monitor->target }}@if($monitor->check_type === 'tcp' && $monitor->port):{{ $monitor->port }}@endif
                                        </td>
                                        <td>
                                            @if ($monitor->last_status === 'up')
                                                <span class="badge badge-success">● Up</span>
                                            @elseif ($monitor->last_status === 'down')
                                                <span class="badge badge-danger">● Down</span>
                                                @if ($monitor->last_error)
                                                    <br><small class="text-muted">{{ $monitor->last_error }}</small>
                                                @endif
                                            @else
                                                <span class="badge badge-light">Not checked yet</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $monitor->last_response_time_ms !== null ? $monitor->last_response_time_ms . ' ms' : '—' }}
                                        </td>
                                        <td>
                                            {{ $monitor->last_checked_at ? $monitor->last_checked_at->diffForHumans() : 'Never' }}
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.statusmonitor.toggle', $monitor) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $monitor->is_enabled ? 'btn-success' : 'btn-outline-secondary' }}">
                                                    {{ $monitor->is_enabled ? 'Enabled' : 'Disabled' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-right">
                                            <form action="{{ route('admin.statusmonitor.check-now', $monitor) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                <button type="submit" class="btn btn-info btn-sm">Check now</button>
                                            </form>
                                            <a href="{{ route('admin.statusmonitor.edit', $monitor) }}" class="btn btn-primary btn-sm">Edit</a>
                                            <form action="{{ route('admin.statusmonitor.destroy', $monitor) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="return confirm('Delete this monitor?')" type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted mt-3 mb-0">
                            <small>Statuses refresh automatically every minute via the scheduler, or use "Check now" for an immediate check.</small>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
