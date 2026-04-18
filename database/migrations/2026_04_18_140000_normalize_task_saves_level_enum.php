<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'] as $from => $to) {
            DB::table('task_saves')->where('level', $from)->update(['level' => $to]);
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE task_saves MODIFY COLUMN level ENUM('Easy','Medium','Hard') NOT NULL DEFAULT 'Medium'"
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE task_saves MODIFY COLUMN level VARCHAR(20) NOT NULL DEFAULT \'Medium\''
            );
        }

        DB::table('task_saves')->where('level', 'Easy')->update(['level' => 'easy']);
        DB::table('task_saves')->where('level', 'Medium')->update(['level' => 'medium']);
        DB::table('task_saves')->where('level', 'Hard')->update(['level' => 'hard']);
    }
};
