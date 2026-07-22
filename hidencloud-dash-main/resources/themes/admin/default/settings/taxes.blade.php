@extends(AdminTheme::wrapper(), ['title' => 'Taxes', 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}"/>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">

@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <form action="{{ route('admin.settings.store') }}" method="POST">
                    <div class="card-header">
                        <h4>Taxes</h4>
                    </div>
                    <div class="card-body">
                        @csrf
                        <div class="row">

                            <div class="form-group col-12">
                                <div class="control-label">
                                    Enable taxes at checkout
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('taxes', false)) /admin/settings/store?taxes=0 @else /admin/settings/store?taxes=1 @endif';">
                                    <input type="checkbox" name="taxes" value="1" class="custom-switch-input"
                                           @if(settings('taxes', false)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        Calculate taxes at checkout based on buyers country / region
                                    </span>
                                </label>
                            </div>

                            <div class="form-group col-12">
                                <div class="control-label">
                                    Add tax to price
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('tax_add_to_price', false)) /admin/settings/store?tax_add_to_price=0 @else /admin/settings/store?tax_add_to_price=1 @endif';">
                                    <input type="checkbox" name="tax_add_to_price" value="1" class="custom-switch-input"
                                           @if(settings('tax_add_to_price', false)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        Add tax rate to price or include in price
                                    </span>
                                </label>
                            </div>

                            <div class="form-group col-12">
                                <label
                                    for="tax_disabled_gateways[]">Disable tax for gateways</label>
                                <select class="form-control select2 select2-hidden-accessible"
                                        name="tax_disabled_gateways[]" tabindex="-1" aria-hidden="true" multiple>
                                    <option value="0">Remove Selection</option>
                                    @foreach(App\Models\Gateways\Gateway::get() as $gateway)
                                        <option value="{{ $gateway->id }}" @if(in_array($gateway->id, json_decode(settings('tax_disabled_gateways', '["0"]')))) selected @endif>{{ $gateway->name }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    Disable tax calculations for specific gateways in case your gateway manually calculates VAT
                                </small>
                            </div>

                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">{!! __('admin.submit') !!}</button>
                    </div>
                    <div class="table-responsive col-12">
                        <div class="alert alert-info col-12">
                            You can add / edit tax rates in file <code>config/tax.php</code> - Please make an "issue" on our <a target="_blank" style=" color: #0700a5;text-decoration: underline;" href="https://github.com/VertisanPRO/pterodactyl/issues">Github</a> to add new tax rates. Please include the name, standard rate % and country.
                        </div>
                        <table class="table table-striped table-md">
                            <tbody>
                                <tr>
                                    <th>Country</th>
                                    <th>Type</th>
                                    <th class="text-right">Rate</th>
                                </tr>
                                @foreach(config('tax.rates') as $rate)
                                <tr>
                                    <td>{{ $rate['country'] }}</td>
                                    <td>{{ $rate['vat_name'] }} ({{ $rate['vat_abbr'] }})</td>
                                    <td class="text-right">{{ $rate['standard_rate'] }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
            </div>
            </form>
        </div>
    </div>
    <style>
        span.select2.select2-container.select2-container--default {
            width: 100% !important;
        }
    </style>
@endsection
