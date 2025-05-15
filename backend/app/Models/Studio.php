<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Studio\StartAt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id ID
 * @property string $name 名前
 * @property StartAt $start_at 開始時間
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Reservation> $reservations
 * @property-read int|null $reservations_count
 * @method static \Database\Factories\StudioFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Studio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Studio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Studio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Studio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Studio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Studio whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Studio whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Studio whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Studio extends Model
{
    /** @use HasFactory<\Database\Factories\StudioFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'start_at',
    ];

    protected $casts = [
        'start_at' => StartAt::class,
    ];

    /**
     * @return HasMany<Reservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * 終了日時が未来の予約を持っている場合trueを返す
     */
    public function hasFutureReservation(): bool
    {
        return $this->reservations()->where('finish_at', '>=', now())->exists();
    }
}
