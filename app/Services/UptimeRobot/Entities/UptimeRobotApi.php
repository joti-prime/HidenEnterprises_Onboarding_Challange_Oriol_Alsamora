<?php

namespace App\Services\UptimeRobot\Entities;

use Illuminate\Support\Facades\Http;

class UptimeRobotApi
{
    protected const BASE_URL = 'https://api.uptimerobot.com/v3';

    protected string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? (string) settings('uptimerobot::api_key');
    }

    /**
     * Create a new HTTP monitor for the given URL.
     * Returns the decoded monitor payload (includes its "id").
     */
    public function createMonitor(string $friendlyName, string $url, int $interval = 300): array
    {
        return $this->request('POST', '/monitors', [
            'friendlyName' => $friendlyName,
            'url' => $url,
            'type' => 'HTTP',
            'interval' => $interval,
        ]);
    }

    /**
     * Fetch the live state of a monitor: status, uptime ratio, average response time, etc.
     */
    public function getMonitor(string $monitorId): array
    {
        return $this->request('GET', "/monitors/{$monitorId}");
    }

    /**
     * Update a monitor's friendly name, URL and/or check interval.
     */
    public function updateMonitor(string $monitorId, array $attributes): array
    {
        return $this->request('PATCH', "/monitors/{$monitorId}", $attributes);
    }

    public function pauseMonitor(string $monitorId): array
    {
        return $this->request('PATCH', "/monitors/{$monitorId}", ['isPaused' => true]);
    }

    public function resumeMonitor(string $monitorId): array
    {
        return $this->request('PATCH', "/monitors/{$monitorId}", ['isPaused' => false]);
    }

    public function deleteMonitor(string $monitorId): bool
    {
        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout(15)
            ->delete(self::BASE_URL . "/monitors/{$monitorId}");

        if ($response->failed()) {
            throw new \Exception('UptimeRobot API error (delete monitor): ' . $this->errorMessage($response));
        }

        return true;
    }

    /**
     * A cheap, low-cost call used purely to validate that the API key works.
     */
    public function testConnection(): void
    {
        $this->request('GET', '/monitors?limit=1');
    }

    protected function request(string $method, string $path, array $body = []): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('The UptimeRobot API key has not been configured yet.');
        }

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout(15)
            ->{strtolower($method)}(self::BASE_URL . $path, $body);

        if ($response->failed()) {
            throw new \Exception('UptimeRobot API error: ' . $this->errorMessage($response));
        }

        $decoded = $response->json();

        return is_array($decoded) ? $decoded : [];
    }

    protected function errorMessage($response): string
    {
        $status = $response->status();
        $json = $response->json();

        $message = $json['message']
            ?? $json['error']['message']
            ?? (is_array($json) ? json_encode($json) : $response->body());

        return "HTTP {$status} - {$message}";
    }
}
