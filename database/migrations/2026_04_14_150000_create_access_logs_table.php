<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 20); // visit | login | logout
            $table->string('entry_point', 20); // www | admin
            $table->string('ip_address', 45);
            $table->string('user_agent', 1024)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['event_type', 'entry_point']);
            $table->index('occurred_at');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};
