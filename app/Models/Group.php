<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['assignment_id', 'name'];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(GroupTask::class)->orderBy('order');
    }

    public function files(): HasMany
    {
        return $this->hasMany(GroupFile::class)->latest();
    }

    public function nudges(): HasMany
    {
        return $this->hasMany(Nudge::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(GroupMessage::class)->latest();
    }

    public function leader(): ?User
    {
        $member = $this->members()->where('role', 'leader')->first();
        return $member?->user;
    }

    public function hasLeader(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->where('role', 'leader')->exists();
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    // === TASK-BASED PROGRESS (bukan self-report!) ===
    public function progressForMember(User $user): int
    {
        $tasks = $this->tasks()->where('assigned_to', $user->id)->get();
        if ($tasks->isEmpty()) return 0;
        $done = $tasks->where('is_completed', true)->count();
        return (int) round(($done / $tasks->count()) * 100);
    }

    public function overallProgress(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;
        $done = $this->tasks()->where('is_completed', true)->count();
        return (int) round(($done / $total) * 100);
    }

    // === ESCALATION TRIGGER (3x nudge dalam seminggu) ===
    public function nudgesForMemberThisWeek(User $member): int
    {
        return $this->nudges()
            ->where('to_user_id', $member->id)
            ->where('type', 'gentle')
            ->where('created_at', '>=', now()->subWeek())
            ->count();
    }

    public function canEscalate(User $member): bool
    {
        return $this->hasMember($member)
            && $this->nudgesForMemberThisWeek($member) >= 3
            && $this->progressForMember($member) === 0;
    }

    public function hasEscalatedFor(User $member): bool
    {
        return $this->nudges()
            ->where('to_user_id', $member->id)
            ->where('type', 'escalation')
            ->exists();
    }
}
