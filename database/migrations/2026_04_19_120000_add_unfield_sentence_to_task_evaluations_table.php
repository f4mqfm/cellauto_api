<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_evaluations', function (Blueprint $table) {
            $table->unsignedInteger('duplicate_sentence')->default(0)->after('bad_sentence');
        });
    }

    public function down(): void
    {
        Schema::table('task_evaluations', function (Blueprint $table) {
            $table->dropColumn('duplicate_sentence');
        });
    }
};
