<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\Priority;
use App\Enums\RolEnum;
use App\Enums\Status;
use App\Traits\HasTimeWindow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasTimeWindow;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'rol_id',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }
    //Relaciones
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class);
    }
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
    public function performedActions(): HasMany
    {
        return $this->hasMany(Audit::class);
    }
    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class,'auditable');
    } 

    //Helper Method
    public function hasRole(RolEnum $role): bool
    {
        return $this->rol()->where('name', $role)->exists();
        // return $this->rol?->name === $role;
    }
}
