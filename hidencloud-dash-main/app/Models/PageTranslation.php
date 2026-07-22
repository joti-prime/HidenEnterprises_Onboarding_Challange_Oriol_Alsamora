<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageTranslation extends Model
{
    protected $fillable = [
        'page_id',
        'name',
        'locale',
        'title',
        'content',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            Page::clearCache();
        });

        static::deleted(function () {
            Page::clearCache();
        });
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
