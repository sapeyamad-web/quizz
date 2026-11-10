<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_session_id',
        'user_name',
        'status',
        'started_at',
        'submitted_at',
        'last_seen_at',
        'score',
        'tab_switch_count',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function examSession(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function getRemainingSecondsAttribute(): int
    {
        if (!$this->started_at) {
            return $this->examSession->duration_minutes * 60;
        }

        $endedAt = $this->submitted_at ?? now();
        $elapsed = $this->started_at->diffInSeconds($endedAt, false);
        $total = $this->examSession->duration_minutes * 60;

        return max(0, $total - $elapsed);
    }
}
