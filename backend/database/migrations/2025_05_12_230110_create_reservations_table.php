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
        Schema::create('reservations', function (Blueprint $table) {
            $table->comment('予約');

            $table->id();
            $table->unsignedBigInteger('member_id')->comment('会員ID');
            $table->unsignedBigInteger('studio_id')->comment('スタジオID');
            $table->dateTime('start_at')->comment('開始日時');
            $table->dateTime('finish_at')->comment('終了日時');
            $table->text('memo')->nullable()->comment('メモ');
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('studio_id')->references('id')->on('studios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
