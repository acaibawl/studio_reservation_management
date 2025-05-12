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
        Schema::create('regular_holiday_histories', function (Blueprint $table) {
            $table->comment('定休日履歴');

            $table->id();
            $table->tinyInteger('code')->comment('定休日コード');
            $table->unsignedTinyInteger('history_type')->comment('タイプ(1:create,2:update,3:delete)');
            $table->timestamp('created_at')->nullable()->comment('作成日時');

            DB::unprepared(
                '
            CREATE TRIGGER trigger_regular_holidays_create AFTER INSERT ON `regular_holidays`
            FOR EACH ROW
                BEGIN
                    INSERT INTO regular_holiday_histories
                    set code                    = NEW.code,
                        history_type            = 1,
                        created_at              = NEW.created_at;
                END
        '
            );

            DB::unprepared(
                '
            CREATE TRIGGER trigger_regular_holidays_delete AFTER DELETE ON `regular_holidays`
            FOR EACH ROW
                BEGIN
                    INSERT INTO regular_holiday_histories
                    set code = OLD.code,
                        history_type            = 3,
                        created_at              = NOW();
                END
        '
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_regular_holidays_create');
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_regular_holidays_delete');
        Schema::dropIfExists('regular_holiday_histories');
    }
};
