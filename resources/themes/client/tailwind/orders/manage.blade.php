@extends(Theme::path('orders.master'))

@section('title', __('client.services'))

@section('content')
    @includeIf(Theme::serviceView($order->service, 'stats'))

    @foreach (enabledModules() as $module)
        @if(settings("widget:order-manage-top:{$module->getLowerName()}", false))
            @includeIf(Theme::moduleView($module->getLowerName(), 'widgets.order-manage-top-widget'), ['order' => $order])
        @endif
    @endforeach

    @if($order->isRecurring() AND !$order->hasActiveSubscription())
        @if($order->package->settings('allow_auto_balance_renewal', true))
        <div class="mb-4 flex rounded-lg bg-gray-50 p-4 dark:bg-gray-800" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <label class="flex justify-between w-full items-center cursor-pointer" onclick="enableBalanceRenew()">
                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('client.auto_renew_with_balance') }}</span>

                <div>
                    <input type="checkbox" value="" class="sr-only peer" @if($order->auto_balance_renew) checked @endif/>
                    <div
                        class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"
                    ></div>
                </div>
            </label>
        </div>
        @endif
    @endif
    
    <div id="service">
        <div class="mb-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $order->package['name'] }} #{{ $order->id }}@if($order->external_id) - {{ $order->external_id }}@endif ({{ $order->status }})
            </h5>

            <div class="mt-4 grid grid-cols-3 gap-4">
                <div class="relative flex flex-col items-start justify-between rounded-lg bg-gray-100 p-3 dark:bg-gray-700">
                    <h6 class="mb-2 text-base font-medium leading-none text-gray-900 dark:text-white">{!! __('client.package') !!}</h6>
                    <div class="flex items-center text-gray-500 dark:text-gray-400">{{ $order->package['name'] }}</div>
                </div>
                <div class="relative flex flex-col justify-between rounded-lg bg-gray-100 p-3 dark:bg-gray-700">
                    <h6 class="mb-2 text-base font-medium leading-none text-gray-900 dark:text-white">{!! __('client.billing_cycle') !!}</h6>
                    <div class="flex items-center text-gray-500 dark:text-gray-400">
                        <span class="mr-1 font-bold text-gray-500 dark:text-white">
                            {{ price($order->price()->renewal_price) }}
                        </span>
                        / {{ $order->period() }}
                    </div>
                </div>
                <div class="relative rounded-lg bg-gray-100 p-3 dark:bg-gray-700">
                    <h6 class="mb-2 text-base font-medium leading-none text-gray-900 dark:text-white">{!! __('client.status') !!}</h6>
                    <span
                        class="@if ($order->status == 'active') bg-green-100 text-green-800 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300 @elseif($order->status == 'suspended') bg-yellow-100 text-yellow-800 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300 @elseif($order->status == 'cancelled') bg-red-100 text-red-800 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300 @elseif($order->status == 'terminated') bg-red-100 text-red-800 text-sm font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300 @endif">{!! __('admin.' . $order->status) !!}</span>
                </div>
                @if ($order->isRecurring())
                    <div class="relative rounded-lg bg-gray-100 p-3 dark:bg-gray-700">
                        <h6 class="mb-2 text-base font-medium leading-none text-gray-900 dark:text-white">{!! __('client.due_date') !!}</h6>
                        <div class="flex items-center text-gray-500 dark:text-gray-400">
                            {{ $order->due_date->translatedFormat('d M Y') }}
                        </div>
                    </div>
                    <div class="relative rounded-lg bg-gray-100 p-3 dark:bg-gray-700">
                        <h6 class="mb-2 text-base font-medium leading-none text-gray-900 dark:text-white">{!! __('client.last_renewal_date') !!}
                        </h6>
                        <div class="flex items-center text-gray-500 dark:text-gray-400">
                            {{ $order->last_renewed_at->translatedFormat('d M Y') }}
                        </div>
                    </div>
                    <div class="relative rounded-lg bg-gray-100 p-3 dark:bg-gray-700">
                        <h6 class="mb-2 text-base font-medium leading-none text-gray-900 dark:text-white">{!! __('client.next_invoice') !!}</h6>
                        <div class="flex items-center text-gray-500 dark:text-gray-400">
                            {{ $order->due_date->translatedFormat('d M Y') }}
                        </div>
                    </div>
                @endif
            </div>
            <div class="mt-4 flex flex-wrap items-stretch gap-3 [&_a]:whitespace-nowrap [&_button]:whitespace-nowrap">
                @include(Theme::path('components.orders.buttons'), $order)
            </div>
        </div>


        {{-- Cloudy AI Banner --}}
        @if(in_array($order->service, ['pterodactyl', 'freepterodactyl']))
        <div class="mb-4 rounded-lg bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-pink-500/10 border border-indigo-500/20 p-4 dark:from-indigo-500/10 dark:via-purple-500/10 dark:to-pink-500/10">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-indigo-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h5 class="text-lg font-bold text-gray-900 dark:text-white">{{ __("client.cloudy_ai_title") }}</h5>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {!! __("client.cloudy_ai_description") !!}
                        <a href="https://www.hidencloud.com/cloudy-ai" target="_blank" rel="noopener"
                           class="ml-1 inline-flex items-center gap-1 font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                            {{ __('client.cloudy_ai_more_info') }}
                            <i class='bx bx-link-external text-xs'></i>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- Price Breakdown Section --}}
        <div class="mb-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
            <h5 class="mb-4 text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{ __('client.price_breakdown') }}</h5>
            
            <div class="space-y-3">
                {{-- Base Package Price --}}
                <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-gray-900 dark:text-white">{{ __('client.base_package_price') }} ({{ $order->package['name'] }})</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        {{ price($order->package->prices()->where('period', $order->price()->period)->first()->renewal_price ?? 0) }} / {{ $order->period() }}
                    </span>
                </div>
                
                {{-- Configurable Options --}}
                @if($order->priceModifiers->count() > 0)
                    <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-gray-900 dark:text-white">{{ __('client.configurable_options') }}</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            {{ price($order->priceModifiers->sum('daily_price') * $order->price()->period) }} / {{ $order->period() }}
                        </span>
                    </div>
                    
                    {{-- Individual Options Breakdown --}}
                    <div class="ml-4 space-y-2">
                        @foreach($order->priceModifiers as $modifier)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500 dark:text-gray-300">• {{ $modifier->description }}</span>
                                <span class="text-gray-700 dark:text-gray-200">
                                    {{ price($modifier->daily_price * $order->price()->period) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                {{-- Total Price --}}
                <div class="flex justify-between items-center pt-3 mt-3 border-t-2 border-gray-300 dark:border-gray-600">
                    <span class="text-lg font-bold text-gray-900 dark:text-white">{{ __('client.total_price') }}</span>
                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">
                        {{ price($order->price()->renewal_price) }} / {{ $order->period() }}
                    </span>
                </div>
            </div>
        </div>
        
        @if($order->priceModifiers->count() > 0)
        <div class="relative overflow-x-auto shadow-md rounded">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Description
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <div class="flex items-center">
                                Value
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <div class="flex items-center">
                                Base Price
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <div class="flex items-center">
                                Price / {{ $order->price()->period }} days
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <div class="flex items-center">
                                Cancellation Fee
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <div class="flex items-center">
                                Upgrade Fee
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <div class="flex items-center">
                                Created At
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->priceModifiers as $modifier)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $modifier->description }}
                        </th>
                        <td class="px-6 py-4">
                            {{ Str::of($modifier->value)->limit(32); }}

                        </td>
                        <td class="px-6 py-4">
                            {{ price($modifier->base_price) }}
                        </td>
                        <td class="px-6 py-4">
                            {{ price($modifier->daily_price * $order->price()->period) }}
                        </td>
                        <td class="px-6 py-4">
                            {{ price($modifier->cancellation_fee) }}
                        </td>
                        <td class="px-6 py-4">
                            {{ price($modifier->upgrade_fee) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            {{ $modifier->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            Total
                        </th>
                        <td class="px-6 py-4">
                            
                        </td>
                        <td class="px-6 py-4">
                            {{ price($order->priceModifiers->sum('base_price')) }}
                        </td>
                        <td class="px-6 py-4">
                            {{ price($order->priceModifiers->sum('daily_price') * $order->price()->period) }}
                        </td>
                        <td class="px-6 py-4">
                            {{ price($order->priceModifiers->sum('cancellation_fee')) }}
                        </td>
                        <td class="px-6 py-4">
                            {{ price($order->priceModifiers->sum('upgrade_fee')) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        @if ($order->getService()->canChangePassword())
            <div class="mb-4 flex items-end justify-between rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <div>
                    <a href="#">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            {{ __('client.service_account', ['service' => ucfirst($order->package->service)]) }}</h5>
                    </a>
                    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">{{ $order->getExternalUser()->username ?? '' }}</p>
                </div>
                <button type="button" data-drawer-target="drawer-change-password" data-drawer-show="drawer-change-password"
                    data-drawer-placement="right" aria-controls="drawer-change-password"
                    class="inline-flex items-center rounded-lg bg-primary-700 px-3 py-2 text-center text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                    {{ __('client.change_password') }}
                    <svg class="ml-2 h-3.5 w-3.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M1 5h12m0 0L9 1m4 4L9 9" />
                    </svg>
                </button>
            </div>

            <!-- Change Password -->
            <div id="drawer-change-password"
                class="fixed right-0 top-0 z-40 h-screen w-80 translate-x-full overflow-y-auto bg-white p-4 transition-transform dark:bg-gray-800"
                tabindex="-1" aria-labelledby="drawer-change-password-label">
                <h5 id="drawer-change-password-label"
                    class="mb-4 inline-flex items-center text-base font-semibold text-gray-500 dark:text-gray-400"><svg class="mr-2.5 h-4 w-4"
                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>{{ __('client.change_password') }}</h5>
                <button type="button" data-drawer-hide="drawer-change-password" aria-controls="drawer-change-password"
                    class="absolute right-2.5 top-2.5 inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white">
                    <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">{{ __('client.close_menu') }}</span>
                </button>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('client.change_service_password', ['service' => $order->package->service]) }}</p>
                <form action="{{ route('service', ['order' => $order->id, 'page' => 'change-password']) }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="password"
                            class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">{{ __('auth.new_password') }}</label>
                        <input type="text" name="password" id="password"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500"
                            placeholder="{{ __('auth.new_password') }}" required>
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation"
                            class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">{{ __('auth.confirm_new_password') }}</label>
                        <input type="text" name="password_confirmation" id="password_confirmation"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500"
                            placeholder="{{ __('auth.confirm_new_password') }}" required>
                    </div>

                    <div class="">
                        <button type="submit" style="width: 100%"
                            class="items-center rounded-lg bg-primary-700 px-4 py-2 text-center text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                            {{ __('client.change_password') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif

        @includeIf(Theme::serviceView($order->service, 'service'))

        @foreach (enabledModules() as $module)
            @if(settings("widget:order-manage-bottom:{$module->getLowerName()}", false))
                @includeIf(Theme::moduleView($module->getLowerName(), 'widgets.order-manage-bottom-widget'), ['order' => $order])
            @endif
        @endforeach
    </div>

    <script>
        function enableBalanceRenew() 
        {
            window.location.href = "{{ route('service', ['order' => $order->id, 'page' => 'balance-renew']) }}";
        }
    </script>
@endsection
