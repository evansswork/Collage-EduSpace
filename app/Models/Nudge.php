<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nudge extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'from_user_id', 'to_user_id', 'type', 'seen'];
    protected $casts = ['seen' => 'boolean'];

    public function group(): BelongsTo { return $this->belongsTo(Group::class); }
    public function from(): BelongsTo { return $this->belongsTo(User::class, 'from_user_id'); }
    public function to(): BelongsTo { return $this->belongsTo(User::class, 'to_user_id'); }
}
