<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('task_evaluations', 'unfield_sentence')) {
            Schema::table('task_evaluations', function (Blueprint $table) {
                $table->renameColumn('unfield_sentence', 'duplicate_sentence');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('task_evaluations', 'duplicate_sentence')
            && ! Schema::hasColumn('task_evaluations', 'unfield_sentence')) {
            Schema::table('task_evaluations', function (Blueprint $table) {
                $table->renameColumn('duplicate_sentence', 'unfield_sentence');
            });
        }
    }
};
