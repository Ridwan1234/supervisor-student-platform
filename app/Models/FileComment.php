<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'user_id',
        'parent_id',
        'content',
        'is_resolved',
        'resolved_at',
        'resolved_by'
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the file that owns the comment.
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the user who created the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (for replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(FileComment::class, 'parent_id');
    }

    /**
     * Get the user who resolved the comment.
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get replies to this comment.
     */
    public function replies()
    {
        return $this->hasMany(FileComment::class, 'parent_id');
    }

    /**
     * Scope to get only top-level comments (not replies).
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get only resolved comments.
     */
    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    /**
     * Scope to get only unresolved comments.
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Check if the comment can be deleted by the given user.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $user->id === $this->user_id || $user->hasRole(['supervisor', 'admin']);
    }

    /**
     * Check if the comment can be edited by the given user.
     */
    public function canBeEditedBy(User $user): bool
    {
        return $user->id === $this->user_id;
    }

    /**
     * Resolve the comment.
     */
    public function resolve(User $user): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $user->id
        ]);
    }

    /**
     * Unresolve the comment.
     */
    public function unresolve(): void
    {
        $this->update([
            'is_resolved' => false,
            'resolved_at' => null,
            'resolved_by' => null
        ]);
    }
} 