<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

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
 * @property mixed $id
 *
 * @method static wherePath($page)
 */
class Article extends Model
{
    use HasFactory;

    protected $table = 'articles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'path',
        'title',
        'content',
        'short_desc',
    ];

    protected $casts = [
        'keywords' => 'array',
        'labels' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(ArticleComment::class);
    }

    public function reactions()
    {
        return $this->hasMany(ArticleReaction::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }

    public function translate($locale = null): void
    {
        $locale = $locale ?: App::getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();
        if ($translation) {
            $this->title = $translation->title;
            $this->content = $translation->content;
        }
    }
}
