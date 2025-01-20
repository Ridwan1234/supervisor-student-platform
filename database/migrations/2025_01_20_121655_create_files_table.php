<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path');
            $table->string('type'); // e.g., "pdf", "docx", "png"
            $table->unsignedBigInteger('size'); // File size in bytes
            $table->unsignedBigInteger('uploaded_by'); // User who uploaded the file
            $table->unsignedBigInteger('project_id')->nullable(); // Associated project/task
            $table->unsignedBigInteger('task_id')->nullable(); // Associated task
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
