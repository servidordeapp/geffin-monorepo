<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['event_type', 'user_id', 'email_hash', 'ip_address', 'user_agent', 'outcome', 'reason'])]
class PasswordResetAuditEvent extends Model
{
    public const UPDATED_AT = null;

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
