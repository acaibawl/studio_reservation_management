<?php

namespace App\Console\Commands;

use App\Models\Owner;
use Illuminate\Console\Command;
use Illuminate\Validation\Rule;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Validator;

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
    public function handle(): int
    {
        $validator = Validator::make($this->arguments(), [
            'email' => ['required', 'email', 'max:255', Rule::unique('owners', 'email')],
            'password' => ['required', 'string', 'between:8,32'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return CommandAlias::FAILURE;
        }

        $email = $this->argument('email');
        $password = $this->argument('password');
        // オーナーの作成処理
        Owner::create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $this->info("オーナーが作成されました: {$email}");
        return CommandAlias::SUCCESS;
    }
}
