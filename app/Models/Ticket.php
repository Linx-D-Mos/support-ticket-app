<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'agent_id',
        'title',
        'status',
        'priority',
        'last_reply_at',
        'resolve_at',
        'close_at',
    ];
    protected $casts = [
        'status' => Status::class,
        'priority' => Priority::class,
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
        return $this->belongsToMany(Label::class)->withTimestamps();
    }
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }
    public function hasStatus(Status $status): bool
    {
        return $this->status === $status;
    }
    public function hasPriority(Priority $priority): bool
    {
        return $this->priority === $priority;
    }

        public function scopeStatus($query, ?string $status)
    {
        if ($status) {
            $query->where('status', $status);
        }
    }
    public function scopePriority($query, ?string $priority)
    {
        if ($priority) {
            $query->where('priority', $priority);
        }
    }
    public function scopeSearch($query, ?string $term)
    {
        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('title', 'ILIKE', "%{$term}%");
            });
        }
    }
}
