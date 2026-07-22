<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Reusable helper for posting payloads to Discord webhooks with retry logic.
 *
 * Behavior:
 * - HTTP 2xx       → success.
 * - HTTP 429       → read `retry_after` from the JSON body, sleep, retry up to $maxAttempts.
 * - HTTP 5xx       → exponential backoff (1s, 2s, 4s capped at 8s), retry up to $maxAttempts.
 * - HTTP 4xx other → log and give up (no retry, the payload is invalid).
 * - Network error  → exponential backoff, retry up to $maxAttempts.
 *
 * This trait is intended for synchronous senders. Queue-based senders (Jobs)
 * should use Job::release($retryAfter) instead so they don't block a worker.
 */
trait SendsDiscordWebhook
{
    protected function sendDiscordWebhook(
        string $url,
        array $payload,
        int $maxAttempts = 3,
        int $timeoutSeconds = 10
    ): bool {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = Http::timeout($timeoutSeconds)->post($url, $payload);
            } catch (Throwable $e) {
                Log::warning('Discord webhook network error', [
                    'attempt' => $attempt,
                    'error'   => $e->getMessage(),
                ]);
                if ($attempt < $maxAttempts) {
                    $this->discordWebhookBackoff($attempt);
                    continue;
                }
                return false;
            }

            if ($response->successful()) {
                return true;
            }

            $status = $response->status();

            if ($status === 429) {
                $retryAfter = (float) ($response->json('retry_after') ?? 1.0);
                Log::warning('Discord webhook rate limited', [
                    'retry_after' => $retryAfter,
                    'attempt'     => $attempt,
                ]);
                if ($attempt < $maxAttempts) {
                    $microseconds = (int) min($retryAfter * 1_000_000 + 100_000, 10_000_000);
                    usleep($microseconds);
                    continue;
                }
                Log::error('Discord webhook still rate limited after retries', ['url' => $url]);
                return false;
            }

            if ($status >= 500 && $status < 600) {
                Log::warning('Discord webhook server error', [
                    'status'  => $status,
                    'attempt' => $attempt,
                ]);
                if ($attempt < $maxAttempts) {
                    $this->discordWebhookBackoff($attempt);
                    continue;
                }
                Log::error('Discord webhook failed after retries (server error)', [
                    'status' => $status,
                ]);
                return false;
            }

            // 4xx other than 429: the payload is wrong or the webhook is gone. No retry.
            Log::error('Discord webhook client error', [
                'status' => $status,
                'body'   => substr($response->body(), 0, 500),
            ]);
            return false;
        }

        return false;
    }

    private function discordWebhookBackoff(int $attempt): void
    {
        $delaySeconds = min(2 ** ($attempt - 1), 8);
        sleep($delaySeconds);
    }
}
