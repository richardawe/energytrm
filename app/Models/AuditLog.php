<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $fillable = [
        'auditable_type', 'auditable_id',
        'user_id', 'action',
        'old_values', 'new_values', 'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Fields that are noisy / not worth logging
    public static array $ignoredFields = [
        'updated_at', 'created_at', 'remember_token',
    ];

    public static function record(
        Model $model,
        string $action,
        array $oldValues = [],
        array $newValues = []
    ): void {
        static::create([
            'auditable_type' => get_class($model),
            'auditable_id'   => $model->getKey(),
            'user_id'        => auth()->id(),
            'action'         => $action,
            'old_values'     => $oldValues ?: null,
            'new_values'     => $newValues ?: null,
            'ip_address'     => request()->ip(),
        ]);
    }

    public function actionBadgeClass(): string
    {
        return match ($this->action) {
            'created'   => 'bg-success',
            'updated'   => 'bg-primary',
            'validated' => 'bg-info text-dark',
            'reverted'  => 'bg-warning text-dark',
            'deleted'   => 'bg-danger',
            default     => 'bg-secondary',
        };
    }
}
