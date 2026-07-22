@extends(AdminTheme::path('packages/edit/master'), ['title' => 'Package Links', 'tab' => 'links'])

@section('content')
<div>
    <div class="row">
        <div class="form-group col-12">
            <label>{{ __('admin.package_checkout') }}</label>
            <input type="text" class="form-control"
                   value="{{ route('store.package', $package->id) }}" readonly="">
            <small class="form-text text-muted">
                {{ __('admin.direct_link_to_the_checkout_page_on_your_application') }}
            </small>
        </div>
        <div class="form-group col-12">
            <label>{{ __('admin.package_process_payment') }}</label>
            <input type="text" class="form-control"
                   value="{{ route('payment.package', ['package' => $package->id, 'price_id' => '1', 'gateway' => '1']) }}"
                   readonly="">
            <small class="form-text text-muted">
                {{ __('admin.important_replace_price_id_with_the_id') }}
            </small>
        </div>
    </div>
</div>
@endsection