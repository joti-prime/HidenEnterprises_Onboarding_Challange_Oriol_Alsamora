<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

/**
 * @property mixed $redirect_url
 * @property mixed $new_tab
 * @property mixed $content
 * @property mixed $placement
 * @property mixed $icon
 * @property mixed $path
 * @property mixed $title
 * @property mixed $name
 * @property mixed $basic_page
 * @property mixed $is_enabled
 * @property mixed $id
 *
 * @method static wherePath($page)
 */
class Page extends Model
{
    protected $table = 'pages';

    protected $fillable = [
        'path',
        'title',
        'content',
        'is_enabled',
        'placement',
        'redirect_url',
    ];

    protected $casts = [
        'placement' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(function ($page) {
            self::clearCache();
        });

        static::deleted(function ($page) {
            self::clearCache();
        });
    }

    public function translate($locale = null): void
    {
        $locale = $locale ?: App::getLocale();
        $translation = $this->translations->where('locale', $locale)->first();
        if ($translation) {
            $this->name = $translation->name;
            $this->title = $translation->title;
            $this->content = $translation->content;
        }
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class);
    }

    public static function getActive()
    {
        $cacheKey = 'active_pages_' . App::getLocale();

        return Cache::remember($cacheKey, 3600, function () {
            $pages = Page::whereIsEnabled(1)->with('translations')->get();
            $pages->each(function ($page) {
                $page->translate();
            });

            return $pages;
        });
    }

    public static function clearCache(): void
    {
        $locales = PageTranslation::distinct()->pluck('locale')->toArray();
        $locales[] = 'en';
        $locales[] = App::getLocale();
        $locales = array_unique($locales);
        foreach ($locales as $locale) {
            Cache::forget('active_pages_' . $locale);
        }
    }
}
