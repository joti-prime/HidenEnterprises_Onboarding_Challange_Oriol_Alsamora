@extends(AdminTheme::path('orders.edit.wrapper'), ['active' => 'price'])

@section('outside-order-section')
{{-- Create Price Modifier Modal --}}  
<div class="modal fade" id="createPriceModifierModal" tabindex="-1" aria-labelledby="createPriceModifierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{ route('orders.price-modifiers.create', $order->id) }}" method="POST">
            @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="createPriceModifierModalLabel">Create Price Modifier</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" name="description" class="form-control" id="description" value="" placeholder="Description" required>
            </div>
            <div class="form-group">
                <label for="value">Value</label>
                <input type="text" name="value" class="form-control" id="value_create" value="" placeholder="Value (optional)">
            </div>
            <div class="form-group">
                <label for="base_price">Base Price</label>
                <input type="text" name="base_price" class="form-control" id="base_price" value="0" placeholder="Base Price">
            </div>
            <div class="form-group">
                <label for="monthly_price">Price @ 30 days</label>
                <input type="text" name="monthly_price" class="form-control" id="monthly_price" value="" placeholder="Price @ 30 days">
            </div>
            <div class="form-group">
                <label for="cancellation_fee">Cancellation Fee</label>
                <input type="text" name="cancellation_fee" class="form-control" id="cancellation_fee" value="0" placeholder="Cancellation Fee">
            </div>
            <div class="form-group">
                <label for="upgrade_fee">Upgrade Fee</label>
                <input type="text" name="upgrade_fee" class="form-control" id="upgrade_fee" value="0" placeholder="Upgrade Fee">
            </div>
            <div class="form-group">
                <label for="start_date">Starts at (Optional)</label>
                <input type="date" name="start_date" class="form-control" id="start_date" placeholder="Starts at">
            </div>
            <div class="form-group">
                <label for="end_date">Ends at (Optional)</label>
                <input type="date" name="end_date" class="form-control" id="end_date" placeholder="Ends at">
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Create Modifier</button>
        </div>
        </form>
      </div>
    </div>
  </div>
{{-- Create Price Modifier Modal End --}}

