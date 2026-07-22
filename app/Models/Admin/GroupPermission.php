<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Admin\GroupPermission
 *
 * @property int $group_id
 * @property int $permission_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Group $group
 * @property-read Permission $permission
 *
 * @method static \Illuminate\Database\Eloquent\Builder|GroupPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupPermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupPermission whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupPermission wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupPermission whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class GroupPermission extends Model
{
    use HasFactory;

    protected $table = 'group_permission';

    protected $fillable = [
        'group_id', 'permission_id',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}
