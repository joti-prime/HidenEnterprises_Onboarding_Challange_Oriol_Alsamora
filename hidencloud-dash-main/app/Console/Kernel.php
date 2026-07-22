<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // setup cron queue
        $schedule->command('queue:start')->everyMinute();
        $schedule->command('queue:start --force')->everyFiveMinutes();

        // cron tasks
        $schedule->command('subscriptions:check')->hourly();
        $schedule->command('cron:orders:remind-expiry')->hourly();
        $schedule->command('cron:orders:suspend-expired')->hourly();
        $schedule->command('cron:orders:suspend-cancelled')->hourly();
        $schedule->command('cron:orders:terminate-suspended')->hourly();
        $schedule->command('cron:payments:delete-expired')->hourly();
        $schedule->command('cron:orders:delete-terminate')->daily();
        $schedule->command('cron:orders:balance-auto-renew')->everyMinute();
        $schedule->command('cron:emails:send-mass-mails')->everyMinute();
        $schedule->command('permissions:save')->everyFiveMinutes();
        $schedule->command('user:remove-requested')->everyMinute();
        $schedule->command('twofactor:clean-expired')->hourly();

        // packages
        $schedule->command('cloudflare:reload')->daily();
        $schedule->command('geoip:update')->monthlyOn(3, '04:00');
        // check if cronjobs are active
        $schedule->call(function () { $this->checkCronStatus(); })->everyTenSeconds();
        // check if queue is active
        $schedule->call(function () { $this->checkQueueStatus(); })->everyTenSeconds();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    protected function checkCronStatus()
    {
        if (!Cache::has('cron_active')) {
            Cache::put('cron_active', true, 10800);
        }
    }

    protected function checkQueueStatus()
    {
        if (!Cache::has('queue_active') and !Cache::has('last_queue_check_at')) {
            \App\Jobs\CheckQueue::dispatch();
            Cache::put('last_queue_check_at', now(), 10800);
        }
    }
}
