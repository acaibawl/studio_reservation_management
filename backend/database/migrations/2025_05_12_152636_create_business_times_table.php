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
        Schema::create('business_times', function (Blueprint $table) {
            $table->comment('営業時間');

            $table->id()->comment('ID');
            $table->time('open_time')->comment('営業開始時間');
            $table->time('close_time')->comment('営業終了時間');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_times');
    }
};
