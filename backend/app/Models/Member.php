<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property int $id ID
 * @property string $name 名前
 * @property string $email メールアドレス
 * @property string $address 住所
 * @property string $tel 電話番号
 * @property string $password パスワード
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Reservation> $reservations
 * @property-read int|null $reservations_count
 * @method static \Database\Factories\MemberFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereTel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Member extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\MemberFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'address',
        'tel',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    /** 終了時間が未来の予約をもつ場合はtrue */
    public function hasReservation(): bool
    {
        return $this->reservations
            ->where('finish_at', '>=', now())
            ->isNotEmpty();
    }

    public function reservations(): HasMany|Reservation
    {
        return $this->hasMany(Reservation::class);
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * @return array<int, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
