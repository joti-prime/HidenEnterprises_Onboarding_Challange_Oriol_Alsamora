@extends(AdminTheme::wrapper(), ['title' =>  __('admin.payments'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
<link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/bootstrap-daterangepicker/daterangepicker.css')) }}">
<link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}" />
<link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">
@endsection

@section('js_libraries')
<script src="{{ asset(AdminTheme::assets('modules/bootstrap-daterangepicker/daterangepicker.js')) }}"></script>
<script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
<script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
@endsection

@section('container')

<div class="row">
		<div class="col-12 col-md-12 col-lg-12">
            <form action="{{ route('payments.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
			<div class="card">
				<div class="card-header">
					<h4>{!! __('admin.create_payment', ['default' => 'Create Payment']) !!}</h4>
				</div>
				<div class="card-body">
					<div class="row">

                        <div class="form-group col-md-12 col-12">
                            <label for="user">{!! __('admin.user') !!}</label>
                            <select class="form-control select2 select2-hidden-accessible" name="user_id" tabindex="-1" aria-hidden="true">
                            @foreach (User::get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->username }} ({{ $user->email }})</option>
                            @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-12 col-12">
                            <label for="user">{!! __('admin.status') !!}</label>
                            <select class="form-control select2 select2-hidden-accessible" name="status" tabindex="-1" aria-hidden="true">
                                <option value="paid">{!! __('admin.paid') !!}</option>
                                <option value="unpaid" selected="">{!! __('admin.unpaid') !!}</option>
                                <option value="refunded">{!! __('admin.refund') !!}</option>
                            </select>
                        </div>

						<div class="form-group col-md-12 col-12">
							<label>{!! __('admin.payment_description', ['default' => 'Payment Description']) !!}</label>
							<input type="text" class="form-control" name="description" placeholder="{!! __('admin.description') !!}" required="">
						</div>

                        <div class="form-group col-md-4 col-12">
                            <label for="amount">{!! __('admin.amount') !!}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        {{ currency('symbol') }}
                                    </div>
                                </div>
                                <input type="number" class="form-control" name="amount" value="5.00" min="0.01" step="0.01" max="999" required="" />
                            </div>
                        </div>

                        <div class="form-group col-md-4 col-12">
                            <label for="currency">{!! __('admin.currency') !!}</label>
                            <input type="text" class="form-control" value="USD" disabled="">
                        </div>

                        <div class="form-group col-md-4 col-12">
                            <label for="coupon">{!! __('admin.coupon') !!} {!! __('admin.optional') !!}</label>
                            <input type="text" class="form-control" name="coupon" placeholder="{!! __('admin.coupon') !!}">
                        </div>

                        <div class="orm-group col-md-4 col-12">
                            <div class="control-label">{!! __('admin.payment_enable_due_date', ['default' => 'Enable due date']) !!}</div>
                            <label class="custom-switch mt-2">
                                <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input" value="1"/>
                                <span class="custom-switch-indicator"></span>
                                <span class="custom-switch-description">
                                    {!! __('admin.payment_enable_due_date_desc', ['default' => 'Check this option to set a date before the invoice must be paid']) !!}
                                </span>
                            </label>
                        </div>

                        <div class="form-group col-md-8 col-12">
                            <label>{!! __('admin.due_date', ['default' => 'Due date']) !!}</label>
                            <input type="text" class="form-control datetimepicker" name="due_date" value="{{ Carbon::now()->addMonth(1) }}">
                        </div>

                        <div class="form-group col-md-12 col-12">
                            <label>{!! __('admin.notes') !!} {!! __('admin.optional') !!}</label>
                            <textarea type="text" class="form-control" rows="4" name="notes"></textarea>
                            <div class="custom-control custom-checkbox mt-4">
                                <input type="checkbox" class="custom-control-input" id="send_email" name="send_email" value="1" checked="">
                                <label class="custom-control-label" for="send_email">
                                    {!! __('admin.payment_notes_desc', ['default' => 'Email invoice to user']) !!}
                                </label>
                            </div>
                        </div>
					</div>
					</div>
					<div class="card-footer text-right">
						<button class="btn btn-dark" type="submit">{!! __('admin.create') !!}</button>
					</div>
				</div>
            </form>
		</div>
	</div>
@endsection
