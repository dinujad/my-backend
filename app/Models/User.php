<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->role)) {
                $user->role = 'customer';
            }
        });
    }

    /** Full store access except staff management. Editor is not included. */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin', 'chat_manager', 'web_manager'], true);
    }

    public function isFullAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    public function isWebManager(): bool
    {
        return $this->role === 'web_manager';
    }

    public function canEditStore(): bool
    {
        return $this->isAdmin();
    }

    /** Add and edit products, including stock. */
    public function canManageProducts(): bool
    {
        return $this->canEditStore() || $this->isEditor();
    }

    /** Read orders, quotes, customers, and reports. */
    public function canViewOperations(): bool
    {
        return $this->canEditStore() || $this->isWebManager();
    }

    public function canAccessAdmin(): bool
    {
        return $this->canManageProducts() || $this->canViewOperations();
    }

    public function canManageStaff(): bool
    {
        return $this->isFullAdmin();
    }

    public function isChatStaff(): bool
    {
        return in_array($this->role, ['admin', 'super_admin', 'chat_manager', 'chat_agent'], true);
    }

    /** @return array<string, string> */
    public static function assignableRoles(): array
    {
        return [
            'admin' => 'Admin',
            'editor' => 'Editor',
            'web_manager' => 'Web Manager',
            'chat_manager' => 'Chat Manager',
            'chat_agent' => 'Chat Agent',
        ];
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function addresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Address::class);
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
