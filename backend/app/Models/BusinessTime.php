<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
