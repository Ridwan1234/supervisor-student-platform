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
        Schema::table('file_comments', function (Blueprint $table) {
            // Rename comment column to content
            $table->renameColumn('comment', 'content');
            
            // Add new fields
            $table->boolean('is_resolved')->default(false)->after('content');
            $table->timestamp('resolved_at')->nullable()->after('is_resolved');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null')->after('resolved_at');
            
            // Drop old position fields
            $table->dropColumn(['line_number', 'position_x', 'position_y']);
            
            // Add indexes
            $table->index(['file_id', 'parent_id']);
            $table->index(['is_resolved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_comments', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['file_id', 'parent_id']);
            $table->dropIndex(['is_resolved']);
            
            // Add back old position fields
            $table->integer('line_number')->nullable();
            $table->integer('position_x')->nullable();
            $table->integer('position_y')->nullable();
            
            // Remove new fields
            $table->dropForeign(['resolved_by']);
            $table->dropColumn(['resolved_by', 'resolved_at', 'is_resolved']);
            
            // Rename content back to comment
            $table->renameColumn('content', 'comment');
        });
    }
};
