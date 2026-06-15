<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('parent_days', 'is_active_for_students')) {
            return;
        }

        Schema::table('parent_days', function (Blueprint $table) {
            $table->boolean('is_active_for_students')->default(true);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('parent_days', 'is_active_for_students')) {
            return;
        }

        Schema::table('parent_days', function (Blueprint $table) {
            $table->dropColumn('is_active_for_students');
        });
    }
};
