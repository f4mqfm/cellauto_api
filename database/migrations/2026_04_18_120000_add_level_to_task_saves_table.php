<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_saves', function (Blueprint $table) {
            $table->string('level', 20)->default('Medium')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('task_saves', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
