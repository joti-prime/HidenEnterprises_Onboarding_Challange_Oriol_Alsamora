<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class QueueWorker extends Command
{
    protected $signature = 'queue:start {--force} {--kill}';

    protected $description = 'Restart the queue worker and start it';

    public function handle(): void
    {
        if ($this->option('kill')) {
            $this->killQueueWorker();

            return;
        }

        // Checking if the queue is enabled
        if (!config('app.cron_queue', true)) {
            $this->warn('Queue is disabled in the configuration file.');

            return;
        }

        $pidFile = storage_path('queue.pid');

        // Checking and clearing the log file if it's too large
        $this->checkAndClearLog();

        // Checking if the process is already running
        if (file_exists($pidFile) && !$this->option('force')) {
            $pid = trim(file_get_contents($pidFile));
            if ($pid && file_exists("/proc/$pid")) {
                $this->logInfo("Queue worker is already running with PID: $pid");

                return;
            }
        }

        // Forcefully stopping any running queue worker if force option is specified
        if ($this->option('force') && file_exists($pidFile)) {
            $pid = trim(file_get_contents($pidFile));
            if ($pid) {
                system("kill -9 $pid");
                $this->logInfo("Forcefully stopped queue worker with PID: $pid");
            }
            file_put_contents($this->getLogFilePath(), '');
        }

        // Starting a new queue and getting the PID
        $pid = system('php artisan queue:work > /dev/null 2>&1 & echo $!');
        file_put_contents($pidFile, $pid);
        $this->logInfo('Queue worker started with PID: ' . $pid);
    }

    private function killQueueWorker(): void
    {
        // Displaying the PID of the queue worker and killing it
        system('ps aux | grep "[q]ueue:work" | awk \'{print $2}\'');
        system('ps aux | grep "[q]ueue:start" | awk \'{print $2}\'');
        // Killing the queue worker
        system('ps aux | grep "[q]ueue:work" | awk \'{print $2}\' | xargs kill -9');
        system('ps aux | grep "[q]ueue:start" | awk \'{print $2}\' | xargs kill -9');
        unlink(storage_path('queue.pid'));
    }

    private function checkAndClearLog(): void
    {
        if (file_exists($this->getLogFilePath()) && filesize($this->getLogFilePath()) > 1048576) { // 1 MB
            file_put_contents($this->getLogFilePath(), ''); // Clear the log file
            $this->logInfo('Log file cleared due to excessive size.');
        }
    }

    private function getLogFilePath(): string
    {
        return storage_path('logs/queue.log');
    }

    private function logInfo(string $message): void
    {
        $this->info($message);
        file_put_contents($this->getLogFilePath(), '[' . now() . '] ' . $message . PHP_EOL, FILE_APPEND);
    }
}
