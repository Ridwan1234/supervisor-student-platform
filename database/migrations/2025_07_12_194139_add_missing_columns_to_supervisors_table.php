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
        Schema::table('supervisors', function (Blueprint $table) {
            // Add user_id column
            $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
            
            // Add department column
            $table->string('department')->nullable()->after('user_id');
            
            // Add expertise_areas as JSON
            $table->json('expertise_areas')->nullable()->after('department');
            
            // Drop old columns that are not needed
            $table->dropColumn(['fullname', 'email', 'gender', 'phone_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supervisors', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'department', 'expertise_areas']);
            
            // Restore old columns
            $table->string('fullname');
            $table->string('email')->unique();
            $table->integer('gender');
            $table->string('phone_number')->unique();
        });
    }
};
