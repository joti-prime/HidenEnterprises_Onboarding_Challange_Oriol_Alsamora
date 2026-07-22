@extends(Theme::wrapper())

@section('title', __('Upgrade Configurable Options'))

@section('container')
<div class="mx-auto max-w-4xl py-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('Upgrade / Downgrade Server Resources') }}
            </h1>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ $order->name }}
            </span>
        </div>

        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        {{ __('How it works') }}
                    </h3>
                    <div class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                        <p>• <strong>Resource Upgrades:</strong> {{ __('Pay the difference and changes apply immediately') }}</p>
                        <p>• <strong>Resource Downgrades:</strong> {{ __('Apply immediately without refund') }}</p>
                        <p>• <strong>Package Change:</strong> Not available - only individual resource modifications are allowed</p>
                        <p>• <strong>Daily Limit:</strong> You can make <span class="font-semibold">{{ $remainingChanges }} more change(s)</span> today (max 2 per day)</p>
                    </div>
                </div>
            </div>
        </div>

        @if($remainingChanges == 0)
        <div class="mb-6 p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg border border-orange-200 dark:border-orange-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">
                        {{ __('Daily Limit Reached') }}
                    </h3>
                    <div class="mt-1 text-sm text-orange-700 dark:text-orange-300">
                        <p>You have reached the maximum of 2 configurable option changes for today. Please try again tomorrow.</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <form action="{{ route('configurable-options.upgrade', $order) }}" method="POST" id="upgradeForm" @if($remainingChanges == 0) style="pointer-events: none; opacity: 0.6;" @endif>
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($packageOptions as $optionKey => $limits)
                    @php
                        $currentValue = $currentOptions[$optionKey] ?? $limits['base'];
                        $optionName = ucfirst(str_replace('_', ' ', $optionKey));
                        $unit = '';
                        $displayValue = $currentValue;
                        $displayMin = $limits['min'];
                        $displayMax = $limits['max'];
                        $displayStep = $limits['step'];
                        
                        switch($optionKey) {
                            case 'cpu_limit':
                                $unit = $currentValue == 1 ? ' Core' : ' Cores';
                                break;
                            case 'memory_limit':
                                $unit = ' GB';
                                $displayValue = $currentValue / 1024;
                                $displayMin = $limits['min'] / 1024;
                                $displayMax = $limits['max'] / 1024;
                                $displayStep = $limits['step'] / 1024;
                                break;
                            case 'disk_limit':
                                $unit = ' GB';
                                $displayValue = $currentValue / 1024;
                                $displayMin = $limits['min'] / 1024;
                                $displayMax = $limits['max'] / 1024;
                                $displayStep = $limits['step'] / 1024;
                                break;
                            case 'backup_limit':
                                $unit = ' GB';
                                break;
                            case 'allocation_limit':
                                $unit = $currentValue == 1 ? ' Port' : ' Ports';
                                break;
                            case 'database_limit':
                                $unit = $currentValue == 1 ? ' Database' : ' Databases';
                                break;
                        }
                    @endphp
                    
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $optionName }}
                            </h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Current') }}: {{ number_format($displayValue) }}{{ $unit }}
                            </span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <label for="{{ $optionKey }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 w-16">
                                    {{ __('New') }}:
                                </label>
                                <input type="range" 
                                       id="{{ $optionKey }}" 
                                       name="{{ $optionKey }}" 
                                       min="{{ $limits['min'] }}" 
                                       max="{{ $limits['max'] }}" 
                                       step="{{ $limits['step'] }}" 
                                       value="{{ $currentValue }}"
                                       class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-600 range-slider"
                                       data-option="{{ $optionKey }}"
                                       data-current="{{ $currentValue }}"
                                       data-price-per-unit="{{ $limits['price_per_unit'] }}"
                                       data-base="{{ $limits['base'] }}"
                                       data-step="{{ $limits['step'] }}"
                                       data-unit="{{ $unit }}"
                                       data-display-divisor="{{ in_array($optionKey, ['memory_limit', 'disk_limit']) ? 1024 : 1 }}">
                                <span id="{{ $optionKey }}_display" class="text-sm font-medium text-gray-900 dark:text-white w-20 text-right">
                                    {{ number_format($displayValue) }}{{ $unit }}
                                </span>
                            </div>
                            
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ __('Min') }}: {{ number_format($displayMin) }}{{ $unit }}</span>
                                <span id="{{ $optionKey }}_price" class="font-medium">
                                    €{{ number_format(($limits['price_per_unit'] * (($currentValue - $limits['base']) / $limits['step']) / 30) * $order->price()->period, 2) }}/{{ $order->period() }}
                                </span>
                                <span>{{ __('Max') }}: {{ number_format($displayMax) }}{{ $unit }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Price Summary') }}
                    </h3>
                </div>
                
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('Base package price') }}:</span>
                        <span class="font-medium text-gray-600 dark:text-gray-400">{{ price($order->price['renewal_price']) }}/{{ $order->period() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('Current configurable options') }}:</span>
                        <span id="current_options_price" class="font-medium text-gray-600 dark:text-gray-400">€{{ number_format($order->price()->renewal_price - $order->price['renewal_price'], 2) }}/{{ $order->period() }}</span>
                    </div>
                    <div class="flex justify-between font-medium">
                        <span class="text-gray-700 dark:text-gray-300">{{ __('Current total') }}:</span>
                        <span id="current_total" class="text-gray-900 dark:text-white">{{ price($order->price()->renewal_price) }}/{{ $order->period() }}</span>
                    </div>
                    <hr class="border-gray-300 dark:border-gray-600">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('New configurable options') }}:</span>
                        <span id="new_options_price" class="font-medium text-gray-600 dark:text-gray-400">€{{ number_format(array_sum(array_map(function($key) use ($packageOptions, $currentOptions, $order) {
                            return \App\Rules\ConfigurableOptionLimits::calculatePrice($order->package_id, $key, $currentOptions[$key] ?? $packageOptions[$key]['base']);
                        }, array_keys($packageOptions))), 2) }}/{{ $order->period() }}</span>
                    </div>
                    <div class="flex justify-between font-medium">
                        <span class="text-gray-700 dark:text-gray-300">{{ __('New total') }}:</span>
                        <span id="new_total" class="text-gray-900 dark:text-white">{{ price($order->price['renewal_price'] + array_sum(array_map(function($key) use ($packageOptions, $currentOptions, $order) {
                            return \App\Rules\ConfigurableOptionLimits::calculatePrice($order->package_id, $key, $currentOptions[$key] ?? $packageOptions[$key]['base']);
                        }, array_keys($packageOptions)))) }}/{{ $order->period() }}</span>
                    </div>
                    <hr class="border-gray-300 dark:border-gray-600">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-900 dark:text-white">{{ __('Price difference') }}:</span>
                        <span id="price_difference" class="text-green-600 dark:text-green-400">€0.00</span>
                    </div>
                    <div id="upgrade_fee_row" class="flex justify-between items-center" style="display: none;">
                        <span class="text-gray-900 dark:text-white">{{ __('Processing fee') }} (One-Time):</span>
                        <span id="upgrade_fee" class="text-orange-600 dark:text-orange-400">€1.50</span>
                    </div>
                    <div id="total_to_pay_row" class="flex justify-between items-center text-lg font-semibold" style="display: none;">
                        <span class="text-gray-900 dark:text-white">{{ __('Total to pay') }}:</span>
                        <span id="total_to_pay" class="text-red-600 dark:text-red-400">€0.00</span>
                    </div>
                </div>

                <div id="payment_section" class="mt-6" style="display: none;">
                    <label for="gateway" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('client.payment_method') }}
                    </label>
                    <select name="gateway" id="gateway" 
                            class="block w-full px-3 py-2 border border-primary-300 rounded-md shadow-sm bg-white focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-primary-500 dark:text-white">
                        <option value="">{{ __('client.select_payment_method') }}</option>
                        @foreach(App\Models\Gateways\Gateway::getActive() as $gateway)
                            @if (!str_starts_with($gateway->name, 'Test') || (auth()->check() && auth()->user()->is_admin()))
                                @if ($gateway->name == 'Balance')
                                    <option value="{{ $gateway->id }}">
                                        {{ __('client.pay_with_balance') }} ({{ price(Auth::user()->balance) }})
                                    </option>
                                    @continue
                                @endif
                                <option value="{{ $gateway->id }}">{{ $gateway->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                
                <div class="mt-6 flex items-center justify-between">
                    <a href="{{ route('service', ['order' => $order->id, 'page' => 'manage']) }}" 
                       class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-600 dark:border-red-600 dark:hover:bg-red-700">
                        {{ __('client.cancel') }}
                    </a>
                    
                    <button type="submit" id="submit_button"
                            class="px-6 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ __('client.apply_changes') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sliders = document.querySelectorAll('.range-slider');
    const basePrice = {{ $order->price['renewal_price'] }};
    const currentOptionsPrice = {{ $order->price()->renewal_price - $order->price['renewal_price'] }};
    const currentTotal = {{ $order->price()->renewal_price }};
    const orderPeriod = {{ $order->price()->period }};
    const daysRemaining = {{ now()->diffInDays($order->due_date) }};
    const periodName = '{{ $order->period() }}';
    const remainingChanges = {{ $remainingChanges }};
    let newOptionsPrice = currentOptionsPrice;
    let currentUpgradeFee = 0;
    
    // Add event listener for gateway selection to show/hide upgrade fee
    const gatewaySelect = document.getElementById('gateway');
    gatewaySelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const upgradeFeeRow = document.getElementById('upgrade_fee_row');
        const totalToPayRow = document.getElementById('total_to_pay_row');
        const priceDifferenceEl = document.getElementById('price_difference');
        const totalToPayEl = document.getElementById('total_to_pay');
        
        // Get current price difference
        const currentPriceDiffText = priceDifferenceEl.textContent;
        const priceDiffMatch = currentPriceDiffText.match(/€([\d.]+)/);
        const currentPriceDiff = priceDiffMatch ? parseFloat(priceDiffMatch[1]) : 0;
        
        // Check if Balance is selected
        const isBalance = selectedOption && selectedOption.text.includes('{{ __("client.pay_with_balance") }}');
        
        if (this.value && !isBalance && currentPriceDiff > 0) {
            // Show upgrade fee for non-balance payment methods
            currentUpgradeFee = 1.5;
            upgradeFeeRow.style.display = 'flex';
            totalToPayRow.style.display = 'flex';
            const totalAmount = roundToTwo(currentPriceDiff + currentUpgradeFee);
            totalToPayEl.textContent = '€' + totalAmount.toFixed(2);
        } else {
            // Hide upgrade fee
            currentUpgradeFee = 0;
            upgradeFeeRow.style.display = 'none';
            totalToPayRow.style.display = 'none';
        }
    });
    
    // Add loading overlay for form submission
    const upgradeForm = document.getElementById('upgradeForm');
    upgradeForm.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submit_button');
        
        // Prevent submission if no changes
        if (submitBtn.disabled) {
            e.preventDefault();
            return false;
        }
        
        // Check if there are any changes and determine type
        const fullPeriodDifference = roundToTwo(newOptionsPrice - currentOptionsPrice);
        const monthlyDifference = roundToTwo((fullPeriodDifference / orderPeriod) * 30);
        const currentDifference = roundToTwo((monthlyDifference / 30) * daysRemaining);
        const isUpgrade = currentDifference > 0;
        let hasValueChanges = false;
        
        sliders.forEach(slider => {
            const value = parseFloat(slider.value);
            const current = parseFloat(slider.dataset.current);
            if (value !== current) {
                hasValueChanges = true;
            }
        });
        
        const gatewaySelect = document.getElementById('gateway');
        
        // Check if it's a downgrade and show confirmation
        if (submitBtn.hasAttribute('data-downgrade')) {
            e.preventDefault();
            
            // Show downgrade confirmation modal
            const confirmationModal = document.createElement('div');
            confirmationModal.className = 'fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50';
            confirmationModal.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.464 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Confirm Downgrade
                            </h3>
                        </div>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <strong>Warning:</strong> This downgrade will be applied immediately <strong>without refund</strong>. Your server resources will be reduced instantly and cannot be reverted without making a new change.
                        </p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-action" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="button" id="confirm-action" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Apply Downgrade
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(confirmationModal);
            
            // Handle confirmation buttons
            document.getElementById('cancel-action').addEventListener('click', function() {
                document.body.removeChild(confirmationModal);
            });
            
            document.getElementById('confirm-action').addEventListener('click', function() {
                document.body.removeChild(confirmationModal);
                // Continue with the original form submission
                submitBtn.removeAttribute('data-downgrade'); // Prevent infinite loop
                
                // Show loading overlay
                showLoadingOverlay();
                
                upgradeForm.submit();
            });
            
            return false; // Prevent form submission until confirmed
        }
        
        // Check if it's apply changes (balanced changes) and show confirmation
        if (submitBtn.hasAttribute('data-apply-changes')) {
            e.preventDefault();
            
            // Show apply changes confirmation modal
            const confirmationModal = document.createElement('div');
            confirmationModal.className = 'fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50';
            confirmationModal.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Confirm Changes
                            </h3>
                        </div>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <strong>Notice:</strong> These configuration changes will be applied immediately to your server. The balanced changes result in no price difference.
                        </p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-action" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-gray-300 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="button" id="confirm-action" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Apply Changes
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(confirmationModal);
            
            // Handle confirmation buttons
            document.getElementById('cancel-action').addEventListener('click', function() {
                document.body.removeChild(confirmationModal);
            });
            
            document.getElementById('confirm-action').addEventListener('click', function() {
                document.body.removeChild(confirmationModal);
                // Continue with the original form submission
                submitBtn.removeAttribute('data-apply-changes'); // Prevent infinite loop
                
                // Show loading overlay
                showLoadingOverlay();
                
                upgradeForm.submit();
            });
            
            return false; // Prevent form submission until confirmed
        }
        
        // If it's an upgrade and no gateway selected, let browser validation handle it
        if (isUpgrade && !gatewaySelect.value) {
            return; // Let browser show validation message
        }
        
        // Show loading overlay immediately for valid submissions
        showLoadingOverlay();
        
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    });
    
    sliders.forEach(slider => {
        slider.addEventListener('input', function() {
            const optionKey = this.dataset.option;
            const current = parseFloat(this.dataset.current);
            const pricePerUnit = parseFloat(this.dataset.pricePerUnit);
            const base = parseFloat(this.dataset.base);
            const step = parseFloat(this.dataset.step);
            const unit = this.dataset.unit;
            const displayDivisor = parseFloat(this.dataset.displayDivisor);
            const value = parseFloat(this.value);
            
            // Update display
            const display = document.getElementById(optionKey + '_display');
            const priceDisplay = document.getElementById(optionKey + '_price');
            
            const displayValue = value / displayDivisor;
            display.textContent = number_format(displayValue) + unit;
            
            // Calculate price - convert monthly base to current period
            const unitsAboveBase = (value - base) / step;
            const monthlyPrice = unitsAboveBase * pricePerUnit;
            // Convert monthly price to period price (monthly price * period in days / 30)
            const periodPrice = roundToTwo((monthlyPrice / 30) * orderPeriod);
            priceDisplay.textContent = '€' + periodPrice.toFixed(2) + '/' + periodName;
            
            // Recalculate total
            calculateTotal();
        });
    });
    
    // Initial calculation to set correct button state
    calculateTotal();
    
    function calculateTotal() {
        newOptionsPrice = 0;
        let hasValueChanges = false;
        
        sliders.forEach(slider => {
            const value = parseFloat(slider.value);
            const current = parseFloat(slider.dataset.current);
            const pricePerUnit = parseFloat(slider.dataset.pricePerUnit);
            const base = parseFloat(slider.dataset.base);
            const step = parseFloat(slider.dataset.step);
            
            // Check if there are actual value changes
            if (value !== current) {
                hasValueChanges = true;
            }
            
            // Use current value for calculation instead of slider value to get accurate pricing
            const calcValue = value;
            const unitsAboveBase = (calcValue - base) / step;
            const monthlyPrice = unitsAboveBase * pricePerUnit;
            // Convert monthly price to period price
            const periodPrice = roundToTwo((monthlyPrice / 30) * orderPeriod);
            newOptionsPrice += periodPrice;
        });
        
        // Round newOptionsPrice to avoid floating point precision issues
        newOptionsPrice = roundToTwo(newOptionsPrice);
        
        // If no changes, ensure newOptionsPrice matches currentOptionsPrice exactly
        if (!hasValueChanges) {
            newOptionsPrice = roundToTwo(currentOptionsPrice);
        }
        
        // Update configurable options prices
        document.getElementById('new_options_price').textContent = '€' + newOptionsPrice.toFixed(2) + '/' + periodName;
        
        // Update new total (base + options)
        const newTotal = roundToTwo(basePrice + newOptionsPrice);
        document.getElementById('new_total').textContent = '€' + newTotal.toFixed(2) + '/' + periodName;
        
        // Calculate prorated difference for upgrade payments using correct formula
        const fullPeriodDifference = roundToTwo(newOptionsPrice - currentOptionsPrice);
        // Convert period difference back to monthly, then prorate: (periodDiff / period * 30) / 30 * daysRemaining
        const monthlyDifference = roundToTwo((fullPeriodDifference / orderPeriod) * 30);
        const proratedDifference = roundToTwo((monthlyDifference / 30) * daysRemaining);
        const differenceElement = document.getElementById('price_difference');
        const paymentSection = document.getElementById('payment_section');
        const submitButton = document.getElementById('submit_button');
        
        const gatewaySelect = document.getElementById('gateway');
        
        // Check if daily limit reached
        if (remainingChanges === 0) {
            differenceElement.textContent = 'Daily limit reached';
            differenceElement.className = 'text-red-600 dark:text-red-400';
            paymentSection.style.display = 'none';
            submitButton.textContent = 'Daily limit reached (2/2)';
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            submitButton.removeAttribute('data-downgrade');
            submitButton.removeAttribute('data-apply-changes');
            gatewaySelect.removeAttribute('required');
            return;
        }
        
        if (proratedDifference > 0) {
            differenceElement.textContent = '€' + proratedDifference.toFixed(2) + ' (prorated for ' + daysRemaining + ' days)';
            differenceElement.className = 'text-red-600 dark:text-red-400';
            paymentSection.style.display = 'block';
            submitButton.textContent = '{{ __("client.pay_upgrade") }}';
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            submitButton.removeAttribute('data-downgrade');
            submitButton.removeAttribute('data-apply-changes');
            gatewaySelect.setAttribute('required', 'required');
            
            // Update upgrade fee display if gateway is already selected
            if (gatewaySelect.value) {
                const selectedOption = gatewaySelect.options[gatewaySelect.selectedIndex];
                const isBalance = selectedOption && selectedOption.text.includes('{{ __("client.pay_with_balance") }}');
                const upgradeFeeRow = document.getElementById('upgrade_fee_row');
                const totalToPayRow = document.getElementById('total_to_pay_row');
                const totalToPayEl = document.getElementById('total_to_pay');
                
                if (!isBalance) {
                    currentUpgradeFee = 1.5;
                    upgradeFeeRow.style.display = 'flex';
                    totalToPayRow.style.display = 'flex';
                    const totalAmount = roundToTwo(proratedDifference + currentUpgradeFee);
                    totalToPayEl.textContent = '€' + totalAmount.toFixed(2);
                } else {
                    currentUpgradeFee = 0;
                    upgradeFeeRow.style.display = 'none';
                    totalToPayRow.style.display = 'none';
                }
            } else {
                // Hide fee rows when no gateway selected
                document.getElementById('upgrade_fee_row').style.display = 'none';
                document.getElementById('total_to_pay_row').style.display = 'none';
            }
        } else if (proratedDifference < 0) {
            differenceElement.textContent = '€' + proratedDifference.toFixed(2);
            differenceElement.className = 'text-green-600 dark:text-green-400';
            paymentSection.style.display = 'none';
            submitButton.textContent = '{{ __("client.apply_downgrade") }}';
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            submitButton.setAttribute('data-downgrade', 'true');
            submitButton.removeAttribute('data-apply-changes');
            gatewaySelect.removeAttribute('required');
            // Hide upgrade fee rows for downgrades
            document.getElementById('upgrade_fee_row').style.display = 'none';
            document.getElementById('total_to_pay_row').style.display = 'none';
            currentUpgradeFee = 0;
        } else if (hasValueChanges) {
            // Price difference is 0 but there are configuration changes
            differenceElement.textContent = '€0.00';
            differenceElement.className = 'text-blue-600 dark:text-blue-400';
            paymentSection.style.display = 'none';
            submitButton.textContent = '{{ __("client.apply_changes") }}';
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            submitButton.setAttribute('data-apply-changes', 'true');
            submitButton.removeAttribute('data-downgrade');
            gatewaySelect.removeAttribute('required');
            // Hide upgrade fee rows for balanced changes
            document.getElementById('upgrade_fee_row').style.display = 'none';
            document.getElementById('total_to_pay_row').style.display = 'none';
            currentUpgradeFee = 0;
        } else {
            differenceElement.textContent = '€0.00';
            differenceElement.className = 'text-gray-600 dark:text-gray-400';
            paymentSection.style.display = 'none';
            submitButton.textContent = '{{ __("client.no_changes") }}';
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            submitButton.removeAttribute('data-downgrade');
            submitButton.removeAttribute('data-apply-changes');
            gatewaySelect.removeAttribute('required');
            // Hide upgrade fee rows when no changes
            document.getElementById('upgrade_fee_row').style.display = 'none';
            document.getElementById('total_to_pay_row').style.display = 'none';
            currentUpgradeFee = 0;
        }
    }
    
    function number_format(number) {
        return new Intl.NumberFormat().format(number);
    }
    
    // Function to handle decimal precision issues
    function roundToTwo(num) {
        return Math.round((num + Number.EPSILON) * 100) / 100;
    }
    
    // Function to show loading overlay (same as checkout)
    function showLoadingOverlay() {
        const loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'upgrade-loading';
        loadingOverlay.className = 'fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50';
        loadingOverlay.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-lg p-8 max-w-sm w-full mx-4 text-center">
                <div class="mb-4">
                    <svg class="animate-spin h-12 w-12 text-primary-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    Applying configuration changes
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Please wait while we update your server...
                </p>
                <div class="mt-4">
                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div id="upgrade-progress-bar" class="bg-primary-600 h-full rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
        
        // Animate progress bar (same timing as checkout)
        const progressBar = document.getElementById('upgrade-progress-bar');
        let progress = 0;
        const targetProgress = 90; // Stop at 90% to show it's still loading
        const duration = 7000; // 7 seconds
        const interval = 50; // Update every 50ms
        const increment = (targetProgress / (duration / interval));
        
        const progressInterval = setInterval(() => {
            progress += increment;
            if (progress >= targetProgress) {
                progress = targetProgress;
                clearInterval(progressInterval);
                // Keep pulsing at 90% to show it's still working
                progressBar.classList.add('animate-pulse');
            }
            progressBar.style.width = progress + '%';
        }, interval);
    }
});
</script>
@endsection