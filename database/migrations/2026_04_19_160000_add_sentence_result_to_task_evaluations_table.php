<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_evaluations', function (Blueprint $table) {
            $table->text('sentence_result')->nullable()->after('duplicate_sentence');
        });
    }

    public function down(): void
    {
        Schema::table('task_evaluations', function (Blueprint $table) {
            $table->dropColumn('sentence_result');
        });
    }
};
