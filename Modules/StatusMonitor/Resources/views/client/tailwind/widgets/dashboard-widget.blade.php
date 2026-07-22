@php
    $monitors = \Modules\StatusMonitor\Entities\Monitor::where('is_enabled', true)->orderBy('name')->get();
    $downCount = $monitors->where('last_status', 'down')->count();
@endphp

@if ($monitors->isNotEmpty())
    <div class="mb-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold dark:text-white">Status</h3>
            <a href="{{ route('statusmonitor.status') }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-500">
                View all
            </a>
        </div>

        <ul class="mt-3 divide-y divide-gray-200 dark:divide-gray-700">
            @foreach ($monitors->take(4) as $monitor)
                <li class="flex items-center justify-between py-2 text-sm">
                    <span class="text-gray-700 dark:text-gray-300">{{ $monitor->name }}</span>
                    @if ($monitor->last_status === 'up')
                        <span class="inline-flex items-center text-xs font-medium text-green-700 dark:text-green-400">
                            <span class="mr-1.5 h-2 w-2 rounded-full bg-green-500"></span> Up
                        </span>
                    @elseif ($monitor->last_status === 'down')
                        <span class="inline-flex items-center text-xs font-medium text-red-700 dark:text-red-400">
                            <span class="mr-1.5 h-2 w-2 rounded-full bg-red-500"></span> Down
                        </span>
                    @else
                        <span class="text-xs text-gray-400">Checking...</span>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($downCount > 0)
            <p class="mt-2 text-xs font-medium text-red-600 dark:text-red-400">
                {{ $downCount }} {{ $downCount === 1 ? 'service is' : 'services are' }} currently down.
            </p>
        @endif
    </div>
@endif
