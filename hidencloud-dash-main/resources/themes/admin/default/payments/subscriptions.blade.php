@extends(AdminTheme::wrapper(), ['title' => __('admin.payments'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <section class="section">
        <div class="section-body">
            <div class="col-12 mb-4">
                <div class="card mb-0">
                    <div class="card-body">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link @if($status == 'paid') active @endif"
                                   href="{{ route('payments.subscriptions', ['status' => 'paid']) }}">{!! __('admin.paid') !!}
                                    <span
                                        class="badge @if($status == 'paid') badge-white @else badge-primary @endif">{{ Payment::where('type', 'subscription')->whereStatus('paid')->count() }}</span></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if($status == 'unpaid') active @endif"
                                   href="{{ route('payments.subscriptions', ['status' => 'unpaid']) }}">{!! __('admin.unpaid') !!}
                                    <span
                                        class="badge @if($status == 'unpaid') badge-white @else badge-primary @endif">{{ Payment::where('type', 'subscription')->whereStatus('unpaid')->count() }}</span></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if($status == 'refunded') active @endif"
                                   href="{{ route('payments.subscriptions', ['status' => 'refunded']) }}">{!! __('admin.refunded') !!}
                                    <span
                                        class="badge @if($status == 'refunded') badge-white @else badge-primary @endif">{{ Payment::where('type', 'subscription')->whereStatus('refunded')->count() }}</span></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{!! __('admin.payments') !!}</h4>
                        <div class="card-header-action">
                            <a href="{{ route('payments.create') }}" class="btn btn-icon icon-left btn-primary"><i
                                    class="fas fa-solid fa-plus"></i> {!! __('admin.create') !!}</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-md">
                                <tbody>
                                <tr>
                                    <th>{!! __('admin.id') !!}</th>
                                    <th>{!! __('admin.user') !!}</th>
                                    <th>{!! __('admin.description') !!}</th>
                                    <th>{!! __('admin.amount') !!}</th>
                                    <th>{!! __('admin.type') !!}</th>
                                    <th>{!! __('admin.status') !!}</th>
                                    <th>{!! __('admin.last_updated') !!}</th>
                                    <th>{!! __('admin.create_at') !!}</th>
                                    <th class="text-right">{!! __('admin.actions') !!}</th>
                                </tr>

                                @foreach($payments as $payment)
                                    <tr>
                                        <td>{{ Str::substr($payment->id, 0, 8) }}</td>
                                        <td>
                                            <a href="{{ route('users.edit', ['user' => $payment->user->id]) }}"
                                               style="display: flex; color: #6c757d">
                                                <img alt="image" src="{{ $payment->user->avatar() }}"
                                                     class="rounded-circle mr-1 mt-1" width="32px" height="32px"
                                                     data-toggle="tooltip" title=""
                                                     data-original-title="{{ $payment->user->first_name }} {{ $payment->user->last_name }}">
                                                <div class="flex">
                                                    {{ $payment->user->username }} <br>
                                                    <small>{{ $payment->user->email }}</small>
                                                </div>
                                            </a>
                                        </td>

                                        <td>{{ $payment->description }}</td>

                                        <td>{{ price($payment->amount) }}</td>

                                        <td>{{ $payment->type }}</td>

                                        <td>
                                            <div class="@if($payment->status == 'paid') badge badge-success
                                            @elseif($payment->status == 'unpaid') badge badge-danger @endif">
                                                {!! __('admin.' . $payment->status) !!}
                                            </div>
                                        </td>
                                        <td>{{ $payment->updated_at->translatedFormat(settings('date_format', 'd M Y')) }}</td>
                                        <td>{{ $payment->created_at->translatedFormat(settings('date_format', 'd M Y')) }}</td>

                                        <td class="text-right">
                                            <a href="{{ route('payments.edit', ['payment' => $payment->id]) }}"
                                               class="btn btn-primary">{!! __('admin.manage') !!}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        {{ $payments->links(AdminTheme::pagination()) }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
