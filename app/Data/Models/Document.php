<?php

namespace App\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Document extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'titulo',
        'nome_paciente',
        'nome_medico',
        'tipo_documento',
        'data_documento',
        'is_processing',
        'caminho_arquivo',
        'user_id',
    ];

    protected static function newFactory(): \Database\Factories\DocumentFactory
    {
        return \Database\Factories\DocumentFactory::new();
    }

    /**
     * Get the user that owns the document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shares associated with the document.
     */
    public function shares(): BelongsToMany
    {
        return $this->belongsToMany(Share::class)->withTimestamps();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_processing' => 'boolean',
            'data_documento' => 'date',
        ];
    }
}
