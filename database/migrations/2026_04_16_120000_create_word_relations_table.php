<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('lists')->cascadeOnDelete();
            $table->foreignId('from_word_id')->constrained('words')->cascadeOnDelete();
            $table->foreignId('to_word_id')->constrained('words')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['list_id', 'from_word_id', 'to_word_id'], 'word_relations_unique');
            $table->index(['list_id', 'from_word_id']);
            $table->index(['list_id', 'to_word_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_relations');
    }
};

