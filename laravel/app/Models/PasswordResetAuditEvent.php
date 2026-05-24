<?php

namespace App\Models;

use App\Enums\Auth\PasswordResetEventTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['event_type', 'user_id', 'email_hash', 'ip_address', 'user_agent', 'outcome', 'reason'])]
class PasswordResetAuditEvent extends Model
{
    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_type' => PasswordResetEventTypeEnum::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function emailHash(string $email): string
    {
        return hash('sha256', mb_strtolower(trim($email)));
    }
}
