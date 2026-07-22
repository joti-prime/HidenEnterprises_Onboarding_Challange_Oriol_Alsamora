@extends(AdminTheme::wrapper(), ['title' => __('admin.orders'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <section class="section">
        <div class="section-body">
            <div class="col-12">
                @includeIf(AdminTheme::path('users.user_nav'))
            </div>
        </div>

        <div class="col-12">
            <div>
                <div class="card">
                    <div class="card-header">
                        <h4>{!! __('admin.orders') !!}</h4>
                        <div class="card-header-action">
                            <a href="{{ route('orders.create', ['user' => $user->id]) }}" class="btn btn-icon icon-left btn-primary"><i
                                    class="fas fa-solid fa-plus"></i> {!! __('admin.create') !!}</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-md">
                                <tbody>
                                <tr>
                                    <th>{!! __('admin.id') !!}</th>
                                    <th>{!! __('admin.description') !!}</th>
                                    <th>{!! __('admin.user') !!}</th>
                                    <th>{!! __('admin.price') !!}</th>
                                    <th>{!! __('admin.service') !!}</th>
                                    <th>{!! __('admin.status') !!}</th>
                                    <th>{!! __('admin.create_at') !!}</th>
                                    <th class="text-right">{!! __('admin.actions') !!}</th>
                                </tr>

                                @foreach($user->orders()->get() as $order)
                                    <tr>
                                        <td>{{ Str::substr($order->id, 0, 8) }}</td>
                                        <td>
                                            <a href="{{ route('orders.edit', ['order' => $order->id]) }}"
                                               style="display: flex; color: #6c757d">
                                                <img alt="image"
                                                     src="{{ asset('storage/products/' . $order->package['icon']) }}"
                                                     class="mr-1 mt-1" style="border-radius: 5px" width="32px"
                                                     height="32px" data-toggle="tooltip" title=""
                                                     data-original-title="{{ $order->package['name'] }}">

                                                <div class="flex">
                                                    {{ $order->name }} <br>
                                                    <small>{{  $order->package['name'] }}</small>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('users.edit', ['user' => $order->user->id]) }}"
                                               style="display: flex; color: #6c757d">
                                                <img alt="image" src="{{ $order->user->avatar() }}"
                                                     class="rounded-circle mr-1 mt-1" width="32px" height="32px"
                                                     data-toggle="tooltip" title=""
                                                     data-original-title="{{ $order->user->first_name }} {{ $order->user->last_name }}">
                                                <div class="flex">
                                                    {{ $order->user->username }} <br>
                                                    <small>{{ $order->user->email }}</small>
                                                </div>
                                            </a>
                                        </td>
                                        <td>{{ price($order->price()->renewal_price) }}
                                            / {{ $order->periodToHuman() }}</td>
                                        <td>{{ $order->service }}</td>
                                        <td>
                                            <div class="flex align-items-center">
                                                <i class="fas fa-solid fa-circle @if($order->status == 'active') text-success
                                                @elseif($order->status == 'suspended') text-warning
                                                @elseif($order->status == 'cancelled'
                                                OR $order->status == 'terminated') text-danger @endif"
                                                   style="font-size: 11px;"></i> {!! __('admin.' . $order->status) !!}
                                            </div>
                                        </td>
                                        <td>
                                            {!! __('admin.created') !!}: {{ $order->created_at->translatedFormat(settings('date_format', 'd M Y')) }}
                                            <br>
                                            {!! __('admin.due_date') !!}: {{ $order->due_date->translatedFormat(settings('date_format', 'd M Y')) }}
                                            <br>
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('orders.edit', ['order' => $order->id]) }}"
                                               class="btn btn-primary">{!! __('admin.manage') !!}  </a></td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- <div class="card-footer text-right">
                      {{ $orders->links(AdminTheme::pagination()) }}
                    </div> --}}
                </div>
            </div>
        </div>
    </section>
@endsection
