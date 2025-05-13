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
        Schema::create('business_time_histories', function (Blueprint $table) {
            $table->comment('営業時間履歴');

            $table->id()->comment('ID');
            $table->unsignedBigInteger('business_time_id')->index()->comment('営業時間ID');
            $table->unsignedTinyInteger('history_type')->comment('タイプ(1:create,2:update,3:delete)');

            $table->time('open_time')->comment('営業開始時間');
            $table->time('close_time')->comment('営業終了時間');
            $table->timestamp('created_at')->nullable()->comment('作成日時');

            DB::unprepared(
                '
            CREATE TRIGGER trigger_business_times_create AFTER INSERT ON `business_times`
            FOR EACH ROW
                BEGIN
                    INSERT INTO business_time_histories
                    set business_time_id = NEW.id,
                        history_type            = 1,
                        open_time               = NEW.open_time,
                        close_time              = NEW.close_time,
                        created_at              = NEW.created_at;
                END
        '
            );

            DB::unprepared(
                '
            CREATE TRIGGER trigger_business_times_update AFTER UPDATE ON `business_times`
            FOR EACH ROW
                BEGIN
                    INSERT INTO business_time_histories
                    set business_time_id = NEW.id,
                        history_type            = 2,
                        open_time               = NEW.open_time,
                        close_time              = NEW.close_time,
                        created_at              = NOW();
                END
        '
            );

            DB::unprepared(
                '
            CREATE TRIGGER trigger_business_times_delete AFTER DELETE ON `business_times`
            FOR EACH ROW
                BEGIN
                    INSERT INTO business_time_histories
                    set business_time_id = OLD.id,
                        history_type            = 3,
                        open_time               = OLD.open_time,
                        close_time              = OLD.close_time,
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
        Schema::dropIfExists('business_time_histories');
    }
};
