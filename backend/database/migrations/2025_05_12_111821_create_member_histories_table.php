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
        Schema::create('member_histories', function (Blueprint $table) {
            $table->comment('会員履歴');

            $table->id()->comment('ID');
            $table->unsignedBigInteger('member_id')->index()->comment('会員ID'); // unsignedBiginteger、PK
            $table->unsignedTinyInteger('history_type')->comment('タイプ(1:create,2:update,3:delete)');

            $table->string('name', 50)->comment('名前');
            $table->string('email', 255)->comment('メールアドレス');
            $table->string('address', 128)->comment('住所');
            $table->string('tel', 11)->comment('電話番号');
            $table->string('password', 128)->comment('パスワード'); // 、bcryptでハッシュ化された値
            $table->timestamp('created_at')->nullable()->comment('作成日時');

            DB::unprepared(
                '
            CREATE TRIGGER trigger_members_create AFTER INSERT ON `members`
            FOR EACH ROW
                BEGIN
                    INSERT INTO member_histories
                    set member_id             = NEW.id,
                        history_type          = 1,
                        name                  = NEW.name,
                        email                 = NEW.email,
                        address               = NEW.address,
                        tel                   = NEW.tel,
                        password              = NEW.password,
                        created_at            = NEW.created_at;
                END
        '
            );

            DB::unprepared(
                '
            CREATE TRIGGER trigger_members_update AFTER UPDATE ON `members`
            FOR EACH ROW
                BEGIN
                    INSERT INTO member_histories
                    set member_id             = NEW.id,
                        history_type          = 2,
                        name                  = NEW.name,
                        email                 = NEW.email,
                        address               = NEW.address,
                        tel                   = NEW.tel,
                        password              = NEW.password,
                        created_at            = NEW.updated_at;
                END
        '
            );

            DB::unprepared(
                '
            CREATE TRIGGER trigger_members_delete AFTER DELETE ON `members`
            FOR EACH ROW
                BEGIN
                    INSERT INTO member_histories
                    set member_id             = OLD.id,
                        history_type          = 3,
                        name                  = OLD.name,
                        email                 = OLD.email,
                        address               = OLD.address,
                        tel                   = OLD.tel,
                        password              = OLD.password,
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
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_members_create');
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_members_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_members_delete');
        Schema::dropIfExists('member_histories');
    }
};
