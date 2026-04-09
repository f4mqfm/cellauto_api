<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('words') || Schema::hasColumn('words', 'position')) {
            return;
        }

        Schema::table('words', function (Blueprint $table) {
            $table->unsignedInteger('position')->nullable()->after('word');
        });

        $rows = DB::table('words')->orderBy('list_id')->orderBy('id')->get();
        $perList = [];
        foreach ($rows as $row) {
            $lid = (int) $row->list_id;
            $perList[$lid] = ($perList[$lid] ?? -1) + 1;
            DB::table('words')->where('id', $row->id)->update(['position' => $perList[$lid]]);
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE words MODIFY position INT UNSIGNED NOT NULL');
        } else {
            Schema::table('words', function (Blueprint $table) {
                $table->unsignedInteger('position')->nullable(false)->change();
            });
        }

        Schema::table('words', function (Blueprint $table) {
            $table->unique(['list_id', 'position'], 'words_list_id_position_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('words') || !Schema::hasColumn('words', 'position')) {
            return;
        }

        Schema::table('words', function (Blueprint $table) {
            $table->dropUnique('words_list_id_position_unique');
        });

        Schema::table('words', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
