<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'whatsapp',
        'role',
        'ecommerce_user_id',
    ];

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

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function customerOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'customer_id');
    }

    public function saOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'sa_id');
    }

    public function technicianOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'technician_id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'technician_id');
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(WorkLog::class, 'technician_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTechnician(): bool
    {
        return $this->role === 'teknisi';
    }

    public function whatsappNumber(): string
    {
        $number = $this->whatsapp ?: $this->phone ?: '';

        return preg_replace('/\D+/', '', $number);
    }
}
