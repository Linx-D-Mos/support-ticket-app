<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class File extends Model
{
    /** @use HasFactory<\Database\Factories\FileFactory> */
    use HasFactory;
    protected $fillable = [
        'model_id',
        'model_type',
        'file_path'
    ];
    public function fileables(): MorphTo
    {
        return $this->morphTo();
    }
}
