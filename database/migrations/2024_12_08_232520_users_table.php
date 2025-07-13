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
        // Update the 'role' column to allow 'admin' as a value
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'supervisor', 'student'])->default('student')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the 'role' column to only allow 'supervisor' and 'student'
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['supervisor', 'student'])->default('student')->change();
        });
    }
};
