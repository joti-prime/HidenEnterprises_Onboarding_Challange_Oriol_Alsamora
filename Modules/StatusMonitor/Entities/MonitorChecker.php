<?php

namespace Modules\StatusMonitor\Entities;

use Illuminate\Support\Facades\Http;
use Throwable;

class MonitorChecker
{
    /**
     * Timeout, in seconds, applied to both HTTP and TCP checks.
     */
    protected const TIMEOUT_SECONDS = 5;

    /**
     * Run the appropriate real check for a monitor and persist the result.
     */
    public function check(Monitor $monitor): Monitor
    {
        $result = $monitor->check_type === 'tcp'
            ? $this->checkTcp($monitor)
            : $this->checkHttp($monitor);

        $monitor->update([
            'last_status' => $result['status'],
            'last_response_time_ms' => $result['response_time_ms'],
            'last_status_code' => $result['status_code'],
            'last_error' => $result['error'],
            'last_checked_at' => now(),
        ]);

        return $monitor->refresh();
    }

    /**
     * Real HTTP check: performs an actual request and validates the status code.
     */
    protected function checkHttp(Monitor $monitor): array
    {
        $start = microtime(true);

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->withoutVerifying(false)
                ->get($monitor->target);

            $elapsedMs = (int) round((microtime(true) - $start) * 1000);
            $expected = $monitor->expected_status_code ?: 200;
            $up = $response->status() === $expected;

            return [
                'status' => $up ? 'up' : 'down',
                'response_time_ms' => $elapsedMs,
                'status_code' => $response->status(),
                'error' => $up ? null : "Expected HTTP {$expected}, got {$response->status()}",
            ];
        } catch (Throwable $e) {
            $elapsedMs = (int) round((microtime(true) - $start) * 1000);

            return [
                'status' => 'down',
                'response_time_ms' => $elapsedMs,
                'status_code' => null,
                'error' => $this->shortErrorMessage($e),
            ];
        }
    }

    /**
     * Real reachability check beyond HTTP: opens a raw TCP connection to host:port.
     */
    protected function checkTcp(Monitor $monitor): array
    {
        $host = $monitor->target;
        $port = $monitor->port ?: 80;
        $start = microtime(true);

        $connection = @fsockopen($host, $port, $errno, $errstr, self::TIMEOUT_SECONDS);
        $elapsedMs = (int) round((microtime(true) - $start) * 1000);

        if ($connection === false) {
            return [
                'status' => 'down',
                'response_time_ms' => $elapsedMs,
                'status_code' => null,
                'error' => trim("{$errstr} ({$errno})"),
            ];
        }

        fclose($connection);

        return [
            'status' => 'up',
            'response_time_ms' => $elapsedMs,
            'status_code' => null,
            'error' => null,
        ];
    }

    protected function shortErrorMessage(Throwable $e): string
    {
        $message = $e->getMessage();

        return strlen($message) > 200 ? substr($message, 0, 200) . '...' : $message;
    }
}
