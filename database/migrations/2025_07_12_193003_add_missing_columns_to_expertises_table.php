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
        Schema::table('expertises', function (Blueprint $table) {
            // Rename expertise_area to name for consistency
            $table->renameColumn('expertise_area', 'name');
            
            // Add missing columns
            $table->text('description')->nullable()->after('name');
            $table->integer('proficiency_level')->default(50)->after('description');
            $table->integer('years_experience')->default(0)->after('proficiency_level');
            $table->string('icon')->default('bi bi-award')->after('years_experience');
            $table->json('tags')->nullable()->after('icon');
            $table->integer('projects_completed')->default(0)->after('tags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expertises', function (Blueprint $table) {
            $table->renameColumn('name', 'expertise_area');
            $table->dropColumn(['description', 'proficiency_level', 'years_experience', 'icon', 'tags', 'projects_completed']);
        });
    }
};
