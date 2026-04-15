<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('words', function (Blueprint $table) {
            $table->dropUnique('words_list_id_position_unique');
            $table->dropColumn('position');
        });
    }

    public function down(): void
    {
        Schema::table('words', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('word');
            $table->unique(['list_id', 'position'], 'words_list_id_position_unique');
        });
    }
};
