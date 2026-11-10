<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'exam_session_id',
        'question_text',
        'question_type',
        'image_path',
        'points',
        'sort_order',
    ];

    public function examSession(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class)->orderBy('sort_order');
    }

    public function isEssay(): bool
    {
        return $this->question_type === 'essay';
    }
}
