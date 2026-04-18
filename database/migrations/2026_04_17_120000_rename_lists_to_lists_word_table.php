<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function tryDropForeign(string $table, string $foreignKey): void
    {
        try {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$foreignKey}`");
        } catch (Throwable $e) {
            // Constraint may not exist with this name in all environments.
        }
    }

    public function up(): void
    {
        $this->tryDropForeign('words', 'words_list_id_foreign');
        $this->tryDropForeign('words', 'fk_words_list');
        $this->tryDropForeign('word_relations', 'word_relations_list_id_foreign');
        $this->tryDropForeign('task_saves', 'task_saves_word_list_id_foreign');

        if (Schema::hasTable('lists') && ! Schema::hasTable('lists_word')) {
            Schema::rename('lists', 'lists_word');
        }

        if (Schema::hasTable('words')) {
            Schema::table('words', function (Blueprint $table) {
                $table->foreign('list_id', 'fk_words_list')
                    ->references('id')
                    ->on('lists_word');
            });
        }

        if (Schema::hasTable('word_relations')) {
            Schema::table('word_relations', function (Blueprint $table) {
                $table->foreign('list_id')
                    ->references('id')
                    ->on('lists_word')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('task_saves')) {
            Schema::table('task_saves', function (Blueprint $table) {
                $table->foreign('word_list_id')
                    ->references('id')
                    ->on('lists_word')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $this->tryDropForeign('words', 'words_list_id_foreign');
        $this->tryDropForeign('words', 'fk_words_list');
        $this->tryDropForeign('word_relations', 'word_relations_list_id_foreign');
        $this->tryDropForeign('task_saves', 'task_saves_word_list_id_foreign');

        if (Schema::hasTable('words')) {
            Schema::table('words', function (Blueprint $table) {
                $table->foreign('list_id', 'fk_words_list')
                    ->references('id')
                    ->on('lists');
            });
        }

        if (Schema::hasTable('word_relations')) {
            Schema::table('word_relations', function (Blueprint $table) {
                $table->foreign('list_id')
                    ->references('id')
                    ->on('lists')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('task_saves')) {
            Schema::table('task_saves', function (Blueprint $table) {
                $table->foreign('word_list_id')
                    ->references('id')
                    ->on('lists')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('lists_word') && ! Schema::hasTable('lists')) {
            Schema::rename('lists_word', 'lists');
        }
    }
};
