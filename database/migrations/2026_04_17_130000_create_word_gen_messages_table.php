<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_gen_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('lists_word')->cascadeOnDelete();
            $table->unsignedInteger('generation');
            $table->text('correct_answer_message')->nullable();
            $table->text('incorrect_answer_message')->nullable();
            $table->timestamps();

            $table->unique(['list_id', 'generation'], 'word_gen_messages_list_generation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_gen_messages');
    }
};
