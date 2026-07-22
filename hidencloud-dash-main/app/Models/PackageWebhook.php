<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageWebhook extends Model
{
    use HasFactory;

    protected $table = 'package_webhooks';

    protected $casts = [
        'data' => 'array',
        'headers' => 'array',
    ];
}
