@extends(AdminTheme::wrapper(), ['title' => __('admin.gateways'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{!! __('admin.gateways') !!}</div>

                <div class="card-body">
                    <a href="{{ route('gateways.create') }}"
                       class="btn btn-primary">{!! __('admin.create_gateway') !!}</a>
                    <hr>
                    @if($gateways->count() == 0)
                        @include(AdminTheme::path('empty-state'), ['title' => 'No records found', 'description' => 'You haven\'t setup any gateways yet'])
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>{!! __('admin.name') !!}</th>
                                    <th>{!! __('admin.type') !!}</th>
                                    <th>{!! __('admin.driver') !!}</th>
                                    <th>{!! __('admin.status') !!}</th>
                                    <th>{!!  __('admin.refund_support_title', ['default' => 'Refund Support']) !!}</th>
                                    <th class="text-right">{!! __('admin.actions') !!}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($gateways as $gateway)
                                    <tr>
                                        <td>{{ Str::replace('_', ' ', $gateway->name) }}</td>
                                        <td>{{ $gateway->type }}</td>
                                        <td>{{ $gateway->driver }}</td>
                                        <td>{{ $gateway->status ? __('admin.enabled') : __('admin.disabled') }}</td>
                                        <td>{{ $gateway->refund_support ? __('admin.yes') : __('admin.no') }}</td>
                                        <td class="text-right">
                                            @php($btn = $gateway->status ? 'warning' : 'primary')
                                            @php($default = $gateway->default ? 'primary' : 'warning')
                                            @if($gateway->config())
                                                <a href="{{ route('gateways.edit', $gateway->id) }}"
                                                   class="btn btn-primary btn-sm">{!! __('admin.edit') !!}</a>
                                            @endif

                                            <a href="{{ route('gateways.default', $gateway->id) }}"
                                               class="btn btn-{{ $default }} btn-sm">{{ __('admin.default') }}</a>


                                            <a href="{{ route('gateways.toggle', $gateway->id) }}"
                                               class="btn btn-{{ $btn }} btn-sm">{{ $gateway->status ? __('admin.disable') : __('admin.enable') }}</a>
                                            <form action="{{ route('gateways.destroy', $gateway->id) }}" method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('{!! __('admin.you_sure') !!}')">{!! __('admin.delete') !!}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">{!! __('admin.no_gateways') !!}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
