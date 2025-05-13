<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $member_id 会員ID
 * @property int $studio_id スタジオID
 * @property Carbon $start_at 開始日時
 * @property Carbon $finish_at 終了日時
 * @property string|null $memo メモ
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Member $member
 * @property-read \App\Models\Studio $studio
 * @method static \Database\Factories\ReservationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereFinishAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereMemo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereStudioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reservation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'member_id',
        'studio_id',
        'start_at',
        'finish_at',
        'memo',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'finish_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @return BelongsTo<Studio, $this>
     */
    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }
}
