<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Auth\Models;

use App\Notifications\PasswordResetRequested;
use Database\Factories\TenantUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Tenant-scoped user.
 *
 * Lives in the tenant database (resolved through the default connection once
 * tenancy is initialized). Unlike the central App\Models\User, it has no
 * `is_central_admin` column — the tenant `users` table never defines one — so it
 * must never be cast as the central model.
 *
 * @property-read bool $is_central_admin
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class TenantUser extends Authenticatable
{
    /** @use HasFactory<TenantUserFactory> */
    use HasFactory;
    use Notifiable;

    protected $table = 'users';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Tenant users are never central admins. Exposing this as a constant
     * attribute lets the shared layout/gates that read `is_central_admin` work
     * uniformly across both the central User and the tenant-scoped TenantUser.
     */
    protected function isCentralAdmin(): Attribute
    {
        return Attribute::get(fn (): bool => false);
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new PasswordResetRequested($token));
    }

    protected static function newFactory(): TenantUserFactory
    {
        return TenantUserFactory::new();
    }
}
