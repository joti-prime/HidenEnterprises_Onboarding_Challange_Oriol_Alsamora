<?php

namespace App\Models;

use App\Jobs\SendEmail;
use App\Traits\Models\HasSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\EmailHistory
 *
 * @property int $id
 * @property int $user_id
 * @property string $sender
 * @property string $receiver
 * @property string $subject
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EmailHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailHistory whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailHistory whereReceiver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailHistory whereSender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailHistory whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailHistory whereUserId($value)
 *
 * @mixin \Eloquent
 */
class EmailHistory extends Model
{
    use HasSettings;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'identifier',
        'sender',
        'receiver',
        'subject',
        'content',
        'button',
        'attachment',
        'is_sent',
        'show',
        'has_footer',
        'seen',
    ];

    protected $casts = [
        'button' => 'array',
        'attachment' => 'array',
        'is_sent' => 'boolean',
        'show' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($email) {
            // dispach job to send email
            SendEmail::dispatch($email);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getPendingEmails()
    {
        return self::where('is_sent', false)->get();
    }

    public function resend()
    {
        SendEmail::dispatch($this);
        $this->is_sent = false;
        $this->save();
    }

    public function markAsSent()
    {
        $this->is_sent = true;
        $this->save();
    }

    public function isHidden(): bool
    {
        return !$this->show;
    }
}
