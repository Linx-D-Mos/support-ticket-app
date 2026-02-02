<?php

namespace App\Models;

use App\Traits\HasTimeWindow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class File extends Model
{
    /** @use HasFactory<\Database\Factories\FileFactory> */
    use HasFactory, HasTimeWindow;
    protected $fillable = [
        'fileable_id',
        'fileable_type',
        'file_path'
    ];
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }
}
