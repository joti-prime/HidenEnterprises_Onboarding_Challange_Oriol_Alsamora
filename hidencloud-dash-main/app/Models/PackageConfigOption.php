<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageConfigOption extends Model
{
    use HasFactory;

    protected $table = 'package_config_options';

    protected $fillable = [
        'package_id',
        'key',
        'type',
        'price_per_30_days',
        'is_onetime',
        'is_required',
        'icon',
        'rules',
        'data',
        'order',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function rules()
    {
        return $this->rules ?? [];
    }

    public function move($direction)
    {
        if($direction == 'up') {
            $this->increment('order');
        } 
    
        if($direction == 'down') {
            $this->decrement('order');
        }
    }
}
