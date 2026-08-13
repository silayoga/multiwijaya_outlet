<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneOtp extends Model
{
    // This table only has created_at (no updated_at) — see migration.
    public $timestamps = false;

    protected $fillable = ['phone_number', 'code_hash', 'expires_at', 'attempts'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function hasExceededMaxAttempts(): bool
    {
        return $this->attempts >= 5;
    }
}
