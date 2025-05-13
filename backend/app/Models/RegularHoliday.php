<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\WeekDay;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property WeekDay $code 定休日コード
 * @property \Illuminate\Support\Carbon $created_at
 * @method static \Database\Factories\RegularHolidayFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegularHoliday newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegularHoliday newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegularHoliday query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegularHoliday whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegularHoliday whereCreatedAt($value)
 * @mixin \Eloquent
 */
class RegularHoliday extends Model
{
    /** @use HasFactory<\Database\Factories\RegularHolidayFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'code',
    ];

    protected $casts = [
        'code' => WeekDay::class,
        'created_at' => 'datetime',
    ];
}
