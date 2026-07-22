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
    <section class="section">
        <div class="section-body">
            <div class="col-12">

            </div>
        </div>
        <div class="row mt-sm-4">
            <div class="col-12 col-md-12 col-lg-12">

            <form method="post" action="{{ route('orders.store') }}" class="needs-validation" novalidate="">
                @csrf
                <div class="card">
                        <div class="card-header">
                            <h4>{!!  __('admin.create', ['default' => 'Create']) !!}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <div class="form-group col-md-12 col-12">
                                    <label
                                        for="package_id">{!!  __('admin.package', ['default' => 'Package']) !!}</label>
                                    <select class="form-control select2 select2-hidden-accessible"
                                        onchange="setPackage(this.value)"
                                        name="package_id" id="package_id"
                                            tabindex="-1" aria-hidden="true">
                                        <option value="0"></option>
                                        @foreach (Package::get() as $packageList)
                                            @if($packageList->status == 'inactive')
                                                @continue;
                                            @endif
                                            <option value="{{ $packageList->id }}" @if(isset($package) AND $package->id == $packageList->id) selected @endif>{{ $packageList->name }}
                                                ({{ $packageList->service }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                @if(isset($package))
                                <div class="form-group col-md-12 col-12">
                                    <label for="price">{!!  __('admin.price', ['default' => 'Price']) !!}</label>
                                    <select class="form-control select2 select2-hidden-accessible" name="price"
                                            id="price" tabindex="-1" aria-hidden="true">
                                            @foreach ($package->prices as $price)
                                                @if(!$price->is_active)
                                                    @continue;
                                                @endif
                                                <option value="{{ $price->id }}">{{ price($price->renewal_price) }} @ {{ $price->periodToHuman() }}</option>
                                            @endforeach
                                    </select>
                                </div>
                                @endif

                                <div class="form-group col-md-12 col-12">
                                    <label for="user">{!!  __('admin.user', ['default' => 'User']) !!}</label>
                                    <select class="form-control select2 select2-hidden-accessible" name="user_id"
                                            tabindex="-1" aria-hidden="true">
                                        @foreach (User::get() as $user)
                                            <option value="{{ $user->id }}" @if(request()->get('user') == $user->id) selected @endif >{{ $user->username }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-12 col-12">
                                    <label for="price">{!!  __('admin.status', ['default' => 'Status']) !!}</label>
                                    <select class="form-control select2 select2-hidden-accessible" name="status"
                                            tabindex="-1" aria-hidden="true">
                                        <option value="active"
                                                selected>{!!  __('admin.active', ['default' => 'Active']) !!}</option>
                                        <option
                                            value="suspended">{!!  __('admin.suspended', ['default' => 'Suspended']) !!}</option>
                                        <option
                                            value="terminated">{!!  __('admin.terminated', ['default' => 'Terminated']) !!}</option>
                                    </select>
                                </div>

                                @if(isset($package) AND $package->require_domain)
                                <div class="form-group col-md-12 col-12">
                                    <label for="status">
                                        {!!  __('admin.domain', ['default' => 'Domain']) !!}
                                    </label>
                                    <input type="text" class="form-control" name="domain" value="" required
                                           placeholder="example.com"/>
                                    <small class="form-text text-muted"></small>
                                </div>
                                @endif

                                <div class="form-group col-md-6 col-12">
                                    <label
                                        for="status">{!!  __('admin.last_renewed_at', ['default' => 'Last Renewed At']) !!}</label>
                                    <input type="date" class="form-control" name="last_renewed_at"
                                           value="{{ Carbon::now()->translatedFormat('Y-m-d') }}" placeholder=""/>
                                    <small class="form-text text-muted"></small>
                                </div>

                                <div class="form-group col-md-6 col-12">
                                    <label for="status">{!!  __('admin.due_date', ['default' => 'Due Date']) !!}</label>
                                    <input type="date" class="form-control" name="due_date" value="" placeholder=""/>
                                    <small class="form-text text-muted"></small>
                                </div>

                                <div class="form-group col-md-12 col-12">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="create_instance" value="1"
                                               class="custom-control-input" id="customCheck1" checked>
                                        <label class="custom-control-label" for="customCheck1">
                                            {!!  __('admin.create_instance_package_service', ['default' => 'Create an instance of package service']) !!}
                                        </label>
                                    </div>
                                    <small>
                                        {!!  __('admin.create_instance_package_service_desc', ['default' =>
                                        'If this option is enabled, when the order is created it will also create an
                                        instance of the Package Service. For Example, if the package service
                                        is pterodactyl, when the order is created it will create a brand new pterodactyl
                                        server with it.'
                                        ]) !!}

                                    </small>
                                </div>

                                <div class="form-group col-md-12 col-12">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="notify_user" value="1" class="custom-control-input"
                                               id="notify_user" checked>
                                        <label class="custom-control-label" for="notify_user">
                                            {!!  __('admin.send_user_email', ['default' => 'Send user email']) !!}
                                        </label>
                                    </div>
                                    <small>
                                        {!!  __('admin.send_user_email_desc', ['default' =>
                                        'Check this option if you want to notify the user via email that a new order has
                                        been created for them.'
                                        ]) !!}

                                    </small>
                                </div>

                            </div>
                        </div>
                </div>

                @if(isset($package) AND $package->service()->hasCheckoutConfig($package))
                <div class="card">
                    <div class="card-body row">
                        @foreach($package->service()->getCheckoutConfig($package)->all() ?? [] as $name => $field)
                        <div class="form-group @isset($field['col']) {{$field['col']}} @else col-6 @endisset" style="display: flex;flex-direction: column;">
                            <label>{!! $field['name'] !!}</label>
                            @if($field['type'] == 'select')
                            <select class="form-control select2 select2-hidden-accessible" tabindex="-1" aria-hidden="true"
                            name="{{ $field['key'] }}"
                            id="{{ $field['key'] }}"
                            @if(isset($field['save_on_change']) AND $field['save_on_change']) onchange="saveServiceSettings()" @endif
                            @if(isset($field['multiple']) AND $field['multiple']) multiple @endif
                            >
                                @foreach($field['options'] ?? [] as $key => $option)
                                    <option value="{{ $key }}"
                                            @if(in_array($key, (array) getValueByKey($field['key'], $package->data, $field['default_value'] ?? ''))) selected @endif
                                    >{{ is_string($option) ? $option : $option['name'] }}</option>
                                @endforeach
                            </select>
                            @elseif($field['type'] == 'bool')
                            <label class="custom-switch mt-2">
                                <input type="hidden" name="{{ $field['key'] }}" value="0">
                                <input type="checkbox" name="{{ $field['key'] }}" @if(isset($field['save_on_change']) AND $field['save_on_change']) onchange="saveServiceSettings()" @endif value="1" class="custom-switch-input" @if($package->data($field['key'], $field['default_value'] ?? '')) checked @endif>
                                <span class="custom-switch-indicator"></span>
                              </label>
                            @else
                            <input class="form-control"
                            type="{{ $field['type'] }}"
                            name="{{ $field['key'] }}"
                            id="{{ $field['key'] }}"
                            @isset($field['min']) min="{{$field['min']}}" @endisset
                            @isset($field['max']) max="{{$field['max']}}" @endisset
                            @if(isset($field['save_on_change']) AND $field['save_on_change']) onchange="saveServiceSettings()" @endif
                            value="{{ $package->data($field['key'], $field['default_value'] ?? '') }}"
                            placeholder="@isset($field['placeholder']){{$field['placeholder']}} @else{{ $field['name'] }} @endisset"
                            @if(in_array('required', $field['rules'])) required="" @endif>
                            @endif
                            <small class="form-text text-muted">
                                {!! $field['description'] !!}
                            </small>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="card">
                    <div class="card-footer text-right">
                        <button class="btn btn-success" type="submit">{!!  __('admin.create', ['default' => 'Create']) !!}</button>
                    </div>
                </div>
            </form>

            </div>
        </div>
    </section>

    <script>
        function setPackage(id) {
            // redirect the user to the same page with package=ID in the request
            return location.href = '/admin/orders/create?package=' + id;
        }
    </script>
@endsection
