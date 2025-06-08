<?php

namespace App\Console\Commands;

use App\Models\Owner;
use Illuminate\Console\Command;

class CreateOwnerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-owner-command {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'オーナーを作成します';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        // passwordは8文字以上32文字以下であることを確認
        if (strlen($password) < 8 || strlen($password) > 32) {
            $this->error('パスワードは8文字以上32文字以下である必要があります。');
            return;
        }

        // オーナーの作成処理
        Owner::create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $this->info("オーナーが作成されました: {$email}");
    }
}