@foreach($order->priceModifiers()->withoutActiveModifierScope()->get() as $modifier)
{{-- Edit Price Modifier Modal --}}  
<div class="modal fade" id="editPriceModifierModal{{ $modifier->id }}" tabindex="-1" aria-labelledby="editPriceModifierModal{{ $modifier->id }}Label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{ route('orders.price-modifiers.update', ['order' => $order->id, 'modifier' => $modifier->id]) }}" method="POST">
            @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="editPriceModifierModal{{ $modifier->id }}Label">Update Price Modifier</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" name="description" class="form-control" id="description" value="{{ $modifier->description ?? '' }}" placeholder="Description" required>
            </div>
            <div class="form-group">
                <label for="key">Key</label>
                <input type="text" name="key" class="form-control" id="key" value="{{ $modifier->key ?? '' }}" placeholder="Key (optional)" readonly>
                <small class="form-text text-muted">Key cannot be modified after creation</small>
            </div>
            <div class="form-group">
                <label for="value">Value</label>
                <input type="text" name="value" class="form-control" id="value_{{ $modifier->id }}" value="{{ $modifier->value ?? '' }}" placeholder="Value (optional)">
                @if(in_array($modifier->key, ['memory_limit', 'disk_limit', 'backup_limit']))
                <div class="mt-2 p-2 bg-light border rounded">
                    <small class="text-muted mb-2 d-block"><strong>GB to MB Calculator:</strong></small>
                    <div class="row">
                        <div class="col-6">
                            <input type="number" class="form-control form-control-sm" id="gbCalc_{{ $modifier->id }}" placeholder="Enter GB" onchange="convertAndFill('{{ $modifier->id }}')">
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="convertAndFill('{{ $modifier->id }}')">Convert & Fill</button>
                        </div>
                    </div>
                    <small class="text-muted">Enter GB amount and click "Convert & Fill" to automatically set MB value above</small>
                </div>
                @endif
            </div>
            <div class="form-group">
                <label for="base_price">Base Price</label>
                <input type="text" name="base_price" class="form-control" id="base_price" value="{{ $modifier->base_price ?? 0 }}" placeholder="Base Price">
            </div>
            <div class="form-group">
                <label for="monthly_price">Price @ 30 days</label>
                <input type="text" name="monthly_price" class="form-control" id="monthly_price" value="{{ $modifier->daily_price * 30 ?? 0 }}" placeholder="Price @ 30 days">
            </div>
            <div class="form-group">
                <label for="cancellation_fee">Cancellation Fee</label>
                <input type="text" name="cancellation_fee" class="form-control" id="cancellation_fee" value="{{ $modifier->cancellation_fee ?? 0 }}" placeholder="Cancellation Fee">
            </div>
            <div class="form-group">
                <label for="upgrade_fee">Upgrade Fee</label>
                <input type="text" name="upgrade_fee" class="form-control" id="upgrade_fee" value="{{ $modifier->upgrade_fee ?? 0 }}" placeholder="Upgrade Fee">
            </div>
            <div class="form-group">
                <label for="start_date">Starts at (Optional)</label>
                <input type="date" name="start_date" class="form-control" id="start_date" @if($modifier->start_date) value="{{ $modifier->start_date->format('Y-m-d') }}" @endif placeholder="Starts at">
            </div>
            <div class="form-group">
                <label for="end_date">Ends at (Optional)</label>
                <input type="date" name="end_date" class="form-control" id="end_date" @if($modifier->end_date) value="{{ $modifier->end_date->format('Y-m-d') }}" @endif placeholder="Ends at">
            </div>
            <div class="form-group">
                <label class="custom-switch mt-2">
                    <input type="checkbox" name="is_active" value="1" class="custom-switch-input" @if($modifier->is_active) checked="" @endif>
                    <span class="custom-switch-indicator"></span>
                    <span class="custom-switch-description">
                        Is Active
                    </span>
                </label>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Update Modifier</button>
        </div>
        </form>
      </div>
    </div>
  </div>
{{-- Edit Price Modifier Modal End --}}
@endforeach

@endsection

