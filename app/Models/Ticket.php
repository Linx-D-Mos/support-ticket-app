<?php

namespace App\Models;

use App\Enums\priority;
use App\Enums\status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'agent_id',
        'title',
        'status',
        'priority',
        'last_reply_at',
    ];
    protected $casts = [
        'status' => status::class,
        'priority' => priority::class,
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class);
    }
}
