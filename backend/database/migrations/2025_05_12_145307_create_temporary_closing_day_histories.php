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
        Schema::create('temporary_closing_day_histories', function (Blueprint $table) {
            $table->comment('臨時休業日履歴');

            $table->id()->comment('ID');
            $table->unsignedBigInteger('temporary_closing_day_id')->index()->comment('臨時休業日ID');
            $table->unsignedTinyInteger('history_type')->comment('タイプ(1:create,2:update,3:delete)');

            $table->date('date')->comment('日付');
            $table->timestamp('created_at')->nullable()->comment('作成日時');

            DB::unprepared(
                '
            CREATE TRIGGER trigger_temporary_closing_days_create AFTER INSERT ON `temporary_closing_days`
            FOR EACH ROW
                BEGIN
                    INSERT INTO temporary_closing_day_histories
                    set temporary_closing_day_id = NEW.id,
                        history_type            = 1,
                        date                    = NEW.date,
                        created_at              = NEW.created_at;
                END
        '
            );

            DB::unprepared(
                '
            CREATE TRIGGER trigger_temporary_closing_days_update AFTER UPDATE ON `temporary_closing_days`
            FOR EACH ROW
                BEGIN
                    INSERT INTO temporary_closing_day_histories
                    set temporary_closing_day_id = NEW.id,
                        history_type            = 2,
                        date                    = NEW.date,
                        created_at              = NOW();
                END
        '
            );

            DB::unprepared(
                '
            CREATE TRIGGER trigger_temporary_closing_days_delete AFTER DELETE ON `temporary_closing_days`
            FOR EACH ROW
                BEGIN
                    INSERT INTO temporary_closing_day_histories
                    set temporary_closing_day_id = OLD.id,
                        history_type            = 3,
                        date                    = OLD.date,
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
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_temporary_closing_days_create');
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_temporary_closing_days_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_temporary_closing_days_delete');
        Schema::dropIfExists('temporary_closing_day_histories');
    }
};
