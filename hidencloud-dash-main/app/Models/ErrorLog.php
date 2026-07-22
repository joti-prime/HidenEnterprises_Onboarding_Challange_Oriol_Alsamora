<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;

    protected $table = 'error_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'order_id',
        'source',
        'severity',
        'message',
    ];

    public static function catch($source, $error, $severity = 'ERROR'): void
    {
        event(new \App\Events\ErrorLog($source, $error, $severity));
        ErrorLog::updateOrCreate([
            'severity' => $severity,
            'source' => $source,
            'message' => $error,
        ]);
    }
}