@section('order-section')
<div class="card">
    <form action="{{ route('orders.update-price', $order->id) }}" method="POST">
        @csrf
    <div class="card-header">
        <h4>Price</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="form-group col-md-12 col-12">
                <label for="price[type]">{!! __('admin.type', ['default' => 'Type']) !!}</label>
                <select class="form-control select2 select2-hidden-accessible" name="price[type]" id="price[type]"tabindex="-1" aria-hidden="true">
                    <option value="recurring" @if($order->isRecurring()) selected="" @endif>{{ __('admin.recurring') }}</option>
                    <option value="one_time" @if(!$order->isRecurring()) selected="" @endif>{{ __('admin.one_time') }}</option>
                </select>
                <small class="form-text text-muted"></small>
            </div>
            <div class="col-md-12 col-12 @if(!$order->isRecurring()) d-none @endif" id="">
                <div class="form-group">
                    <label for="period">{{ __('admin.period') }}</label>
                    <select
                        class="form-control select2 select2-hidden-accessible hide"
                        id="period" name="price[period]" tabindex="-1"
                        aria-hidden="true">
                        <option value="1"
                                @if ($order->price['period'] == 1) selected @endif>
                            {{ __('admin.daily') }}
                        </option>
                        <option value="7"
                                @if ($order->price['period'] == 7) selected @endif>
                            {{ __('admin.weekly') }}
                        </option>
                        <option value="30"
                                @if ($order->price['period'] == 30) selected @endif>
                            {{ __('admin.monthly') }}
                        </option>
                        <option value="90"
                                @if ($order->price['period'] == 90) selected @endif>
                            {{ __('admin.quaterly') }}
                        </option>
                        <option value="365"
                                @if ($order->price['period'] == 365) selected @endif>
                            {{ __('admin.yearly') }}
                        </option>
                        <option value="730"
                                @if ($order->price['period'] == 730) selected @endif>
                            {!! __('admin.per_years', ['years' => 2]) !!}
                        </option>
                        <option value="1825"
                                @if ($order->price['period'] == 1825) selected @endif>
                            {!! __('admin.per_years', ['years' => 5]) !!}
                        </option>
                        <option value="3650"
                                @if ($order->price['period'] == 3650) selected @endif>
                            {!! __('admin.per_years', ['years' => 10]) !!}
                        </option>
                    </select>
                    <small class="form-text text-muted">The renewal period for this order</small>
                </div>
            </div>
            <div class="form-group col-md-12 col-12">
                <label>{!! __('admin.renewal_price') !!}</label>
                <div class="alert alert-warning" id="renewal-price-alert" style="display: none;">
                    <i class="fa fa-exclamation-triangle"></i> <strong>Attention:</strong> You have modified the renewal period. Make sure to also update the renewal price according to the package base price.
                </div>
                <input type="number" step="0.01" class="form-control" name="price[renewal_price]" id="renewal_price" value="{{ $order->price['renewal_price'] ?? 0 }}" required/>
                <small class="form-text text-muted">The renewal price the customer has to pay to renew the service</small>
            </div>
            <div class="form-group col-md-12 col-12">
                <label>{!! __('admin.upgrade_fee') !!}</label>
                <input type="number" step="0.01" class="form-control" name="price[upgrade_fee]" value="{{ $order->price['upgrade_fee'] ?? 0 }}" required/>
                <small class="form-text text-muted">The fee the user must pay in order to upgrade to a higher plan</small>
            </div>
            <div class="form-group col-md-12 col-12">
                <label>{!! __('admin.cancellation_fee') !!}</label>
                <input type="number" step="0.01" class="form-control" name="price[cancellation_fee]" value="{{ $order->price['cancellation_fee'] ?? 0 }}" required/>
                <small class="form-text text-muted">The fee the user must pay in order to cancel</small>
            </div>
            <div class="col-12">
                <a href="#more_price_settings" onclick="toggleMorePriceSettings()">Show Advanced Settings</a>
            </div>
            <div class="col-md-12 col-12 mt-4" id="more_price_settings" style="display: none;">
                <div class="form-group">
                    <label>{!! __('admin.price') !!}</label>
                    <input type="number" step="0.01" class="form-control" name="price[price]" value="{{ $order->price['price'] ?? 0 }}" required/>
                    <small class="form-text text-muted">The initial price user paid when they purchased the order</small>
                </div>
                <div class="form-group">
                    <label>{!! __('admin.setup_fee') !!}</label>
                    <input type="number" step="0.01" class="form-control" name="price[setup_fee]" value="{{ $order->price['setup_fee'] ?? 0 }}" required/>
                    <small class="form-text text-muted">The initial setup fee the user paid</small>
                </div>
            </div>
        </div>              
    </div>
    <div class="card-footer text-right">
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h4>Price Modifiers</h4>
        <div class="card-header-action">
            <form action="{{ route('orders.price-modifiers.apply', $order->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to apply these modifiers to the service? This will update the server configuration.')">
                @csrf
                <button type="submit" class="btn btn-success mr-2">Apply Modifiers</button>
            </form>
            <form action="{{ route('orders.reset-daily-limit', $order->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to reset the daily configurable options limit for this order?')">
                @csrf
                <button type="submit" class="btn btn-warning mr-2">Reset Daily Limit</button>
            </form>
            <button type="submit" class="btn btn-primary" data-toggle="modal" data-target="#createPriceModifierModal">Create Modifier</button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-md">
                <tbody>
                    <tr>
                        <th>Description</th>
                        <th>Value</th>
                        <th>Base Price</th>
                        <th>Recurring Price</th>
                        <th>Cancel Fee</th>
                        <th>Upgrade Fee</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    @foreach($order->priceModifiers()->withoutActiveModifierScope()->get() as $modifier)
                    <tr>
                        <td>{{ $modifier->description }}</td>
                        <td>{{ $modifier->key }} ({{ $modifier->value ?? 'N/A' }})</td>
                        <td>{{ price($modifier->base_price) }}</td>
                        <td>{{ price($modifier->daily_price * $order->price()->period) }}</td>
                        <td>{{ price($modifier->cancellation_fee) }}</td>
                        <td>{{ price($modifier->upgrade_fee) }}</td>
                        <td>
                            @if($modifier->isActive())
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-secondary" data-toggle="modal" data-target="#editPriceModifierModal{{ $modifier->id }}">Edit</button>
                            @if($modifier->value === null || $modifier->value === 'N/A')
                                <form action="{{ route('orders.price-modifiers.delete', ['order' => $order->id, 'modifier' => $modifier->id]) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this price modifier?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm ml-1">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>  
    </div>
