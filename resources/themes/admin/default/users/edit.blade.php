@extends(AdminTheme::wrapper(), ['title' => __('admin.users'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">

@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
    <section class="section">
        <div class="section-body">
            <div class="col-12">
                @includeIf(AdminTheme::path('users.user_nav'))

                @if($user->punishments()->exists())
                @php
                    $activeBan = $user->punishments()
                        ->where('type', 'ban')
                        ->where(function ($q) {
                            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                        })
                        ->latest('created_at')
                        ->first();
                @endphp
                <div class="alert alert-dark alert-has-icon">
                    <div class="alert-body">
                        <div class="alert-title">
                            {{ __('admin.punishments_on_record') }}
                            @if($activeBan)
                                <span class="badge badge-danger ml-2">Ban</span>
                            @endif
                        </div>
                        {{ __('admin.history_of_punishments', ['number' => $user->punishments()->count()]) }}
                    </div>
                </div>
            @endif

            @if($user->status == 'pending')
                   <div class="alert alert-warning alert-has-icon">
                       <div class="alert-icon"><i class="far fa-lightbulb"></i></div>
                       <div class="alert-body">
                           <div
                               class="alert-title">Restricted Account | Cuenta Restringida</div>
                               This account has been restricted, for more details please check the emails sent to the account. Do not reactivate the account unless you know what you are doing!<br>Esta cuenta ha sido restringida, para ver más detalles consulte los correos enviados a la cuenta. ¡No reactiva la cuenta a menos que sepa que está haciendo!
                           <a href="{{ route('admin.user.activate', ['user' => $user->id]) }}" class="btn btn-primary">
                               {!! __('admin.activate', ['default' => 'Activate']) !!}
                           </a>
                       </div>
                   </div>
               @endif


                @if(!$user->is_verified())
                    <div class="alert alert-warning alert-has-icon">
                        <div class="alert-icon"><i class="far fa-lightbulb"></i></div>
                        <div class="alert-body">
                            <div
                                class="alert-title">{!! __('admin.pending_email_verification', ['default' => 'Pending Email Verification']) !!}</div>
                            {!! __('admin.pending_email_verification_desc', ['default' => 'This user has not yet verified their email address']) !!}
                            <a href="{{ route('admin.user.verify', ['user' => $user->id]) }}" class="btn btn-primary">
                                {!! __('admin.manually_verify', ['default' => 'Manually Verify']) !!}
                            </a>
                        </div>
                    </div>
                @endif

            </div>
        </div>
        <div class="row mt-sm-4">
            <div class="col-12 col-md-12 col-lg-4">
                <div class="card profile-widget">
                    <div class="profile-widget-header">
                        <img alt="image" src="{{ $user->avatar() }}" class="rounded-circle profile-widget-picture" style="max-width: 100px; max-height: 100px;"/>
                        <div class="profile-widget-items">
                            <div class="profile-widget-item">
                                <div class="profile-widget-item-label">
                                    {!! __('admin.status') !!}
                                </div>
                                <div class="profile-widget-item-value">
                                    <a href="#" class="badge
                                        @if($user->status == 'active') badge-success
                                        @elseif($user->status == 'pending'
                                        OR $user->status == 'suspended') badge-warning
                                        @elseif($user->status == 'banned') badge-danger
                                        @else badge-primary @endif">{!! __('admin.'. $user->status) !!}
                                    </a>
                                </div>
                            </div>
                            <div class="profile-widget-item">
                                <div class="profile-widget-item-label">{!! __('admin.payments') !!}</div>
                                <div class="profile-widget-item-value">{{ $user->payments->count() }}</div>
                            </div>
                            <div class="profile-widget-item">
                                <div class="profile-widget-item-label">{!! __('admin.orders') !!}</div>
                                <div class="profile-widget-item-value">{{ $user->orders->count() }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="profile-widget-description">
                        <div class="profile-widget-name">
                            {{ $user->first_name }} {{ $user->last_name }}
                            <div class="text-muted d-inline font-weight-normal">
                                <div class="slash"></div>
                                {{ $user->email }}
                            </div>
                        </div>
                        {{ $user->address->company ?? '' }} <br>
                        {{ $user->address->address ?? ''}} {{ $user->address->address_2 ?? ''}} <br>
                        {{ $user->address->zip_code ?? ''}} {{ $user->address->city ?? ''}}
                        {{ $user->address->province ?? ''}} {{ $user->address->country ?? ''}}
                    </div>
                    <div class="card-footer text-left">
                        <a href="{{ route('admin.user.impersonate', ['user' => $user->id]) }}"
                            class="btn btn-icon icon-left btn-primary"><i class="fas fa-user"></i>
                             {!! __('admin.login_as_user', ['default' => 'Login as User']) !!}
                         </a>
                        <a href="{{ route('users.email-password-reset', ['user' => $user->id]) }}"
                           class="btn btn-icon icon-left btn-info"><i class="fas fa-envelope"></i>
                            {!! __('admin.email_password_reset', ['default' => 'Email Password Reset']) !!}
                        </a>
                    </div>
                </div>

                @if($user->has2FA())
                    <div class="card">
                        <div class="card-header">
                            <h4>{!! __('client.two_factor_authentication', ['default' => 'Two Factor Authentication']) !!}</h4>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('admin.user.2fa.disable', $user->id) }}"
                                class="btn btn-icon icon-left btn-danger"><i class="fas fa-qrcode"></i>
                                {!! __('client.disable_2fa', ['default' => 'Disable 2FA']) !!}
                             </a>
                        </div>
                    </div>
                @endif

                @if(Module::has('MultiAccountsChecker') && Module::isEnabled('MultiAccountsChecker'))
                    @php
                        // Count verification attempts from database instead of cache
                        $usedAttempts = \Modules\MultiAccountsChecker\Entities\DiditVerificationSession::where('user_id', $user->id)
                            ->whereIn('status', ['failed', 'completed'])
                            ->count();
                        $hasFailedGroup = $user->groups()->where('name', 'FailedVerification')->exists();
                        $hasVerifiedGroup = $user->groups()->where('name', 'UserVerified')->exists();
                    @endphp
                    <div class="card">
                        <div class="card-header">
                            <h4>Verification Attempts</h4>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">
                                @if($usedAttempts == 0)
                                    <span class="badge badge-success">{{ $usedAttempts }}/2 attempts used</span>
                                @elseif($usedAttempts == 1)
                                    <span class="badge badge-warning">{{ $usedAttempts }}/2 attempts used</span>
                                @else
                                    <span class="badge badge-danger">{{ $usedAttempts }}/2 attempts used</span>
                                @endif
                                @if($hasFailedGroup)
                                    <span class="badge badge-danger ml-2">Failed Verification</span>
                                @elseif($hasVerifiedGroup)
                                    <span class="badge badge-success ml-2">Approved Verification</span>
                                @endif
                            </p>
                            @if(!$hasVerifiedGroup)
                                <form action="{{ route('admin.multiaccounts.user.reset-verification-attempts', $user->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to reset verification attempts for this user?')">
                                    @csrf
                                    <button type="submit" class="btn btn-icon icon-left btn-warning">
                                        <i class="fas fa-redo"></i>
                                        Reset Verification Attempts
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('users.update-balance', ['user' => $user->id]) }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-8 col-8">
                                    <label>{!! __('admin.amount') !!}</label>
                                    <input type="number" class="form-control" name="amount" value="0" step="0.01" required="">
                                </div>
                                <div class="form-group col-md-4 col-4">
                                    <label>{!! __('admin.type') !!}</label>
                                    <select id="type" name="type" class="form-control">
                                        <option value="+">{!! __('admin.ADD') !!}</option>
                                        <option value="-">{!! __('admin.REMOVE') !!}</option>
                                        <option value="=">{!! __('admin.SET') !!}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-12 col-12">
                                    <label>{!! __('admin.description') !!} (optional)</label>
                                    <input type="text" class="form-control" name="description" placeholder="optional">
                                </div>
                                <div class="col-12" style="display: flex;justify-content: space-between;">
                                    <div class="profile-widget-name">
                                        {!! __('admin.current_balance', ['default' => 'Current Balance']) !!}:
                                        <strong>{{ price($user->balance) }}</strong>
                                    </div>
                                    <button class="btn btn-success" type="submit">
                                        {!! __('admin.update_balance', ['default' => 'Update Balance']) !!}
                                    </button>
                                </div>
                            </div>
                        </form>
                        <hr>
                        <div class="summary">
                            <div class="summary-item">
                                <h6>
                                    {!! __('admin.balance_history', ['default' => 'Balance History']) !!}
                                    <span
                                        class="text-muted">
                                        ({{ $user->balance_transactions->count() }} {!! __('admin.transactions', ['default' => 'transactions']) !!})
                                    </span>
                                </h6>
                                <ul class="list-unstyled list-unstyled-border">
                                    @foreach($user->balance_transactions()->latest()->paginate(5) as $transaction)
                                        <li class="media">
                                            <div class="media-body">
                                                <div class="media-title"><a href="#">{{ $transaction->description }}</a>
                                                </div>
                                                <div class="text-muted text-medium">
                                                    {!! __('admin.before_transaction', ['default' => 'Before transaction']) !!}
                                                    :
                                                    {{ price($transaction->balance_before_transaction) }}
                                                    <div class="bullet"></div>
                                                    {{ $transaction->created_at->diffForHumans() }}
                                                    <div class="bullet"></div>
                                                    <span class="@if($transaction->result == '+' ) text-success
                                                        @elseif($transaction->result == '-') text-danger
                                                        @else text-secondary @endif">
                                                        {{ $transaction->result }} {{ price($transaction->amount) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                {{ $user->balance_transactions()->latest()->paginate(5)->links(AdminTheme::pagination()) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="summary">

                            <div class="summary-item">
                                <h6>
                                    {!! __('admin.device_list', ['default' => 'Device List']) !!}
                                    <span
                                        class="text-muted">({{ $user->devices->count() }} {{ mb_strtolower(__('admin.items')) }})</span>
                                </h6>
                                <ul class="list-unstyled list-unstyled-border">

                                    @foreach ($user->devices()->latest()->paginate(5) as $device)
                                        <li class="media">
                                            @if ($device->device_name == 'Phone')
                                                <svg class="dark:text-white mr-2" fill="none" stroke="currentColor"
                                                     style="width: 2rem; height: 2rem"
                                                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                            @else
                                                <svg class="w-6 h-6 dark:text-white mr-2" fill="none"
                                                     stroke="currentColor" style="width: 2rem; height: 2rem"
                                                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                            @endif
                                            <div class="media-body">
                                                <div class="media-right"><a href="{{ route('admin.user.devices.destroy', ['user' => $user->id, 'device' => $device->id]) }}"
                                                                            class="btn btn-icon btn-sm btn-danger"><i
                                                            class="fas fa-trash-alt"></i></a> <a href="{{ route('admin.user.devices.revoke', ['user' => $user->id, 'device' => $device->id]) }}"
                                                                                                 class="btn btn-icon btn-sm btn-warning"><i
                                                            class="fas fa-recycle"></i></a></div>
                                                <div class="media-title"><a href="#">{{ $device->device_type }}</a>
                                                </div>
                                                <div class="text-muted text-small">@if(!$device->is_revoked)
                                                        <span
                                                            class="text-success">{!! __('admin.active', ['default' => 'active']) !!}</span>
                                                    @else
                                                        <span
                                                            class="text-danger">{!! __('admin.revoked', ['default' => 'revoked']) !!}</span>
                                                    @endif
                                                    <div class="bullet"></div>
                                                    <a href="#">{{ $device->ip_address }}</a>
                                                    <div
                                                        class="bullet"></div> {{ $device->last_login_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach

                                </ul>
                                {{ $user->devices()->latest()->paginate(5)->links(AdminTheme::pagination()) }}
                            </div>
                        </div>
                    </div>
                </div>

                @if(Route::has('affiliates.edit') && $user->affiliate()->exists())
                    <div class="card">
                        <div class="card-header">
                            <h4>Affiliate Information</h4>
                        </div>
                        <div class="card-body">
                            @php
                                $affiliate = $user->affiliate;
                            @endphp
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Affiliate Code</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="{{ $affiliate->code }}" readonly>
                                            <div class="input-group-append">
                                                <a href="{{ route('affiliates.edit', ['affiliate' => $affiliate->id]) }}" 
                                                   class="btn btn-primary">
                                                   Manage
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Balance</label>
                                        <input type="text" class="form-control" value="{{ price($affiliate->balance) }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Clicks</label>
                                        <input type="text" class="form-control" value="{{ $affiliate->clicks }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Commission (%)</label>
                                        <input type="text" class="form-control" value="{{ $affiliate->commission }}%" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Discount (%)</label>
                                        <input type="text" class="form-control" value="{{ $affiliate->discount }}%" readonly>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Total Invites</label>
                                        <input type="text" class="form-control" value="{{ $affiliate->invites()->count() }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($user->oauth()->exists())
                    <div class="card">
                        <div class="card-header">
                            <h4>OAuth Information</h4>
                        </div>
                        <div class="card-body">
                            @foreach($user->oauth as $oauth)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="text-capitalize mb-0">
                                            @if($oauth->driver == 'discord')
                                                <i class="fab fa-discord mr-1"></i>
                                            @elseif($oauth->driver == 'google')
                                                <i class="fab fa-google mr-1"></i>
                                            @else
                                                <i class="fas fa-link mr-1"></i>
                                            @endif
                                            {{ ucfirst($oauth->driver) }}
                                        </h6>
                                        <a href="{{ route('admin.user.oauth.unlink', ['user' => $user->id, 'oauth' => $oauth->id]) }}"
                                           class="btn btn-icon icon-left btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to unlink {{ ucfirst($oauth->driver) }} from this user?');">
                                            <i class="fas fa-unlink"></i> Unlink
                                        </a>
                                    </div>
                                    <div class="ml-3">
                                        @if($oauth->driver == 'discord')
                                            @if($oauth->data && isset($oauth->data->username))
                                                <p class="mb-1"><strong>Username:</strong> {{ $oauth->data->username }}@if(isset($oauth->data->discriminator) && $oauth->data->discriminator != '0')#{{ $oauth->data->discriminator }}@endif</p>
                                            @endif
                                            @if($oauth->data && isset($oauth->data->id))
                                                <p class="mb-1"><strong>Discord ID:</strong> {{ $oauth->data->id }}</p>
                                            @endif
                                        @elseif($oauth->driver == 'google')
                                            @if($oauth->email)
                                                <p class="mb-1"><strong>Email:</strong> {{ $oauth->email }}</p>
                                            @endif
                                        @else
                                            @if($oauth->email)
                                                <p class="mb-1"><strong>Email:</strong> {{ $oauth->email }}</p>
                                            @endif
                                        @endif
                                        <p class="mb-0 text-muted small">Connected: {{ $oauth->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                @if(!$loop->last)
                                    <hr>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
            <div class="col-12 col-md-12 col-lg-8">

                <div class="card">
                    <div class="card-header">
                        <h4>{!! __('admin.statistics', ['default' => 'Statistics']) !!}</h4>
                        <div class="card-header-action">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="summary">
                            <div class="summary-info">
                                <h4>{{ price($user->payments()->whereStatus('paid')->getAmountSum()) }}</h4>
                                <div class="text-muted">{{ $user->payments()->whereStatus('paid')->count() }}
                                    {!! __('admin.payments', ['default' => 'payments']) !!} {!! __('admin.on', ['default' => 'on']) !!} {{ $user->orders()->count() }} {{ mb_strtolower(__('admin.orders')) }}
                                </div>
                                <div class="d-block mt-2">
                                    <a href="{{ route('admin.user.orders', $user->id) }}">{!! __('admin.view_all', ['default' => 'View All']) !!}</a>
                                </div>
                            </div>
                            <div class="summary-item">
                                <h6>{!! __('admin.order_list', ['default' => 'Order List']) !!}
                                    <span
                                        class="text-muted">({{ $user->orders->count() }} {{ mb_strtolower(__('admin.items')) }})</span>
                                </h6>
                                <ul class="list-unstyled list-unstyled-border">
                                    @foreach($user->orders()->paginate(5) as $order)
                                        <li class="media">
                                            <a href="{{ route('orders.edit', $order->id) }}">
                                                <img class="mr-3 rounded" width="50"
                                                     src="{{ asset('storage/products/' . $order->package['icon']) }}"
                                                     alt="icon">
                                            </a>
                                            <div class="media-body">
                                                <div class="media-right">
                                                    {{ price($order->price()->renewal_price) }}
                                                    /
                                                    {{ $order->periodToHuman() }} -> UUID: {{ $order->external_id }} {{-- -> in {{ $order->due_date->diffInDays() }} days --}}
                                                </div>
                                                <div class="media-title"><a href="{{ route('orders.edit', $order->id) }}">{{ $order->name }}</a></div>
                                                <div class="text-muted text-small">{{ mb_strtolower(__('admin.' . $order->status)) }}
                                                    <div class="bullet"></div>
                                                    {{ $order->service }}
                                                    <div
                                                        class="bullet"></div> {{ $order->due_date->translatedFormat(settings('date_format', 'd M Y')) }}
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                {{$user->orders()->paginate(5)->links(AdminTheme::pagination()) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <form method="post" action="{{ route('users.update', ['user' => $user->id]) }}"
                          class="needs-validation" novalidate="">
                        @csrf
                        @method('put')
                        <div class="card-header">
                            <h4>{!! __('admin.edit_profile', ['default' => 'Edit Profile']) !!}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-12 col-12">
                                    <label>{!! __('admin.username') !!}</label>
                                    <input type="text" class="form-control" name="username"
                                           value="{{ $user->username }}" required/>
                                </div>
                                <div class="form-group col-md-6 col-12">
                                    <label>{!! __('admin.first_name', ['default' => 'First Name']) !!}</label>
                                    <input type="text" class="form-control" name="first_name"
                                           value="{{ $user->first_name }}"/>
                                </div>
                                <div class="form-group col-md-6 col-12">
                                    <label>{!! __('admin.last_name', ['default' => 'Last Name']) !!}</label>
                                    <input type="text" class="form-control" name="last_name"
                                           value="{{ $user->last_name }}"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12 col-12">
                                    <label>{!! __('admin.email') !!}</label>
                                    <input type="email" class="form-control" name="email" value="{{ $user->email }}"/>
                                    <div class="invalid-feedback">
                                        {!! __('admin.fill_in_email', ['default' => 'Please fill in the email']) !!}
                                    </div>
                                </div>

                                <div class="form-group col-md-12 col-12">
                                    <label>{!! __('admin.password') !!}</label>
                                    <input type="password" class="form-control" name="password"/>
                                    <div class="small mt-1">
                                        {!! __('admin.edit_password_desc', ['default' => 'Leave this field empty if you don\'t want to reset the password']) !!}
                                    </div>
                                </div>

                                <div class="form-group col-md-12 col-12">
                                    <label>{!! __('admin.status') !!}</label>
                                    <select class="form-control select2 select2-hidden-accessible " name="status"
                                            tabindex="-1" aria-hidden="true">
                                        <option value="active" @if($user->status == 'active') selected @endif>
                                            {!! __('admin.active') !!}
                                        </option>
                                        <option value="pending" @if($user->status == 'pending') selected @endif>
                                            {!! __('admin.pending') !!}
                                        </option>
                                        <option value="suspended" @if($user->status == 'suspended') selected @endif>
                                            {!! __('admin.suspended') !!}
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group col-md-12 col-12">
                                    <label>{!! __('admin.groups') !!}</label>
                                    <select
                                        class="form-control select2 select2-hidden-accessible  @error('groups') is-invalid @enderror"
                                        name="groups[]" multiple="" tabindex="-1" aria-hidden="true">
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}"
                                                    @if ($user->groups()->find($group->id)) selected @endif>{{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('groups')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12 col-12">
                                    <label>{!! __('admin.company_name', ['default' => 'Company Name']) !!} {!! __('admin.optional') !!}</label>
                                    <input type="text" class="form-control" name="company_name"
                                           value="{{ $user->address->company_name ?? '' }}"/>
                                </div>

                                <div class="form-group col-md-12 col-12">
                                    <label>{!! __('admin.street') !!}</label>
                                    <input type="text" class="form-control" name="address"
                                           value="{{ $user->address->address ?? ''}}"/>
                                </div>

                                <div class="form-group col-md-12 col-12">
                                    <label>{!! __('admin.street_2', ['default' => 'Street 2']) !!}</label>
                                    <input type="text" class="form-control" name="address_2"
                                           value="{{ $user->address->address_2 ?? '' }}"/>
                                </div>

                                <div class="form-group col-md-3 col-6">
                                    <label>{!! __('admin.zip_code', ['default' => 'Zip Code']) !!}</label>
                                    <input type="text" class="form-control" name="zip_code"
                                           value="{{ $user->address->zip_code ?? '' }}"/>
                                </div>

                                <div class="form-group col-md-3 col-6">
                                    <label>{!! __('admin.city') !!}</label>
                                    <input type="text" class="form-control" name="city"
                                           value="{{ $user->address->city ?? '' }}"/>
                                </div>

                                <div class="form-group col-md-3 col-6">
                                    <label>{!! __('admin.province_state', ['default' => 'Province / State']) !!}</label>
                                    <input type="text" class="form-control" name="region"
                                           value="{{ $user->address->region ?? '' }}"/>
                                </div>

                                <div class="form-group col-md-3 col-6">
                                    <label for="inputState">{!! __('admin.country') !!} *</label>
                                    <select id="inputState" name="country"
                                            class="form-control select2 select2-hidden-accessible">
                                        @foreach(config('utils.countries') as $key => $country)
                                            <option value="{{$key}}" @if($user->address->country == $key) selected @endif>{{ $country }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group mb-0 col-12">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="is_subscribed" class="custom-control-input"
                                               id="newsletter" @if($user->is_subscribed) checked @endif/>
                                        <label class="custom-control-label" for="newsletter">
                                            {!! __('admin.subscribe_to_newsletter', ['default' => 'Subscribe to newsletter']) !!}
                                        </label>
                                        <div class="text-muted form-text">
                                            {!! __('admin.subscribe_to_newsletter_desc', ['default' => 'This user will get new information about products, offers and promotions']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button class="btn btn-danger" type="button" onclick="deleteUser()">{!! __('admin.delete', ['default' => 'Delete']) !!}</button>
                            <button class="btn btn-success" type="submit">{!! __('admin.update_changes', ['default' => 'Update Changes']) !!}</button>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>{!! __('admin.upload_new_avatar', ['default' => 'Upload new Avatar']) !!}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.user.update-avatar', ['user' => $user->id]) }}" method="POST"
                              class="drop-zone-md" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="avatar" accept="image/*" required>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('admin.user.reset-avatar', ['user' => $user->id]) }}" class="btn btn-danger">
                            {!! __('admin.reset_avatar', ['default' => 'Reset Avatar']) !!}
                        </a>
                        <button class="btn btn-success"
                                type="submit">{!! __('admin.upload_avatar', ['default' => 'Upload Avatar']) !!}</button>
                    </div>
                    </form>
                </div>

            </div>
        </div>
        </div>
    </section>

<script>
    function deleteUser() {
        if (window.confirm('Are you sure you want to delete this user? All orders, payments and all other data belonging to the user will be deleted')) {
            window.location.href = "/admin/users/{{ $user->id }}/delete";
        } else {
            event.preventDefault();
        }
    }
</script>
@endsection
