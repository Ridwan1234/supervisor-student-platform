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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade'); // Foreign key for the user who assigned the task
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade'); // Foreign key for the student assigned the task
            $table->unsignedBigInteger('project_id')->nullable(); // Project the task belongs to
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'overdue'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->integer('progress')->default(0); // Progress percentage (0-100)
            $table->foreignId('group_id')->nullable(); // Group
            $table->timestamp('due_date');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
