<?php

namespace App\Models;

use App\Enums\RolEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    /** @use HasFactory<\Database\Factories\RolFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
    ];
    protected $casts = [
        'name' => RolEnum::class,
    ];
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
