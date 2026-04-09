<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('colors')) {
            return;
        }

        Schema::table('colors', function (Blueprint $table) {
            if (!Schema::hasColumn('colors', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('colors', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('colors')) {
            return;
        }

        Schema::table('colors', function (Blueprint $table) {
            if (Schema::hasColumn('colors', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn('colors', 'created_at')) {
                $table->dropColumn('created_at');
            }
        });
    }
};
