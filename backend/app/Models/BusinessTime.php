<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id ID
 * @property \Illuminate\Support\Carbon $open_time 営業開始時間
 * @property \Illuminate\Support\Carbon $close_time 営業終了時間
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read bool $is_cross_date_operation
 * @method static \Database\Factories\BusinessTimeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTime query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTime whereCloseTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTime whereOpenTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTime whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BusinessTime extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessTimeFactory> */
    use HasFactory;

    protected $fillable = [
        'open_time',
        'close_time',
    ];

    protected $casts = [
        'open_time' => 'datetime:H:i',
        'close_time' => 'datetime:H:i',
    ];

    public function getIsCrossDateOperationAttribute(): bool
    {
        return $this->close_time->lessThanOrEqualTo($this->open_time);
    }
}
