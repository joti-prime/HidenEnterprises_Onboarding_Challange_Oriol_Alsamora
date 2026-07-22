@extends(AdminTheme::wrapper(), ['title' => __('admin.orders', ['default' => 'Orders']), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
    <link rel="stylesheet" href="{{ Theme::get('Default')->assets }}assets/modules/summernote/summernote-bs4.css">
    <link rel="stylesheet" href="{{ Theme::get('Default')->assets }}assets/modules/codemirror/lib/codemirror.css">
    <link rel="stylesheet" href="{{ Theme::get('Default')->assets }}assets/modules/codemirror/theme/duotone-dark.css">
    <link rel="stylesheet" href="{{ Theme::get('Default')->assets }}assets/modules/jquery-selectric/selectric.css">
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">
@endsection

@section('js_libraries')
    <script src="{{ Theme::get('Default')->assets }}assets/modules/codemirror/lib/codemirror.js"></script>
    <script src="{{ Theme::get('Default')->assets }}assets/modules/codemirror/mode/javascript/javascript.js"></script>
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')

    @yield('outside-order-section')
    <section class="section">
        <div class="section-body">

            @if($order_errors->count() !== 0)
                <div class="alert alert-danger alert-dismissible show fade">
                    <div class="alert-body">
                        {!!  __('admin.try_again_order_desc', ['default' =>
                         ' We faced some errors whilst attempting to create this server. Click "Try Again" to attempt to create the server again.']) !!}
                        <a href="{{ route('orders.try-again', $order->id) }}" class="btn btn-sm btn-info mr-2">
                            {!!  __('admin.try_again', ['default' => 'Try Again']) !!}
                        </a>
                    </div>
                </div>
            @endif

            @foreach($order_errors as $error)
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert">
                                <span>×</span>
                            </button>
                            {{ $error->message }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="row mt-sm-4">
            <div class="col-12 col-md-12 col-lg-4">
                <div class="card profile-widget">
                    <div class="profile-widget-header">
                        <img alt="image" src="{{ asset('storage/products/' . $order->package['icon']) }}"
                             class="rounded-circle profile-widget-picture"/>
                        <div class="profile-widget-items">
                            <div class="profile-widget-item">
                                <div
                                    class="profile-widget-item-label">{!!  __('admin.status', ['default' => 'Status']) !!}</div>
                                <div class="profile-widget-item-value">
                                    <a href="#" class="badge
                                        @if($order->status == 'active') badge-success @elseif($order->status == 'suspended') badge-warning @elseif($order->status == 'cancelled' OR $order->status == 'terminated') badge-danger
                                        @else badge-primary @endif">
                                        {!!  __('admin.' . $order->status, ['default' => $order->status]) !!}
                                    </a>
                                </div>
                            </div>
                            <div class="profile-widget-item">
                                <div
                                    class="profile-widget-item-label">{!!  __('admin.price', ['default' => 'Price']) !!}</div>
                                <div
                                    class="profile-widget-item-value">{{ price($order->price()->renewal_price) }}</span>
                                    / {!!  $order->periodToHuman() !!}</div>
                            </div>
                        </div>
                    </div>
                    <div class="profile-widget-description">
                        <div class="profile-widget-name">
                            {{ $order->name }}
                            <div class="text-muted d-inline font-weight-normal">
                                <div class="slash"></div>
                                {{ $order->service }}
                            </div>
                        </div>

                        <a href="{{ route('users.edit', ['user' => $order->user->id]) }}"
                            style="display: flex; color: #6c757d">
                             <img alt="image" src="{{ $order->user->avatar() }}"
                                  class="rounded-circle mr-2 mt-1" width="48px" height="48px"
                                  data-toggle="tooltip" title="" data-original-title="
                                 {{ $order->user->first_name }} {{ $order->user->last_name }}">
                             <p class="flex">
                                 {{ $order->user->username }} <br>
                                 <small>{{ $order->user->email }}</small>
                             </p>
                         </a>
                    </div>
                    <div class="card-footer text-left">
                        <div class="row">
                            <div class="col-6 mb-2 d-flex">
                                <button type="button" data-toggle="modal" data-target="#extendDateModal"
                                        class="btn btn-icon icon-left btn-primary w-100"><i
                                        class="fas fa-solid fa-calendar-days"></i> {!! __('admin.extend_date', ['default' => 'Extend Date']) !!}
                                </button>
                            </div>
                            <div class="col-6 mb-2 d-flex">
                                <button type="button" data-toggle="modal" data-target="#cancelServiceModal"
                                        class="btn btn-icon icon-left btn-warning w-100"><i
                                        class="fa-solid fa-xmark"></i> {!! __('admin.cancel', ['default' => 'Cancel']) !!}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <a href="{{ route('service', ['order' => $order->id, 'page' => 'manage']) }}"
                            class="btn btn-icon icon-left btn-primary mr-2 mb-3"><i class="fas fa-solid fa-up-right-from-square"></i>
                            Manage
                        </a>
                        <br>
                        <a href="{{ route('orders.action', ['order' => $order->id, 'action' => 'unsuspend']) }}"
                            class="btn btn-icon icon-left btn-info mr-2"><i class="fas fa-solid fa-rotate-left"></i>
                            {!! __('admin.unsuspend', ['default' => 'Unsuspend']) !!}
                        </a>
                        <a href="{{ route('orders.action', ['order' => $order->id, 'action' => 'suspend']) }}"
                            class="btn btn-icon icon-left btn-warning  mr-2"><i class="fas fa-solid fa-ban"></i>
                            {!! __('admin.suspend', ['default' => 'Suspend']) !!}
                        </a>
                        <a onclick="terminate(event)"
                            href="{{ route('orders.action', ['order' => $order->id, 'action' => 'terminate']) }}"
                            class="btn btn-icon icon-left btn-danger mr-2"><i class="fas fa-solid fa-trash-can"></i>
                            {!! __('admin.terminate', ['default' => 'Terminate']) !!}
                        </a>
                        <br>
                        <a href="{{ route('orders.action', ['order' => $order->id, 'action' => 'force_suspend']) }}"
                            class="btn btn-icon icon-left btn-warning mr-2 mt-4 "><i class="fas fa-solid fa-ban"></i>
                             {!! __('admin.force_suspend', ['default' => 'Force Suspend']) !!}
                         </a>
                         <a onclick="terminate(event)"
                            href="{{ route('orders.action', ['order' => $order->id, 'action' => 'force_terminate']) }}"
                            class="btn btn-icon icon-left btn-danger mr-2  mt-4"><i class="fas fa-solid fa-trash-can"></i>
                             {!! __('admin.force_terminate', ['default' => 'Force Terminate']) !!}
                         </a>
                         @if($order->status === 'terminated' && (Auth::user()->isRootAdmin() || Auth::user()->hasPerm('orders.destroy')))
                            <a onclick="return confirm('Delete this order? All payments and data will be wiped.');"
                               href="{{ route('orders.destroy', $order->id) }}"
                               class="btn btn-icon icon-left btn-danger mr-2 mt-4"><i class="fas fa-solid fa-trash"></i>
                                Delete Order
                            </a>
                         @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>{!! __('admin.recent_payments', ['default' => 'Recent Payments']) !!}</h4>
                        <div class="card-header-action">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="summary">
                            <div class="summary-info">
                                <h4>{{ price($order->payments()->whereStatus('paid')->getAmountSum()) }}</h4>
                                <div class="text-muted">{{ $order->payments()->whereStatus('paid')->count() }}
                                    {!! __('admin.paid_payments', ['default' => 'Paid Payments']) !!}
                                </div>
                                <div class="d-block mt-2">
                                    <a href="#">{!! __('admin.view_all', ['default' => 'View All']) !!}</a>
                                </div>
                            </div>
                            <div class="summary-item">
                                <h6>{!! __('admin.paid_payments', ['default' => 'Paid Payments']) !!}</h6>
                                <ul class="list-unstyled list-unstyled-border">
                                    @foreach($order->payments()->latest()->paginate(8) as $payment)
                                        <li class="media">
                                            <div class="media-body">
                                                <div
                                                    class="media-right">{{ price($payment->amount) }}</span></div>
                                                <div class="media-title"><a href="{{ route('payments.edit', $payment->id) }}">{{ $payment->description }}</a>
                                                </div>
                                                <div
                                                    class="text-muted text-small">{!!  __('admin.' . $payment->status, ['default' => $payment->status]) !!}
                                                    <div
                                                        class="bullet"></div> {{ $order->created_at->translatedFormat(settings('date_format', 'd M Y')) }}
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                {{$order->payments()->latest()->paginate(8)->links(AdminTheme::pagination()) }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-12 col-md-12 col-lg-8">

                @if($order->status == 'cancelled')
                    <div class="alert alert-warning" role="alert">
                        {!! __('admin.service_cancelled', ['default' => 'This service was cancelled on the']) !!} <a
                            class="alert-link">@isset($order->cancelled_at)
                                {{ $order->cancelled_at->translatedFormat(settings('date_format', 'd M Y')) }}
                            @endisset</a> for: <code style="color: #454545;">@isset($order->cancel_reason)
                                {{ $order->cancel_reason }}
                            @else
                                {!! __('admin.no_reason', ['default' => 'No reason given']) !!}
                            @endisset</code>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link @if($active == 'details') active @endif" href="{{ route('orders.edit', $order->id) }}"><i class="fas fa-box"></i> Details</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if($active == 'price') active @endif" href="{{ route('orders.edit-price', $order->id) }}"><i class="fa-solid fa-tag"></i> Price</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if($active == 'service') active @endif" href="{{ route('orders.edit-service', $order->id) }}"><i class="fa-solid fa-server"></i> {{ ucfirst($order->service ?? '') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
                

                @yield('order-section')

            </div>
        </div>
        </div>
    </section>


    <!-- Extend Date Modal -->
    <div class="modal fade" id="extendDateModal" tabindex="-1" role="dialog" aria-labelledby="extendDateModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="extendDateModalLabel">
                        {!! __('admin.extend_date', ['default' => 'Extend Date']) !!} [{!! __('admin.order', ['default' => 'Order']) !!} #{{ $order->id }}]
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('orders.extend', ['order' => $order->id]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-12 col-12">
                                <label for="due_date">{!! __('admin.new_due_date', ['default' => 'New Due Date']) !!}</label>
                                <input type="date" class="form-control" name="new_due_date"
                                       value="{{ $order->due_date->translatedFormat('Y-m-d') }}" required="">
                                <small class="form-text text-muted">{!! __('admin.new_due_date_desc', ['default' => 'The current due date']) !!}
                                    is {{ $order->due_date }}</small>
                            </div>
                            <div class="form-group col-md-12 col-12">
                                <label>{!! __('admin.email', ['default' => 'Email']) !!} {!! __('admin.optional', ['default' => '(optional)']) !!}</label>
                                <textarea type="text" class="form-control" rows="4" name="email">Our team has updated the due date of your order. Find the details below.</textarea>
                                <small class="form-text text-muted">
                                    {!! __('admin.new_due_date_order_email_desc', ['default' => '
                                    Leave this field empty to not send the user an email
                                    notifying them of the new due date.
                                    ']) !!}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{!! __('admin.close', ['default' => 'Close']) !!}</button>
                        <button type="submit" class="btn btn-primary">{!! __('admin.update', ['default' => 'Update']) !!}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Extend Date Modal -->

    <!-- Cancel Service Modal -->
    <div class="modal fade" id="cancelServiceModal" tabindex="-1" role="dialog"
         aria-labelledby="cancelServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelServiceModalLabel">{!! __('admin.cancel_service', ['default' => 'Cancel Service']) !!} [{!! __('admin.order') !!} #{{ $order->id }}]</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('orders.cancel', ['order' => $order->id]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @if($order->price['cancellation_fee'] > 0)
                            <div class="alert alert-warning" role="alert">
                                {!! __('admin.cancel_service_modal_body', ['currency'=> currency('symbol'), 'price'=> price($order->price['cancellation_fee']),
                                'default' => '
                                This service has a cancellation fee of <a class="alert-link">:currency :price</a>
                                - The cancellation fee will be waved when you proceed with the cancellation below.
                                ']) !!}

                            </div>
                        @endif
                        <div class="row">
                            <div class="form-group col-md-12 col-12">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="customRadio1" name="cancelled_at"
                                           class="custom-control-input" value="end_of_term" checked>
                                    <label class="custom-control-label" for="customRadio1">
                                        {!! __('admin.cancel_at_end_of_term', ['default' => 'Cancel at end of term']) !!}
                                    </label>
                                    <small class="form-text text-muted mt-0">
                                        {!! __('admin.cancel_at_end_of_term_order_desc', ['date'=> $order->due_date->translatedFormat(settings('date_format', 'd M Y')),
                                        'default' => '
                                        The service will be cancelled gracefully at
                                        the due date: :date
                                        ']) !!}
                                    </small>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="customRadio2" name="cancelled_at"
                                           class="custom-control-input" value="immediately">
                                    <label class="custom-control-label" for="customRadio2">
                                        {!! __('admin.cancel_immediately', ['default' => 'Cancel immediately']) !!}
                                    </label>
                                    <small class="form-text text-muted mt-0">
                                        {!! __('admin.cancel_immediately_order_desc', ['default' =>
                                        'The service will be cancelled within 24 hours. <br>
                                        All files and data attached to your service will be deleted right away.
                                        ']) !!}

                                    </small>
                                </div>
                            </div>
                            <div class="form-group col-md-12 col-12">
                                <label>
                                    {!! __('admin.cancellation_reason', ['default' => 'Cancellation Reason']) !!} {!! __('admin.optional') !!}
                                </label>
                                <textarea type="text" class="form-control" rows="4" name="cancel_reason"></textarea>
                                <small class="form-text text-muted">
                                    {!! __('admin.cancellation_reason_order_desc', ['default' => 'The reason for the cancellation']) !!}

                                </small>
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label>{!! __('admin.email') !!} {!! __('admin.optional') !!}</label>
                                <textarea type="text" class="form-control" rows="4" name="email">Our team has cancelled your order. Find the details below.</textarea>
                                <small class="form-text text-muted">
                                    {!! __('admin.cancellation_order_email_desc', ['default' => '
                                    Leave this field empty to not send the user an email
                                    notifying them of the new due date.
                                    ']) !!}
                                    </small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{!! __('admin.close') !!}</button>
                        <button type="submit" class="btn btn-primary">{!! __('admin.update') !!}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Cancel Service Modal -->

    <script>
        function terminate(event) {
            if (window.confirm(
                '{!! __('admin.cancellation_confirm_order_warn', ['default' => 'You are about to terminate this service. Terminating may delete all data including files or servers on third party applications. This process cannot be undone.']) !!}'
                )) {
            } else {
                event.preventDefault();
            }
        }
    </script>

    <style>
        li.list-group-item {
            background: transparent !important;
        }
    </style>
@endsection
