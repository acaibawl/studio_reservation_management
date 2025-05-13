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
        Schema::create('studio_histories', function (Blueprint $table) {
            $table->comment('スタジオ履歴');

            $table->id()->comment('ID');
            $table->unsignedBigInteger('studio_id')->index()->comment('スタジオID');
            $table->unsignedTinyInteger('history_type')->comment('タイプ(1:create,2:update,3:delete)');

            $table->string('name', 50)->comment('名前');
            $table->tinyInteger('start_at')->comment('開始時間');
            $table->timestamp('created_at')->nullable()->comment('作成日時');

            DB::unprepared(
                '
            CREATE TRIGGER trigger_studios_create AFTER INSERT ON `studios`
            FOR EACH ROW
                BEGIN
                    INSERT INTO studio_histories
                    set studio_id             = NEW.id,
                        history_type          = 1,
                        name                  = NEW.name,
                        start_at              = NEW.start_at,
                        created_at            = NEW.created_at;
                END
        '
            );

            DB::unprepared(
                '
            CREATE TRIGGER trigger_studios_update AFTER UPDATE ON `studios`
            FOR EACH ROW
                BEGIN
                    INSERT INTO studio_histories
                    set studio_id             = NEW.id,
                        history_type          = 2,
                        name                  = NEW.name,
                        start_at              = NEW.start_at,
                        created_at            = NEW.updated_at;
                END
        '
            );

            DB::unprepared(
                '
            CREATE TRIGGER trigger_studios_delete AFTER DELETE ON `studios`
            FOR EACH ROW
                BEGIN
                    INSERT INTO studio_histories
                    set studio_id             = OLD.id,
                        history_type          = 3,
                        name                  = OLD.name,
                        start_at              = OLD.start_at,
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
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_studios_create');
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_studios_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_studios_delete');
        Schema::dropIfExists('studio_histories');
    }
};
