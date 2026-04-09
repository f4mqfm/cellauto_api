<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('color_lists')) {
            return;
        }

        Schema::table('color_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('color_lists', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('color_lists', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('color_lists')) {
            return;
        }

        Schema::table('color_lists', function (Blueprint $table) {
            if (Schema::hasColumn('color_lists', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn('color_lists', 'created_at')) {
                $table->dropColumn('created_at');
            }
        });
    }
};
