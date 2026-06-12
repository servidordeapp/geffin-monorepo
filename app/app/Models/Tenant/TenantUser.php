<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Notifications\ResetPasswordNotification;
use Database\Factories\Tenant\TenantUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class TenantUser extends Authenticatable
{
    /** @use HasFactory<TenantUserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The table associated with the model (tenant database).
     */
    protected $table = 'users';

    /**
     * Send a password reset notification to the user.
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
