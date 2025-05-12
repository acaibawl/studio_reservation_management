<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->comment('会員');

            $table->id()->comment('ID');
            $table->string('name', 50)->comment('名前');
            $table->string('email', 255)->unique()->comment('メールアドレス');
            $table->string('address', 128)->comment('住所');
            $table->string('tel', 11)->comment('電話番号');
            $table->string('password', 128)->comment('パスワード'); // 、bcryptでハッシュ化された値
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
