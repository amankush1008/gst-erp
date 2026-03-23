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
        'mobile',
        'password',
        'role',
        'business_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function businesses()
    {
        return $this->hasMany(Business::class, 'user_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(\Illuminate\Support\Facades\DB::table('activity_logs'));
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canAccess(string $permission): bool
    {
        if ($this->isAdmin()) return true;

        $permissions = [
            'accountant' => ['invoices', 'payments', 'reports', 'parties'],
            'staff'      => ['invoices', 'products', 'parties'],
            'viewer'     => ['reports'],
        ];

        return in_array($permission, $permissions[$this->role] ?? []);
    }
}
