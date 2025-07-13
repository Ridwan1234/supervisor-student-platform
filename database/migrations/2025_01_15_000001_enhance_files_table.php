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
        Schema::table('files', function (Blueprint $table) {
            $table->integer('version')->default(1)->after('mime_type');
            $table->unsignedBigInteger('parent_id')->nullable()->after('version');
            $table->boolean('is_public')->default(false)->after('parent_id');
            $table->integer('download_count')->default(0)->after('is_public');
            $table->integer('view_count')->default(0)->after('download_count');
            $table->json('tags')->nullable()->after('view_count');
            
            // Add foreign key for parent_id
            $table->foreign('parent_id')->references('id')->on('files')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['version', 'parent_id', 'is_public', 'download_count', 'view_count', 'tags']);
        });
    }
}; 