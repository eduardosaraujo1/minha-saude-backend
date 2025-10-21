<?php

namespace App\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Export extends Model
{
    /** @use HasFactory<\Database\Factories\ExportFactory> */
    use HasFactory;

    protected $fillable = [
        'file_path',
        'user_id',
    ];

    protected static function newFactory(): \Database\Factories\ExportFactory
    {
        return \Database\Factories\ExportFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
