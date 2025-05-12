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
        Schema::create('reservation_histories', function (Blueprint $table) {
            $table->comment('予約履歴');

            $table->id();
            $table->unsignedBigInteger('reservation_id')->index()->comment('予約ID');
            $table->unsignedTinyInteger('history_type')->comment('タイプ(1:create,2:update,3:delete)');

            $table->unsignedBigInteger('member_id')->comment('会員ID');
            $table->unsignedBigInteger('studio_id')->comment('スタジオID');
            $table->dateTime('start_at')->comment('開始日時');
            $table->dateTime('finish_at')->comment('終了日時');
            $table->text('memo')->nullable()->comment('メモ');
            $table->timestamp('created_at')->nullable()->comment('作成日時');

            DB::unprepared(
                '
            CREATE TRIGGER trigger_reservations_create AFTER INSERT ON `reservations`
            FOR EACH ROW
                BEGIN
                    INSERT INTO reservation_histories
                    set reservation_id        = NEW.id,
                        history_type          = 1,
                        member_id             = NEW.member_id,
                        studio_id             = NEW.studio_id,
                        start_at              = NEW.start_at,
                        finish_at             = NEW.finish_at,
                        memo                  = NEW.memo,
                        created_at            = NEW.created_at;
                END
        '
            );

            DB::unprepared(
                '
            CREATE TRIGGER trigger_reservations_update AFTER UPDATE ON `reservations`
            FOR EACH ROW
                BEGIN
                    INSERT INTO reservation_histories
                    set reservation_id        = NEW.id,
                        history_type          = 2,
                        member_id             = NEW.member_id,
                        studio_id             = NEW.studio_id,
                        start_at              = NEW.start_at,
                        finish_at             = NEW.finish_at,
                        memo                  = NEW.memo,
                        created_at            = NEW.created_at;
                END
        '
            );

            DB::unprepared(
                '
            CREATE TRIGGER trigger_reservations_delete AFTER DELETE ON `reservations`
            FOR EACH ROW
                BEGIN
                    INSERT INTO reservation_histories
                    set reservation_id        = OLD.id,
                        history_type          = 3,
                        member_id             = OLD.member_id,
                        studio_id             = OLD.studio_id,
                        start_at              = OLD.start_at,
                        finish_at             = OLD.finish_at,
                        memo                  = OLD.memo,
                        created_at            = NOW();
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
        Schema::dropIfExists('reservation_histories');
    }
};
