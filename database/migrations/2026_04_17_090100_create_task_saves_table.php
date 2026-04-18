<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_saves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_save_group_id')->constrained('task_save_groups')->cascadeOnDelete();
            $table->foreignId('word_list_id')->nullable()->constrained('lists_word')->nullOnDelete();
            $table->string('name', 255);
            $table->string('generation_mode', 50); // square_lateral | square_apex | hexagonal
            $table->unsignedInteger('board_size');
            $table->unsignedInteger('generations_count');
            $table->unsignedInteger('time_limit'); // seconds
            $table->json('payload');
            $table->timestamps();

            $table->unique(['task_save_group_id', 'name'], 'task_saves_group_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_saves');
    }
};
