<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ErrorLog
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $source,
        public string $error,
        public string $severity = 'ERROR'
    ) {}
}
