@props([
    'order' => $order,
    'data' => $order->data,
])

{{-- Check if we're on the dashboard --}}
@php
    $isDashboard = request()->routeIs('dashboard') || request()->routeIs('client.dashboard');
@endphp

@if (!$isDashboard)
    {{-- Show all buttons on non-dashboard pages --}}
    @foreach ($order->package->service()->getServiceButtons($order)->all() as $key => $button)
        @if (empty($button))
            @continue;
        @endif
        <{{ $button['tag'] ?? 'a' }} href="{{ $button['href'] ?? '#' }}" target="{{ $button['target'] ?? '' }}"
            @isset($button['onclick']) onclick="{{ $button['onclick'] }}" @endisset
            @isset($button['data-copy-value']) data-copy-value="{{ $button['data-copy-value'] }}" @endisset
            class="bg-{{ $button['color'] }}-700 hover:bg-{{ $button['color'] }}-800 focus:ring-{{ $button['color'] }}-300 dark:bg-{{ $button['color'] }}-600 dark:hover:bg-{{ $button['color'] }}-700 dark:focus:ring-{{ $button['color'] }}-800 rounded-lg px-3 py-2 text-sm font-medium text-white focus:outline-none focus:ring-4">
            <span class="font-xl mr-1">{!! $button['icon'] ?? '' !!}</span>
            {!! $button['name'] !!}
            </{{ $button['tag'] ?? 'a' }}>
    @endforeach

    @if ($order->isRecurring())
        @if($order->status !== 'terminated')
            @include(Theme::path('components.orders.renew-modal'), $order)
        @endif
    @endif
@endif

@if (request('page') !== 'manage')
    @if ($isDashboard)
        {{-- On dashboard: Show only Manage button, centered and wider --}}
        <div class="w-full flex justify-center">
            <a href="{{ route('service', ['order' => $order->id, 'page' => 'manage']) }}"
                class="bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 flex items-center justify-center rounded-lg px-5 py-2.5 text-center text-sm font-medium text-white focus:outline-none focus:ring-4 w-full max-w-[200px]">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 h-4 w-4" viewbox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                    <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                        clip-rule="evenodd" />
                </svg>
                {!! __('client.manage') !!}
            </a>
        </div>
    @else
        {{-- On other pages: Show normal Manage button --}}
        <a href="{{ route('service', ['order' => $order->id, 'page' => 'manage']) }}"
            class="bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 flex items-center rounded-lg px-3 py-2 text-center text-sm font-medium text-white focus:outline-none focus:ring-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-4 w-4" viewbox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                    clip-rule="evenodd" />
            </svg>
            {!! __('client.manage') !!}
        </a>
    @endif
@endif

@if (!$isDashboard)
    {{-- Show these buttons only on non-dashboard pages --}}
    @if ($order->getService()->canLoginToPanel())
        <a href="{{ route('service', ['order' => $order->id, 'page' => 'login-to-panel']) }}" target="_blank"
            class="bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 flex items-center rounded-lg px-3 py-2 text-center text-sm font-medium text-white focus:outline-none focus:ring-4">
            {!! __('client.login_to_panel') !!}
        </a>
    @endif

    @if (request('page') === 'manage' && in_array(strtolower($order->package->service ?? ''), ['pterodactyl', 'freepterodactyl'], true))
        @include(Theme::path('components.orders.change-panel-password-modal'), ['order' => $order])
    @endif

    {{-- Traditional package upgrade (only if service can upgrade and no configurable options) --}}
    @if ($order->getService()->canUpgrade() && $order->package->settings('allow_upgrading', true))
        @if($order->status !== 'terminated')
            @include(Theme::path('components.orders.upgrade-drawer'), $order)
        @endif
    @endif

    {{-- Configurable Options Upgrade Button (independent button) --}}
    @if (strtolower($order->package->service) === 'pterodactyl' && $order->status === 'active' && request('page') == 'manage' && $order->package->settings('allow_upgrading', true))
        @php
            $packageOptions = \App\Rules\ConfigurableOptionLimits::getPackageOptions($order->package_id);
        @endphp
        @if(!empty($packageOptions))
            <a href="{{ route('configurable-options.show', $order) }}"
                class="bg-blue-700 hover:bg-blue-800 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 rounded-lg px-3 py-2 text-sm font-medium text-white focus:outline-none focus:ring-4">
                <i class='bx bx-slider font-xl mr-1'></i>
                {{ __('Upgrade / Downgrade') }}
            </a>
        @endif
    @endif

    @if($order->status !== 'terminated' && (auth()->id() === $order->user_id || auth()->user()?->is_admin()))
        @if($order->package && $order->package->service === 'freepterodactyl')
            @include(Theme::path('components.orders.delete-modal'), $order)
        @elseif($order->package->settings('allow_cancellation', true))
            @include(Theme::path('components.orders.cancel-modal'), $order)
        @endif
    @endif
@endif
