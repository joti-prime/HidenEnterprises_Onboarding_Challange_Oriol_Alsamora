<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url;

    protected $data;

    protected $method;

    protected $headers;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $url, string $method, array $data = [], array $headers = [])
    {
        $this->url = $url;
        $this->data = $data;
        $this->method = $method;
        $this->headers = $headers;
    }

    /**
     * Delay between retries (seconds) for transient failures other than 429.
     * 429 uses the `retry_after` value supplied by the destination.
     *
     * @return array<int>
     */
    public function backoff(): array
    {
        return [10, 30];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $method = $this->method;

        $response = Http::withHeaders(array_merge([
            'Accept' => 'application/json',
        ], $this->headers))->$method($this->url, $this->data);

        if ($response->successful()) {
            return;
        }

        // Discord (and similar) signal rate limiting with HTTP 429 + a JSON
        // `retry_after` field in seconds. Honour it via release() so the worker
        // is not blocked and Discord stops counting the request as abuse.
        if ($response->status() === 429) {
            $retryAfter = (int) ceil((float) ($response->json('retry_after') ?? 1.0)) + 1;
            $this->release($retryAfter);
            return;
        }

        // Anything else (5xx or 4xx other than 429): let the queue retry up to
        // $tries with the backoff above. After that the job lands in failed_jobs.
        ErrorLog('order::webhooks::failed', 'HTTP ' . $response->status() . ': ' . substr($response->body(), 0, 500), 'WARNING');
        throw new \Exception('Webhook request failed (HTTP ' . $response->status() . ')');
    }

    /**
     * The job failed to process.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        //
    }
}
