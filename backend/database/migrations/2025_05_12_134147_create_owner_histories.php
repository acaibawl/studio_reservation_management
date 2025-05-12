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
        Schema::create('owner_histories', function (Blueprint $table) {
            $table->comment('オーナー履歴');

            $table->id();
            $table->unsignedBigInteger('owner_id')->index()->comment('オーナーID');
            $table->unsignedTinyInteger('history_type')->comment('タイプ(1:create,2:update,3:delete)');

            $table->string('email', 255)->unique()->comment('メールアドレス');
            $table->string('password', 128)->comment('パスワード');
            $table->timestamp('created_at')->nullable()->comment('作成日時');
        });

        DB::unprepared(
            '
            CREATE TRIGGER trigger_owners_create AFTER INSERT ON `owners`
            FOR EACH ROW
                BEGIN
                    INSERT INTO owner_histories
                    set owner_id              = NEW.id,
                        history_type          = 1,
                        email                 = NEW.email,
                        password              = NEW.password,
                        created_at            = NEW.created_at;
                END
        '
        );

        DB::unprepared(
            '
            CREATE TRIGGER trigger_owners_update AFTER UPDATE ON `owners`
            FOR EACH ROW
                BEGIN
                    INSERT INTO owner_histories
                    set owner_id              = NEW.id,
                        history_type          = 2,
                        email                 = NEW.email,
                        password              = NEW.password,
                        created_at            = NEW.updated_at;
                END
        '
        );

        DB::unprepared(
            '
            CREATE TRIGGER trigger_owners_delete AFTER DELETE ON `owners`
            FOR EACH ROW
                BEGIN
                    INSERT INTO owner_histories
                    set owner_id              = OLD.id,
                        history_type          = 3,
                        email                 = OLD.email,
                        password              = OLD.password,
                        created_at            = NOW();
                END
        '
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_owners_create');
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_owners_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_owners_delete');
        Schema::dropIfExists('owner_histories');
    }
};
