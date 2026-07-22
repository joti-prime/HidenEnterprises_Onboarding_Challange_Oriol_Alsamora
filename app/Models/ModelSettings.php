<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelSettings extends Model
{
    use HasFactory;

    protected $table = 'model_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get all of the owning metable models.
     */
    public function metable()
    {
        return $this->morphTo();
    }
}
