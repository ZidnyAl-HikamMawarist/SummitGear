<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'role', 'pin', 'two_factor_secret', 'two_factor_confirmed_at', 'two_factor_recovery_codes'])]
#[Hidden(['password', 'remember_token', 'pin', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
        ];
    }

    /**
     * Memeriksa apakah autentikasi dua faktor (Google Authenticator) aktif
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !empty($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }
}