</div>

<script>
    function toggleMorePriceSettings() {
        var morePriceSettings = document.getElementById('more_price_settings');
        if (morePriceSettings.style.display === 'none') {
            morePriceSettings.style.display = 'block';
        } else {
            morePriceSettings.style.display = 'none';
        }
    }

    // GB to MB Calculator for modals
    function convertAndFill(modifierId) {
        const gbValue = parseFloat(document.getElementById('gbCalc_' + modifierId).value);
        if (!isNaN(gbValue)) {
            const mbValue = gbValue * 1024;
            document.getElementById('value_' + modifierId).value = mbValue;
            
            // Visual feedback
            const valueField = document.getElementById('value_' + modifierId);
            valueField.style.backgroundColor = '#d4edda';
            setTimeout(function() {
                valueField.style.backgroundColor = '';
            }, 2000);
        }
    }


    // Auto-convert GB values in modifier value fields
    function autoConvertGBtoMB(input) {
        const value = input.value.trim();
        // Check if value ends with 'GB' or 'gb' (case insensitive)
        const gbMatch = value.match(/^(\d+(?:\.\d+)?)\s*(gb|GB)$/i);
        if (gbMatch) {
            const gbValue = parseFloat(gbMatch[1]);
            const mbValue = gbValue * 1024;
            input.value = mbValue;
            
            // Show a temporary indicator that conversion happened
            input.style.backgroundColor = '#d4edda';
            input.title = `Converted from ${gbValue}GB to ${mbValue}MB`;
            setTimeout(function() {
                input.style.backgroundColor = '';
            }, 3000);
        }
    }

    // Add event listeners when page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Add listeners to value input fields for auto-conversion
        const valueInputs = document.querySelectorAll('input[name="value"]');
        valueInputs.forEach(function(input) {
            input.addEventListener('blur', function() {
                autoConvertGBtoMB(this);
            });
        });

        // Period change detection
        const periodSelect = $('#period'); // Use jQuery for Select2
        const renewalPriceAlert = document.getElementById('renewal-price-alert');
        const renewalPriceInput = document.getElementById('renewal_price');
        let originalPeriod = periodSelect.length ? periodSelect.val() : null;

        if (periodSelect.length && renewalPriceAlert) {
            // Listen to Select2 change event
            periodSelect.on('change', function() {
                const currentValue = $(this).val();
                if (currentValue !== originalPeriod) {
                    renewalPriceAlert.style.display = 'block';
                    renewalPriceInput.style.borderColor = '#ffc107';
                    renewalPriceInput.style.boxShadow = '0 0 0 0.2rem rgba(255, 193, 7, 0.25)';
                }
            });

            // Hide alert when renewal price is modified
            renewalPriceInput.addEventListener('input', function() {
                renewalPriceAlert.style.display = 'none';
                renewalPriceInput.style.borderColor = '';
                renewalPriceInput.style.boxShadow = '';
            });
        }
    });
</script>
@endsection