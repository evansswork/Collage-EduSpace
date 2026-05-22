<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaultFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id', 'uploaded_by', 'title', 'file_name', 'file_path',
        'mime_type', 'file_size', 'week', 'topic', 'ai_categorized'
    ];

    protected $casts = ['ai_categorized' => 'boolean'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if (!$bytes) return '–';
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < 3; $i++) $bytes /= 1024;
        return round($bytes, 1) . ' ' . $units[$i];
    }
}
