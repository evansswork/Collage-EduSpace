<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'title', 'instructions', 'due_at', 'type', 'max_score'];
    protected $casts = ['due_at' => 'datetime'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function forumPosts(): HasMany
    {
        return $this->hasMany(ForumPost::class)->whereNull('parent_id')->orderByDesc('is_pinned');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    // === URGENCY HELPERS ===
    public function isUrgent(): bool
    {
        return $this->due_at->isFuture() && $this->due_at->diffInHours(now()) <= 24;
    }

    public function urgencyLevel(): string
    {
        if ($this->due_at->isPast()) return 'overdue';
        $hours = now()->diffInHours($this->due_at, false);
        if ($hours <= 24) return 'urgent';   // merah
        if ($hours <= 72) return 'soon';     // kuning
        return 'normal';                      // hijau/normal
    }

    public function submissionFor(?User $user): ?Submission
    {
        if (!$user) return null;
        return $this->submissions()->where('user_id', $user->id)->first();
    }
}
