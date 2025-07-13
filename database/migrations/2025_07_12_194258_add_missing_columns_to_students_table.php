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
        Schema::table('students', function (Blueprint $table) {
            // Add user_id column
            $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
            // Add student_id, major, year_level
            $table->string('student_id')->nullable()->after('user_id');
            $table->string('major')->nullable()->after('student_id');
            $table->integer('year_level')->nullable()->after('major');
            // Drop old columns
            $table->dropColumn(['fullname', 'email', 'gender', 'phone_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'student_id', 'major', 'year_level']);
            // Restore old columns
            $table->string('fullname');
            $table->string('email')->unique();
            $table->integer('gender');
            $table->string('phone_number')->unique();
        });
    }
};
