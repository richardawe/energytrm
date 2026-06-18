<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EndurQuizAttempt extends Model
{
    protected $fillable = ['user_id', 'module_id', 'score', 'passed', 'answers', 'passed_at'];

    protected $casts = [
        'passed'    => 'boolean',
        'answers'   => 'array',
        'passed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function bestPass(int $userId, string $moduleId): ?self
    {
        return static::where('user_id', $userId)
                     ->where('module_id', $moduleId)
                     ->where('passed', true)
                     ->latest()
                     ->first();
    }
}
