<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // role is intentionally NOT mass-assignable.
    protected $fillable = [
        'name', 'email', 'password',
        'staff_no', 'designation', 'department', 'bank_account',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
