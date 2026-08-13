<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// google_id and password_set_at are deliberately not fillable — they're only
// ever set via direct property assignment in trusted server-side code
// (GoogleAuthController, CreateNewUser, SetPasswordController), never from
// request input.
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
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
            'password_set_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * True when this account was created via Google and the user has never
     * chosen their own password — the current password hash is an unusable
     * random placeholder, so proving email access alone must not be enough
     * to set a working password via /forgot-password.
     */
    public function isGoogleOnly(): bool
    {
        return ! is_null($this->google_id) && is_null($this->password_set_at);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
