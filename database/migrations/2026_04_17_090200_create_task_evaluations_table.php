<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_save_id')->constrained('task_saves')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('date');
            $table->text('note')->nullable();
            $table->unsignedInteger('total_good_cell')->default(0);
            $table->unsignedInteger('good_cell')->default(0);
            $table->unsignedInteger('bad_cell')->default(0);
            $table->unsignedInteger('possible_sentence')->default(0);
            $table->unsignedInteger('good_sentence')->default(0);
            $table->unsignedInteger('bad_sentence')->default(0);
            $table->unsignedInteger('completed_time')->default(0);
            $table->timestamps();

            $table->index(['task_save_id', 'user_id']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_evaluations');
    }
};
