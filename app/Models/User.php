<?php

namespace App\Models;

use App\Enums\Gender;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'gender',
        'avatar',
        'address',
        'city',
        'is_active',
        'clinic_id',
        'pending_patient_data',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'date_of_birth'     => 'date',
            'gender'            => Gender::class,
            'is_active'         => 'boolean',
            'pending_patient_data' => 'array',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && (
            $this->hasRole(['super_admin', 'admin', 'receptionist', 'dentist'])
        );
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function dentist(): HasOne
    {
        return $this->hasOne(Dentist::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    public function getRoleLabelAttribute(): ?string
    {
        $role = $this->getRoleNames()->first();

        return $role ? ucwords(str_replace('_', ' ', $role)) : null;
    }

    public function getRoleAndNameLabelAttribute(): string
    {
        return $this->role_label ? "{$this->role_label} - {$this->name}" : $this->name;
    }
}
