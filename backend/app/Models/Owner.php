<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $email メールアドレス
 * @property string $password パスワード
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\OwnerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Owner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Owner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Owner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Owner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Owner whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Owner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Owner wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Owner whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Owner extends Model
{
    /** @use HasFactory<\Database\Factories\OwnerFactory> */
    use HasFactory;
}
