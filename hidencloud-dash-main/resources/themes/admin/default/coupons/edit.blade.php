@extends(AdminTheme::wrapper(), ['title' => 'Coupons', 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">
@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
        <form action="{{ route('coupons.update', $coupon->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                        <div class="card-header">
                            <h4>{!!  __('admin.edit_coupon', ['default' => 'Edite Coupon']) !!}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-12 col-12">
                                    <label for="code">{!!  __('admin.coupon_code', ['default' => 'Coupon Code']) !!}</label>
                                    <input type="text" name="code" id="code"
                                        class="form-control @error('code') is-invalid @enderror" value="{{ $coupon->code }}"
                                        required>
                                    @error('code')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6 col-6">
                                    <label for="discount_amount">{!!  __('admin.discount_amount', ['default' => 'Discount Amount']) !!}</label>
                                    <div class="input-group mb-2">
                                        <input type="number" class="form-control text-right" name="discount_amount" id="discount_amount" value="{{ $coupon->discount_amount }}" min="0" required="">
                                        <div class="input-group-append">
                                            <div class="input-group-text" id="type">@if($coupon->discount_type == 'percentage') % @else {{ currency('symbol') }} @endif</div>
                                        </div>
                                        <small class="form-text text-muted"> </small>
                                    </div>
                                </div>

                                <div class="form-group col-md-6 col-6">
                                    <label for="discount_type">{!!  __('admin.discount_type', ['default' => 'Discount Type']) !!}</label>
                                    <select onchange="updateType()" class="form-control select2 select2-hidden-accessible" name="discount_type" id="discount_type" tabindex="-1" aria-hidden="true">
                                        <option value="percentage" @if($coupon->discount_type == 'percentage') selected @endif>{!!  __('admin.percentage', ['default' => 'Percentage %']) !!}</option>
                                        <option value="flat" @if($coupon->discount_type == 'flat') selected @endif>{!!  __('admin.flat', ['default' => 'Flat {{ currency("symbol") }}']) !!}</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6 col-6">
                                    <label for="allowed_uses">{!!  __('admin.allowed_users', ['default' => 'Allowed Uses']) !!}</label>
                                    <div class="input-group mb-2">
                                        <input type="number" class="form-control text-left" name="allowed_uses" id="allowed_uses" value="{{ $coupon->allowed_uses }}" min="0" required="">
                                        <small class="form-text text-muted"> </small>
                                    </div>
                                </div>

                                <div class="form-group col-md-6 col-6">
                                    <label for="expires_at">{!!  __('admin.expires_at', ['default' => 'Expires At']) !!}</label>
                                    <div class="input-group mb-2">
                                        <input type="date" class="form-control text-left" name="expires_at" id="expires_at" @isset($coupon->expires_at) value="{{ $coupon->expires_at->translatedFormat('Y-m-d') }}" @endisset>
                                        <small class="form-text text-muted">
                                            {!!  __('admin.expires_coupon_desc', ['default' => 'Leave this field empty if you do not want to set an expiration date for this coupon. If a expiration date is set, the coupon will be valid until that date.']) !!}
                                        </small>
                                    </div>
                                </div>

                                <div class="form-group col-md-12 col-12">
                                    <label for="applicable_products">{!!  __('admin.applicable_packages', ['default' => 'Applicable Packages']) !!}</label>
                                    <div class="input-group mb-2">
                                        <select name="applicable_products[]" id="applicable_products" class="form-control select2 select2-hidden-accessible"
                                            multiple="" tabindex="-1" aria-hidden="true">
                                            @foreach(Package::latest()->get() as $package)
                                                <option value="{{ $package->id }}" @if(in_array($package->id, $coupon->applicable_products)) selected @endif>{{ $package->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted text-primary"><a href="#" onclick="selectAllPackages()">Select All (after select a item from the menu to load all)</a></small>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button class="btn btn-dark" type="submit">{!! __('admin.update') !!}</button>
                        </div>
                </div>
            </form>
            </div>
            <style>
                span.select2.select2-container.select2-container--default {
                    width: 100% !important;
                }
            </style>
            <script>
            function selectAllPackages() {
                let select = document.getElementById("applicable_products");
                for (let option of select.options) {
                    option.selected = true;
                }
            }

            function updateType() {
                if(document.getElementById("discount_type").value == 'flat') {
                    document.getElementById("type").innerHTML = '{{ currency("symbol") }}';
                } else {
                    document.getElementById("type").innerHTML = '%';
                }
            }
            </script>
@endsection
