<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumPost extends Model
{
    use HasFactory;

    protected $fillable = ['assignment_id', 'user_id', 'parent_id', 'body', 'is_pinned', 'is_lecturer_reply'];
    protected $casts = ['is_pinned' => 'boolean', 'is_lecturer_reply' => 'boolean'];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ForumPost::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ForumPost::class, 'parent_id')->orderBy('created_at');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ForumVote::class);
    }

    public function votedBy(User $user): bool
    {
        return $this->votes()->where('user_id', $user->id)->exists();
    }
}
