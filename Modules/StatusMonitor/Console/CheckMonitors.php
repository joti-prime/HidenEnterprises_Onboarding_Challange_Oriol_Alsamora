<?php

namespace Modules\StatusMonitor\Console;

use Illuminate\Console\Command;
use Modules\StatusMonitor\Entities\Monitor;
use Modules\StatusMonitor\Entities\MonitorChecker;

class CheckMonitors extends Command
{
    /**
     * php artisan statusmonitor:check
     * php artisan statusmonitor:check --id=5   (check a single monitor, used by "Check now")
     */
    protected $signature = 'statusmonitor:check {--id= : Only check the monitor with this ID}';

    protected $description = 'Run real HTTP/TCP checks against all enabled status monitors';

    public function handle(MonitorChecker $checker): int
    {
        $query = Monitor::query()->where('is_enabled', true);

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        }

        $monitors = $query->get();

        if ($monitors->isEmpty()) {
            $this->info('No enabled monitors to check.');

            return self::SUCCESS;
        }

        foreach ($monitors as $monitor) {
            $checker->check($monitor);
            $this->info("[{$monitor->name}] {$monitor->last_status} ({$monitor->last_response_time_ms}ms)");
        }

        return self::SUCCESS;
    }
}
