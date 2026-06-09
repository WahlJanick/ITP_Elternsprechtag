<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excel_import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('filename');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->json('parent_days')->nullable();
            $table->unsignedInteger('teachers_created')->default(0);
            $table->unsignedInteger('teachers_updated')->default(0);
            $table->unsignedInteger('rows_skipped')->default(0);
            $table->unsignedInteger('timeslots_created')->default(0);
            $table->unsignedInteger('timeslots_existing')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excel_import_logs');
    }
};
