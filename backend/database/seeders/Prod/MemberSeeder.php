<?php

declare(strict_types=1);

namespace Database\Seeders\Prod;

use App\Models\Member;
use Hash;
use Illuminate\Database\Seeder;
use Str;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Member::find(9999999) !== null) {
            return;
        }
        // オーナー画面で予約作成時にダミーで入るユーザーなので、ログインはできないようにパスワードを不明とする
        Member::factory()->create([
            'id' => 9999999,
            'name' => 'owner',
            'email' => 'owner@example.com',
            'address' => 'オーナー住所',
            'tel' => '00000000000',
            'password' => Hash::make(Str::random(16)),
        ]);
    }
}
