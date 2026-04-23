<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['first_name', 'last_name', 'email', 'phone', 'password', 'type', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /** @var array<int, string> */
    public array $auditIgnore = ['updated_at', 'remember_token', 'password'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            '2fa_enabled' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function accessRights(): HasMany
    {
        return $this->hasMany(AccessRight::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class)->latest('created_at');
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->active()->latest('end_date');
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()->active()->exists();
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
