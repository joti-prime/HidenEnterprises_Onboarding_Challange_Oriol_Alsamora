<?php

namespace Modules\StatusMonitor\Entities;

use Illuminate\Database\Eloquent\Model;

class Monitor extends Model
{
    protected $table = 'status_monitors';

    protected $fillable = [
        'name',
        'target',
        'check_type',
        'port',
        'expected_status_code',
        'is_enabled',
        'last_status',
        'last_response_time_ms',
        'last_status_code',
        'last_error',
        'last_checked_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'expected_status_code' => 'integer',
        'port' => 'integer',
        'last_response_time_ms' => 'integer',
        'last_status_code' => 'integer',
        'last_checked_at' => 'datetime',
    ];

    public function isUp(): bool
    {
        return $this->last_status === 'up';
    }

    public function isDown(): bool
    {
        return $this->last_status === 'down';
    }
}
