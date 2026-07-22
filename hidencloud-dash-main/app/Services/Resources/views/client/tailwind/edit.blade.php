@extends(Theme::path('orders.master'))
@section('title',  $order->name)
@php($domainList = cfHelper()::getDomainsList()->toArray())
@section('content')
    <div class="container mx-auto">
        <div class="mb-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
            <form action="{{ route('cf.update.domain', ['order' => $order->id]) }}" method="POST">
                @csrf
                <div class="flex flex-wrap -mx-3 mb-6">
                    <!-- Subdomain field -->
                    <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                        <label for="subdomain"
                               class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{!! __('admin.domain') !!}</label>
                        <input required type="text" value="{{ $order->options['subdomain'] ?? '' }}" id="subdomain"
                               name="subdomain"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                    </div>
                    <!-- Domain field -->
                    <div class="w-full md:w-1/2 px-3">
                        <label for="domain"
                               class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">&nbsp;</label>
                        <select required type="text" id="domain" name="domain"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @foreach($order->package->data['domains'] as $domain)
                                <option value="{{ $domain }}"
                                        @if($order->options['domains'] == $domain) selected @endif>{{ $domainList[$domain] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- IP Address field -->
                <div class="mb-6">
                    <label for="ip"
                           class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('admin.ip_address') }}</label>
                    <input required type="text" value="{{ $order->options['ip'] ?? '' }}" id="ip" name="ip"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                </div>
                <!-- Submit button -->
                <button type="submit"
                        class="bg-primary-500 hover:bg-primary-700 text-white font-bold py-1 px-2 text-sm rounded mt-5">
                    {{ __('client.update') }}
                </button>
            </form>
        </div>
    </div>

@endsection