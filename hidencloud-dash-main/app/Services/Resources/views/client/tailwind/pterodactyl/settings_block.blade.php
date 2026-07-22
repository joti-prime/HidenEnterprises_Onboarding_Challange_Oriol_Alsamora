
@if(cf()::where('package_id', $order->package->id)->exists())
    @php($subdomain = cf()::getOrderSubdomain($order->id))
    @php($allocation = collect($server['relationships']['allocations']['data'])->filter(fn($allocation) => $allocation['attributes']['is_default'] == true)->first())
    <div class="shadow-2xl p-3">
        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">{{ __('client.domain') }} ({{ $subdomain->domain_data['subdomain'] ?? '' }}.{{ $subdomain->domain_data['domain'] ?? '' }})</h3>
        <form method="POST" action="{{ route('cf.pterodactyl.save.domain') }}" class="flex items-end space-x-2">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <input type="hidden" name="ip" value="{{ $allocation['attributes']['ip'] }}">
            <input type="hidden" name="port" value="{{ $allocation['attributes']['port'] }}">
            <input name="subdomain" id="subdomain" class="flex-1 {{ $inputClass }}" placeholder="Subdomain"
                   value="{{ $subdomain->domain_data['subdomain'] ?? '' }}">
            <select id="domain" name="domain" class="flex-1 {{ $selectClass }}">
                @foreach(cf()::getDomainsByPackage($order->package->id) as $id => $domain)
                    <option value="{{ $id }}::{{ $domain }}" @if($id == ($subdomain->domain_data['id'] ?? '')) selected @endif>
                        {{ $domain }}
                    </option>
                @endforeach
            </select>
            <button type="submit"
                    class="{{ $btnClass }} bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                {!! __('admin.update') !!}
            </button>
        </form>
    </div>
@endif
