<?php

namespace App\Modules\Students\Models;

use Database\Factories\GuardianFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Modules\Auth\Notifications\GuardianResetPasswordNotification;
use Laravel\Sanctum\HasApiTokens;

class Guardian extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    protected $table = 'guardians';

    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    protected static function newFactory(): GuardianFactory
    {
        return GuardianFactory::new();
    }
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new GuardianResetPasswordNotification($token));
    }
}
