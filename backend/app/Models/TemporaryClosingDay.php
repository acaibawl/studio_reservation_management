<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id ID
 * @property string $date 日付
 * @property \Illuminate\Support\Carbon $created_at
 * @method static \Database\Factories\TemporaryClosingDayFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemporaryClosingDay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemporaryClosingDay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemporaryClosingDay query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemporaryClosingDay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemporaryClosingDay whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemporaryClosingDay whereId($value)
 * @mixin \Eloquent
 */
class TemporaryClosingDay extends Model
{
    /** @use HasFactory<\Database\Factories\TemporaryClosingDayFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'date',
    ];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];
}
