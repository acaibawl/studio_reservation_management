<?php

declare(strict_types=1);

namespace Database\Seeders\Prod;

use App\Models\Member;
use Hash;
use Illuminate\Database\Seeder;
use Str;

class MemberSeeder extends Seeder
{
    const int OWNER_MEMBER_ID = 9999999;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Member::where('id', self::OWNER_MEMBER_ID)->exists()) {
            return;
        }
        // オーナー画面で予約作成時にダミーで入るユーザーなので、ログインはできないようにパスワードを不明とする
        Member::factory()->create([
            'id' => self::OWNER_MEMBER_ID,
            'name' => 'owner',
            'email' => 'owner@example.com',
            'address' => 'オーナー住所',
            'tel' => '00000000000',
            'password' => Hash::make(Str::random(16)),
        ]);
    }
}
