@extends(Theme::wrapper())
@section('title', 'Status')
@section('keywords', 'HCTestDash Status, HCTestDash Panel')

@section('container')
    <div class="flex flex-wrap">
        <div class="w-full px-2">
            <section class="py-3 dark:bg-gray-900 sm:py-5">
                <div class="mb-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:p-6">
                    <h3 class="text-xl font-bold dark:text-white">Service Status</h3>
                    <p class="mt-1 text-sm font-normal text-gray-500 dark:text-gray-400">
                        Live status of our monitored services, refreshed automatically.
                    </p>
                </div>

                @if ($monitors->isEmpty())
                    <div class="rounded-lg bg-white p-6 text-center text-gray-500 shadow dark:bg-gray-800 dark:text-gray-400">
                        No monitors are configured yet.
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($monitors as $monitor)
                            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:p-6">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-lg font-semibold dark:text-white">{{ $monitor->name }}</h4>

                                    @if ($monitor->last_status === 'up')
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300">
                                            <span class="mr-1.5 h-2 w-2 rounded-full bg-green-500"></span> Up
                                        </span>
                                    @elseif ($monitor->last_status === 'down')
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300">
                                            <span class="mr-1.5 h-2 w-2 rounded-full bg-red-500"></span> Down
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            Checking...
                                        </span>
                                    @endif
                                </div>

                                <dl class="mt-4 space-y-1 text-sm text-gray-500 dark:text-gray-400">
                                    <div class="flex justify-between">
                                        <dt>Response time</dt>
                                        <dd>{{ $monitor->last_response_time_ms !== null ? $monitor->last_response_time_ms . ' ms' : '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt>Last checked</dt>
                                        <dd>{{ $monitor->last_checked_at ? $monitor->last_checked_at->diffForHumans() : 'Never' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
