<?php

namespace App\Models;

use App\Enums\Type;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Label extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];
    protected $casts = [
        'name' => Type::class,
    ];
    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class);
    }
}
