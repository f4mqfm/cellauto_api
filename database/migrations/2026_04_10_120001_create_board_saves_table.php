<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('board_saves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('board_save_group_id')->constrained('board_save_groups')->cascadeOnDelete();
            $table->string('name', 255);
            $table->json('payload');
            $table->timestamps();

            $table->unique(['board_save_group_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_saves');
    }
};
