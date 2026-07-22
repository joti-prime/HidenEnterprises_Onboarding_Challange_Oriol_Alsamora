@extends(AdminTheme::wrapper(), ['title' => __('admin.payments'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
    <link rel="stylesheet"
          href="{{ asset(AdminTheme::assets('modules/bootstrap-daterangepicker/daterangepicker.css')) }}">
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}"/>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">
@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/bootstrap-daterangepicker/daterangepicker.js')) }}"></script>
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
    <script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
@endsection

@section('container')
    <div class="row">
        <div class="col-12 col-md-12 col-lg-4">

            <form action="{{ route('payments.update', ['payment' => $payment->id]) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf

                <div class="card">
                    <div class="card-header">
                        <h4>{!! __('admin.payment_details', ['default' => 'Payment Details']) !!}</h4>
                    </div>
                    <div class="card-body">
                        <p>
                            <strong>{!! __('admin.id') !!}: </strong> {{ $payment->id }} <br>
                            <strong>{!! __('admin.status') !!}: </strong>{!! __('admin.' . $payment->status) !!}<br>
                            <strong>{!! __('admin.description') !!}: </strong> {{ $payment->description }} <br>
                            <strong>{!! __('admin.amount') !!}
                                : </strong> {{ price($payment->amount) }} <br>
                            @isset($payment->due_date)
                                <strong>{!! __('admin.due_date') !!}
                                    : </strong> {{ $payment->due_date->translatedFormat(settings('date_format', 'd M Y')) }}
                                ({{ $payment->due_date->diffForHumans() }}) <br>
                            @endisset
                            <strong>{!! __('admin.create_at') !!}
                                : </strong> {{ $payment->created_at->translatedFormat(settings('date_format', 'd M Y')) }}
                            ({{ $payment->created_at->diffForHumans() }})
                        </p>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('users.edit', ['user' => $payment->user->id]) }}"
                           class="btn btn-icon icon-left btn-dark"><i
                                class="fas fa-solid fa-user"></i>
                            {!! __('admin.user') !!}
                        </a>
                        @isset($payment->order_id)
                            <a href="{{ route('orders.edit', $payment->order_id) }}" class="btn btn-icon icon-left btn-dark"><i
                                    class="fas fa-solid fa-server"></i>
                                {!! __('admin.order') !!}
                            </a>
                        @endisset
                        <a href="{{ route('invoice', ['payment' => $payment->id]) }}" target="_blank"
                           class="btn btn-icon icon-left btn-dark"><i
                                class="fas fa-solid fa-file"></i>
                            {!! __('admin.invoice') !!}
                        </a>
                        @if($payment->status == 'unpaid')
                            <button type="button" data-toggle="modal" data-target="#CompletePaymentModal" class="btn btn-icon icon-left btn-dark"><i
                                    class="fas fa-solid fa-circle-check"></i>
                                {!! __('admin.complete_payment', ['default' => 'Complete Payment']) !!}
                            </button>
                        @endif
                    </div>
                </div>

                @if($payment->status == 'paid' OR $payment->status == 'refunded')
                    <div class="card">
                        <div class="card-header">
                            <h4>{!! __('admin.gateway_details', ['default' => 'Gateway Details']) !!}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-8 mb-3">
                                    <label
                                        for="currency">{!! __('admin.gateway_name', ['default' => 'Gateway Name']) !!}</label>
                                    <input type="text" class="form-control" value="{{ $payment->gateway['name'] ?? '' }}"
                                           disabled="">
                                </div>

                                <div class="col-4 mb-3">
                                    <label for="currency">{!! __('admin.type', ['default' => 'Type']) !!}</label>
                                    <input type="text" class="form-control" value="{{ $payment->gateway['type'] ?? '' }}"
                                           disabled="">
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="transaction_id">{!! __('admin.transaction_id', ['default' => 'Transaction ID']) !!}</label>
                                    <input type="text" class="form-control" name="transaction_id"
                                           value="{{ $payment->transaction_id }}" placeholder="{!! __('admin.transaction_id', ['default' => 'Transaction ID']) !!}">
                                </div>
                            </div>
                            <!-- Button trigger modal -->
                            <button type="button" class="btn btn-icon icon-left btn-warning" data-toggle="modal"
                                    data-target="#refundModal">
                                <i class="fa-solid fa-arrows-rotate"></i> {!! __('admin.refund') !!}
                            </button>
                        </div>
                    </div>
            @endif
        </div>
        <div class="col-12 col-md-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4>{!! __('admin.update_payment', ['default' => 'Update Payment']) !!}
                        <div class="ml-2
                            @if($payment->status == 'paid') badge badge-success
                            @elseif($payment->status == 'unpaid') badge badge-danger
                            @elseif($payment->status == 'refunded') badge badge-warning
                            @endif">
                            {!! __('admin.' . $payment->status) !!}
                        </div>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-12 col-12">
                            <label>{!! __('admin.payment_description', ['default' => 'Payment Description']) !!}</label>
                            <input type="text" class="form-control" name="description"
                                   value="{{ $payment->description }}" required="">
                        </div>

                        <div class="form-group col-md-4 col-12">
                            <label for="amount"> {!! __('admin.amount') !!}</label>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        {{ currency('symbol') }}
                                    </div>
                                </div>
                                <input type="number" class="form-control" name="amount"
                                       value="{{ number_format($payment->amount, 2) }}"
                                       required="" @if($payment->status == 'paid') @endif>
                            </div>
                        </div>

                        <div class="form-group col-md-4 col-12">
                            <label for="currency"> {!! __('admin.currency') !!}</label>
                            <input type="text" class="form-control" value="{{ $payment->currency }}" disabled="">
                        </div>

                        <div class="form-group col-md-4 col-12">
                            <label for="coupon">{!! __('admin.coupon') !!} {!! __('admin.optional') !!}</label>
                            <input type="text" class="form-control" name="coupon" value="{{ $payment->coupon }}"
                                   placeholder="TO DO" @if($payment->status == 'paid') disabled="" @endif>
                        </div>

                        {{--
                        <div class="form-group col-md-12 col-12">
                            <label for="user">{!! __('admin.user') !!}</label>
                            <select class="form-control select2 select2-hidden-accessible" name="user_id" tabindex="-1"
                                    aria-hidden="true">
                                @foreach (User::get() as $user)
                                    <option value="{{ $user->id }}"
                                            @if($payment->user->id == $user->id) selected="" @endif>{{ $user->username }}
                                        ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        --}}

                        @isset($payment->due_date)
                            <div class="form-group col-md-12 col-12">
                                <label>{!! __('admin.due_date') !!}</label>
                                <input type="text" class="form-control datetimepicker" name="due_date"
                                       value="{{ $payment->due_date }}">
                            </div>
                        @endisset

                        <div class="form-group col-md-12 col-12">
                            <label>{!! __('admin.notes') !!} {!! __('admin.optional') !!}</label>
                            <textarea type="text" class="form-control" rows="4"
                                      name="notes">{{ $payment->notes }}</textarea>
                        </div>
                    </div>

                </div>
                <div class="card-footer text-right">
                    <button class="btn btn-dark" type="submit">{!! __('admin.update') !!}</button>
                </div>
            </div>
            </form>
        </div>
    </div>

    <!-- Complete Payment Modal -->
    <div class="modal fade" id="CompletePaymentModal" tabindex="-1" role="dialog" aria-labelledby="CompletePaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="CompletePaymentModalLabel">Complete Payment</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <form action="{{ route('payments.complete', $payment->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>You can manually complete payments that are unpaid. On completion, the application will attempt to mark the payment as complete and run the program the payment was created for.</p>

                    <div class="form-group">
                        <label for="user">{!! __('client.payment_method') !!}</label>
                        <select class="form-control select2 select2-hidden-accessible" name="gateway" tabindex="-1"
                                aria-hidden="true">
                            @foreach (App\Models\Gateways\Gateway::getActive('subscription') as $gateway)
                                <option value="{{ $gateway->id }}" @if(isset($payment->gateway['id']) AND $payment->gateway['id'] == $gateway->id) selected @endif>{{ $gateway->name }} ({!! __('client.subscription') !!})</option>
                            @endforeach
                            @foreach (App\Models\Gateways\Gateway::getActive() as $gateway)
                                <option value="{{ $gateway->id }}" @if(isset($payment->gateway['id']) AND $payment->gateway['id'] == $gateway->id) selected @endif>{{ $gateway->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Transaction ID (optional)</label>
                        <input type="text" class="form-control" name="transaction_id" value="">
                    </div>
                </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Complete Payment</button>
                </div>
            </form>
        </div>
        </div>
    </div>

    @if($payment->status == 'paid' OR $payment->status == 'refunded')
        <!-- Refund Modal -->
        <div class="modal fade" id="refundModal" tabindex="-1" role="dialog" aria-labelledby="refundModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="refundModalLabel">{!! __('admin.refund_payment', ['default' => 'Refund Payment']) !!}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('payments.refund', ['payment' => $payment->id]) }}" method="post">
                        @csrf
                        <div class="modal-body">
                            @if(!isset($payment->gateway['refund_support']) OR !$payment->gateway['refund_support'])
                                <div class="alert alert-warning">
                                    {!! __('admin.refund_payment_not_support', ['default' => 'This gateway does not support refunds']) !!}
                                </div>

                            @elseif($payment->status == 'refunded')
                                <div class="alert alert-info">
                                    {!! __('admin.refund_payment_exist', ['default' => 'This payment was already refunded']) !!}
                                </div>
                            @elseif($payment->status == 'paid')
                                <div class="form-group">
                                    <label>{!! __('admin.refund_amount', ['default' => 'Refund Amount']) !!}</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                {{ currency('symbol') }}
                                            </div>
                                        </div>
                                        <input type="number" name="amount" class="form-control currency"
                                               value="{{ number_format($payment->amount, 2) }}" min="0.01"
                                               max="{{ $payment->amount }}" step="0.01">
                                    </div>
                                </div>

                                @isset($payment->order_id)
                                    @php
                                        $order = \App\Models\Order::find($payment->order_id);
                                        $isWithinRefundPeriod = false;
                                        $daysFromCreation = 0;
                                        if($order) {
                                            $daysFromCreation = $order->created_at->diffInDays(now());
                                            $isWithinRefundPeriod = $daysFromCreation <= 3;
                                        }
                                    @endphp
                                    
                                    @if($isWithinRefundPeriod)
                                        <div class="alert alert-success mb-3">
                                            <i class="fas fa-check-circle"></i> This order is within the 3-day refund period ({{ $daysFromCreation }} days old)
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-3">
                                            <i class="fas fa-exclamation-triangle"></i> This order is outside the 3-day refund period ({{ $daysFromCreation }} days old)
                                        </div>
                                    @endif
                                    
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="terminate_order" class="custom-control-input"
                                               id="terminateOrderCheck" value="1" checked>
                                        <label class="custom-control-label" for="terminateOrderCheck">
                                            Terminate Order
                                            <small class="text-muted d-block">This will immediately terminate the associated service</small>
                                        </label>
                                    </div>
                                @endisset
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{!! __('admin.close') !!}</button>
                            @if(isset($payment->gateway['refund_support']) AND $payment->gateway['refund_support'] AND $payment->status == 'paid')
                                <button type="submit" class="btn btn-primary">{!! __('admin.submit_refund', ['default' => 'Submit Refund']) !!}</button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        </div>
    @endif
@endsection
