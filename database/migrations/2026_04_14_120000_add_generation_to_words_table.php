<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('words', function (Blueprint $table) {
            $table->unsignedInteger('generation')->default(1)->after('list_id');
        });

        // Legacy unique index name in current DB is `list_id` for (list_id, word).
        Schema::table('words', function (Blueprint $table) {
            $table->dropUnique('list_id');
        });

        Schema::table('words', function (Blueprint $table) {
            $table->unique(['list_id', 'generation', 'word'], 'words_list_generation_word_unique');
        });
    }

    public function down(): void
    {
        Schema::table('words', function (Blueprint $table) {
            $table->dropUnique('words_list_generation_word_unique');
        });

        Schema::table('words', function (Blueprint $table) {
            $table->unique(['list_id', 'word'], 'list_id');
            $table->dropColumn('generation');
        });
    }
};
