@php
    use App\Services\UptimeRobot\Entities\UptimeRobotApi;

    $monitor = null;
    $error = null;

    if (!empty($order->external_id)) {
        try {
            $monitor = (new UptimeRobotApi())->getMonitor($order->external_id);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
    }

    $pick = function (?array $data, array $keys) {
        if (!$data) {
            return null;
        }
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                return $data[$key];
            }
        }
        return null;
    };

    $isPaused = $pick($monitor, ['isPaused', 'is_paused']);
    $rawStatus = $pick($monitor, ['status']);
    $uptimeRatio = $pick($monitor, ['uptimeRatio', 'uptime_ratio', 'uptime']);
    $avgResponseTime = $pick($monitor, ['averageResponseTime', 'average_response_time', 'avgResponseTime', 'responseTime']);
    $lastChecked = $pick($monitor, ['lastCheckedAt', 'last_check', 'lastCheckTime', 'checkedAt']);
    $friendlyName = $pick($monitor, ['friendlyName', 'friendly_name']) ?? $order->option('monitor_name');
    $monitorUrl = $pick($monitor, ['url']) ?? $order->option('monitor_url');
    $interval = $pick($monitor, ['interval']) ?? ($order->package ? $order->package->data('interval', 300) : 300);

    if ($isPaused === true) {
        $statusLabel = 'Paused';
        $statusClasses = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    } elseif (is_string($rawStatus) && str_contains(strtolower($rawStatus), 'down')) {
        $statusLabel = 'Down';
        $statusClasses = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    } elseif (is_string($rawStatus) && str_contains(strtolower($rawStatus), 'up')) {
        $statusLabel = 'Up';
        $statusClasses = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    } else {
        $statusLabel = $rawStatus ?? 'Unknown';
        $statusClasses = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
@endphp

<div class="mb-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
    <h5 class="mb-4 text-xl font-bold tracking-tight text-gray-900 dark:text-white">Uptime Monitor</h5>

    @if ($error)
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900 dark:text-red-300">
            Couldn't load live data from UptimeRobot: {{ $error }}
        </div>
    @endif

    @if (empty($order->external_id))
        <div class="rounded-lg bg-yellow-50 p-4 text-sm text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
            No monitor has been provisioned for this service yet.
        </div>
    @elseif ($monitor)
        {{-- Live status --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="relative rounded-lg bg-gray-100 p-3 dark:bg-gray-700">
                <h6 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</h6>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClasses }}">
                    {{ $statusLabel }}
                </span>
            </div>
            <div class="relative rounded-lg bg-gray-100 p-3 dark:bg-gray-700">
                <h6 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Uptime</h6>
                <div class="text-gray-500 dark:text-gray-400">{{ $uptimeRatio !== null ? $uptimeRatio . '%' : 'N/A' }}</div>
            </div>
            <div class="relative rounded-lg bg-gray-100 p-3 dark:bg-gray-700">
                <h6 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Avg. response time</h6>
                <div class="text-gray-500 dark:text-gray-400">{{ $avgResponseTime !== null ? $avgResponseTime . ' ms' : 'N/A' }}</div>
            </div>
            <div class="relative rounded-lg bg-gray-100 p-3 dark:bg-gray-700">
                <h6 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Last checked</h6>
                <div class="text-gray-500 dark:text-gray-400">{{ $lastChecked ?? 'N/A' }}</div>
            </div>
        </div>

        {{-- Pause / Resume --}}
        <div class="mt-4 flex items-center space-x-3">
            @if ($isPaused === true)
                <form action="{{ route('uptimerobot.monitor.resume', $order) }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-lg bg-green-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-green-700">
                        Resume monitor
                    </button>
                </form>
            @else
                <form action="{{ route('uptimerobot.monitor.pause', $order) }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-lg bg-yellow-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-yellow-700">
                        Pause monitor
                    </button>
                </form>
            @endif
        </div>

        {{-- Edit monitor --}}
        <div class="mt-6 border-t border-gray-200 pt-4 dark:border-gray-700">
            <h6 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">Edit monitor</h6>
            <form action="{{ route('uptimerobot.monitor.update', $order) }}" method="POST" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input type="text" name="friendlyName" value="{{ old('friendlyName', $friendlyName) }}"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-900 dark:text-white">URL</label>
                    <input type="text" name="url" value="{{ old('url', $monitorUrl) }}"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-900 dark:text-white">Check interval (s)</label>
                    <input type="number" name="interval" value="{{ old('interval', $interval) }}" min="60"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
                </div>
                <div class="sm:col-span-3">
                    <button type="submit" class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-800">
                        Save changes
                    </button>
                </div>
            </form>
        </div>

        <details class="mt-6 text-xs text-gray-500 dark:text-gray-400">
            <summary class="cursor-pointer">Raw UptimeRobot response</summary>
            <pre class="mt-2 overflow-x-auto rounded bg-gray-100 p-3 dark:bg-gray-900">{{ json_encode($monitor, JSON_PRETTY_PRINT) }}</pre>
        </details>
    @endif
</div>
